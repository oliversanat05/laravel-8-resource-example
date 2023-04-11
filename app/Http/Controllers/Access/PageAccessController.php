<?php

namespace App\Http\Controllers\Access;

use App\Traits\ApiResponse;
use App\Models\Access\Pages;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PageAccessController extends Controller
{
    use ApiResponse;
    public function index()
    {
        $pageAccess = Pages::whereStatus(true)->with(['roles'])->get();
        return response()->json($pageAccess);
    }
}
