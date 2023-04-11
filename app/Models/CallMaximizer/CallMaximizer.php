<?php

namespace App\Models\CallMaximizer;

use Illuminate\Support\Facades\Lang;
use Illuminate\Database\Eloquent\Model;
use App\Models\CallMaximizer\CallMaximizerData;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CallMaximizer extends Model
{
    protected $table = 'callMaximizer';
    public $primaryKey = 'callMaximizerId';

    /**
     * relation between callMaximizer and controls
     */
    public function controlType(){
        return $this->hasOne('App\Models\CallMaximizer\ControlType', 'controlTypeId', 'controlTypeId');
    }

    /**
     * Get all of the callMaximizerData for the CallMaximizer
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function callMaximizerData()
    {
        return $this->hasOne(CallMaximizerData::class, 'callMaximizerId', 'callMaximizerId');
    }

    public static function processCoachCall()
    {
        $coachCall = Lang::get('core.coachCall');

        $sectionArray = [];
        foreach ($coachCall as $key => $values) {

            foreach ($values as $index => $value) {

                $dataArray  = [];

                if( is_array($value) )
                {   
                    $dataKey    = array_key_first($value);
                    $dataValues = array_values($value);

                    foreach ($dataValues as $niddle => $subsets) 
                    {
                        $sets    = [];
                        foreach ($subsets as $key => $subset) {
                            
                            $dataArray['key']      = $key;
                            $dataArray['desc']     = $subset;

                            $sets[]             = $dataArray;
                        }

                        $dataArray['key']      = $index;
                        $dataArray['subset']    = true;
                        $dataArray['desc']    = $dataKey;
                        $dataArray['subsets']   = $sets;
                        $sectionArray[$key][] = $dataArray;
                    }
                }
                else{

                    $dataArray['key']      = $index;
                    $dataArray['desc']      = $value;

                    $sectionArray[$key][] = $dataArray;
                }
            }


        }

        return collect($sectionArray)->values();
    }
}
