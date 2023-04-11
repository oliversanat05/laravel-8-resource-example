<?php

namespace App\Services\ActionItemService;

use App\Models\ActionItem\ActionItemArea;
use App\Models\ActionItem\ActionItemData;
use Auth;

class ActionItemService
{

    /**
     * for fetching the action item data
     *
     * @return void
     */
    public function getActionItemData()
    {
        $data = ActionItemArea::with(['actionItem' => function ($query) {
            $query->with(['actionItemData' => function ($query) {
                $query->where('userId', Auth::user()->user_id);
            }]);
        }])->get();

        return $data;
    }

    /**
     * for storing the action item data
     *
     * @param [type] $data
     * @return void
     */
    public function storeActionItemData($data)
    {
        $dataArray = [];

        $actionItems = '';

        foreach ($data as $key => $value) {
            $question = explode('-', $key);

            $controlType = $question[0];

            $item['userId'] = Auth::user()->user_id;
            $item['itemCompleted'] = ($controlType == 'check') && $value ? 1 : 0;
            $item['itemNotes'] = ($controlType != 'check') && !empty($value) ? $value : null;

            $item['actionItemId'] = $question[1];
            $checkSurveyExists = ActionItemData::whereIn('actionItemId', [$question[1]])
                ->where('userId', Auth::user()->user_id)->exists();

            if ($checkSurveyExists) {
                $actionItems = ActionItemData::whereIn('actionItemId', [$question[1]])
                    ->where('userId', Auth::user()->user_id)
                    ->update($item);

            } else {
                $actionItems = ActionItemData::create($item);
            }

        }

        return $actionItems;
    }
}
