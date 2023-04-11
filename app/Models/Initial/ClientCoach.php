<?php

namespace App\Models\Initial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientCoach extends Model {
	use HasFactory;

	protected $table = 'clientCoach';
	public $primaryKey = 'clientCoachId';
	public $timestamps = false;

	protected $fillable = [
		'clientUserId', 'coachUserId', 'additionalCoach',
	];
}
