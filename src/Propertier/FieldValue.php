<?php

namespace DKulyk\Eloquent\Propertier;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Config;
use DKulyk\Eloquent\Propertier\Contracts\Value as ValueContract;

/**
 * Class Value.
 *
 * @property-read int $id
 * @property integer  $entity_id
 * @property integer  $property_id
 * @property string   $value
 * @property Field    $property
 */
class FieldValue extends Eloquent implements ValueContract
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['entity_id', 'property_id', 'value'];

    /**
     * Create a new model instance that is existing.
     *
     * @param array       $attributes
     * @param string|null $connection
     *
     * @return static
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $manager = Container::getInstance()->make('dkulyk.propertier');
        /* @var FieldValue $instance */
        $field =$manager->getFields()->get($attributes->field_id);
        $instance = $manager->resolve($field);


        $model = $instance->newInstance([], true);
        $model->setTable($instance->getTable());
        $model->setRelation('property', $field);
        $model->setRawAttributes((array) $attributes, true);
        $model->setConnection($connection ?: $this->connection);

        return $model;
    }

    /**
     * Get property relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function field()
    {
        return $this->belongsTo(Field::class, 'property_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->getAttribute('value');
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        return $this->setAttribute('value', $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getSimpleValue()
    {
        return $this->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->getSimpleValue();
    }
}
