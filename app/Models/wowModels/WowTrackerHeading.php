<?php

namespace App\Models\wowModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WowTrackerHeading extends Model
{
    use HasFactory;
    protected $fillable=['month'];
}
