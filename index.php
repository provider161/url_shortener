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
        <form action="" method="POST">
            <p><b>Enter url</b><br>
            <textarea name="longurl" cols="50" rows="5" wrap="off"></textarea>
            <br>
            <p><b>Enter desired short url</b><br>
            <textarea name="desireurl" cols="50" rows="5" wrap="off"></textarea>
            <input type="submit" value="Send">
        </form>
        <form action="dbcleaner.php" method="POST">
            <p><b>Clean URLs in DataBase older, than (in days):</b><br>
            <textarea name="cleanperiod" cols="10" rows="1" wrap="off"></textarea>
            <input type="checkbox" name="cleanall"> Clean all URLs in database<br>
            <input type=submit value=Clean>
        </form>
    <?php
        require_once "settings.php";
        
        $longurl = trim(filter_input(INPUT_POST, 'longurl'));
        $desireurl = trim(filter_input(INPUT_POST, 'desireurl'));
        htmlentities($longurl);
        htmlentities($desireurl);
        $time = time();
        global $time, $longurl, $desireurl;

        #Connection to DB
        $dbconn = new mysqli($dbhost, $dbuser, $dbpass, $dbname, $dbport);
        if ($dbconn->connect_error) {
            die('Connect Error (' . $dbconn->connect_errno . ') ' . $dbconn->connect_error);
        }
               
        #Making an URL object
        $resulturl = new Urlgenerator($domain, $length, $longurl, $time, $desireurl, $dbconn);
         
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
        $urlcountquery = $dbconn->query('SELECT * FROM shorturl');
        $urlcount = $urlcountquery->num_rows;
        echo "<br>URLs in database: ".$urlcount."<br>";
        
        #Closing database    
        $dbconn::close;
            
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
            function __construct($classdomain, $classlength, $classlongurl, $classtime, $classdesireurl, $database) {
                $this->domain = $classdomain;
                $this->length = $classlength;
                $this->longurl = $classlongurl;
                $this->time = $classtime;
                $this->desireurl = $classdesireurl;
                $this->database = $database;
            }
                                                
            function desireurl(){
                #checking desire url for doubles in DB
                $desireresult=$this->database->query("SELECT `shorturl` FROM `shorturl` WHERE `shorturl`='$this->desireurl'"); 
                $desireurldouble=$desireresult->fetch_array(MYSQLI_ASSOC);
                if(isset($desireurldouble['shorturl'])) {exit ("<br>Such desire url already exists, enter another one");}
                #Make a shorturl and put it in DB    
                $shorturl = "$this->domain$this->desireurl";
                $this->database->query("INSERT INTO `shorturl` (`ID`, `longurl`, `shorturl`, `time`) VALUES (NULL,'$this->longurl','$this->desireurl','$this->time')");
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
                $this->database->query("INSERT INTO `shorturl`(`ID`, `longurl`, `shorturl`, `time`) VALUES (NULL,'$this->longurl','$rand','$this->time')");
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
