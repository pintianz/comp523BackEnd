<?php

//By Derek Gu
require_once("User.php");

class Admin {

    private $siteID;
    private $userID;
    private $isValid;

    private function __construct( $userID, $siteID, $isValid) {
        $this->siteID = $siteID;
        $this->userID = $userID;
        $this->isValid = $isValid;
    }
    
    //find an admin by user id
    //return admin obj if success
    //return null if DB error
    //return -1 if userID does not belong to an admin
    public static function findByUserID($userID) {
        $mysqli = new mysqli("pocketprotection.org", "pocket14_pt", "password123", "pocket14_catepillarTest");
        if ($mysqli->connect_errno) {
            //echo $mysqli->connect_error;
            return null;
        }
        
        $result = $mysqli->query("SELECT * FROM tbl_siteAdmin WHERE userID ='" . $userID . "'");
        if ($result) {

            if (0 == $result->num_rows) {
                //Not an administrator
                return -1;
            }
            $admin_info = $result->fetch_array();
            return new Admin(intval($admin_info['userID']), intval($admin_info['siteID']), intval($admin_info['isValid']));
        } 
        else {
            //echo $mysqli->error;
            return null;
        }
    }

    
    public static function findByEmail($email){
        $user = User::find($email);
        
        if(is_null($user)){
            return null;
        }
        
        $admin = self::findByUserID($user->getID());
        
        return $admin;
    }
    
    public function getUserID(){
        return $this->userID;
    }
    
    public function getSiteID(){
        return $this->siteID;
    }
    
    public function isValid(){
        return $this->isValid;
    }
    
    public function getJSON(){
        $json_obj = array(
            "userID"=> $this->userID,
            "siteID"=> $this->siteID,
            "isValid"=>  $this->isValid
        );
        return json_encode($json_obj);
    }
}

?>