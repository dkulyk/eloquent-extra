<?php

namespace DKulyk\Eloquent\Properties;

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
        $properties = Factory::getPropertiesByEntity($model);
        if (count($properties) === 0) {
            return;
        }
        $query = $builder->getQuery();
        $table = $model->getTable();
        $columns = $this->parseWhere($properties, $query->wheres, $table);
        if (count($columns) === 0) {
            return;
        }

        $value = new Value();
        $query->select($table.'.*');
        $multiple = false;
        foreach ($columns as $column => $property) {
            if ($property->multiple) {
                $multiple = true;
            }
            $query->leftJoin(
                $value->getTable().' AS '.'eav_'.$property->name,
                function (JoinClause $join) use ($model, $property) {
                    $join->on($model->getTable().'.'.$model->getKeyName(), '=', 'eav_'.$property->name.'.'.'entity_id');
                    $join->where('eav_'.$property->name.'.'.'property_id', '=', $property->id);
                }
            );
        }

        if ($multiple) {
            //Grouping if condition by multiple values
            $query->groupBy($table.'.'.$model->getKeyName());
        }
    }

    /**
     * @param Collection $properties
     * @param            $wheres
     * @param string     $table
     *
     * @return array
     */
    protected function parseWhere($properties, &$wheres, $table)
    {
        $fields = [];
        if (is_array($wheres)) {
            foreach ($wheres as &$where) {

                if (in_array($where['type'], ['Basic', 'Null'], true)) {
                    $column = str_replace($table.'.', '', $where['column']);
                    $property = $properties->get($column);
                    if ($property !== null) {
                        $fields[$column] = $property;
                        $where['column'] = 'eav_'.$column.'.value';

                    }
                } else {
                    $fields = array_merge($fields, $this->parseWhere($properties, $where['query']->wheres, $table));
                }
            }
        }

        return $fields;
    }


}