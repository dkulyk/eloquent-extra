<?php

namespace DKulyk\Eloquent\Propertier\Values;

use DKulyk\Eloquent\Propertier\FieldValue;

class JsonValue extends FieldValue
{
    /**
     * Value casting.
     *
     * @var array
     */
    protected $casts
        = [
            'value' => 'array',
        ];
}
