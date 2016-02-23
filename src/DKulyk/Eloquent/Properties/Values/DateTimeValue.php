<?php

namespace DKulyk\Eloquent\Properties\Values;

use DKulyk\Eloquent\Properties\Value;

class DateTimeValue extends Value
{
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['value'];

    /**
     * {@inheritdoc}
     */
    public function getSimpleValue()
    {
        return $this->serializeDate($this->getValue());
    }
}
