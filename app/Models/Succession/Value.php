<?php

namespace App\Models\Succession;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Config;

class Value extends Model
{
    use HasFactory;
    protected $table = 'value';
    public $primaryKey = 'valueId';

    protected $fillable = ['vMapId', 'valueTitle', 'displayOrder', 'valueStatement', 'isDelete', 'valueUrl', 'completed', 'statusId', 'completedDate'];

    public function kpis()
    {
        return $this->hasMany('App\Models\Succession\Kpi', 'valueId', 'valueId')->with('trackingData');
        // ->with('trackingData', 'strategy')->whereIsdelete(false)->orderBy('kOrder');;
    }
}
