<?php

namespace DKulyk\Eloquent\Properties\Relations;

use DKulyk\Eloquent\Properties;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;

class Values extends HasOneOrMany
{
    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->query->get();
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array  $models
     * @param  string $relation
     *
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        return $models;
    }

    /**
     * Get the parent model of the relation.
     *
     * @return \Illuminate\Database\Eloquent\Model|Properties
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set the property constraints on the relation query.
     */
    public function addPropertyConstraints()
    {
        $properties = $this->getParent()->getPropertyFactory()->getProperties();
        if ($properties->count()) {
            $this->query->getQuery()
                ->whereIn('property_id', $properties->pluck('id'));
        } else {
            $this->query->getQuery()->whereIn('property_id', []);
        }
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            $this->query->where($this->foreignKey, '=', $this->getParentKey());

            $this->query->getQuery()->whereNotNull($this->foreignKey);
            $this->addPropertyConstraints();
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array $models
     *
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $this->query->getQuery()->whereIn($this->foreignKey, $this->getKeys($models, $this->localKey));
        $this->addPropertyConstraints();
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  Eloquent[]|Properties[] $models
     * @param  Collection              $results
     * @param  string                  $relation
     *
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        if ($results->count() === 0) {
            return $models;
        }

        $dictionary = $this->buildDictionary($results);

        // Once we have the dictionary we can simply spin through the parent models to
        // link them up with their children using the keyed dictionary to make the
        // matching very convenient and easy work. Then we'll just return them.
        foreach ($models as $model) {
            $key = $model->getAttribute($this->localKey);

            if (array_key_exists($key, $dictionary)) {
                $value = $this->getRelationValue($dictionary, $key, 'many');

                $model->getPropertyFactory()->setPropertyValues($value);
            } else {
                $model->getPropertyFactory()->setPropertyValues(null);
            }
        }

        return $models;
    }
}