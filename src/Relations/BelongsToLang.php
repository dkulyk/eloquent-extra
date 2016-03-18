<?php

namespace DKulyk\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Lang;

/**
 * Class BelongToLang.
 */
class BelongsToLang extends BelongsTo
{
    protected $lang;
    protected $fallback;
    protected $langKey;
    protected $fallbackQuery;

    /**
     * BelongToLang constructor.
     *
     * @param Builder  $query
     * @param Eloquent $parent
     * @param string   $foreignKey
     * @param string   $otherKey
     * @param string   $langKey
     * @param string   $relation
     * @param null     $lang
     * @param null     $fallback
     */
    public function __construct(Builder $query, Eloquent $parent, $foreignKey, $otherKey, $langKey, $relation, $lang = null, $fallback = null)
    {
        $this->lang = $lang ?: Lang::getLocale();
        $this->fallback = $fallback ?: Lang::getFallback();
        $this->langKey = $langKey ?: 'lang';
        $this->fallbackQuery = clone $query;

        parent::__construct($query, $parent, $foreignKey, $otherKey, $relation);
    }

    /**
     * Set the base constraints on the relation query.
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            $key = $this->query->getQuery()->getGrammar()->wrap($this->related->getTable().'.'.$this->langKey);
            $this->query->getQuery()->orderByRaw('FIELD('.$key.', ?, ?) DESC', [$this->fallback, $this->lang]);
        }
        parent::addConstraints();
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param array $models
     */
    public function addEagerConstraints(array $models)
    {
        // We'll grab the primary key name of the related models since it could be set to
        // a non-standard name and not "id". We will then construct the constraint for
        // our eagerly loading query so it returns the proper models from execution.
        $langKey = $this->related->getTable().'.'.$this->langKey;
        $this->query->where($langKey, $this->lang);

        parent::addEagerConstraints($models);
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param array      $models
     * @param Collection $results
     * @param string     $relation
     *
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $foreign = $this->foreignKey;

        $other = $this->otherKey;

        // First we will get to build a dictionary of the child models by their primary
        // key of the relationship, then we can easily match the children back onto
        // the parents using that dictionary and the primary key of the children.
        $dictionary = [];

        foreach ($results as $result) {
            $dictionary[$result->getAttribute($other)] = $result;
        }

        $fallbackModels = [];
        // Once we have the dictionary constructed, we can loop through all the parents
        // and match back onto their children using these keys of the dictionary and
        // the primary key of the children to map them onto the correct instances.
        foreach ($models as $model) {
            if (isset($dictionary[$model->$foreign])) {
                $model->setRelation($relation, $dictionary[$model->$foreign]);
            } else {
                $fallbackModels[] = $model;
            }
        }

        if (count($fallbackModels) > 0) {
            $query = $this->query;
            $lang = $this->lang;
            $this->lang = $this->fallback;
            $this->query = $this->fallbackQuery;
            $this->addEagerConstraints($fallbackModels);
            parent::match($fallbackModels, $this->getEager(), $relation);
            $this->query = $query;
            $this->lang = $lang;
        }

        return $models;
    }

    /**
     * Handle dynamic method calls to the relationship.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $result = parent::__call($method, $parameters);
        if ($method !== 'get') {
            call_user_func_array([$this->fallbackQuery, $method], $parameters);
        }

        return $result;
    }
}
