<?php

//By Pintian Zhang
require_once('Site.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST'){

  //Get all site (short version, has sites & states): $action= "getAllSiteState"
  //Get all site (full version): $action= "getAll"
  //Get one site by ID: $action= "getOneByID", $siteID
  //Create new site: $action= "create", $email,$password,$siteName,$siteLat,$siteLong,$siteDescription,$siteState,$sitePassword
  //check site password: action="checkSitePassword", $siteID,$sitePasswordCheck
  //change site password: action="changeSitePassword", $email,$password,$siteID,$newSitePassword
  
  $post = json_decode(file_get_contents("php://input"), true);

  $action = $post['action'];
  $email = $post['email'];
  $password = $post['password'];
  $siteName = $post['siteName'];
  $siteLat = $post['siteLat'];
  $siteLong = $post['siteLong'];
  $siteDescription = $post['siteDescription'];
  $siteState = $post['siteState'];
  $sitePassword = $post['sitePassword'];
  $sitePasswordCheck = $post['sitePasswordCheck'];
  
  $siteID = $post['siteID'];
  $newSitePassword = $post['newSitePassword'];

  //Get all site & states
  if(!is_null($action) && ($action == "getAllSiteState" || $action == "getAll")){

    $site = Site::getAll($action);

    if($site == -1){

      header("HTTP/1.1 501 Not Implemented");
      print("un-supported action");
      exit();

    }

    if (is_null($site)){

      header("HTTP/1.1 500 Internal Server Error");
      print("Get sites failed");
      exit();
    }

  header("Content-type: application/json");    
  print(json_encode($site));
  exit();

  }
  
  //Get one site
  if(!is_null($action) && $action == "getOneByID"){

    $site = Site::find($siteID);

    if($site == -1){
      header("HTTP/1.1 501 Not Implemented");
      print("un-supported action");
      exit();
    }

    if (is_null($site)){
      header("HTTP/1.1 500 Internal Server Error");
      print("Get sites failed");
      exit();
    }

  header("Content-type: application/json");    
  print(json_encode($site->getJSON()));
  exit();

  }

   //Create new site
  if(!is_null($action) && $action == "create"){

    $site = Site::create($email,$password,$siteName,$siteLat,$siteLong,$siteDescription,$siteState,$sitePassword);
    if($site == -1){

      header("HTTP/1.1 401 Unauthorized");
      print("User not authorized");
      exit();

    }
    if (is_null($site)){

      header("HTTP/1.1 500 Internal Server Error");
      print("sites creation failed");
      exit();
    }

  header("Content-type: application/json");    
  print(json_encode($site->getJSON()));
  exit();

  }
  
  //check site password
  if(!is_null($action) && $action == "checkSitePassword" && !is_null($siteID) && !is_null($sitePasswordCheck)){
    $result = Site::checkSitePassword($siteID,$sitePasswordCheck);
    if (is_null($result)){

      header("HTTP/1.1 500 Internal Server Error");
      print("site password change failed");
      exit();
    }
  header("Content-type: application/json");    
  print(json_encode($result));
  exit();
  }
  
  //modify site password
  if(!is_null($action) && $action == "changeSitePassword"){
    $site = Site::changeSitePassword($email,$password,$siteID,$newSitePassword);
    if($site == -1){

      header("HTTP/1.1 401 Unauthorized");
      print("User not authorized");
      exit();

    }
    if (is_null($site)){

      header("HTTP/1.1 500 Internal Server Error");
      print("site password change failed");
      exit();
    }
  header("HTTP/1.1 200 OK");    
  print("Site Pass word change successful");
  exit();
  }

  header("HTTP/1.1 400 Bad Request");
  print("Format not recognized");
  exit();

}


?>