<?php

namespace App\Models\Profile;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkHistory extends Model
{
    use HasFactory;

    public $primaryKey = 'workHistoryId';
    protected $table = 'workHistory';

    protected $fillable = ['position', 'company', 'duration', 'reasonForChoosing', 'profileId', 'displayOrder'];
    public $timestamps = true;
}
