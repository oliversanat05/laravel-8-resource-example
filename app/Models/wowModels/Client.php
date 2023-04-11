<?php

namespace App\Models\wowModels;

use App\Models\wowModels\WowTracker;
use App\Models\wowModels\Ideas;
use App\Models\wowModels\Users;
use App\Models\wowModels\ClientScore;
use Illuminate\Database\Eloquent\Model;
use App\Models\wowModels\GapAnalysisScore;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
         'user_id', 'name','address','birthday','aniversery','listing_id'
    ];


    /**
	 * Relations between the Client and the ClientScore
	 */
	public function clientScore() {
		return $this->hasMany(ClientScore::class,'client_id','id');
	}

    /**
     * Get all of the gapAnalysisHeading for the Client
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function gapAnalysis()
    {
        return $this->hasMany(GapAnalysisScore::class, 'client_id', 'id');
    }

   /**
    * Get all of the wow_tracker for the Client
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
   public function wow_tracker()
   {
       return $this->hasMany(WowTracker::class, 'client_id', 'id');
   }

}
