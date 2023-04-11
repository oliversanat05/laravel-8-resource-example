<?php

namespace App\Models\Profile;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    public $primaryKey = 'addressId';
    protected $table = 'address';

    protected $fillable = ['streetAddress1', 'addressTypeId', 'streetAddress2', 'city', 'state', 'zip', 'country', 'profileId'];
    public $timestamps = true;

}
