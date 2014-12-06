<?php

//By: Pintian Zhang and Derek Gu
require_once("Domain.php");
require_once("User.php");

class Survey {

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

    private function __construct($surveyID, $siteID, $userID, $circle, $survey, $timeStart, $timeSubmit, $temperatureMin, $temperatureMax, $siteNotes, $plantSpecies, $herbivory, $leavePhoto, $isValid) {
        $this->surveyID = $surveyID;
        $this->siteID = $siteID;
        $this->userID = $userID;
        $this->circle = $circle;
        $this->survey = $survey;
        $this->timeStart = $timeStart;
        $this->timeSubmit = $timeSubmit;
        $this->temperatureMin = $temperatureMin;
        $this->temperatureMax = $temperatureMax;
        $this->siteNotes = $siteNotes;
        $this->plantSpecies = $plantSpecies;
        $this->herbivory = $herbivory;
        $this->leavePhoto = $leavePhoto;
        $this->isValid = $isValid;
    }

    //return null on db error
    //return -1 on invalid user
    //return survey object on success
    public static function create($siteID, $userID, $circle, $survey, $timeStart, $temperatureMin, $temperatureMax, $siteNotes, $plantSpecies, $herbivory) {
        $mysqli = new mysqli("pocketprotection.org", "pocket14_pt", "password123", "pocket14_catepillarTest");
        if ($mysqli->connect_errno) {
            return null;
        }
        //check valid user
        $userObj = User::findByID($userID);
        if (!is_object($userObj) || !($userObj->checkValid())) {
            return -1;
        }

        $result = $mysqli->query("INSERT INTO tbl_surveys (surveyID, siteID, userID,circle,survey,timeStart,temperatureMin,temperatureMax,siteNotes,plantSpecies,herbivory) VALUES (0," . $siteID . "," . $userID . "," . $circle . ",\"" . $survey . "\",\"" . $timeStart . "\"," . $temperatureMin . "," . $temperatureMax . ",\"" . $siteNotes . "\",\"" . $plantSpecies . "\"," . $herbivory . ")");
        $newID = $mysqli->insert_id;
        if (!$result) {
            return null;
        }
        //create leavePhoto URL according to userID and newly generated surveyID
        $leavePhoto = Domain::getDomain() . "survey" . $userID . "-" . $newID . ".jpg";
        //update the tuple in the database with leavePhoto URL
        $result = $mysqli->query("UPDATE tbl_surveys SET leavePhoto ='" . $leavePhoto . "' WHERE surveyID =" . $newID);
        if (!$result) {
            return null;
        }
        //return survey object
        return Survey::findByID($newID);
    }

    //return null on db error
    //return survey object on success
    public static function findByID($surveyID) {
        $mysqli = new mysqli("pocketprotection.org", "pocket14_pt", "password123", "pocket14_catepillarTest");
        if ($mysqli->connect_errno)
            return null;

        $result = $mysqli->query("SELECT * FROM tbl_surveys WHERE surveyID = " . $surveyID);
        if (!$result || 0 == $result->num_rows) {
            return null;
        }
        $survey_info = $result->fetch_array();

        return new Survey(intval($survey_info['surveyID']), intval($survey_info['siteID']), intval($survey_info['userID']), intval($survey_info['circle']), strval($survey_info['survey']), strval($survey_info['timeStart']), strval($survey_info['timeSubmit']), intval($survey_info['temperatureMin']), intval($survey_info['temperatureMax']), strval($survey_info['siteNotes']), strval($survey_info['plantSpecies']), intval($survey_info['herbivory']), strval($survey_info['leavePhoto']), intval($survey_info['isValid']));
    }

    //By Derek Gu
    //return null if db error or siteIDs invalid or no survey found
    //return list of sites found on sucess
    public static function getAllBySiteID($siteID, $startDate, $endDate) {
        $mysqli = new mysqli("pocketprotection.org", "pocket14_pt", "password123", "pocket14_catepillarTest");
        if ($mysqli->connect_errno)
            return null;

        $query = "SELECT * FROM tbl_surveys WHERE siteID =" . $siteID;
        if ($startDate) {
            $query.= " AND DATE(timeStart) >= '" . $startDate."'";
        }
        if ($endDate) {
            $query.= " AND DATE(timeStart) <= '" . $endDate."'";
        }

        $result = $mysqli->query($query);
        if ($result) {
            if (0 == $result->num_rows) {
                return null;
            }
            while ($row = $result->fetch_array()) {
                $surveys[] = new Survey(intval($row['surveyID']), intval($row['siteID']), intval($row['userID']), intval($row['circle']), strval($row['survey']), strval($row['timeStart']), strval($row['timeSubmit']), intval($row['temperatureMin']), intval($row['temperatureMax']), strval($row['siteNotes']), strval($row['plantSpecies']), intval($row['herbivory']), strval($row['leavePhoto']), intval($row['isValid']));
            }
            return $surveys;
        } else
            return null;
    }

    //By Derek Gu
    // Takes an array of siteIDs as parameter
    // returns an array of surveys if sucess
    // returns null if db error or siteIDs invalid or no survey found
    public static function getAllBySiteIDArray($siteIDs, $startDate = '', $endDate = '') {
        $mysqli = new mysqli("pocketprotection.org", "pocket14_pt", "password123", "pocket14_catepillarTest");
        if ($mysqli->connect_errno)
            return null;

        $query = "SELECT * FROM tbl_surveys WHERE (siteID = " . $siteIDs[0];
        for ($i = 1; $i < count($siteIDs); $i++) {
            $query.= " OR siteID = " . $siteIDs[$i];
        }
        $query.=")";
        if ($startDate) {
            $query.= " AND DATE(timeStart) >= '" . $startDate."'";
        }
        if ($endDate) {
            $query.= " AND DATE(timeStart) <= '" . $endDate."'";
        }

        $result = $mysqli->query($query);
        if ($result) {
            if (0 == $result->num_rows) {
                return null;
            }
            while ($row = $result->fetch_array()) {
                $surveys[] = new Survey(intval($row['surveyID']), intval($row['siteID']), intval($row['userID']), intval($row['circle']), strval($row['survey']), strval($row['timeStart']), strval($row['timeSubmit']), intval($row['temperatureMin']), intval($row['temperatureMax']), strval($row['siteNotes']), strval($row['plantSpecies']), intval($row['herbivory']), strval($row['leavePhoto']), intval($row['isValid']));
            }
            return $surveys;
        } else
            return null;
    }

   //return null on db error or user already invalid
   //return 1 on sucess
    public static function markInvalid($surveyID) {
        $mysqli = new mysqli("pocketprotection.org", "pocket14_pt", "password123", "pocket14_catepillarTest");
        if ($mysqli->connect_errno) {
            //echo $mysqli->connect_error;
            return null;
        }

        $result = $mysqli->query("UPDATE tbl_surveys SET isValid = 0 WHERE surveyID =" . $surveyID);

        return $result && $mysqli->affected_rows != 0;
    }

    public function getSurveyID() {
        return $this->surveyID;
    }

    public function getSiteID() {
        return $this->siteID;
    }

    public function getUserID() {
        return $this->userID;
    }

    public function getCircle() {
        return $this->circle;
    }

    public function getSurvey() {
        return $this->survey;
    }

    public function getTimeStart() {
        return $this->timeStart;
    }

    public function getTimeSubmit() {
        return $this->timeSubmit;
    }

    public function getTemperatureMin() {
        return $this->temperatureMin;
    }

    public function getTemperatureMax() {
        return $this->temperatureMax;
    }

    public function getSiteNotes() {
        return $this->siteNotes;
    }

    public function getPlantSpecies() {
        return $this->plantSpecies;
    }

    public function getHerbivory() {
        return $this->herbivory;
    }

    public function getLeavePhoto() {
        return $this->leavePhoto;
    }

    public function isValid() {
        return $this->isValid == 1;
    }

    public function getJSON() {
        $json_obj = array(
            'surveyID' => $this->surveyID,
            'siteID' => $this->siteID,
            'userID' => $this->userID,
            'circle' => $this->circle,
            'survey' => $this->survey,
            'timeStart' => $this->timeStart,
            'timeSubmit' => $this->timeSubmit,
            'temperatureMin' => $this->temperatureMin,
            'temperatureMax' => $this->temperatureMax,
            'siteNotes' => $this->siteNotes,
            'plantSpecies' => $this->plantSpecies,
            'herbivory' => $this->herbivory,
            'leavePhoto' => $this->leavePhoto,
            'isValid' => $this->isValid
        );
        return $json_obj;
    }

    public function getJSONSimple() {
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