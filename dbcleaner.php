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
        #Cleaning DB
        $this->database->query("TRUNCATE shorturl");          
        $this->cleandirectories($this->getdirslist($result));
    }
    
    function clean_period(){
        $result = $this->database->query("SELECT `shorturl` FROM `shorturl` WHERE `time` < $this->period");
        #Cleaning DB
        $this->database->query("DELETE FROM `shorturl` WHERE `time` < $this->period");
        $this->cleandirectories($this->getdirslist($result));
    }
    
    function cleandirectories($dir){
        foreach ($dir as $directorie) {
            unlink("$directorie/index.php");
            rmdir("$directorie");
        }
        
    }
    
    function getdirslist($sqlresult){
        $delurlcount = $sqlresult->num_rows;
        $dirs = array();
        while ($row = $$row = $sqlresult->fetch_row()){
            $dirs[] = $row[0];
        }
        echo "Totally URLs deleted: ".$delurlcount."<br>";       
        return $dirs;
    }
}