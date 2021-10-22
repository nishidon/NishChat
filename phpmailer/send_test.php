<?php
require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';
require 'setting.php';

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
    $mail->setFrom('nishchat6@gmail.com', 'Ryuichi Nishimura');
    $mail->addAddress('nishidon777@gmail.com', '利用者様'); //受信者（送信先）を追加する
//    $mail->addReplyTo('xxxxxxxxxx@xxxxxxxxxx','返信先');
//    $mail->addCC('xxxxxxxxxx@xxxxxxxxxx'); // CCで追加
//    $mail->addBcc('xxxxxxxxxx@xxxxxxxxxx'); // BCCで追加
    $mail->Subject = MAIL_SUBJECT; // メールタイトル
    $mail->isHTML(true);    // HTMLフォーマットの場合はコチラを設定します
    $body = 'メールの中身';

    $mail->Body  = $body; // メール本文
    // メール送信の実行
    if(!$mail->send()) {
    	echo 'メール送信失敗';
    	echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
    	echo '送信完了！';
    }
?>