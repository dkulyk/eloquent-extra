<?php namespace DKulyk\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class Logging
 *
 * @package Model
 * @mixin Model
 * @property Model[]|Collection $logs
 */
trait Logging
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function logs()
    {
        return $this->morphMany(Logging\Model::class, 'object');
    }

    /**
     * The "booting" method of the trait.
     */
    public static function bootLogging()
    {
        static::observe(Logging\Listener::class);
    }
}