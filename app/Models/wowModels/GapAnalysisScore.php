<?php

namespace App\Models\wowModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GapAnalysisScore extends Model
{
    use HasFactory;
    protected $fillable = ['client_id','listing_id','gap_analysis_heading_id','score'];


    /**
     * Get the Heading that owns the GapAnalysisScore
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gapAnalysisHeading()
    {
        return $this->belongsTo(GapAnalysisHeading::class, 'gap_analysis_heading_id', 'id');
    }

    /**
     * Get the Client that has the GapAnalysisScore
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }
}
