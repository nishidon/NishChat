<?php
// //DB情報
// $user = 'root';//データベースユーザ名
// $password = 'root';//データベースパスワード
// $dbName = "NishChat";//データベース名
// $host = "localhost";//ホスト
// //DB接続
// $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";
// $pdo = new PDO($dsn, $user, $password);
// $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
// $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

 //Information about your database
 $servername = "localhost";
 $username = "root";
 $password = "root";
 $database = "kredo";

 //connection stiring
 $pdo = new mysqli($servername, $username, $password, $database);

 //check if connection is okay
 if( $pdo->connect_error) {
   die("Connection failed: " . $pdo->connect_error); //die means it exits if executed. That is why echo below will not be shown when fail.
 }
?>