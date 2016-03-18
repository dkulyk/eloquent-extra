<?php

namespace DKulyk\Eloquent\Logging;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Class Listener.
 */
class LoggingListener
{
    public function created(Eloquent $object)
    {
        $object->logs()->create([
            'type' => LoggingModel::CREATE,
            'data' => $object->getAttributes(),
        ]);
    }

    public function updated(Eloquent $object)
    {
        $object->logs()->create([
            'type' => LoggingModel::UPDATE,
            'data' => $object->getOriginal(),
        ]);
    }

    public function deleted(Eloquent $object)
    {
        $object->logs()->create([
            'type' => LoggingModel::DELETE,
            'data' => $object->getOriginal(),
        ]);
    }

    public function restored(Eloquent $object)
    {
        $object->logs()->create([
            'type' => LoggingModel::RESTORE,
            'data' => $object->getOriginal(),
        ]);
    }
}
