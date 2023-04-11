<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventCoachTrack extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'eventCoachTrack';
    public $primaryKey = 'coachEventId';
    protected $guarded = ['_token'];
}
