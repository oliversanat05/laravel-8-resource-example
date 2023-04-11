<?php

namespace App\Models\wowModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\wowModels\MetricArea;
use App\Models\wowModels\MetricHeading;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\wowModels\StatementListing;

class ClientMetricData extends Model
{
    use HasFactory,SoftDeletes;

	/**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'metric_id', 'listing_id', 'metric_heading_id', 'metric_value'
    ];
    /**
	 * Relations between the metricHeading and the statement clientMetricData
	 */
	public function metricArea() {
		return $this->hasMany(MetricArea::class,'id','metric_id');
	}


	/**
	 * Get all of the comments for the ClientMetricData
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function metricHeading()
	{
		return $this->hasMany(MetricHeading::class,'id','metric_heading_id');
	}

    /**
	 * Relations between the metricHeading and the statement clientMetricData
	 */
	public function statementListing() {
		return $this->belongsTo(StatementListing::class);
	}

}
