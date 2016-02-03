<?php namespace DKulyk\Eloquent\Logging;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Class Listener
 */
class Listener
{
    public function created(Eloquent $object)
    {
        $object->logs()->create([
            'type' => Model::CREATE,
            'data' => $object->getAttributes()
        ]);
    }

    public function updated(Eloquent $object)
    {
        $object->logs()->create([
            'type' => Model::CHANGE,
            'data' => $object->getAttributes()
        ]);
    }

    public function deleted(Eloquent $object)
    {
        $object->logs()->create([
            'type' => Model::DELETE,
            'data' => $object->getAttributes()
        ]);
    }

    public function restored(Eloquent $object)
    {
        $object->logs()->create([
            'type' => Model::RESTORE,
            'data' => $object->getAttributes()
        ]);
    }
}