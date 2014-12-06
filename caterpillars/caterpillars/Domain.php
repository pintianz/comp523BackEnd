<?php
    //By Derek Gu
    class Domain{
        private static $domain = 'uploads/';
        
        public static function getDomain(){
            return self::$domain;
        }
    }
?>