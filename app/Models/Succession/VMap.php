<?php

namespace App\Models\Succession;

use App\Models\ActivityTitle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VMap extends Model
{
    use HasFactory;

    public $primaryKey = 'vMapId';
    protected $table = 'vMap';

    protected $fillable = ['formTitle', 'formDate', 'visionStatement', 'missionStatement'];

    /**
     * Create a new model instance to use the polymorphic relation with multiple tables.
     * @param NA
     * @return void
     */

    public function values()
    {
        return $this->hasMany('App\Models\Succession\Value', 'vMapId', 'vMapId');
    }

    /**
     * Get the activityTitle associated with the VMap
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function activityTitle()
    {
        return $this->hasOne(ActivityTitle::class, 'vmvId', 'vMapId');
    }
}
