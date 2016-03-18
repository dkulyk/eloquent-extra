<?php

namespace DKulyk\Eloquent\Propertier;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Config;

/**
 * Class Property.
 *
 * @property int    $id
 * @property string $entity
 * @property string $name
 * @property string $type
 * @property bool   $multiple
 * @property mixed  $default_value
 * @property string $reference
 */
class Field extends Eloquent
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts
        = [
            'multiple' => 'boolean',
        ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable
        = [
            'partner',
            'name',
            'type',
            'multiple',
        ];
}
