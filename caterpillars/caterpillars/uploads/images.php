<?php

require_once("Image.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  //Resize to new width: $action = "resizeToWidth", $path, $newWidth;

  $post = json_decode(file_get_contents("php://input"), true);
  $action = $post['action'];
  $filename = $post['filename'];
  $newWidth = $post['newWidth'];

  
    //Resize to new width
    //return 404 no file found
    //return null other error
    //return 200 ok on sucess
    if (!is_null($action) && $action == "resizeToWidth" && !is_null($filename) && !is_null($newWidth)) {
        $image = Image::getByFilename($filename);
        if (!is_object($image)) {
            header("HTTP/1.1 404 Not Found");
            print("image file not found");
            exit();
        } elseif ($image == -1){
            header("HTTP/1.1 501 Not Implemented");
            print("file type not supported");
            exit();
        }
        $image->resizeToWidth($newWidth);
        $result = $image->save($filename, $image->getImage_type());
        if($result == -1){
            header("HTTP/1.1 501 Not Implemented");
            print("file type not supported");
            exit();
        }
        header("HTTP/1.1 200 ok");
        print("resize successful");
        exit();
    }



    header("HTTP/1.1 400 Bad Request");
    print("Format not recognized");
    exit();
}
?>