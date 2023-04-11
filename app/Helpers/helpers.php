<?php

/**
 * this function is used for debug the code or print the data
 * @param data
 * @return NA
 */

function aprint($data)
{
    echo '<pre>';
    print_r($data);
    echo '<pre>';
}
function between($x, $a, $b)
{
    return $a <= $x && $x <= $b;
}

function isDataValid($min_value,$max_value)
{
    if(is_int($min_value)&&is_int($max_value)){
        if($min_value>=0 && $max_value>0)
          return $min_value<$max_value;
    }
    return false;
}

// Function to return the nearest value in an array
function checkNearest($score,$valueArray) {
    $temp = abs($valueArray[0] - $score);
    $closest = 0;
    if ($valueArray[0] > $score) return $valueArray[0];
    if ($valueArray[count($valueArray) - 1] < $score) return $valueArray[count($valueArray) - 1];
    foreach ($valueArray as $key => $value) {
        if (abs($valueArray[$key] - $score) < $temp) {
            $temp = abs($valueArray[$key] - $score);
            $closest = $valueArray[$key];
        }
    }
    return $closest;
}

//function to check user tier type
function userTierType($score,$tiers){
    $tierType = null;
    $tierArray = [];
    foreach ($tiers as $key => $value) {
        if(($value['min_value'] <= $score) && ($score <= $value['max_value'])){
            $tierType = $value['tier_type'];
            break;
        }
        array_push($tierArray,$value['min_value']);
        array_push($tierArray,$value['max_value']);
    }
    if($tierType){
        return $tierType;
    }else{
        sort($tierArray);
        $closest = checkNearest($score,$tierArray);
        foreach ($tiers as $key => $value) {
            if(($value['min_value'] == $closest) || ($closest == $value['max_value'])){
                $tierType = $value['tier_type'];
                break;
            }
        }
       return $tierType;
    }
}
// Function to check tiers value doesn't collide with each other

function tiersValueValidater(iterable $data)
{
    $valueArray = [];
    foreach ($data['tiers'] as $key => $value) {
        foreach ($data['tiers'] as $key1 => $value1) {

            if ($key1 == $key) {
                continue;
            }
            if(!isDataValid( $value['min_value'], $value['max_value'])){
                return false;
            }
            if (between($value1['min_value'], $value['min_value'], $value['max_value']) || between($value1['max_value'], $value['min_value'], $value['max_value'])) {
                return false;
            }
        }
    }
    return true;
}



function formatClientScoreData(iterable $data)
{
    $formatedData = [];
    $formatedData1=[];
    $lastId=null;
    $headings = [];

    foreach ($data as $key => $value) {

        if($lastId==null){
            $lastId=$value["metric_id"];
        }
        if($value["metric_id"]==$lastId){
            $Data=[
                    "metric_id"=>$value["metric_id"],
                    "listing_id"=>$value["listing_id"],
                    "metric_title"=>$value["metric_area"][0]["title"],
                    "metric_type"=>$value["metric_area"][0]["metric_type"],
            ];
            $heading=[
                    "metric_heading_id"=>$value["metric_heading_id"],
                    "metric_value_id"=>$value["id"],
                    "metric_id"=>$value["metric_id"],
                    "metric_value"=>$value["metric_value"]
            ];
            array_push($headings,$heading);
            $lastId=$value["metric_id"];
        }
        if($key==count($data)-1){
            $lastId=null;
        }
        if($value["metric_id"]!=$lastId){
            $lastId=$value["metric_id"];
            $formatedData1=$Data;
            $formatedData1["metric_data"]=$headings;
            array_push($formatedData,$formatedData1);
            $headings=[];
            $Data=[];
            $heading=[];
            $heading=[
                "metric_heading_id"=>$value["metric_heading_id"],
                "metric_value_id"=>$value["id"],
                "metric_id"=>$value["metric_id"],
                "metric_value"=>$value["metric_value"]
            ];
            array_push($headings,$heading);
        }
    }
        return $formatedData;
}

function checkReduntant ($val,$data){
    $filtered = array_filter($data, function($value) use ($val) {
        return $value['idea_id'] == $val;
    });
    return $filtered;
}

function formatClientGridData(iterable $data)
{
    $formatedData= $formatedData1 =$headings = [];
    $lastId=null;
    foreach ($data as $key => $value) {
        // at first intializing the last id to value id
        if($lastId==null){
            $lastId=$value["idea_id"];
        }
        // First time it will match
        if($value["idea_id"]==$lastId){
            $Data= createDataForGrid($value);
            $heading=createHeadingForGrid($value);
            array_push($headings,$heading);
            $lastId=$value["idea_id"];
        }
        if($key==count($data)-1){
            $lastId=null;
        }
        if($value["idea_id"]!=$lastId){
            $lastId=$value["idea_id"];
            $formatedData1=$Data;
            $formatedData1["tiers"]=$headings;
            array_push($formatedData,$formatedData1);
            $headings=$Data=$heading=[];

            $heading=createHeadingForGrid($value);
            array_push($headings,$heading);
            // If only 1 value is present for a particular idea and it's the last iteration of the loop
            // Running this condition
            if($key == (count($data) - 1) && count(checkReduntant($lastId,$data)) == 1 && count($data) >= 2 ){
                $Data= createDataForGrid($value);
                $heading=createHeadingForGrid($value);
                $formatedData1=$Data;
                $formatedData1["tiers"]=$headings;
                array_push($formatedData,$formatedData1);
            }
        }
    }
    return $formatedData;
}

function createDataForGrid($value)
{
   return [
        "id"=>$value["id"],
        "idea_id"=>$value["idea_id"],
        "responsible_person_id"=>$value["responsible_person_id"],
        "idea_title"=>$value["ideas"]["idea_title"]
    ];
}

function createHeadingForGrid($value)
{

    return [
        "tier_id"=>$value["tier_id"],
        "status"=>$value["status"],
        ];
}

function formatClientGapData(iterable $data,$tiers){

    // Running Iteration on data
    foreach ($data as $key => $value) {
        $heading=[];
        $index = -1;
        $postion = 0;
        $tracker_id = null;
        $score=0;

       //Running loop in wow tracker
        foreach ($value['wow_tracker'] as $key1 => $value1) {

            if($tracker_id == null || $tracker_id != $value1['tracker_id']){
                $tempData = [
                    'id'=>$value1['tracker_heading']['id'],
                    'month'=>$value1['tracker_heading']['month']
                ];
                $index = $index + 1;
                $postion = 0;
                array_push($heading,$tempData);
                $tracker_id = $value1['tracker_id'];
            }
            $idea = [
                'id'=>$value1['ideas']['id'],
                'idea_title'=>$value1['ideas']['idea_title']
            ];

            $heading[$index]['idea'][$postion] = $idea;
            $postion = $postion + 1;
       }

       //running loop in client score
       foreach ($value['client_score']  as $key3 => $value2) {
                $score+=$value2['score'];
        }
        unset($data[$key]['wow_tracker']);
        unset($data[$key]['client_score']);

        $data[$key]["client_total_score"]=$score;
        $data[$key]["tier_type"]= userTierType($score,$tiers);
        $data[$key]['headings']=$heading;
    }
     return $data;
}

// Code to format  wow Tracker Ideas List
function formatIdeaDataWowTracker(iterable $data){
    foreach ($data as $key => $value) {
       foreach ($value['relational_grid'] as $key1 => $value1) {
           $data[$key][$value1['tier']['tier_type']]=$value1['status'];
       }
       unset($data[$key]['relational_grid']);
    }
    return $data;
}

/**
 * parseString this function will use to validate the user input
 * @param data
 * @return $data
 */
function parseString($data)
{
  $string = $data;
  try {
    $string = html_entity_decode($string,ENT_QUOTES);
    $string = trim(strip_tags($string));
  } catch (\Exception $e) {
    $string = trim(strip_tags($string));
  }
  return $string;
}
