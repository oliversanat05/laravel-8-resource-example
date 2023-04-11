<?php

namespace App\Http\Controllers\Api\V1\Pages;

use App\Http\Controllers\Controller;
use App\Models\Access\Pages;
use App\Traits\ApiResponse;

class PageController extends Controller {

    use ApiResponse;

	/**
	 * This route will get the page list
	 */
	public function getPageList() {
		$pages = Pages::pages();

		return $this->successApiResponse(__('core.pageDetailsFetched'), $pages);
	}
}
