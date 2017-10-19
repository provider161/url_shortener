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
        global $time, $longurl, $desireurl;

        #Connection to DB
        Database::dbconnect($dbhost, $dbuser, $dbpass);
        Database::dbselect($dbname);
             
        #Making an URL object
        $resulturl = new Urlgenerator($domain, $length, $longurl, $time, $desireurl);
         
        #Generate short URL
        if ($longurl != NULL) {
            $checkurl = check_url($longurl);
            if ($desireurl != NULL){
                $resulturl->desireurl();
            }
            else{
                $resulturl->randomurl();
            }
        }
                        
        #Printing number of urls in DB
        $urlcountquery = mysql_query("SELECT * FROM shorturl");
        $urlcount = mysql_num_rows($urlcountquery);
        echo "<br>URLs in database: ".$urlcount."<br>";
        
        #Closing database    
        Database::dbclose();
            
        #Function for url validation      
        function check_url($url) {
            $c = curl_init();
            curl_setopt($c, CURLOPT_HEADER, 1);
            curl_setopt($c, CURLOPT_NOBODY, 1);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($c, CURLOPT_FRESH_CONNECT, 1);
            curl_setopt($c, CURLOPT_URL, $url);
            curl_exec($c);
            $httpcode = curl_getinfo($c, CURLINFO_HTTP_CODE);
            curl_close($c);
            if (filter_var($url, FILTER_VALIDATE_URL) == FALSE OR $httpcode == 404) {
                exit ("<br>Entered URL not found or invalid");
            }
            else{
                return TRUE;
            }
        }   
            
        class Urlgenerator {
            function __construct($classdomain, $classlength, $classlongurl, $classtime, $classdesireurl) {
                $this->domain = $classdomain;
                $this->length = $classlength;
                $this->longurl = $classlongurl;
                $this->time = $classtime;
                $this->desireurl = $classdesireurl;
                
            }
                                    
            function desireurl(){
                #checking desire url for doubles in DB
                $desireresult=mysql_query("SELECT `shorturl` FROM `shorturl` WHERE `shorturl`='$this->desireurl'"); 
                $desireurldouble=mysql_fetch_array($desireresult);
                if(isset($desireurldouble['shorturl'])) {exit ("<br>Such desire url already exists, enter another one");}
                #Make a shorturl and put it in DB    
                $shorturl = "$this->domain$this->desireurl";
                $sql1 = "INSERT INTO `shorturl`(`ID`, `longurl`, `shorturl`, `time`) VALUES (NULL,'$this->longurl','$this->desireurl','$this->time')";
                mysql_query($sql1);
                #Make redirect
                mkdir("$this->desireurl");
                $redirect = "<?php header(\"HTTP/1.1 301 Moved Permanently\"); header(\"Location: $this->longurl\"); exit();";
                file_put_contents("$this->desireurl/index.php", $redirect);
                
                echo "Short url: "."<a href=$$this->longurl target='_blank'>$shorturl</a><br>";
             }
                
            function randomurl(){
                $symbols = "QqWwEeRrTtYyUuIiOoPpAaSsDdFfGgHhJjKkLlZzXxCcVvBbNnMm1234567890";
                $rand = trim(substr(str_shuffle($symbols),0,$this->length));
                #Make random URL and put in DB
                $shorturl2 = "$this->domain$rand";
                $sql2 = "INSERT INTO `shorturl`(`ID`, `longurl`, `shorturl`, `time`) VALUES (NULL,'$this->longurl','$rand','$this->time')";
                mysql_query($sql2);
                #Make redirect
                mkdir("$rand");
                $redirect = "<?php header(\"HTTP/1.1 301 Moved Permanently\"); header(\"Location: $this->longurl\"); exit();";
                file_put_contents("$rand/index.php", $redirect);
                
                echo "Short url: "."<a href=$this->longurl target='_blank'>$shorturl2</a><br>";
            }
            
        }
    ?>
    </body>
</html>
