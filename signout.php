<?php
session_start();

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');
$_SESSION = array();//セッションの中身をすべて削除
session_destroy();//セッションを破壊
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="3; URL=index.html">
    <title>NishChat SeeYou</title>
    <link rel="stylesheet" href="css/stylesheet.css">
</head>
<body>
    <header>
        <a href="index.html"><img class = "headerlogo" src="images for WS/NishChat logo.png"></a>
    </header>
    <main>
        <br>
        <br>
        <h1>サインアウトしました。<br>ご利用ありがとうございました。</h1>
    </main>
</body>
</html>