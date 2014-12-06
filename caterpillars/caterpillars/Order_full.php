<?php
//By Pintian Zhang
require_once("Domain.php");
require_once("User.php");

class Order {

    private $orderID;
    private $surveyID;
    private $orderArthropod;
    private $orderLength;
    private $orderNotes;
    private $orderCount;
    private $insectPhoto;
    private $timeStamp;
    private $isValid;

    private function __construct($orderID, $surveyID, $orderArthropod, $orderLength, $orderNotes, $orderCount, $insectPhoto, $timeStamp, $isValid) {
        $this->orderID = $orderID;
        $this->surveyID = $surveyID;
        $this->orderArthropod = $orderArthropod;
        $this->orderLength = $orderLength;
        $this->orderNotes = $orderNotes;
        $this->orderCount = $orderCount;
        $this->insectPhoto = $insectPhoto;
        $this->timeStamp = $timeStamp;
        $this->isValid = $isValid;
    }

    //return null on db error
    //return -1 on invalid user
    //return order object on success
    public static function create($surveyID, $userID, $orderArthropod, $orderLength, $orderNotes, $orderCount) {
        $mysqli = new mysqli("pocketprotection.org", "pocket14_pt", "password123", "pocket14_catepillarTest");
        if ($mysqli->connect_errno) {
            return null;
        }
        //check valid user
        $userObj = User::findByID($userID);
        if (!is_object($userObj) || !($userObj->checkValid())) {
            return -1;
        }

        $result = $mysqli->query("INSERT INTO tbl_orders (orderID, surveyID, orderArthropod, orderLength, orderNotes, orderCount) VALUES (0," . $surveyID . ",\"" . $orderArthropod . "\"," . $orderLength . ",\"" . $orderNotes . "\"," . $orderCount . ")");
        $newID = $mysqli->insert_id;
        if (!$result) {
            return null;
        }

        //create insectPhoto URL according to userID and newly generated surveyID
        $insectPhoto = Domain::getDomain() . "order" . $surveyID . "-" . $newID . ".jpg";
        //update the tuple in the database with insectPhoto URL
        $result = $mysqli->query("UPDATE tbl_orders SET insectPhoto ='" . $insectPhoto . "' WHERE orderID =" . $newID);
        if (!$result) {
            return null;
        }

        return Order::findByID($newID);
    }

    //return null on db error
    //return order object on success
    public static function findByID($orderID) {
        $mysqli = new mysqli("pocketprotection.org", "pocket14_pt", "password123", "pocket14_catepillarTest");
        if ($mysqli->connect_errno) {
            return null;
        }

        $result = $mysqli->query("SELECT * FROM tbl_orders WHERE orderID = " . $orderID);
        if (!$result || 0 == $result->num_rows) {
            return null;
        }
        $order_info = $result->fetch_array();

        return new Order(intval($order_info['orderID']), intval($order_info['surveyID']), strval($order_info['orderArthropod']), intval($order_info['orderLength']), strval($order_info['orderNotes']), intval($order_info['orderCount']), strval($order_info['insectPhoto']), strval($order_info['timeStamp']), intval($order_info['isValid']));
    }
    
    //By Derek Gu
    //return null on db error or invalid surveyID or no result found
    //return list of order for a given surveyID on sucess
    public static function getAllBySurveyID($surveyID) {
        $mysqli = new mysqli("pocketprotection.org", "pocket14_pt", "password123", "pocket14_catepillarTest");
        if ($mysqli->connect_errno) {
            return null;
        }
        $result = $mysqli->query("SELECT * FROM tbl_orders WHERE surveyID = " . $surveyID);
        
        if(!$result || $result->num_rows == 0){
            return null;
        }
        
        while($row = $result->fetch_array()){
            $orders[] = new Order(intval($row['orderID']), intval($row['surveyID']), strval($row['orderArthropod']), intval($row['orderLength']), strval($row['orderNotes']), intval($row['orderCount']), strval($row['insectPhoto']), strval($row['timeStamp']), intval($row['isValid']));
        }
        return $orders;
    }
    
    //By Derek Gu
    //return null on db error or already invalid
    //return 1 on sucess
    public static function markInvalid($orderID){
        $mysqli = new mysqli("pocketprotection.org", "pocket14_pt", "password123", "pocket14_catepillarTest");
         if ($mysqli->connect_errno) {
            //echo $mysqli->connect_error;
            return null;
        }
        
        $result = $mysqli->query("UPDATE tbl_orders SET isValid = 0 WHERE orderID =" . $orderID);
        
        return $result && $mysqli->affected_rows != 0;
    }
    

    public function getOrderID() {
        return $this->orderID;
    }

    public function getSurveyID() {
        return $this->surveyID;
    }

    public function getOrderArthropod() {
        return $this->orderArthropod;
    }

    public function getOrderLength() {
        return $this->orderLength;
    }

    public function getOrderNotes() {
        return $this->orderNotes;
    }

    public function getOrderCount() {
        return $this->orderCount;
    }

    public function getInsectPhoto() {
        return $this->insectPhoto;
    }

    public function getTimeStamp() {
        return $this->timeStamp;
    }

    public function isValid() {
        return $this->isValid == 1;
    }

    public function getJSON() {
        $json_obj = array(
            'orderID' => $this->orderID,
            'surveyID' => $this->surveyID,
            'orderArthropod' => $this->orderArthropod,
            'orderLength' => $this->orderLength,
            'orderNotes' => $this->orderNotes,
            'orderCount' => $this->orderCount,
            'insectPhoto' => $this->insectPhoto,
            'timeStamp' => $this->timeStamp,
            'isValid' => $this->isValid
        );
        return $json_obj;
    }

    public function getJSONSimple() {
        $json_obj = array(
            'orderID' => $this->orderID,
            'surveyID' => $this->surveyID,
            'insectPhoto' => $this->insectPhoto
        );
        return $json_obj;
    }

}

?>