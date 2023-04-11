<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvatarDetails extends Model
{
    use HasFactory;

    protected $table = 'avatar_details';
    protected $fillable = [
        'accessoriesType', 'clotheColor', 'clotheType', 'eyeType', 'eyebrowType', 'facialHairType', 'hairColor', 'mouthType', 'skinColor', 'topType', 'userId', 'healthId',
    ];
}
