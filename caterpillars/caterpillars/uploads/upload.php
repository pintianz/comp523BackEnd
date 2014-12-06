<?php
$uploaddir = '.';//Uploading to same directory as PHP file
$file = basename($_FILES['userfile']['name']);
$uploadFile = $file;
$randomNumber = rand(0, 99999);

// $newName = $uploadDir . $randomNumber . $uploadFile;
$newName = $uploadDir . $uploadFile;

if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
	echo "Temp file uploaded.";
} else {
	echo "Temp file not uploaded.";
}

// if ($_FILES['userfile']['size']> 300000) {
// 	exit("Your file is too large.");
// }

move_uploaded_file($_FILES['userfile']['tmp_name'], $newName);

// if (move_uploaded_file($_FILES['userfile']['tmp_name'], $newName)) {
// 	$postsize = ini_get('post_max_size');//Not necessary, I was using these
// 	$canupload = ini_get('file_uploads');//server variables to see what was
// 	$tempdir = ini_get('upload_tmp_dir');//going wrong.
// 	$maxsize = ini_get('upload_max_filesize');
// 	echo "http://www.iroboticshowoff.com/dir/{$file}" . "\r\n" . $_FILES['userfile']['size'] . "\r\n" . $_FILES['userfile']['type'];
// }
?>