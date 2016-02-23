<?php

namespace DKulyk\Eloquent\Properties;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Config;
use DKulyk\Eloquent\Properties\Contracts\Value as ValueContract;

/**
 * Class Value.
 *
 * @property-read int $id
 * @property integer  $entity_id
 * @property integer  $property_id
 * @property string   $value
 * @property Property $property
 */
class Value extends Eloquent implements ValueContract
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'property_values';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['entity_id', 'property_id', 'value'];

    /**
     * Create a new Value model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = Config::get('eloquent-extra.values_table', $this->table);
        parent::__construct($attributes);
    }

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
        /* @var Value $instance */
        $property = Factory::getPropertyById($attributes->property_id);
        $instance = Factory::getType($property->type) ?: $this;

        $model = $instance->newInstance([], true);
        $model->setRelation('property', $property);
        $model->setRawAttributes((array) $attributes, true);
        $model->setConnection($connection ?: $this->connection);

        return $model;
    }

    /**
     * Set the keys for a save update query.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        $query->where(
            [
                'entity_id'   => $this->entity_id,
                'property_id' => $this->property_id,
            ]
        );

        return $query;
    }

    /**
     * Get property relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
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

    public function jsonSerialize()
    {
        return $this->getSimpleValue();
    }
}
