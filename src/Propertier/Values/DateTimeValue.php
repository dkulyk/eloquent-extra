<?php

namespace DKulyk\Eloquent\Propertier\Values;

use DKulyk\Eloquent\Propertier\FieldValue;

class DateTimeValue extends FieldValue
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
