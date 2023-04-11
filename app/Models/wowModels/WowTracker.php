<?php

namespace App\Models\wowModels;

use App\Models\wowModels\Client;
use App\Models\wowModels\Ideas;
use App\Models\wowModels\WowTrackerHeading;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WowTracker extends Model
{
    use HasFactory,SoftDeletes;
    protected $with= ['tracker_heading','ideas'];
    protected $guarded=[ ];

    /**
     * Get all of the  for the WowTracker
     *
     * @return \Illuminate\Database\Eloquent\Relations\Belongsto
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    /**
     * Get all of the  for the WowTracker
     *
     * @return \Illuminate\Database\Eloquent\Relations\Belongsto
     */
    public function ideas()
    {
        return $this->hasOne(Ideas::class,'id','idea_id');
    }

    /**
     * Get all of the  for the WowTracker
     *
     * @return \Illuminate\Database\Eloquent\Relations\Belongsto
     */
    public function tracker_heading()
    {
        return $this->belongsTo(WowTrackerHeading::class, 'tracker_id', 'id');
    }


    /**
     * Get all of the  for the WowTracker
     *
     * @return \Illuminate\Database\Eloquent\Relations\Belongsto
     */
    public function clientScores()
    {
        return $this->hasMany(ClientScore::class, 'client_id', 'client_id');
    }

}
