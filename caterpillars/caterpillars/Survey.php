<?php
//depricated, please see Survey_full
require_once("Domain.php");

class Survey{

	private $surveyID;
	private $siteID;
	private $userID;
	private $circle;
	private $survey;
	private $timeStart;
	private $timeSubmit;
	private $temperatureMin;
	private $temperatureMax;
	private $siteNotes;
	private $plantSpecies;
	private $herbivory;
	private $leavePhoto;
	private $isValid;
	
	
	private function __construct($surveyID, $siteID, $userID,$leavePhoto){
		$this->surveyID = $surveyID;
		$this->siteID = $siteID;
		$this->userID = $userID;
                $this->leavePhoto = $leavePhoto;
	}
	
        //create a survey object and insert it in the database
	public static function create($siteID, $userID){
		$mysqli = new mysqli("pocketprotection.org", "pocket14_pt", "password123", "pocket14_catepillarTest");
		if($mysqli->connect_errno){
			return null;
		}
		
		$result = $mysqli->query("INSERT INTO tbl_surveys (surveyID, siteID, userID) VALUES (0,". $siteID. ",". $userID . ")");
		$newID = $mysqli->insert_id;
                
		if(!$result){
                    //insertion failed
                    return -1;
		}
                
                //create leavePhoto URL according to userID and newly generated surveyID
                $leavePhoto = Domain::getDomain()."survey".$userID."-".$newID.".jpg";
                
                //update the tuple in the database with leavePhoto URL
                $result = $mysqli->query("UPDATE tbl_surveys SET leavePhoto ='".$leavePhoto."' WHERE surveyID =". $newID);
                
                if(!$result){
                    //update failed
                    return -1;
                }
                
                return new Survey($newID, $userID, $siteID, $leavePhoto);
	}
	
	public static function findByID($surveyID){
		$mysqli = new mysqli("pocketprotection.org", "pocket14_pt", "password123", "pocket14_catepillarTest");
		if($mysqli->connect_errno)
			return null;
		
		
		$result = $mysqli->query("SELECT surveyID, siteID, userID FROM tbl_surveys WHERE surveyID = ". $surveyID);
		if($result)
			if($result->num_rows == 0)
				return null; //no survey found with given surveyID
			else {
				$row = $result->fetch_array();
				return new Survey($row['surveyID'], $row['siteID'], $row['userID']);
			}
		
		return null;
	}
	
	public function getSurveyID(){
		return $surveyID;
	}
	
	public function getSiteID(){
		return $siteID;
	}
	
	public function getUserID(){
		return $userID;
	}
        
        public function getPhoto(){
            return $leavePhoto;
        }
	
	public function getJSON(){
		$json_obj = array(
			'surveyID' => $this->surveyID,
			'siteID' => $this->siteID,
			'userID' => $this->userID,
                        'leavePhoto' => $this->leavePhoto
		);
                return $json_obj;
	}
}
?>