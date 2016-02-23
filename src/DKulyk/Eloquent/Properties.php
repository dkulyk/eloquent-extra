<?php

namespace DKulyk\Eloquent;

use DKulyk\Eloquent\Properties\Relations\Values;
use DKulyk\Eloquent\Properties\Value;
use Illuminate\Database\Eloquent\Model as Eloquent;
use DKulyk\Eloquent\Properties\Contracts\Value as ValueContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class Properties.
 *
 * @mixed Eloquent
 */
trait Properties
{
    /**
     * Property factory for this model instance.
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
     * Get property factory for this model instance.
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
     * Get Values relationship.
     *
     * @param Collection|null $properties
     *
     * @return Values
     */
    public function fields(Collection $properties = null)
    {
        $instance = new Value();
        $instance->setConnection($this->getConnectionName());

        //Builder $query, Model $parent, $foreignKey, $localKey
        return new Values(
            $instance->newQuery(),
            $this,
            $instance->getTable().'.entity_id',
            $this->getKeyName(),
            $properties
        );
    }

    /**
     * Set a given attribute on the model.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     *
     * @return $this
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
     * Unset an attribute on the model.
     *
     * @param string $key
     */
    public function __unset($key)
    {
        $factory = $this->getPropertyFactory();
        parent::__unset($key);
        $factory->has($key) && $factory->setValue($key, null);
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $factory = $this->getPropertyFactory();
        if (preg_match('/get([^;]+?)Attribute/', $method, $m)) {
            $name = lcfirst(Str::snake($m[1]));
            if ($factory->has($name)) {
                $value = $factory->getPropertyValue($name);
                return $value instanceof ValueContract ? $value->getSimpleValue() : null;
            }
        }

        return $factory->has($method) ? $this->fields(new Collection([$factory->get($method)])) : parent::__call($method, $parameters);
    }
}
