<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <form action="" method="GET">
            Enter url<br>
            <textarea name=longurl cols=50 rows=5 wrap=virtual></textarea>
            <br>
            Enter desired short url<br>
            <textarea name=desireurl cols=50 rows=5 wrap=virtual></textarea>
            <input type=submit value=Send>
        </form>
        <?php
        $settings = parse_ini_file("settings.php");
        $domain = $settings['domain'];
        $longurl = trim(filter_input(INPUT_GET, 'longurl'));
        $desireurl = trim(filter_input(INPUT_GET, 'desireurl'));
        //Connection to DB
            $dbconnect = mysql_connect($settings['dbhost'], $settings['dbuser'], $settings['dbpass']);
            $dbselect = mysql_select_db($settings['dbname']);
                if (!$dbconnect) exit("DB connection failed, check your settings");
                if (!$dbselect) exit("DB selection failed, check your settings");
        //Function for url validation      
            function get_curl_data($url) {
                $c = curl_init();
                curl_setopt($c, CURLOPT_HEADER, 1);
                curl_setopt($c, CURLOPT_NOBODY, 1);
                curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($c, CURLOPT_FRESH_CONNECT, 1);
                curl_setopt($c, CURLOPT_URL, $url);
                curl_exec($c);
                $httpcode = curl_getinfo($c, CURLINFO_HTTP_CODE);
                curl_close($c);
                    return array("httpcode" => $httpcode); 
            }
        //Function for redirect
            function redirect($url) {
            mkdir("$url");
            $f = fopen("$url/index.php", "w+");
            $redirect = "<?php header(\"HTTP/1.1 301 Moved Permanently\"); header(\"Location: $longurl\"); exit();";
            fwrite($f, $redirect);
            fclose($f);
            }
        //Printing number of urls in DB
        $urlcountquery = mysql_query("SELECT * FROM shorturl");
        $urlcount = mysql_num_rows($urlcountquery);
        echo "urls in database: ".$urlcount."<br>";
        //url validation
            $longurldata = get_curl_data($longurl);
            if ($longurl != NULL){
                    if (filter_var($longurl, FILTER_VALIDATE_URL) == FALSE OR $longurldata['httpcode'] == 404) {
                        echo "url not found or invalid";
                        }
        //Desire short url generating
                    else {
                        if ($desireurl != NULL) {
                            //checking url for doubles
                                $result=mysql_query("SELECT shorturl FROM shorturl WHERE shorturl='$desireurl'"); 
                                $desireurldouble=mysql_fetch_array($result);
                                if(isset($desireurldouble['shorturl'])) {exit ("<br>Such desire url already exists, enter another one");}
                            $shorturl = "$domain$desireurl";
                            redirect($desireurl);
                            //Put in DB
                            $sql1 = "INSERT INTO `shorturl`(`ID`, `longurl`, `shorturl`) VALUES (NULL,'$longurl','$desireurl')";
                            mysql_query($sql1);
                            echo "<br>Short url: "."<a href=$longurl target='_blank'>$shorturl</a>";//printing short url
                        }
                        else {
        //Random short url generating
                        $symbols = "QqWwEeRrTtYyUuIiOoPpAaSsDdFfGgHhJjKkLlZzXxCcVvBbNnMm1234567890"; //symbols for random short url
                        $rand = trim(substr(str_shuffle($symbols),0,$settings['length'])); //choosing random symbols
                        $shorturl2 = "$domain$rand";//short url
                        redirect($rand);
                        //Put in DB
                        $sql2 = "INSERT INTO `shorturl`(`ID`, `longurl`, `shorturl`) VALUES (NULL,'$longurl','$rand')";
                        mysql_query($sql2);
                        echo "<br>Short url: "."<a href=$longurl target='_blank'>$shorturl2</a>";//printing short url
                        }
                    }
            }
            mysqlclose($dbconnect);
        ?>
    </body>
</html>
