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
                <textarea name="longurl" cols="50" rows="5" wrap="off" autofocus="autofocus" placeholder="Enter your URL" required="required"></textarea>
            <br>
            <p><b>Enter desired short url</b><br>
                <textarea name="desireurl" cols="50" rows="5" wrap="off" placeholder="There you can type desire short URL"></textarea>
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
                # Checking desire url for doubles in DB
                $desireresult=$this->database->query("SELECT `shorturl` FROM `shorturl` WHERE `shorturl`='$this->desireurl'"); 
                $desireurldouble=$desireresult->fetch_array(MYSQLI_ASSOC);
                if(isset($desireurldouble['shorturl'])) {exit ("<br>Such desire url already exists, enter another one");}
                # Create desire shorturl
                $this->makingshorturl($this->desireurl);
                $this->makingredirect($this->desireurl);
             }
                
            function randomurl(){
                # Checking DB for already existing longurl
                $checkresult=$this->database->query("SELECT `longurl`,`shorturl` FROM `shorturl` WHERE `longurl`='$this->longurl'"); 
                $longurldouble=$checkresult->fetch_row();
                if(isset($longurldouble)){
                    $shorturl = $this->domain.$longurldouble[1];
                    echo "Short url: "."<a href=$$this->longurl target='_blank'>$shorturl</a><br>";
                } else{
                # Create random short url
                $symbols = "QqWwEeRrTtYyUuIiOoPpAaSsDdFfGgHhJjKkLlZzXxCcVvBbNnMm1234567890";
                $rand = trim(substr(str_shuffle($symbols),0,$this->length));
                $this->makingshorturl($rand);
                $this->makingredirect($rand);
                }
            }
            
            function makingshorturl($url){
                $shorturl = "$this->domain$url";
                #$this->database->query("INSERT INTO `shorturl` (`ID`, `longurl`, `shorturl`, `time`) VALUES (NULL,'$this->longurl','$url','$this->time')");
                $this->mysqlsecureinput($this->longurl, $url);
                echo "Short url: "."<a href=$$this->longurl target='_blank'>$shorturl</a><br>";
            }
            
            function makingredirect($url){
                mkdir("$url");
                $redirect = "<?php header(\"HTTP/1.1 301 Moved Permanently\"); header(\"Location: $this->longurl\"); exit();";
                file_put_contents("$url/index.php", $redirect);
            }            
            
            function mysqlsecureinput($url1, $url2){
                $input = $this->database->prepare("INSERT INTO `shorturl` (`ID`, `longurl`, `shorturl`, `time`) VALUES (NULL,?,?,'$this->time')");
                $input->bind_param("ss", $url1, $url2);
                $input->execute();
                $input->close();
            }
        }
    ?>
    </body>
</html>"
