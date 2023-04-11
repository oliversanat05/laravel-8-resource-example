<?php
namespace App\Services;
use App\Models\ActivityTitle;

class ActivityTypeTitleList {

    public function __construct()
    {
        $this->model = new ActivityTitle();
    }
	/**
	 * method is used to get the activity type title list
	 */
	public function getActivityTitleList($vMapIds, $pageSize) {
		return ActivityTitle::whereIn('vmvid', $vMapIds)->paginate($pageSize);
	}

	/**
	 * method is used to get the activity title
	 */
	public function getActivityTitleForVmapId($vMapId) {
		return ActivityTitle::whereVmvid($vMapId)->first();
	}

    /**
     * This function will update the activity title for the given vmap
     * @param $data
     */
	public function updateActivityTitle($data, $id) {

        $resultArray = [];
        $result = $this->model->getActivtyData($data, $id);

        if($result){
            return $result;
        }else{
            return false;
        }
	}

    /**
     * This function will delete the activity from the database
     * @param $id
     * @return bool
     */
    public function deleteActivityTitle($id)
    {
        return ActivityTitle::find($id)->delete();
    }
}
