<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>URL shortener</title>
    </head>
    <body>
        <form action="" method="GET">
            <p><b>Enter url</b><br>
            <textarea name="longurl" cols="50" rows="5" wrap="off"></textarea>
            <br>
            <p><b>Enter desired short url</b><br>
            <textarea name="desireurl" cols="50" rows="5" wrap="off"></textarea>
            <input type="submit" value="Send">
        </form>
        <form action="dbcleaner.php" method="GET">
            <p><b>Clean URLs in DataBase older, than (in days):</b><br>
            <textarea name="cleanperiod" cols="10" rows="1" wrap="off"></textarea>
            <input type="checkbox" name="cleanall"> Clean all URLs in database<br>
            <input type=submit value=Clean>
        </form>
<?php
        include_once "settings.php";
        include_once 'dbconnection.php';
        
        $longurl = trim(filter_input(INPUT_GET, 'longurl'));
        $desireurl = trim(filter_input(INPUT_GET, 'desireurl'));
        htmlentities($longurl);
        htmlentities($desireurl);
        $time = time();
        
        //Connection to DB
            $connection = new Dbconnection($dbhost, $dbname, $dbuser, $dbpass);
            $connection->dbconnect();
            $connection->dbselect();
                
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
            echo "URLs in database: ".$urlcount."<br>";
        
        //url validation
            $longurldata = get_curl_data($longurl);
            if ($longurl != NULL){
                    if (filter_var($longurl, FILTER_VALIDATE_URL) == FALSE OR $longurldata['httpcode'] == 404) {
                        echo "<br>URL not found or invalid";
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
                            $sql1 = "INSERT INTO `shorturl`(`ID`, `longurl`, `shorturl`, `time`) VALUES (NULL,'$longurl','$desireurl','$time')";
                            mysql_query($sql1);
                            echo "<br>Short url: "."<a href=$longurl target='_blank'>$shorturl</a>";
                        }
                        else {
        //Random short url generating
                        $symbols = "QqWwEeRrTtYyUuIiOoPpAaSsDdFfGgHhJjKkLlZzXxCcVvBbNnMm1234567890";
                        $rand = trim(substr(str_shuffle($symbols),0,$length));
                        $shorturl2 = "$domain$rand";//short url
                        redirect($rand);
                        $sql2 = "INSERT INTO `shorturl`(`ID`, `longurl`, `shorturl`, `time`) VALUES (NULL,'$longurl','$rand','$time')";
                        mysql_query($sql2);
                        echo "<br>Short url: "."<a href=$longurl target='_blank'>$shorturl2</a>";
                        }
                    }
            }
            mysqlclose($dbconnect);
?>
    </body>
</html>
