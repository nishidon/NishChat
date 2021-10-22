<?php
session_start();
//クロスサイトリクエストフォージェリ（CSRF）対策
$_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
$token = $_SESSION['token'];
//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NishChat Sign-in</title>
    <link rel="stylesheet" href="css/stylesheet.css">
</head>
<body>
    <header>
        <a href="index.html"><img class = "headerlogo" src="images for WS/NishChat logo.png"></a>
    </header>
    <main>
    <div class = "container">
        <h1>サインイン</h1>
        <form action="signin.php" method="post">
            <div>
                <label>メールアドレス：<label>
                <input class = "info-input" type="text" name="mail" required>
            </div>
            <div>
                <label>パスワード：<label>
                <input class = "info-input" type="password" name="pass" required>
            </div>
            <input class = "submit" type="submit" value="サインイン">
            <p>まだ登録がお済みでない方は<a href="signup_mail.php">こちら</a></p>
        </form>
    </div>
</main>

</body>
</html>
