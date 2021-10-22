
<script>
    function s() {
        location.href='userhome.php';
    }
    
    function f(){
        location.href='signin_form.php';
    }
</script>

<?php
session_start();


//成功・エラーメッセージの初期化
$errors = array();

//DB接続
require 'db.php';

//ログイン処理
$mailadd = $_POST['mail'];

$sql = "SELECT * FROM user WHERE mail = :mail";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':mail', $mailadd);
$stmt->execute();
$user = $stmt->fetch();
//指定したハッシュがパスワードにマッチしているかチェック
if (password_verify($_POST['pass'], $user['password'])) {
    //DBのユーザー情報をセッションに保存
    $_SESSION['id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['mail'] = $user['mail'];
    $msg = "サインインしました。<script>setTimeout(s, 2000)</script>";
} else {
    $msg = 'メールアドレスもしくはパスワードが間違っています。<br>再度入力してください。<script>setTimeout(f, 2000)</script>';
}
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
        <br>
        <br>
        <h1><?php echo $msg; ?></h1>
    </main>
</body>
</html>