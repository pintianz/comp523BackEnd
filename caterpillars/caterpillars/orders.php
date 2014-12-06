<?php
//By Derek Gu
require_once ("Order_full.php");

    //get all order by durvey ID: action="getAllBySurveyID", $surveyID
    //mark order as invalid: $action == "markInvalid", $orderID
if($_SERVER['REQUEST_METHOD'] == "POST"){
    $post = json_decode(file_get_contents("php://input"), true);
    
    $action = $post['action'];
    $surveyID = $post['surveyID'];
    $orderID = $post['orderID'];
    
    //get all orders related to the provided survey ID
    if($action == "getAllBySurveyID" && !is_null($surveyID)){
        $orders = Order::getAllBySurveyID($surveyID);
        
        if(!$orders){
            header("HTTP/1.1 404 Not Found");
            print("Resource requested not found");
            exit();
        }
        
        foreach($orders as $order){
            if($order->isValid()){
                $json_obj[] = $order->getJSON();
            }
        }
        
        //No valid orders found
        if(count($json_obj) == 0){
            header("HTTP/1.1 404 Not Found");
            print("Resource requested not found");
            exit();
        }
        
        header("Content-type: application/json");
        print(json_encode($json_obj));
        exit();
    }
    
    if($action == "markInvalid" && !is_null($orderID)){
        $result = Order::markInvalid($orderID);
        if(!$result){
            header("HTTP/1.1 404 Not Found");
            print("Resource requested not found");
            exit();
        }
        
        header("HTTP/1.1 200 OK");
        print("Successfully marked order invalid.");
        exit();
    }
}

header("HTTP/1.1 400 Bad Request");
print("Format Not Recognized.");
exit();
?>