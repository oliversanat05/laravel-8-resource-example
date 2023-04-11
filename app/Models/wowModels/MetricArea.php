<?php

namespace App\Models\wowModels;

use App\Models\wowModels\StatementListing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetricArea extends Model
{
    use HasFactory,SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'metric_type', 'user_id', 'is_default', 'title', 'status'
    ];

    /**
     * Relations between the metric area and the statement listing
     */
    public function statementListing()
    {
        return $this->belongsTo(StatementListing::class);
    }
}
