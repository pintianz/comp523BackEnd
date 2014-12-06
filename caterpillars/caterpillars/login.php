<?php

//By Derek Gu
/*
 * Server side that monitors login status of the admin tool
 * Use both session and cookies. Cookies time out in an hour.
 * Cookies store userID of admin.
 */

session_start();
require_once("User.php");

//Log out
if ($_GET['signout'] == 1) {
    $_SESSION['authenticated'] = false;
    setcookie("user", null, time() - 3600);
    print(htmlspecialchars($_REQUEST['resource']));
    exit();
}

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] == true) {
    if (!$_COOKIE['user']) {
        $_SESSION['authenticated'] = false;
        header("HTTP/1.1 403 Forbidden");
        print("You do not have access to this website.");
        exit();
    }

    $userID = htmlspecialchars($_COOKIE['user']);
    $user = User::findByID($userID);
    if (is_null($user)) {
        header("HTTP/1.1 500 Internal Server Error");
        print("Internal Server Error");
        $_SESSION['authenticated'] = false;
        exit();
    }

    /*
     * Underneath are functions that can only be performed when user is logged in
     */
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        header("Content-type: application/json");
        print($user->getJSON());
        exit();
    }
}

/*
 * Login: email, password
 */

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $post = json_decode(file_get_contents("php://input"), true);
    $email = $post['email'];
    $password = $post['password'];

    if (!is_null($password) && !is_null($email)) {

        $validate = User::validatePassword($email, $password);

        if (is_null($validate)) {

            header("HTTP/1.1 404 Not Found");
            print("Resource requested not found");
            exit();
        }

        if ($validate['validUser'] == 1 && $validate['validPw'] == 1 && $validate['active'] == 1) {
            //Login success
            
            $user = User::find($email);
            if(!$user){
                header("HTTP/1.1 500 Internal Server Error");
                print("Internal Server Error");
                exit();
            }
                
            $_SESSION['authenticated'] = true;
            setcookie('user', intval($user->getID()), time() + 3600);
            header("Content-type: application/json");
            print(json_encode($validate));
            exit();
        } else {
            header("Content-type: application/json");
            print(json_encode($validate));
            exit();
        }
    }
}

header("HTTP/1.1 400 Bad Request");
    print("Format not recognized");
    exit();
?>