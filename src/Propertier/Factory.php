<?php

namespace DKulyk\Eloquent\Propertier;

use DKulyk\Eloquent\Propertier;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use DKulyk\Eloquent\Propertier\Contracts\Value as ValueContract;

final class Factory
{
    /**
     * @param string $entity
     *
     * @return Collection|Field[]
     * @deprecated
     */
    public static function getPropertiesByEntity($entity)
    {
        return \App::make('dkulyk.propertier')->getFields($entity);
    }

    /**
     * Get property by ID.
     *
     * @param $id
     *
     * @return Field
     * @deprecated 
     */
    public static function getFieldById($id)
    {
        return \App::make('dkulyk.propertier')->getFields()->get($id);
    }

    /**
     * @param $type
     *
     * @return FieldValue
     * @deprecated
     */
    public static function getType($type)
    {
        return \App::make('dkulyk.propertier')->resolve($type);
    }

    /**
     * @param Eloquent|string $entity
     * @param string          $name
     * @param string          $type
     * @param bool            $multiple
     * @param Eloquent|string $reference
     *
     * @return Field
     */
    public static function addProperty($entity, $name, $type = self::TYPE_STRING, $multiple = false, $reference = null)
    {
        $entity = $entity instanceof Eloquent ? $entity : new $entity();
        $property = new Field(
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

        return $property;
    }

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var Collection|Field[]
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
     * @var Field[]
     */
    protected $queue = [];

    /**
     * Factory constructor.
     *
     * @param Eloquent $entity
     */
    public function __construct(Eloquent $entity)
    {
        $this->manager = \App::make('dkulyk.propertier');
        $this->entity = $entity;
        $this->values = new ValueCollection($this);
        $this->properties = self::getPropertiesByEntity($entity);
    }

    /**
     * Get factory properties.
     *
     * @return Collection|Field[]
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
                function (FieldValue $v) use ($values) {
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
            function (Field $property, $name) {
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
                    [
                        'fields' => function (Properties\Relations\Values $relation) use ($properties) {
                            $relation->setProperties($properties);
                        },
                    ]
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
        /* @var Field $property */
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
     * @param FieldValue $value
     */
    public function queuedDelete(FieldValue $value)
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
        $instance = new FieldValue();
        $connection = $this->entity->getConnection();
        $connection->beginTransaction();
        try {
            if (count($this->queue) > 0) {
                $connection->table($instance->getTable())
                    ->whereIn('id', array_keys($this->queue))
                    ->delete();
            }

            foreach ($this->getPropertyValues(true) as $value) {
                /* @var FieldValue|Collection $value */
                if ($value instanceof Collection) {
                    $value->each(
                        function (FieldValue $value) {
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
            $instance = new FieldValue();
            $connection = $this->entity->getConnection();
            $connection->table($instance->getTable())
                ->where('entity_id', $this->entity->getKey())
                ->whereIn('property_id', $this->properties->pluck('id'))
                ->delete();
        }
    }
}
