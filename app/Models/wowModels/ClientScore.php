<?php

namespace App\Models\wowModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\wowModels\Client;

class ClientScore extends Model
{
    use HasFactory,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'client_id', 'metric_area_id', 'score'
   ];

    /**
     * Relations between the client score and the client
    */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
