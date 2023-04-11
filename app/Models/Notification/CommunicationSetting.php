<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationSetting extends Model
{
    use HasFactory;

    public $primaryKey = 'CID';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'communicationSetting';

    /**
     * Removing dynamic update_at column
     *
     */
    public $timestamps = false;
}
