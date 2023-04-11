<?php

namespace App\Services\wowService\ResponsiblePersonService;

use App\Models\wowModels\Ideas;
use App\Models\wowModels\RelationalGrid;
use App\Models\wowModels\ResponsiblePerson;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ResponsiblePersonService
{
    /**
     * Showing all responsible persons
     * @param $data
     * @return \object
     */
    public function showPersons()
    {
        $userId = Auth::user()->user_id;
        $responsiblePerson = ResponsiblePerson::where('user_id',$userId)->get()->toArray();
        return $responsiblePerson;
    }

    /**
     * Store a new Person in database
     * @param $data
     * @return \object
     */
    public function createPersons($data)
    {
        $userId = Auth::user()->user_id;
        $data['user_id'] = $userId;
        $newPerson = ResponsiblePerson::create($data);
        return $newPerson;
    }

    /**
     * Update a Responsible person in Database
     * @param $data
     * @return \object
     */
    public function updatePerson($data,$id)
    {
        $updatedPerson = ResponsiblePerson::where('id',$id)->update($data);
        return $updatedPerson;
    }

     /**
     * Deleting Responsible person from database
     * @param $data
     * @return \object
     */
    public function deletePerson($id)
    {
        $deletePerson = ResponsiblePerson::where('id',$id)->delete();
        return $deletePerson;
    }
}
