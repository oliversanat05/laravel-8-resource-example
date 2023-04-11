<?php

namespace App\Models\wowModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\wowModels\ClientMetricData;
use App\Models\wowModels\Client;

class StatementListing extends Model
{
    use HasFactory,SoftDeletes;
    use \Bkwld\Cloner\Cloneable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var string[]
	 */
	protected $fillable = [
		'user_id', 'statement_date','statement_title'
	];

    protected $cloneable_relations = ['clientMetricData', 'client','clientScores','gapAnalysisScore','wowTracker'];

 	/**
	 * Relations between the ClientMetricData and the statement listing
	 */
	public function clientMetricData() {
		return $this->hasMany(ClientMetricData::class,'listing_id','id');
	}
	/**
	 * Relations between the Client and the statement listing
	 */
	public function client() {
		return $this->hasMany(Client::class,'listing_id','id');
	}
	/**
	 * Relations between the ClientScore and the statement listing
	 */
	public function clientScores() {
		return $this->hasMany(ClientScore::class,'listing_id','id');
	}
	/**
	 * Relations between the GapAnalysisScore and the statement listing
	 */
	public function gapAnalysisScore() {
		return $this->hasMany(GapAnalysisScore::class,'listing_id','id');
	}
    /**
	 * Relations between the WowTracker and the statement listing
	 */
	public function wowTracker() {
		return $this->hasMany(WowTracker::class,'listing_id','id');
	}
}
