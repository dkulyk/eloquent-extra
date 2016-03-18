<?php

namespace DKulyk\Eloquent\Propertier\Relations;

use DKulyk\Eloquent\Propertier\Field;
use DKulyk\Eloquent\Propertier\Manager;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Support\Collection as BaseCollection;

class Values extends HasOneOrMany
{
    /**
     * @var BaseCollection|Field[]
     */
    protected $fields;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * Values constructor.
     *
     * @param EloquentBuilder $query
     * @param Eloquent        $parent
     * @param string          $foreignKey
     * @param string          $localKey
     * @param BaseCollection  $properties
     */
    public function __construct(EloquentBuilder $query, Eloquent $parent, $foreignKey, $localKey, BaseCollection $properties = null)
    {
        $this->manager = Container::getInstance()->make('dkulyk.propertier');
        $this->setFields($properties);
        parent::__construct($query, $parent, $foreignKey, $localKey);
    }

    /**
     * Get relation fields
     *
     * @return BaseCollection
     */
    public function getFields()
    {
        return $this->fields ?: $this->manager->getFields($this->getParent());
    }

    /**
     * Set relation properties.
     *
     * @param BaseCollection $properties
     */
    public function setFields(BaseCollection $properties = null)
    {
        $this->fields = $properties === null ? null : $properties->keyBy('name');
    }

    /**
     * Get the results of the relationship.
     *
     * @return Collection|Eloquent[]
     */
    public function getResults()
    {
        $this->addFieldConstraints(false);

        return $this->query->get();
    }


    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param array $models
     * @param bool  $fields
     */
    public function addEagerConstraints(array $models, $fields = true)
    {
        parent::addEagerConstraints($models);
        if ($fields) {
            $this->addFieldConstraints($models);
        }
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param array  $models
     * @param string $relation
     *
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        return $models;
    }

    /**
     * Set the property constraints on the relation query.
     *
     * @param array|null $models
     */
    public function addFieldConstraints(array $models = null)
    {
        $baseQuery = $this->query->getQuery();

        $query = $this->getFields()
            ->keyBy('id')
            ->groupBy(function (Field $field) {
                return $this->manager->resolve($field)->getTable();
            }, true)
            ->map(function (BaseCollection $fields, $table) use ($models, $baseQuery) {
                $query = new QueryBuilder(
                    $baseQuery->getConnection(),
                    $baseQuery->getGrammar(),
                    $baseQuery->getProcessor()
                );
                $this->query->setQuery($query);
                if ($models === null) {
                    $this->addConstraints();
                } else {
                    $this->addEagerConstraints($models, false);
                }

                return $query->from($table)->whereIn('field_id', $fields->keys());
            })
            ->reduce(function (QueryBuilder $value = null, QueryBuilder $query) {
                if ($value !== null) {
                    $value->unionAll($query);

                    return $value;
                }

                return $query;
            });
        $this->query->setQuery($query);
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param Field[]    $models
     * @param Collection $results
     * @param string     $relation
     *
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        if ($results->count() === 0) {
            return $models;
        }

        $dictionary = $this->buildDictionary($results);
        $properties = $this->getFields();

        // Once we have the dictionary we can simply spin through the parent models to
        // link them up with their children using the keyed dictionary to make the
        // matching very convenient and easy work. Then we'll just return them.
        foreach ($models as $model) {
            $key = $model->getAttribute($this->localKey);

            if (array_key_exists($key, $dictionary)) {
                $value = $this->getRelationValue($dictionary, $key, 'many');

                $model->getPropertyFactory()->setPropertyValues($properties, $value);
            } else {
                $model->getPropertyFactory()->setPropertyValues($properties, null);
            }
        }

        return $models;
    }
}
