<?php
class Database {    

    static function dbconnect($host, $user, $password){
        $dbconnect = mysql_connect($host, $user, $password);
        if (!$dbconnect) {die("Не удалось подключиться к базе: " . mysql_error());}
    }
    
    static function dbselect($name){
        $dbselect = mysql_select_db($name);
        if (!$dbselect) {die("Не удалось выбрать базу: " . mysql_error());}
    }
    
    static function dbclose(){
        mysqlclose();
    }
}
?>