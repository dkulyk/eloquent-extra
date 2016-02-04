<?php

namespace DKulyk\Eloquent;

use DKulyk\Eloquent\Properties\Relations\Values;
use DKulyk\Eloquent\Properties\Value;
use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Class Properties
 *
 * @mixed Eloquent
 */
trait Properties
{
    /**
     * Property factory for this model instance
     *
     * @var Properties\Factory
     */
    private $propertyFactory;

    public static function bootProperties()
    {
        static::addGlobalScope(new Properties\QueryScope());

        static::saved(
            function (Eloquent $entity) {
                /* @var Eloquent|Properties $entity */
                $entity->getPropertyFactory()->save();
            }
        );

        static::deleted(
            function (Eloquent $entity) {
                /* @var Eloquent|Properties $entity */
                $entity->getPropertyFactory()->delete();
            }
        );
    }

    /**
     * Get property factory for this model instance
     *
     * @return Properties\Factory
     */
    public function getPropertyFactory()
    {
        if ($this->propertyFactory === null) {
            $this->propertyFactory = new Properties\Factory($this);
        }

        return $this->propertyFactory;
    }

    /**
     * Get Values relationship
     *
     * @return Values
     */
    public function values()
    {
        $instance = new Value();
        $instance->setConnection($this->getConnectionName());

        //Builder $query, Model $parent, $foreignKey, $localKey
        return new Values($instance->newQuery(), $this, $instance->getTable().'.entity_id', $this->getKeyName());
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        $factory = $this->getPropertyFactory();

        if ($factory->has($key)) {
            return $factory->getValue($key);
        }

        return parent::getAttribute($key);
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return $this
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function setAttribute($key, $value)
    {
        $factory = $this->getPropertyFactory();
        if ($factory->has($key)) {
            $factory->setValue($key, $value);

            return $this;

        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Get an attribute array of all arrayable attributes.
     *
     * @return array
     */
    protected function getArrayableAttributes()
    {
        return parent::getArrayableAttributes() + $this->getArrayableItems($this->getPropertyFactory()->getValuesToArray());
    }
}