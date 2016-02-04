<?php

namespace DKulyk\Eloquent\Properties\Values;

use DKulyk\Eloquent\Properties\Value;

class DateValue extends Value
{
    /**
     * Value casting.
     *
     * @var array
     */
    protected $casts
        = [
            'value' => 'date',
        ];

    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d';
}