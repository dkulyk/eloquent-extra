<?php

namespace DKulyk\Eloquent;

use Illuminate\Database\Eloquent\Model as Eloquent;
use \DKulyk\Eloquent\Relations\BelongsToLang as BelongsToLangRelation;
use Illuminate\Support\Str;

/**
 * Class BelongsToLang
 *
 * @mixin Eloquent
 */
trait BelongsToLang
{
    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @param  string $related
     * @param  string $lang
     * @param  string $foreignKey
     * @param  string $otherKey
     * @param  string $langKey
     * @param  string $relation
     * @param  string $fallback
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToLang($related, $lang = null, $foreignKey = null, $otherKey = null, $langKey = null, $relation = null, $fallback = null)
    {
        // If no relation name was given, we will use this debug backtrace to extract
        // the calling method's name and use that as the relationship name as most
        // of the time this will be what we desire to use for the relationships.
        if ($relation === null) {
            list($current, $caller) = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

            $relation = $caller['function'];
        }

        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $instance = new $related;

        $localKey = $otherKey ?: $this->getKeyName();

        return new BelongsToLangRelation($instance->newQuery(), $this, $localKey, $foreignKey, $langKey, $relation, $lang, $fallback);
    }
}