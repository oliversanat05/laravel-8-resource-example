<?php

namespace App\Http\Controllers\Api\V1\VMap;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\VMapActionServices\VMapUpdateOrderService;
use Lang;
use App\Traits\ApiResponse;

class VMapUpdateOrderController extends Controller
{

    use ApiResponse;
    private $order;
    public function __construct()
    {
        $this->order = new VMapUpdateOrderService();
    }

    /**
     * This function will update the order of the
     * vmap levels in the database
     * @param NA
     * @response JSON
     */
    public function update()
    {
        $response = $this->order->updateVmapOrder();

        if($response){
            return $this->successApiResponse(__('core.vMapOrderUpdate'));
        }else{
            return $this->unprocessableApiResponse(__('core.vMapOrderUpdateError'));
        }

    }
}
