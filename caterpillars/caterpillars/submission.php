<?php
//depricated, please see submission_full
require_once("Survey.php");
require_once("Order.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //Survey Submission: $type = "survey", $siteID, $userID;
    //Order Submission: $type = "order", $surveyID;

    $post = json_decode(file_get_contents("php://input"), true);
    $type = $post['type'];

    $siteID = $post['siteID'];
    $userID = $post['userID'];

    $surveyID = $post['surveyID'];

    //new survey submission
    if (!is_null($type) && $type == "survey" && !is_null($siteID) && !is_null($userID)) {
        $survey = Survey::create($siteID, $userID);

        if (is_null($survey)) {

            header("HTTP/1.1 500 Internal Server Error");
            print("Survey submission failed");
            exit();
        }
        
         if ($survey == -1) {
            header("HTTP/1.1 404 Not Found");
            print("Invalid siteID or userID");
            exit();
        }

        header("Content-type: application/json");
        print(json_encode($survey->getJSON()));
        exit();
    }

    //new order submission
    if (!is_null($type) && $type == "order" && !is_null($surveyID)) {
        $order = Order::create($surveyID);

        if (is_null($order)) {
            header("HTTP/1.1 500 Internal Server Error");
            print("Order submission failed");
            exit();
        }


        if ($order == -1) {
            header("HTTP/1.1 404 Not Found");
            print("Invalid surveyID");
            exit();
        }


        header("Content-type: application/json");
        print(json_encode($order->getJSON()));
        exit();
    }



    header("HTTP/1.1 400 Bad Request");
    print("Format not recognized");
    exit();
}
?>