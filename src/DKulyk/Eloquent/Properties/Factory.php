<?php

namespace DKulyk\Eloquent\Properties;

use DKulyk\Eloquent\Properties;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;

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
        if (Factory::$allProperties === null) {
            $properties = Property::all();
            Factory::$allPropertiesById = $properties->keyBy('id');
            Factory::$allProperties = $properties->groupBy('entity')->map(
                function (Collection $properties) {
                    return $properties->keyBy('name');
                }
            );
            //$properties->load('values');
        }
        $entity = ($entity instanceof Eloquent ? $entity : new $entity);
        $entity = $entity->getMorphClass();

        $properties = Factory::$allProperties->get($entity);

        return $properties ?: $properties[$entity] = new Collection();
    }

    /**
     * Get property by ID
     *
     * @param $id
     *
     * @return Property
     */
    public static function getPropertyById($id)
    {
        return Factory::$allPropertiesById->get($id);
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
            $class = new $class;
        }
        if (!$class instanceof Value) {
            throw new InvalidArgumentException('Type value must be Value instance or class name');
        }

        Factory::$types[$type] = $class;
    }

    /**
     * @param $type
     *
     * @return Value
     */
    public static function getType($type)
    {
        return Arr::get(Factory::$types, $type);
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
    public static function addProperty($entity, $name, $type = Factory::TYPE_STRING, $multiple = false, $reference = null)
    {
        $entity = $entity instanceof Eloquent ? $entity : new $entity;
        $property = new Property(
            [
                'entity'   => $entity->getMorphClass(),
                'name'     => $name,
                'type'     => $type,
                'multiple' => $multiple,
            ]
        );
        if ($type === Factory::TYPE_REFERENCE) {
            $reference = $reference instanceof Eloquent ? $reference : new $reference;
            $property->reference = $reference->getMorphClass();
        }
        $property->save();
        Factory::getPropertiesByEntity($property->entity)->put($name, $entity);

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
     * Entity values
     *
     * @var Collection
     */
    protected $values;

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
        $this->properties = Factory::getPropertiesByEntity($entity);
    }

    /**
     * Get factory properties
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
        if ($this->entity->getRelationValue($key) || $key === $this->entity->getKeyName()) {
            return false;
        }

        return $this->properties->has($key);
    }

    /**
     * Set Values for entity
     *
     * @param Collection $values
     */
    public function setPropertyValues(Collection $values)
    {
        $this->values = new Collection();
        $values->each(
            function (Value $v) use ($values) {
                $property = $v->property;
                if ($property->multiple) {
                    /* @var Collection $vs */
                    $vs = $this->values->get($property->name, null);
                    if ($vs === null) {
                        $this->values->put($property->name, new Collection([$v]));
                    } else {
                        $vs->push($v);
                    }
                } else {
                    $this->values->put($property->name, $v);
                }
            }
        );
    }

    /**
     * Get all property values
     *
     * @return Collection
     */
    public function getPropertyValues()
    {
        return $this->values ?: $this->values = new Collection();
    }

    /**
     * Get values as array
     *
     * @return array
     */
    public function getValuesToArray()
    {
        return $this->getProperties()->map(
            function (Property $property) {
                $value = $this->getPropertyValue($property->name);
                if ($value instanceof Value) {
                    return Arr::get($value->attributesToArray(), 'value');
                }
                if ($value instanceof Collection) {
                    return $value->map(
                        function (Value $value) {
                            return Arr::get($value->attributesToArray(), 'value');
                        }
                    );
                }

                return null;
            }
        )->toArray();
    }

    /**
     * Get property Value(s) by name
     *
     * @param $key
     *
     * @return Collection|Value|null
     */
    public function getPropertyValue($key)
    {
        /* @var Property $property */
        $values = $this->getPropertyValues();
        $value = $values->get($key);
        $property = $this->properties->get($key);
        if ($property !== null && $value === null && $property->multiple) {
            $values->put($property->name, $value = new Collection());
        }

        return $value;
    }

    /**
     * Get property simple value by name
     *
     * @param string $key
     *
     * @return mixed|Collection
     */
    public function getValue($key)
    {
        $value = $this->getPropertyValue($key);

        if ($value instanceof Collection) {
            return new MultipleCollection(
                $this,
                $this->properties->get($key),
                $value->map(
                    function (Value $value) {
                        return $value->getAttributeValue('value');
                    }
                )
            );
        } elseif ($value instanceof Value) {
            return $value->getAttributeValue('value');
        } else {
            return null;
        }
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function setValue($key, $value)
    {
        /* @var Property $property */
        $property = $this->properties->get($key);
        $v = $this->getPropertyValue($key);
        if ($property->multiple) {
            if (!is_array($value) && !$value instanceof Collection) {
                throw new \InvalidArgumentException('Value must be array');
            }
            $this->queuedDelete($property);
            $this->getPropertyValues()->put($property->name, new Collection());
            foreach ($value as $val) {
                $this->addValue($property, $val);
            }
        } else {
            /* @var Value $value */
            if ($v !== null) {
                if ($value === null) {
                    $this->queuedDelete($property);
                } else {
                    $v->setAttribute('value', $value);
                }
            } elseif ($value !== null) {
                $this->addValue($property, $value);
            }
        }
    }

    /**
     * Add new Value to entity
     *
     * @param Property $property
     * @param mixed    $value
     *
     * @return Value
     */
    public function addValue(Property $property, $value)
    {
        $instance = Factory::getType($property->type);
        if ($instance !== null) {
            $v = $instance->newInstance([], false);
        } else {
            $v = new Value();
        }

        $v->setConnection($this->entity->getConnectionName());
        $v->setRelation('property', $property);

        $v->forceFill(
            [
                'property_id' => $property->getKey(),
                'value'       => $value,
            ]
        );

        if ($property->multiple) {
            $this->getPropertyValue($property->name)->push($v);
        } else {
            $this->getPropertyValues()->put($property->name, $v);
        }

        return $v;
    }

    /**
     * Add property for delete values from entity
     *
     * @param Property $property
     */
    protected function queuedDelete(Property $property)
    {
        $this->queue[$property->id] = $property;
    }

    /**
     * Save entity values
     *
     * @throws \Exception
     */
    public function save()
    {
        $instance = new Value();
        $inserts = new Collection();
        $connection = $this->entity->getConnection();
        $connection->beginTransaction();
        try {
            if (count($this->queue) > 0) {
                $connection->table($instance->getTable())
                    ->where('entity_id', $this->entity->getKey())
                    ->whereIn('property_id', array_keys($this->queue))
                    ->delete();
            }

            foreach ($this->getPropertyValues() as $value) {
                /* @var Value|Collection $value */
                if ($value instanceof Collection) {
                    $inserts = $inserts->merge($value->where('exists', false, true));
                } else {
                    $value->setAttribute('entity_id', $this->entity->getKey());
                    $value->save();
                }
            }

            if ($inserts->count() > 0) {
                $data = $inserts->map(
                    function (Value $value) {
                        return ['entity_id' => $this->entity->getKey()] + $value->attributesToArray();
                    }
                );
                $connection->table($instance->getTable())->insert($data->all());
                $inserts->each(
                    function (Value $value) {
                        $value->exists = true;
                    }
                );
            }

            $this->queue = [];
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
        $connection->commit();
    }

    /**
     * Delete entity values
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