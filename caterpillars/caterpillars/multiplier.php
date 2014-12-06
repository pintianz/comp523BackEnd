<?php
//Demo code
//By Justin Forsyth

require_once('User.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST'){

  	//multiply by 5: $value


	//usually use $_POST['value'] but my web server is weird
	$post = json_decode(file_get_contents("php://input"), true);
	$value = $post['value'];


	$result = $value * 5;



	//header("Content-type: application/json");    
	print($result);
	exit();



  }
  

?>
