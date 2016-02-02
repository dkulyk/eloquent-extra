<?php namespace DKulyk\Journaling\Traits;

use DKulyk\Journaling\JournalModel;
use DKulyk\Journaling\JournalObserver;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Journaling
 *
 * @package Model
 * @mixin Model
 * @property JournalModel $journal
 */
trait Journaling
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function journal()
    {
        return $this->morphMany(JournalModel::class, 'object');
    }

    public static function bootJournaling()
    {
        static::observe(JournalObserver::class);
    }
}