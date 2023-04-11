<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ENCQualifier extends Model
{
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ENCQualifier';
    public $primaryKey = 'ENCQualifierId';
    protected $guarded = ['_token'];

    public $timestamps = false;

    /**
     * Get the user that owns the ENCQualifier
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'userName', 'user_id');
    }

}
