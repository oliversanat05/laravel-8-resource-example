<?php
namespace App\Services;

use Auth;
use Config;
use Request;
use Carbon\Carbon;
use App\Models\CallTime;
use App\Models\DeletedItem;
use App\Models\Succession\Kpi;
use App\Models\Succession\VMap;
use App\Models\Succession\Value;
use App\Models\Succession\Project;
use App\Models\Succession\Strategy;
use App\Models\Profile\CommunicationOption;
use App\Models\Succession\CriticalActivity;

class FilterSystem {

	/**
	 * This function will get the deleted items
	 * @param request
	 * @return object
	 */
	public function getAllDeletedVmaps() {
		$date = Carbon::now();
		return DeletedItem::where('userId', Auth::user()->user_id)->whereRaw('STR_TO_DATE(created_at,"%Y-%m-%d") = "' . $date->format('Y-m-d') . '"')->get();
	}

	/**
	 *
	 * This function will rollback all
	 * the deleted vMaps
	 */
	public function undoVMap($type, $id) {

        // dd($type, $id);

		$response = false;

        $checkId = DeletedItem::whereItemid($id);

        $deletedItem = $checkId->first();

		try {

			if ($deletedItem->tableName) {

				$dataArray['isDelete'] = ($deletedItem->tableName == 'value' || $deletedItem->tableName == 'level1' || $deletedItem->tableName == 'vMap') ? true : false;

                switch ($deletedItem->tableName) {
                    case 'level1':
                    case 'value':
                        $response   =  Value::where('valueId', $deletedItem->tableId)->update($dataArray);
                        break;
                    case 'level2':
                    case 'kpi':
                        $response   =  Kpi::where('kpiId', $deletedItem->tableId)->update($dataArray);
                        break;
                    case 'level3':
                    case 'strategy':
                        $response   =  Strategy::where('strategyId', $deletedItem->tableId)->update($dataArray);
                        break;
                    case 'level4':
                    case 'project':
                        $response   =  Project::where('projectId', $deletedItem->tableId)->update($dataArray);
                        break;
                    case 'level5':
                    case 'criticalActivity':
                        $response   =  CriticalActivity::where('criticalActivityId', $deletedItem->tableId)->update($dataArray);
                        break;
                    case 'vMap':
                        $response   =  VMap::where('vMapId', $deletedItem->tableId)->update($dataArray);
                        break;
                    default:
                        break;
                    }
			}

			return $response;

		} catch (\Exception $th) {

            if(!$checkId->exists()){
                return $th->getMessage();
            }
            return false;
		}

		return null;
	}

    /**
     * This function will save the deleted item in table
     * @param NA
     * @return object
     */
    public function setDeletedItems($data, $id, $type){

        $deletedLevel= new DeletedItem;

        $deletedLevel->deletedItem($data, $id, $type);
    }

    /**
     * This function will delete item
     * @param request
     * @return object
     */

    public function getDeleteItems(int $id){
        DeletedItem::whereItemid($id)->delete();
    }

    /**
     * this function will get the delegate list on the base of the logged in user
     * @param NA
     * @return object
     */
    public function getDelegateList()
    {
        return CommunicationOption::where('userId', Auth::user()->user_id)->get();
    }

    /**
     * This function will get the calltime
     * @param NA
     * @return object
     */
    public function getCallTime()
    {
        return CallTime::get();
    }

    public function createDelegateIfNotExists($data)
    {
        $getDelegates = VMapSystem::getDelegateOption();

        if(!$getDelegates->count()){
            DelegateUser::createDelegate($data);
        }else{
            return "false";
        }
    }

}
