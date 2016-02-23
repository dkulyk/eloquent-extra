<?php

namespace DKulyk\Eloquent\Properties\Values;

class DateValue extends DateTimeValue
{
    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d';
}
