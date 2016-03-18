<?php
use DKulyk\Eloquent\Propertier\Types;
use Illuminate\Container\Container;

Container::getInstance()->make('config')->set('eloquent-extra', [
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

]);

$manager = Container::getInstance()->make('dkulyk.propertier');
$contact = new ContactProperties();

$manager->addField($contact, Types::TYPE_INTEGER, Types::TYPE_INTEGER);
$manager->addField($contact, Types::TYPE_STRING, Types::TYPE_STRING);
$manager->addField($contact, Types::TYPE_DATE, Types::TYPE_DATE);
$manager->addField($contact, Types::TYPE_DATETIME, Types::TYPE_DATETIME);
$manager->addField($contact, Types::TYPE_FLOAT, Types::TYPE_FLOAT);
$manager->addField($contact, Types::TYPE_BOOLEAN, Types::TYPE_BOOLEAN);
$manager->addField($contact, Types::TYPE_JSON, Types::TYPE_JSON);

$manager->addField($contact, 'multiple', Types::TYPE_STRING, true);


ContactProperties::create([
    'email' => 'test@domain.com',
]);