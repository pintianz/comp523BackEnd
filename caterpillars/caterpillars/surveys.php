<?php

//By Derek Gu
require_once("Survey_full.php");

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $post = json_decode(file_get_contents("php://input"), true);
    
  //Get all survey given siteID: $action == "getAllBySiteID", $siteID
  //Get all survey given array of siteIDs: $action == "getAllBySiteIDArray", $siteIDs (array of)
  //mark survey as invalid: $action = "markInvalid", surveyID
  
    $action = $post['action'];
    $siteID = $post['siteID'];
    $siteIDs = $post['siteIDs'];
    $surveyID = $post['surveyID'];
    $startDate = $post['startDate'];
    $endDate = $post['endDate'];
    
    if($action == "getAllBySiteID" && !is_null($siteID)){
        $surveys = Survey::getAllBySiteID($siteID, $startDate, $endDate);
        if(!$surveys){
            header("HTTP/1.1 404 Not Found");
            print("Resource requested not found");
            exit();
        }
        
        foreach($surveys as $survey){
            if($survey->isValid()){
                $json_obj[] = $survey->getJSON();
            }
        }
        
        //No valid surveys found
        if(count($json_obj) == 0){
            header("HTTP/1.1 404 Not Found");
            print("Resource requested not found");
            exit();
        }
        
        header("Content-type: application/json");
        print(json_encode($json_obj));
        exit();
    }
    
    if($action == "getAllBySiteIDArray" && !is_null($siteIDs)){
        $surveys = Survey::getAllBySiteIDArray($siteIDs,  $startDate, $endDate);
        if(!$surveys){
            header("HTTP/1.1 404 Not Found");
            print("Resource requested not found");
            exit();
        }
        
        foreach($surveys as $survey){
            if($survey->isValid()){
                $json_obj[] = $survey->getJSON();
            }
        }
        
        //No valid surveys found
        if(count($json_obj) == 0){
            header("HTTP/1.1 404 Not Found");
            print("Resource requested not found");
            exit();
        }
        
        header("Content-type: application/json");
        print(json_encode($json_obj));
        exit();
    }
    
    if($action == "markInvalid" && !is_null($surveyID)){
        $result = Survey::markInvalid($surveyID);
        if(!$result){
            header("HTTP/1.1 404 Not Found");
            print("Resource requested not found");
            exit();
        }
        
        header("HTTP/1.1 200 OK");
        print("Successfully marked survey invalid.");
        exit();
    }
}

header("HTTP/1.1 400 Bad Request");
print("Format Not Recognized.");
exit();
?>