<?php

namespace DKulyk\Eloquent\Propertier;

use DKulyk\Eloquent\Propertier\Relations\Values;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

class QueryScope implements Scope
{

    /**
     * @param Builder  $builder
     * @param Eloquent $model
     *
     * @throws \InvalidArgumentException
     */
    public function apply(Builder $builder, Eloquent $model)
    {
        if (count($loads = $builder->getEagerLoads()) === 0) {
            return;
        }

        $manager = Container::getInstance()->make('dkulyk.propertier');
        $fields = $manager->getFields($model);
        if (count($fields) === 0) {
            return;
        }

        $query = $builder->getQuery();

        //compact eager loading
        if (count($loads) > 0) {
            $eagerLoads = [];
            $props = [];
            foreach ($loads as $load => $constraints) {
                if ($load === 'fields') {
                    /* @var Values $relation */
                    $relation = $builder->getRelation($load);
                    call_user_func($constraints, $relation);
                    foreach ($relation->getFields() as $field) {
                        $props[$field->name] = $field;
                    }
                }
                if (($field = $fields->get($load)) !== null) {
                    $props[$field->name] = $field;
                } else {
                    $eagerLoads[$load] = $constraints;
                }
            }
            if (count($props)) {
                $eagerLoads['fields'] = function (Values $relation) use ($props) {
                    $relation->setFields(new Collection($props));
                };
            }
            $builder->setEagerLoads($eagerLoads);
        }
    }
}
