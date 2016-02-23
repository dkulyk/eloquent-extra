<?php

namespace DKulyk\Eloquent\Properties;

use DKulyk\Eloquent\Properties;
use DKulyk\Eloquent\Properties\Contracts\Value as ValueContract;
use Illuminate\Support\Collection;

class ValueCollection extends Collection implements ValueContract
{
    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var Property
     */
    protected $property;

    /**
     * The items contained in the collection.
     *
     * @var array|Value[]
     */
    protected $items = [];

    /**
     * ValueCollections constructor.
     *
     * @param Factory  $factory
     * @param Property $property
     * @param array    $items
     */
    public function __construct(Factory $factory, Property $property = null, $items = [])
    {
        $this->property = $property;
        $this->factory = $factory;
        parent::__construct($items);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        array_map([$this->factory, 'queuedDelete'], $this->items);
        $this->items = array_map(
            function ($value) {
                return self::makeValue($value, $this->property);
            },
            $this->getArrayableItems($value)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSimpleValue()
    {
        return array_map(
            function (ValueContract $value) {
                return $value->getSimpleValue();
            },
            $this->items
        );
    }

    /**
     * @param mixed    $value
     * @param Property $property
     *
     * @return Value
     */
    public static function makeValue($value, Property $property)
    {
        if ($value instanceof ValueContract) {
            return $value;
        }
        static $maker = null;
        if ($maker === null) {
            $maker = $maker = Factory::getType($property->type);
        }

        return $maker->newInstance(['property_id' => $property->id])->setValue($value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($key, $value)
    {
        if ($value === null) {
            $this->offsetUnset($key);
        } elseif ($key === null) {
            $this->items[] = self::makeValue($value, $this->property ?: $this->factory->getProperties()->get($key));
        } else {
            if (array_key_exists($key, $this->items)) {
                $this->items[$key]->setValue($value);
            } else {
                $this->items[$key] = self::makeValue($value, $this->property ?: $this->factory->getProperties()->get($key));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($key)
    {
        return $this->items[$key]->getValue();
    }

    public function offsetUnset($key)
    {
        if (array_key_exists($key, $this->items) && $this->items[$key] instanceof Value) {
            $this->factory->queuedDelete($this->items[$key]);
        }
        parent::offsetUnset($key);
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getSimpleValue();
    }

    /**
     * Run a map over each of the items.
     *
     * @param  callable $callback
     *
     * @return static
     */
    public function map(callable $callback)
    {
        $items = [];
        foreach ($this->items as $key => $item) {
            $items[$key] = $callback($item->getValue(), $key);
        }

        return new Collection($items);
    }

}