<?php
session_start();
//クロスサイトリクエストフォージェリ（CSRF）対策
$_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
$token = $_SESSION['token'];
//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

//エラーメッセージの初期化
$errors = array();

//DB接続
require 'db.php';

//送信ボタンクリックした後の処理
if (isset($_POST['submit'])) {
   //メールアドレス空欄の場合
   if (empty($_POST['mail'])) {
       $errors['mail'] = '<span>メールアドレスが未入力です。</span>';
   }else{
       //POSTされたデータを変数に入れる
       $mailadd = isset($_POST['mail']) ? $_POST['mail'] : NULL;
   
       //メールアドレス構文チェック
       if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $mailadd)){
			$errors['mail_check'] = "<span>メールアドレスの形式が正しくありません。</span>";
       }
       //DB確認        
       $sql = "SELECT id FROM user WHERE mail=:mail";
       $stm = $pdo->prepare($sql);
       $stm->bindValue(':mail', $mailadd, PDO::PARAM_STR);
       
       $stm->execute();
       $result = $stm->fetch(PDO::FETCH_ASSOC);
       //user テーブルに同じメールアドレスがある場合、エラー表示
       if(isset($result["id"])){
			$errors['user_check'] = "<span>このメールアドレスはすでに利用されております。</span>";
       }
       
   }
   //エラーがない場合、pre_userテーブルにインサート
   if (count($errors) === 0){
       $urltoken = hash('sha256',uniqid(rand(),1));
       $url = "https://tech-base.net/tb-230372/NishChat/signup.php?urltoken=".$urltoken;
       //ここでデータベースに登録する
       try{
           //例外処理を投げる（スロー）ようにする
           $sql = "INSERT INTO pre_user (urltoken, mail, date, flag) VALUES (:urltoken, :mail, now(), '0')";
           $stm = $pdo->prepare($sql);
           $stm->bindValue(':urltoken', $urltoken, PDO::PARAM_STR);
           $stm->bindValue(':mail', $mailadd, PDO::PARAM_STR);
           $stm->execute();
           $pdo = null;
           $message = "メールをお送りしました。<br>24時間以内にメールに記載されたURLからご登録下さい。";     
       }catch (PDOException $e){
           print('Error:'.$e->getMessage());
           die();
       }
       
           //メール送信処理
        require 'phpmailer/src/Exception.php';
        require 'phpmailer/src/PHPMailer.php';
        require 'phpmailer/src/SMTP.php';
        require 'phpmailer/setting.php';
    
        // PHPMailerのインスタンス生成
        $mail = new PHPMailer\PHPMailer\PHPMailer();
    
        $mail->isSMTP(); // SMTPを使うようにメーラーを設定する
        $mail->SMTPAuth = true;
        $mail->Host = 'smtp.gmail.com'; // メインのSMTPサーバー（メールホスト名）を指定
        $mail->Username = 'nishchat6@gmail.com'; // SMTPユーザー名（メールユーザー名）
        $mail->Password = 'Engineeringnishidon0911'; // SMTPパスワード（メールパスワード）
        $mail->SMTPSecure = 'tls'; // TLS暗号化を有効にし、「SSL」も受け入れます
        $mail->Port = 587; // 接続するTCPポート
    
        // メール内容設定
        $mail->CharSet = "UTF-8";
        $mail->Encoding = "base64";
        $mail->setFrom('nishchat6@gmail.com', 'NishChat');
        $mail->addAddress($mailadd, '新規利用者様'); //受信者（送信先）を追加する
        //    $mail->addReplyTo('xxxxxxxxxx@xxxxxxxxxx','返信先');
        //    $mail->addCC('xxxxxxxxxx@xxxxxxxxxx'); // CCで追加
        //    $mail->addBcc('xxxxxxxxxx@xxxxxxxxxx'); // BCCで追加
        $mail->Subject = '仮登録完了'; // メールタイトル
        $mail->isHTML(true);    // HTMLフォーマットの場合はコチラを設定します
        $body = "下記のURLをクリックして、本登録に進んでください。<br>{$url}";
    
        $mail->Body  = $body; // メール本文
        // メール送信の実行
        if (!$mail->send()) {
        	echo 'メール送信失敗';
        	echo 'Mailer Error: ' . $mail->ErrorInfo;
        }

   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NishChat Sign-up</title>
    <link rel="stylesheet" href="css/stylesheet.css">
</head>
<body>
    <header>
        <a href="index.html"><img class = "headerlogo" src="images for WS/NishChat logo.png"></a>
    </header>
    <main>
        <div class = "container">
            <h1>仮会員登録</h1>
            <?php if (isset($_POST['submit']) && count($errors) === 0): ?>
            <!-- 登録完了画面 -->
            <p><?=$message?></p>
            <?php else: ?>
            <!-- 登録画面 -->
            <?php if (count($errors) > 0): ?>
                <?php
                foreach($errors as $value){
                    echo "<p class='error'>".$value."</p>";
                }
                ?>
            <?php endif; ?>
            <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="post">
                <p>メールアドレス：<input class = "info-input" type="text" name="mail" size="50" value="<?php if( !empty($_POST['mail']) ){ echo $_POST['mail']; } ?>"></p> 
                <input type="hidden" name="token" value="<?=$token?>">
                <input class = "submit" type="submit" name="submit" value="送信">
            </form>
            <p>すでに登録済の方は<a href="signin_form.php">こちら</a></p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>