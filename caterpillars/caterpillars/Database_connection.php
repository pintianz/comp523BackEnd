<?php
    //By Pintian Zhang
class Database_connection{
  
	private function __construct(){
	}  
    public static function getMysqli(){
        return new mysqli("pocketprotection.org", "pocket14_pt", "password123", "pocket14_catepillarTest");
    }
}