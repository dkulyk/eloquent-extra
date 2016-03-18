<?php

use \DKulyk\Eloquent\Propertier\Types;
use \DKulyk\Eloquent\Propertier\Values;

return [
    'logging_table' => 'eloquent_log',
    'fields_table'  => 'fields',
    'values_tables' => [
        Types::TYPE_STRING   => 'property_values_string',
        Types::TYPE_TEXT     => 'property_values_text',
        Types::TYPE_JSON     => 'property_values_text',
        Types::TYPE_DATETIME => 'property_values_date',
        Types::TYPE_DATE     => 'property_values_datetime',
        Types::TYPE_INTEGER  => 'property_values_int',
        Types::TYPE_BOOLEAN  => 'property_values_bool',
        Types::TYPE_FLOAT    => 'property_values_float',
    ],
    'fields_types'  => [
//        Factory::TYPE_DATETIME => Values\DateTimeFieldValue::class,
//        Factory::TYPE_DATE     => Values\DateValue::class,
//        Factory::TYPE_STRING   => Values\StringFieldValue::class,
//        Factory::TYPE_INTEGER  => Values\IntegerFieldValue::class,
//        Factory::TYPE_BOOLEAN  => Values\BooleanFieldValue::class,
//        Factory::TYPE_FLOAT    => Values\FloatFieldValue::class,
//        Factory::TYPE_JSON     => Values\JsonFieldValue::class,
    ],
];
