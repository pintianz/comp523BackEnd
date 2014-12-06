<?php
//By Derek Gu
/*
 * Server side that monitors login status of the admin tool
 * Use both session and cookies. Cookies time out in 60 mins. 
 * Cookies store userID of admin.
 */

session_start();
require_once("Admin.php");
require_once("User.php");
require_once("Privilege.php");

//Log out
if ($_GET['signout'] == 1) {
    $_SESSION['authenticated'] = false;
    setcookie("siteAdmin", null, time() - 3600);
    setcookie("masterAdmin", null, time() - 3600);
    print(htmlspecialchars($_REQUEST['resource']));
    exit();
}

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] == true) {
    if (!$_COOKIE['masterAdmin'] && !$_COOKIE['siteAdmin']) {
        $_SESSION['authenticated'] = false;
        header("HTTP/1.1 403 Forbidden");
        print("You do not have access to this website.");
        exit();
    }

    
    if($_COOKIE['masterAdmin']){
        $userID = htmlspecialchars($_COOKIE['masterAdmin']);
        $admin = User::findByID($userID);
        if($admin->getPrivilegeLevel() < Privilege::getPrivilegeLevel("Master Admin")){
            $admin = -1;
        }
    }
    else if($_COOKIE['siteAdmin']){
         $userID = htmlspecialchars($_COOKIE['siteAdmin']);
         $admin =  Admin::findByUserID($userID);
    }
    
    if (is_null($admin)) {
        header("HTTP/1.1 500 Internal Server Error");
        print("Internal Server Error");
        $_SESSION['authenticated'] = false;
        exit();
    }

    if ($admin == -1) {
        header("HTTP/1.1 403 Forbidden");
        print("Not an Administrator");
        $_SESSION['authenticated'] = false;
        exit();
    }
    /*
     * Underneath are functions that can only be performed when user is logged in
     */

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        header("Content-type: application/json");
        print($admin->getJSON());
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
            /*
             *  Confirmed valid user. 
             *  Now check if user is site admin or master admin or normal user.
             */
            $user_obj = User::find($email);
            
            $isMaster = Privilege::isValidMasterAdmin($user_obj, $password);
            
            if (is_null($isMaster)) {
                header("HTTP/1.1 500 Internal Server Error");
                print("Internal Server Error");
                exit();
            }
            
            if ($isMaster == 1) {
                //Login success
                $_SESSION['authenticated'] = true;
                setcookie('masterAdmin', intval($user_obj->getID()), time() + 3600);
                setcookie("siteAdmin", null, time() - 3600);
                $result = array("isAdmin" => 1, "type" => "master");
                header("Content-type: application/json");
                print(json_encode($result));
                exit();
            }

            //Not a Master Admin
            //Now check if user is site admin
            $isSiteAdmin = Privilege::isValidSiteAdmin($user_obj, $password);

            if (is_null($isSiteAdmin)) {
                header("HTTP/1.1 500 Internal Server Error");
                print("Internal Server Error");
                exit();
            }

            if ($isSiteAdmin == 1) {
                //Login success
                $_SESSION['authenticated'] = true;
                setcookie('siteAdmin', intval($user_obj->getID()), time() + 3600);
                setcookie("masterAdmin", null, time() - 3600);
                $result = array("isAdmin" => 1, "type" => "site");
                header("Content-type: application/json");
                print(json_encode($result));
                exit();
            }
            
            header("HTTP/1.1 403 Forbidden");
            print($isSiteAdmin);
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