<?php

namespace DKulyk\Eloquent\Logging;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Collection;

/**
 * Class Logging.
 *
 * @mixin Eloquent
 *
 * @property LoggingModel[]|Collection $logs
 */
trait Logging
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function logs()
    {
        return $this->morphMany(LoggingModel::class, 'object');
    }

    /**
     * The "booting" method of the trait.
     */
    public static function bootLogging()
    {
        static::observe(LoggingListener::class);
    }
}
