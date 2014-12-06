<?php

//By: Pintian Zhang
require_once("Survey_full.php");
require_once("Order_full.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  //Survey Submission: $type = "survey", $siteID, $userID;
  //Order Submission: $type = "order", $surveyID;

  $post = json_decode(file_get_contents("php://input"), true);
  $type = $post['type'];
	
  $siteID = $post['siteID'];
  $userID = $post['userID'];
	$surveyID = $post['surveyID'];
	//survey
	$circle = $post['circle'];
	$survey = $post['survey'];
	$timeStart = $post['timeStart'];
	$temperatureMin = $post['temperatureMin'];
	$temperatureMax = $post['temperatureMax'];
	$siteNotes = $post['siteNotes'];
	$plantSpecies = $post['plantSpecies'];
	$herbivory = $post['herbivory'];
	//order
	$orderArthropod = $post['orderArthropod'];
	$orderLength = $post['orderLength'];
	$orderNotes = $post['orderNotes'];
	$orderCount = $post['orderCount'];

  
    //new survey submission
    if (!is_null($type) && $type == "survey" && !is_null($siteID) && !is_null($userID)) {
        $surveyReturn = Survey::create($siteID, $userID, $circle, $survey, $timeStart, $temperatureMin, $temperatureMax, $siteNotes, $plantSpecies, $herbivory);
        if (is_null($surveyReturn)) {
            header("HTTP/1.1 500 Internal Server Error");
            print("Survey submission failed");
            exit();
        }
        if ($surveyReturn == -1) {
            header("HTTP/1.1 401 Unauthorized");
            print("User not authorized");
            exit();
        }
        header("Content-type: application/json");
        print(str_replace( '\/', '/', json_encode($surveyReturn->getJSONSimple())));
        exit();
    }

    //new order submission
    if (!is_null($type) && $type == "order" && !is_null($surveyID)) {
        $order = Order::create($surveyID, $userID, $orderArthropod, $orderLength, $orderNotes, $orderCount);
        if (is_null($order)) {
            header("HTTP/1.1 500 Internal Server Error");
            print("Order submission failed");
            exit();
        }
        if ($order == -1) {
            header("HTTP/1.1 401 Unauthorized");
            print("User not authorized");
            exit();
        }
        header("Content-type: application/json");
        print(str_replace( '\/', '/', json_encode($order->getJSONSimple())));
        exit();
    }



    header("HTTP/1.1 400 Bad Request");
    print("Format not recognized");
    exit();
}
?>