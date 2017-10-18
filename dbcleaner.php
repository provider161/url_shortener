<?php
include_once 'settings.php';
include_once 'dbconnection.php';

$cleanperiod = trim(filter_input(INPUT_GET, 'cleanperiod'));
$cleanperiodsql = time() - ($cleanperiod*24*60*60);
$cleanall = filter_input(INPUT_GET, 'cleanall');
htmlentities($cleanperiod);

//Connection to DB
Database::dbconnect($dbhost, $dbuser, $dbpass);
Database::dbselect($dbname);

$cleanall ? Dbclean::clean_all() : Dbclean::clean_period($cleanperiodsql);

//Closing DB
Database::dbclose();

//Class for DB cleaning
class Dbclean {
    
    static function clean_all(){
        $result = mysql_query("SELECT `shorturl` FROM `shorturl`");
        $delurlcount = mysql_num_rows($result);
        $dirs = array();
        while ($row = mysql_fetch_array($result, MYSQL_NUM)){
            $dirs[] = $row[0];
        }
        
        //Cleaning DB
        $sqlcleanall = "TRUNCATE `shorturl`";
        mysql_query($sqlcleanall);
            
        //Cleaning directories
        foreach ($dirs as $directorie) {
            unlink("$directorie/index.php");
            rmdir("$directorie");
        }
        echo "Totally URLs deleted: ".$delurlcount."<br>";
    }
    
    static function clean_period($period){
        $result = mysql_query("SELECT `shorturl` FROM `shorturl` WHERE `time` < $period");
        $delurlcount = mysql_num_rows($result);
        $dirs = array();
        while ($row = mysql_fetch_array($result, MYSQL_NUM)){
            $dirs[] = $row[0];
        }
        
        //Cleaning DB
        $sqlcleanall = "DELETE FROM `shorturl` WHERE `time` < $period";
        mysql_query($sqlcleanall);
            
        //Cleaning directories
        foreach ($dirs as $directorie) {
            unlink("$directorie/index.php");
            rmdir("$directorie");
        }
        echo "Totally URLs deleted: ".$delurlcount."<br>";
    }
    
}