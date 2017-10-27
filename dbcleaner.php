<?php
require_once 'settings.php';

$cleanperiod = trim(filter_input(INPUT_POST, 'cleanperiod'));
$cleanperiodsql = time() - ($cleanperiod*24*60*60);
$cleanall = filter_input(INPUT_POST, 'cleanall');
htmlentities($cleanperiod);

#Connection to DB
$dbconn = new mysqli($dbhost, $dbuser, $dbpass, $dbname, $dbport);
if ($dbconn->connect_error) {
    die('Connect Error (' . $dbconn->connect_errno . ') ' . $dbconn->connect_error);
}

#Cleaning DB
$cleandatabase = new Dbclean($dbconn, $cleanperiodsql);
$cleanall ? $cleandatabase->clean_all() : $cleandatabase->clean_period();

print_r($cleandatabase->clean_all());

#Closing DB
$dbconn::close;

#Class for DB cleaning
class Dbclean {
    function __construct($database, $period) {
        $this->database = $database;
        $this->period = $period;
    }


    function clean_all(){
        $result = $this->database->query("SELECT shorturl FROM shorturl");
        $delurlcount = $result->num_rows;
        $dirs = array();
        while ($row = $this->database->fetch_array($result, MYSQLI_NUM)){
            $dirs[] = $row[0];
        }
        
        //Cleaning DB
        $this->database->query("TRUNCATE shorturl");
            
        //Cleaning directories
        foreach ($dirs as $directorie) {
            unlink("$directorie/index.php");
            rmdir("$directorie");
        }
        echo "Totally URLs deleted: ".$delurlcount."<br>";
    }
    
    function clean_period(){
        $result = $this->database->query("SELECT `shorturl` FROM `shorturl` WHERE `time` < $this->period");
        $delurlcount = $result->num_rows;
        $dirs = array();
        while ($row = $this->database->fetch_array($result, MYSQLI_NUM)){
            $dirs[] = $row[0];
        }
        
        //Cleaning DB
        $this->database->query("DELETE FROM `shorturl` WHERE `time` < $this->period");
            
        //Cleaning directories
        foreach ($dirs as $directorie) {
            unlink("$directorie/index.php");
            rmdir("$directorie");
        }
        echo "Totally URLs deleted: ".$delurlcount."<br>";
    }
    
}