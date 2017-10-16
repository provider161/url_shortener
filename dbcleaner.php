<?php
include_once 'settings.php';
include_once 'dbconnection.php';

$cleanperiod = trim(filter_input(INPUT_GET, 'cleanperiod'));
$cleanperiodsql = time() - ($cleanperiod*24*60*60);
$cleanall = filter_input(INPUT_GET, 'cleanall');
htmlentities($cleanperiod);

//Connection to DB
    $connection = new Dbconnection($dbhost, $dbname, $dbuser, $dbpass);
    $connection->dbconnect();
    $connection->dbselect();
        
//DB cleaning function
    global $cleanperiodsql;
    function cleandb($cleanflag) {
        if ($cleanflag == TRUE) {
        $sqlcleanall = "TRUNCATE `shorturl`";
        mysql_query($sqlcleanall);
        echo "Database is totally cleaned";
        }
        else {
            $delurlcountquery = mysql_query("SELECT * FROM `shorturl` WHERE `time` < $cleanperiodsql");
            $delurlcount = mysql_num_rows($delurlcountquery);
            $sqlclean = "DELETE FROM `shorturl` WHERE `time` < $cleanperiodsql";
            mysql_query($sqlclean);
            echo "Totally URLs deleted: ".$delurlcount."<br>";
        }
    }
cleandb ($cleanall);
mysqlclose($dbconnect);