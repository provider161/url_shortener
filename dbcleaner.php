<?php
$settings = parse_ini_file("settings.php");
$cleanperiod = trim(filter_input(INPUT_GET, 'cleanperiod'));
$cleanperiodsql = time() - ($cleanperiod*24*60*60);
$cleanall = filter_input(INPUT_GET, 'cleanall');
//Connection to DB
$dbconnect = mysql_connect($settings['dbhost'], $settings['dbuser'], $settings['dbpass']);
$dbselect = mysql_select_db($settings['dbname']);
    if (!$dbconnect) {exit("DB connection failed, check your settings");}
    if (!$dbselect) {exit("DB selection failed, check your settings");}
//DB cleaning function
function cleandb($cleanflag) {
    global $cleanperiodsql;
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