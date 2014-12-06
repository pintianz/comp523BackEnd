<?php
//Depricated, see Order_full
require_once("Domain.php");

class Order{

	private $orderID;
	private $surveyID;
	private $orderArthropod;
	private $orderLength;
	private $orderNotes;
	private $orderCount;
	private $insectPhoto;
	private $timeStamp;
	private $isValid;
	
	
	private function __construct($id, $surveyID, $insectPhoto){
		$this->orderID = $id;
		$this->surveyID = $surveyID;
                $this->insectPhoto = $insectPhoto;
	}
	
	public static function create($surveyID){
		$mysqli = new mysqli("pocketprotection.org", "pocket14_pt", "password123", "pocket14_catepillarTest");
		if($mysqli->connect_errno){
			return null;
		}
		
		$result = $mysqli->query("INSERT INTO tbl_orders (orderID, surveyID) VALUES (0,". $surveyID . ")");
		$newID = $mysqli->insert_id;
		if(!$result){
                    //insertion failed
                    return -1;
                }
                
                //create insectPhoto URL according to userID and newly generated surveyID
                $insectPhoto = Domain::getDomain()."order".$surveyID."-".$newID.".jpg";
                
                //update the tuple in the database with insectPhoto URL
                $result = $mysqli->query("UPDATE tbl_orders SET insectPhoto ='".$insectPhoto."' WHERE orderID =". $newID);
                
                if(!$result){
                    //update failed
                    return -1;
                }
                
                return new Order($newID, $surveyID, $insectPhoto);
	}
	
	public static function findByID($orderID){
		$mysqli = new mysqli("pocketprotection.org", "pocket14_pt", "password123", "pocket14_catepillarTest");
		if($mysqli->connect_errno){
			return null;
		}
		
		$result = $mysqli->query("SELECT surveyID, siteID, userID FROM tbl_surveys WHERE surveyID = ". $orderID);
		if($result){
			if($result->num_rows == 0){
				return null; //no survey found with given surveyID
                        }
			else {
				$row = $result->fetch_array();
				return new Survey($row['orderID'], $row['surveyID']);
			}
                }
		return -1;
	}
	
	public function getOrderID(){
            return $orderID;
	}
	
	public function getSurveyID(){
            return $surveyID;
        }
        
        public function getPhoto(){
            return $insectPhoto;
        }
	
	public function getJSON(){
		$json_obj = array(
			'orderID' => $this->orderID,
			'surveyID' => $this->surveyID,
                        'insectPhoto' => $this->insectPhoto
		);
                return $json_obj;
	}
}
?>