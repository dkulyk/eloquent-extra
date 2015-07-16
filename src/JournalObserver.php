<?php namespace Lnk\Journaling;

use Illuminate\Database\Eloquent\Model;

class JournalObserver
{
    public function created(Model $object)
    {
        $object->journal()->create([
            'type' => JournalModel::CREATE,
            'data' => $object->getAttributes()
        ]);
    }

    public function updated(Model $object)
    {
        $object->journal()->create([
            'type' => JournalModel::CHANGE,
            'data' => $object->getAttributes()
        ]);
    }

    public function deleted(Model $object)
    {
        $object->journal()->create([
            'type' => JournalModel::DELETE,
            'data' => $object->getAttributes()
        ]);
    }

    public function restored(Model $object)
    {
        $object->journal()->create([
            'type' => JournalModel::RESTORE,
            'data' => $object->getAttributes()
        ]);
    }
}