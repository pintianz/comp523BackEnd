<?php

//By: Pintian Zhang

require_once('User.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST'){

  //Create: $email, $password, $name
  //Login: $email, $password
  //Find User Obj by Email: $email
  //Find User Obj by userID: $id
  //Mark User as Invalid: $id, $mark_invalid = 1

  $post = json_decode(file_get_contents("php://input"), true);

  $id = $post['userID'];
  $email = $post['email'];

  $password = $post['password'];

  $name = $post['name'];
  
  $mark_invalid = $post['mark_invalid'];

  //Create user
  if(!is_null($email) && !is_null($password) && !is_null($name)){

    $user = User::create($email, $password, $name);

    if($user == -1){

      header("HTTP/1.1 409 Conflict");
      print("Email address is already registered");
      exit();

    }

    if (is_null($user)){

      header("HTTP/1.1 500 Internal Server Error");
      print("User creation failed");
      exit();
    }

  header("Content-type: application/json");    
  print($user->getJSON());
  exit();

  }

  //Login
  if(!is_null($email) && !is_null($password) && is_null($name)){

    $validate = User::validatePassword($email, $password);

    if (is_null($validate)){

      header("HTTP/1.1 404 Not Found");
      print("Resource requested not found");
      exit();
    }

    if($validate == -1){

      header("Content-type: application/json"); 
      $json_rep = array();
      $json_rep['valid'] = false;
      print(json_encode($json_rep));
      exit();

    }

    header("Content-type: application/json");    
    print(json_encode($validate));
    exit();



  }
  
  //Get UserObj by email
  if(!is_null($email) && is_null($password) && is_null($name)){


    $user = User::find($email);


    if (is_null($user)){

      header("HTTP/1.1 404 Not Found");
      print("Resource requested not found");
      exit();
    }

    
    header("Content-type: application/json");    
    print($user->getJSON());
    exit();



  }
  
  //Get User Obj by looking up userID
  if(!is_null($id) && is_null($mark_invalid)){
      $user = User::findByID($id);


    if (is_null($user)){

      header("HTTP/1.1 404 Not Found");
      print("Resource requested not found");
      exit();
    }

    
    header("Content-type: application/json");    
    print($user->getJSON());
    exit();
  }
  
  //mark user as invalid
  if($mark_invalid == 1 && !is_null($id)){
      $result = User::markInvalid($id);
      
      if(!$result){
            header("HTTP/1.1 404 Not Found");
            print("Resource requested not found");
            exit();
        }
        
        header("HTTP/1.1 200 OK");
        print("Successfully marked user invalid.");
        exit();
  }



  header("HTTP/1.1 400 Bad Request");
  print("Format not recognized");
  exit();

}

//acivate user: $id, $activate
if ($_SERVER['REQUEST_METHOD'] == 'GET'){

  //Activate: $id, $activate

  //$get = json_decode(file_get_contents("php://input"), true);
  $id = intval($_GET['userID']);
  $activate = intval($_GET['activate']);



  //Activate: $id, $activate
  if (!is_null($id) && !is_null($activate)){
    $result = User::activate($id);

    //header("Content-type: application/json");    
    //print(json_encode($result));
    header('Content-Type:text/html');
		print("<h1>Welcome to Caterpillars Count!</h1>
				<p>Your account has been <b>successfully activated</b>.<p>");
    exit();
  }
  

  header("HTTP/1.1 400 Bad Request");
  print("Format not recognized");
  exit();

}


?>