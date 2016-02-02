<?php namespace DKulyk\Journaling;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * DtKt\Models\Log
 *
 * @property integer        $owner_id
 * @property integer        $type
 * @property string         $object_type
 * @property integer        $object_id
 * @property \Carbon\Carbon $created_at
 * @property string         $data
 */
class JournalModel extends Eloquent
{
    const NOTICE = 0;
    const WARNING = 1;
    const ERROR = 2;

    const CREATE = 3;
    const CHANGE = 4;
    const DELETE = 5;
    const RESTORE = 6;

    protected $table = 'journaling_log';

    public $timestamps = false;

    protected $fillable
        = [
            'owner_id',
            'type',
            'comment',
            'model',
            'object_id',
            'created_at',
            'data'
        ];

    protected $dates = ['created_at'];

    protected $casts = ['data' => 'array'];

    public function __construct(array $attributes = [])
    {
        $this->table = \Config::get('journaling.table', $this->table);

        parent::__construct($attributes);
    }

    public function save(array $options = [])
    {
        $this->owner_id = \Auth::id();
        $this->created_at = new Carbon();

        return parent::save($options);
    }
}