<?php

/**
 * This function will filter the delegate and array the id only
 * @param NA
 * @return object
 */
function setDelegate($delegate) {

	$dataArray = array();
	if (is_array($delegate)):
		foreach ($delegate AS $index => $value):
			$del = is_array($value) ? $value['value'] : $value;
			array_push($dataArray, $del);
		endforeach;
	else:
		array_push($dataArray, $delegate);
	endif;
	return ($dataArray) ? implode(',', $dataArray) : Auth::user()->user_id;
}
