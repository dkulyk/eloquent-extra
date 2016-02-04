<?php

namespace DKulyk\Eloquent\Properties\Values;

use DKulyk\Eloquent\Properties\Value;

class DateTimeValue extends Value
{
    /**
     * Value casting.
     *
     * @var array
     */
    protected $casts
        = [
            'value' => 'datetime',
        ];
}