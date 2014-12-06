<?php
//By Pintian Zhang
/* 
 * Handles checking priviledge level of users
 */
class Privilege{
    
    //check user validity, password validity, and privilege level with respect to that of site admin
    //returns null if DB error
    //returns -1 criteria not met
    //returns 1 on pass
    public static function isValidSiteAdmin($userObj,$password){
        $mysqli = new mysqli("pocketprotection.org", "pocket14_pt", "password123", "pocket14_catepillarTest");
        if ($mysqli->connect_errno) {
            return null;
        }
        //check if user exist, user is valid, password is valid
        if (!is_object($userObj) || !($userObj->checkValid()) || !($userObj->checkSaltHash($password))){
            return -1;
        } 
        
        //get base privilege level
        $result = $mysqli->query("SELECT privilegeLevel FROM tbl_privilege WHERE privilegeName = 'Site Admin'");
        if(!$result){
            return null;
        }
        //check privilege level
        $baseLevel = $result->fetch_array();
        $baseLevel = intval($baseLevel['privilegeLevel']);
        if($userObj->getPrivilegeLevel() < $baseLevel){
            return -1;
        }
        return 1;
    }
    
    //check user's authority over a site
    //returns null if DB error
    //returns -1 criteria not met
    //returns 1 on pass
    public static function checkAuthorityOverSite($userObj,$siteID){
        $mysqli = new mysqli("pocketprotection.org", "pocket14_pt", "password123", "pocket14_catepillarTest");
        if ($mysqli->connect_errno) {
            return null;
        }
        //get base privilege level
        $result = $mysqli->query("SELECT privilegeLevel FROM tbl_privilege WHERE privilegeName = 'Site Admin'");
        if(!$result){
            return null;
        }
        $siteAdminLevel = $result->fetch_array();
        $siteAdminLevel = intval($siteAdminLevel['privilegeLevel']);
        //get master admin privilege level
        $result = $mysqli->query("SELECT privilegeLevel FROM tbl_privilege WHERE privilegeName = 'Master Admin'");
        if(!$result){
            return null;
        }
        $masterAdminLevel = $result->fetch_array();
        $masterAdminLevel = intval($masterAdminLevel['privilegeLevel']);
        //get siteAdmin record
        $siteAdminRecord = $mysqli->query("SELECT * FROM tbl_siteAdmin WHERE userID =".$userObj->getID());
        if(($userObj->getPrivilegeLevel() == masterAdminLevel) || ($userObj->getPrivilegeLevel() >= $siteAdminLevel && is_object($siteAdminRecord))){
            return 1;
        }
        return -1;
    }
    
    //check user validity, password validity, and privilege level with respect to that of site admin
    //returns null if DB error
    //returns -1 criteria not met
    //returns 1 on pass
    public static function isValidMasterAdmin($userObj,$password){
        $mysqli = new mysqli("pocketprotection.org", "pocket14_pt", "password123", "pocket14_catepillarTest");
        if ($mysqli->connect_errno) {
            return null;
        }
        //check if user exist, user is valid, password is valid
        if (!is_object($userObj) || !($userObj->checkValid()) || !($userObj->checkSaltHash($password))){
            return -1;
        } 
        
        //get base privilege level
        $result = $mysqli->query("SELECT privilegeLevel FROM tbl_privilege WHERE privilegeName = 'Master Admin'");
        if(!$result){
            return null;
        }
        //check privilege level
        $baseLevel = $result->fetch_array();
        $baseLevel = intval($baseLevel['privilegeLevel']);
        if($userObj->getPrivilegeLevel() < $baseLevel){
            return -1;
        }
        return 1;
    }
    
    //return null when DB connection error or privilege name not valid
    //Valid privilege names (case insensitive): Master Admin,Site Admin, General User
    public static function getPrivilegeLevel($privilegeName){
        $mysqli = new mysqli("pocketprotection.org", "pocket14_pt", "password123", "pocket14_catepillarTest");
        if ($mysqli->connect_errno) {
            return null;
        }
        
        //get base privilege level
        $result = $mysqli->query("SELECT privilegeLevel FROM tbl_privilege WHERE UPPER(privilegeName) = UPPER('".$pvilegeName."')");
        if(!$result || $result->num_rows == 0){
            return null;
        }
        $row = $result->fetch_array();
        $level = intval($row['privilegeLevel']);
        return $level;
    }
}

?>