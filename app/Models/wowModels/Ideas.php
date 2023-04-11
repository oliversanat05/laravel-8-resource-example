<?php

namespace App\Models\wowModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\wowModels\RelationalGrid;
class Ideas extends Model
{
    use HasFactory,SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'idea_title', 'user_id', 'is_default', 'idea_type'
    ];

    /**
     * Get the user that owns the Ideas
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function relationalGrid()
    {
        return $this->hasMany(RelationalGrid::class, 'idea_id', 'id');
    }

}
