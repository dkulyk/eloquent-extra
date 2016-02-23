<?php

namespace DKulyk\Eloquent\Properties;

use DKulyk\Eloquent\Properties;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use DKulyk\Eloquent\Properties\Contracts\Value as ValueContract;

final class Factory
{
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_DATETIME = 'datetime';
    const TYPE_DATE = 'date';
    const TYPE_REFERENCE = 'reference';

    /**
     * @var Collection
     */
    public static $allProperties;

    /**
     * @var Collection|Property[]
     */
    protected static $allPropertiesById;

    /**
     * @var array
     */
    protected static $types = [];

    /**
     * @param Eloquent|string $entity
     *
     * @return Collection|Property[]
     */
    public static function getPropertiesByEntity($entity)
    {
        if (self::$allProperties === null) {
            $properties = Property::all();
            self::$allPropertiesById = $properties->keyBy('id');
            self::$allProperties = $properties->groupBy('entity')->map(
                function (Collection $properties) {
                    return $properties->keyBy('name');
                }
            );
        }
        $entity = ($entity instanceof Eloquent ? $entity : new $entity());
        $entity = $entity->getMorphClass();

        $properties = self::$allProperties->get($entity);

        return $properties ?: $properties[$entity] = new Collection();
    }

    /**
     * Get property by ID.
     *
     * @param $id
     *
     * @return Property
     */
    public static function getPropertyById($id)
    {
        return self::$allPropertiesById->get($id);
    }

    /**
     * @param string          $type
     * @param Eloquent|string $class
     *
     * @throws InvalidArgumentException
     */
    public static function registerType($type, $class)
    {
        if (is_string($class) && class_exists($class)) {
            $class = new $class();
        }
        if (!$class instanceof Value) {
            throw new InvalidArgumentException('Type value must be Value instance or class name');
        }

        self::$types[$type] = $class;
    }

    /**
     * @param $type
     *
     * @return Value
     */
    public static function getType($type)
    {
        return Arr::get(self::$types, $type);
    }

    /**
     * @param Eloquent|string $entity
     * @param string          $name
     * @param string          $type
     * @param bool            $multiple
     * @param Eloquent|string $reference
     *
     * @return Property
     */
    public static function addProperty($entity, $name, $type = self::TYPE_STRING, $multiple = false, $reference = null)
    {
        $entity = $entity instanceof Eloquent ? $entity : new $entity();
        $property = new Property(
            [
                'entity'   => $entity->getMorphClass(),
                'name'     => $name,
                'type'     => $type,
                'multiple' => $multiple,
            ]
        );
        if ($type === self::TYPE_REFERENCE) {
            $reference = $reference instanceof Eloquent ? $reference : new $reference();
            $property->reference = $reference->getMorphClass();
        }
        $property->save();
        self::getPropertiesByEntity($property->entity)->put($name, $entity);

        return $property;
    }

    /**
     * @var Collection|Property[]
     */
    protected $properties;

    /**
     * @var Eloquent
     */
    protected $entity;

    /**
     * Entity values.
     *
     * @var Collection
     */
    protected $values;

    /**
     * @var string[]
     */
    protected $loaded = [];

    /**
     * @var Property[]
     */
    protected $queue = [];

    /**
     * Factory constructor.
     *
     * @param Eloquent $entity
     */
    public function __construct(Eloquent $entity)
    {
        $this->entity = $entity;
        $this->values = new ValueCollection($this);
        $this->properties = self::getPropertiesByEntity($entity);
    }

    /**
     * Get factory properties.
     *
     * @return Collection|Property[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Determine if an property exists by key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        if ($key === $this->entity->getKeyName()) {
            return false;
        }

        return $this->properties->has($key);
    }

    /**
     * Set Values for entity.
     *
     * @param Collection      $properties
     * @param Collection|null $values
     */
    public function setPropertyValues(Collection $properties, Collection $values = null)
    {
        if ($values !== null) {
            $values->each(
                function (Value $v) use ($values) {
                    $property = $v->property;
                    if ($property->multiple) {
                        /* @var ValueCollection $vs */
                        $vs = $this->values->get($property->name, null);
                        if ($vs === null) {
                            $this->values->put($property->name, new ValueCollection($this, $property, [$v]));
                        } else {
                            $vs->push($v);
                        }
                    } else {
                        $this->values->put($property->name, $v);
                        $this->entity->append($property->name);
                    }
                }
            );
        }
        $properties->each(
            function (Property $property, $name) {
                $this->loaded !== true && ($this->loaded[$name] = $name);
                $this->updateValue($name);
            }
        );
    }

    /**
     * Get all property values.
     *
     * @param string|bool $need
     *
     * @return Collection
     */
    public function getPropertyValues($need = false)
    {
        //load another values if don`t exist
        if ($this->loaded !== true && $need !== false && !in_array($need, $this->loaded, true)) {
            $properties = $this->properties->filter(
                function ($property, $name) {
                    return $this->loaded !== true && !in_array($name, $this->loaded, true);
                }
            );

            $this->loaded = true;
            if ($properties->count()) {
                $this->entity->load(
                    ['fields' => function (Properties\Relations\Values $relation) use ($properties) {
                        $relation->setProperties($properties);
                    }]
                );
            }
        }

        return $this->values;
    }

    /**
     * Get property Value(s) by name.
     *
     * @param string $key
     *
     * @return Value§
     */
    public function getPropertyValue($key)
    {
        /* @var Property $property */
        $values = $this->getPropertyValues($key);
        $value = $values->get($key);
        $property = $this->getProperties()->get($key);
        if ($property !== null && $value === null && $property->multiple) {
            $values->put($property->name, $value = new ValueCollection($this, $property));
            $this->updateValue($property->name);
        }

        return $value;
    }

    /**
     * Set value to relation.
     *
     * @param string $key
     */
    public function updateValue($key)
    {
        $value = $this->getPropertyValue($key);
        $this->entity->setRelation($key, $value instanceof ValueContract ? $value->getValue() : null);
    }

    /**
     * Set value.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @throws \InvalidArgumentException
     */
    public function setValue($key, $value)
    {
        $v = $this->getPropertyValue($key);
        if ($v instanceof ValueCollection) {
            $v->setValue($value);
        } else {
            $this->values->put($key, $value);
            $this->updateValue($key);
        }
    }

    /**
     * Add property for delete values from entity.
     *
     * @param Value $value
     */
    public function queuedDelete(Value $value)
    {
        if ($value->exists) {
            $this->queue[$value->id] = $value;
        }
    }

    /**
     * Save entity values.
     *
     * @throws \Exception
     */
    public function save()
    {
        $instance = new Value();
        $connection = $this->entity->getConnection();
        $connection->beginTransaction();
        try {
            if (count($this->queue) > 0) {
                $connection->table($instance->getTable())
                    ->whereIn('id', array_keys($this->queue))
                    ->delete();
            }

            foreach ($this->getPropertyValues(true) as $value) {
                /* @var Value|Collection $value */
                if ($value instanceof Collection) {
                    $value->each(
                        function (Value $value) {
                            $value->setAttribute('entity_id', $this->entity->getKey());
                            $value->save();
                        }
                    );
                } else {
                    $value->setAttribute('entity_id', $this->entity->getKey());
                    $value->save();
                }
            }
            $this->queue = [];
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
        $connection->commit();
    }

    /**
     * Delete entity values.
     *
     * @throws \InvalidArgumentException
     */
    public function delete()
    {
        if (!in_array(SoftDeletes::class, class_uses_recursive(get_class($this->entity)), true) || $this->entity->forceDeleting) {
            $instance = new Value();
            $connection = $this->entity->getConnection();
            $connection->table($instance->getTable())
                ->where('entity_id', $this->entity->getKey())
                ->whereIn('property_id', $this->properties->pluck('id'))
                ->delete();
        }
    }
}
