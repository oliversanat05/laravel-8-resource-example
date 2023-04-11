<?php

namespace App\Models\wowModels;

use App\Models\wowModels\Ideas;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelationalGrid extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [];

    /**
     * Get the user that owns the RelationalGrid
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ideas()
    {
        return $this->belongsTo(Ideas::class, 'idea_id', 'id');
    }

    /**
     * Get the user that owns the RelationalGrid
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tier()
    {
        return $this->belongsTo(Tier::class, 'tier_id', 'id');
    }


}
