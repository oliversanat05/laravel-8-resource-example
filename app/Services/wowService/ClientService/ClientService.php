<?php

namespace App\Services\wowService\ClientService;

use App\Models\wowModels\Client;
use Illuminate\Support\Facades\Auth;

class ClientService
{

    /**
     * Function to create a client in a database
     *
     * @return \string clientId
     */
    public function createNewClient($request)
    {
        $userId = Auth::user()->user_id;

        $createdClient = Client::create([
            'name' => $request->name,
            'user_id' => $userId,
            'listing_id' => $request->listing_id,
            'address'=>$request->address,
            'birthday'=>$request->birthday,
            'aniversery'=>$request->aniversery,
        ]);
        return $createdClient;
    }


    /**
     * Get clients as per listing id
     *
     * @return \string clientId
     */
    public function getClientAsPerListing($listingId)
    {
        $getClientsAsPerListing = Client::where('listing_id', $listingId)
            ->orderBy('id', 'DESC')
            ->get();

        return $getClientsAsPerListing;
    }

    /**
     * Function to fetch all clients from database
     *
     * @return \string clientId
     */
    public function getAllClients()
    {
        $userId = Auth::user()->user_id;

        $getClients = Client::where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->get();

        return $getClients;
    }

    /**
     * Function to update client data in database
     *
     * @return \string clientId
     */
    public function updateClientName($id, $request)
    {
        $userId = Auth::user()->user_id;
        $updatedClient = Client::where([
            ['user_id', '=', $userId],
            ['id', '=', $id]])
            ->update([
                'name' => $request->name,
                'address'=>$request->address,
                'birthday'=>$request->birthday,
                'aniversery'=>$request->aniversery,
            ]);

        return $updatedClient;
    }


    /**
     * Function to delete a client from database
     *
     * @return \string clientId
     */
    public function deleteClient($id)
    {
        $deletedClient = Client::where('id', $id)->delete();
        return $deletedClient;
    }

}
