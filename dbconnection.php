<?php
class Dbconnection {    
    function __construct($host, $name, $user, $password) {
        $this->host = $host;
        $this->name = $name;
        $this->user = $user;
        $this->password = $password;
    }
    
    function dbconnect(){
        $dbconnect = mysql_connect($this->host, $this->user, $this->password);
        if (!$dbconnect) {exit("DB connection failed, check your settings");}
    }
    
    function dbselect(){
        $dbselect = mysql_select_db($this->name);
        if (!$dbselect) {exit("DB selection failed, check your settings");}
    }
}
?>