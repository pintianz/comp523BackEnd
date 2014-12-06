<?php

//By Pintian Zhang
require_once('User.php');
require_once('PasswordHash.php');
require_once('Database_connection.php');
require_once('Privilege.php');

class Site{
  private $siteID;
	private $siteName;
	private $siteLat;
	private $siteLong;
	private $siteDescription;
	private $siteState;
  private $timeStamp;
  private $isValid;
  private $siteSaltHash;
  
	private function __construct($siteID,$siteName,$siteLat,$siteLong,$siteDescription,$siteState,$timeStamp,$isValid, $siteSaltHash){
        $this->siteID = $siteID;
        $this->siteName = $siteName;
        $this->siteLat = $siteLat;
        $this->siteLong = $siteLong;
        $this->siteDescription = $siteDescription;
        $this->siteState = $siteState;
        $this->timeStamp = $timeStamp;
        $this->isValid = $isValid;
        $this->siteSaltHash = $siteSaltHash;
	}  

    //returns null if DB error
    //returns -1 if user don't exist/password incorrect/privilege level not high enough
    //returns User on successful creation
    public static function create($email,$password,$siteName,$siteLat,$siteLong,$siteDescription,$siteState,$sitePassword){
        $mysqli = Database_connection::getMysqli();
        if ($mysqli->connect_errno) {
            return null;
        }
        $userObj = User::find($email);
        //check if valid Site Admin
        $isValidSiteAdmin = Privilege::isValidSiteAdmin($userObj,$password);
        if($isValidSiteAdmin != 1){
          return $isValidSiteAdmin;
        }
        //insert site
        $siteSaltHash = create_hash($sitePassword);
        $result = $mysqli->query("INSERT INTO tbl_sites (`siteID`, `siteName`, `siteState`, `siteLat`, `siteLong`, `siteSaltHash`, `siteDescription`) VALUES (0,\"".$siteName."\",\"".$siteState."\",".$siteLat.",".$siteLong.",\"".$siteSaltHash."\",\"".$siteDescription."\")");
        if(!$result){
            return null;
        }
        $newID = $mysqli->insert_id;
        //insert relationship
        $result = $mysqli->query("INSERT INTO `tbl_siteAdmin` (`siteID`, `userID`) VALUES (".$newID.",".$userObj->getID().")");
        if(!$result){
            return null;
        }
        return Site::find($newID);
    }
    
    //returns null if DB error
    //return -1 on un-recognized action
    //returns all valid sites on success
    public static function getAll($action){
        $mysqli = Database_connection::getMysqli();
        if ($mysqli->connect_errno) {
            return null;
        }

        if ($action == "getAllSiteState") {
          $result = $mysqli->query("SELECT siteID, siteName, siteState FROM tbl_sites WHERE isValid = 1");
        } 
        elseif($action == "getAll"){
        	$result = $mysqli->query("SELECT * FROM tbl_sites WHERE isValid = 1");
        } 
        else {
        	return -1;
        }
        if(!$result){
        	return null;
        }

        $rows = array();
        while($r = mysqli_fetch_assoc($result)) {
        	$rows[] = $r;
        }
        return $rows;
    }
    
    //returns null if DB error
    //returns -1 if user don't exist/password incorrect/privilege level not high enough
    //returns 1 success
    public static function changeSitePassword($email,$password,$siteID,$newSitePassword){
        $mysqli = Database_connection::getMysqli();
        if ($mysqli->connect_errno) {
            return null;
        }
        //check if site exist
        $siteObj = Site::find($siteID);
        if(!is_object($siteObj)) return -1;
        $userObj = User::find($email);
        //check if valid Site Admin
        $isValidSiteAdmin = Privilege::isValidSiteAdmin($userObj,$password);
        if($isValidSiteAdmin != 1){
          return $isValidSiteAdmin;
        }
        //check if has authority over site
        $checkAutorityOverSite = Privilege::checkAuthorityOverSite($userObj,$siteID);
        if($checkAutorityOverSite != 1){
          return $checkAutorityOverSite;
        }
        
        //change site password
        $siteSaltHash = create_hash($newSitePassword);
        $result = $mysqli->query("UPDATE tbl_sites SET siteSaltHash='".$siteSaltHash."' WHERE siteID=".$siteID);
        if(!$result){
            return null;
        }
        return 1;
    }
    
    //returns null if DB error
    //returns -1 if user don't exist/password incorrect/privilege level not high enough
    //returns 1 success
    public static function checkSitePassword($siteID,$sitePasswordCheck){
        $mysqli = Database_connection::getMysqli();
        if ($mysqli->connect_errno) {
            return null;
        }

        $siteObj = Site::find($siteID);
        if(!is_object($siteObj)) return null;
        //check if valid Site Admin
        $validSitePassword = validate_password($sitePasswordCheck,$siteObj->getSiteSaltHash());
        $json_rep = array();
        $json_rep['validSitePassword'] = intval($validSitePassword);
        return $json_rep;
    }
    
    //returns null if DB error
    //returns site object on success
    public static function find($siteID){
      $mysqli = Database_connection::getMysqli();
      if ($mysqli->connect_errno) {
        return null;
      }
      $result = $mysqli->query("SELECT * FROM tbl_sites WHERE siteID=" . $siteID);
      if($result){
        if(0 == $result->num_rows){
            return -1;
        }
        $site_info = $result->fetch_array();
        return new Site(intval($site_info['siteID']),
          strval($site_info['siteName']),
          intval($site_info['siteLat']),
          intval($site_info['siteLong']),
          strval($site_info['siteDescription']),
          strval($site_info['siteState']),
          strval($site_info['timeStamp']),
          intval($site_info['isValid']),
          strval($site_info['siteSaltHash']));
      }
      else{
        return null;
      }
     }
    
    public function getSiteID(){
      return $this->siteID;
    }
    public function getSiteName(){
      return $this->siteName;
    }
    public function getSiteLat(){
      return $this->siteLat;
    }
    public function getSiteLong(){
      return $this->siteLong;
    }
    public function getSiteDescription(){
      return $this->siteDescription;
    }
    public function getSiteState(){
      return $this->siteState;
    }
    public function getTimeStamp(){
      return $this->timeStamp;
    }
    public function getIsValid(){
      return $this->isValid;
    }
    public function getSiteSaltHash(){
      return $this->siteSaltHash;
    }
    public function getJSON(){
      $json_rep = array();
      $json_rep['siteID'] = $this->siteID;
      $json_rep['siteName'] = $this->siteName;
      $json_rep['siteLat'] = $this->siteLat;
      $json_rep['siteLong'] = $this->siteLong;
      $json_rep['siteDescription'] = $this->siteDescription;
      $json_rep['siteState'] = $this->siteState;
      $json_rep['timeStamp'] = $this->timeStamp;
      $json_rep['isValid'] = $this->isValid;
      return $json_rep;
    }


}