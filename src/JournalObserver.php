<?php namespace Lnk\Journaling;

use Lnk\Journaling\Traits\Journaling as JournalingTrait;

class JournalObserver
{
    public function created(JournalingTrait $object)
    {
        $object->journal()->create([
            'type' => JournalModel::CREATE,
            'data' => $object->getAttributes()
        ]);
    }

    public function updated(JournalingTrait $object)
    {
        $object->journal()->create([
            'type' => JournalModel::CHANGE,
            'data' => $object->getAttributes()
        ]);
    }

    public function deleted(JournalingTrait $object)
    {
        $object->journal()->create([
            'type' => JournalModel::DELETE,
            'data' => $object->getAttributes()
        ]);
    }

    public function restored(JournalingTrait $object)
    {
        $object->journal()->create([
            'type' => JournalModel::RESTORE,
            'data' => $object->getAttributes()
        ]);
    }
}