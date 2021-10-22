<script>
    function s() {
        location.href='signin_form.php';
    }
    
    function f(){
        location.href='signup_mail.php';
    }
    
    function notoken(){
        location.href='index.html';
    }
</script>
<?php
session_start();
//クロスサイトリクエストフォージェリ（CSRF）対策
$_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
$token = $_SESSION['token'];
//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

function h($s) {
  return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}

//成功・エラーメッセージの初期化
$errors = array();

//DB接続
require 'db.php';

if(empty($_GET)) {
	header("Location: index.html");
	exit();
}else{
	//GETデータを変数に入れる
	$urltoken = isset($_GET["urltoken"]) ? $_GET["urltoken"] : NULL;
	//メール入力判定
	if ($urltoken == ''){
		$errors['urltoken'] = "トークンがありません。<script>setTimeout(notoken, 2000)</script>";
	}else{
		try{	
			//flagが0の未登録者 or 仮登録日から24時間以内
			$sql = "SELECT mail FROM pre_user WHERE urltoken=(:urltoken) AND flag =0 AND date > now() - interval 24 hour";
           $stm = $pdo->prepare($sql);
			$stm->bindValue(':urltoken', $urltoken, PDO::PARAM_STR);
			$stm->execute();
			
			//レコード件数取得
			$row_count = $stm->rowCount();
			
			//24時間以内に仮登録され、本登録されていないトークンの場合
			if( $row_count ==1){
				$mail_array = $stm->fetch();
				$mailadd = $mail_array["mail"];
				$_SESSION['mail'] = $mailadd;
			}else{
				$errors['urltoken_timeover'] = "このURLはご利用できません。有効期限が過ぎたかURLが誤っている可能性がございます。<br>もう一度登録をやりなおして下さい。<script>setTimeout(notoken, 5000)</script>";
			}
			
       
			//データベース接続切断
			$stm = null;
		}catch (PDOException $e){
			print('Error:'.$e->getMessage());
			die();
		}
	}
}

/**
* 確認する(btn_confirm)押した後の処理
*/
if(isset($_POST['btn_confirm'])){
	if(empty($_POST)) {
		header("Location: index.html");
		exit();
	}else{
		//POSTされたデータを各変数に入れる
		$name = isset($_POST['name']) ? $_POST['name'] : NULL;
		$password = isset($_POST['password']) ? $_POST['password'] : NULL;
		
		//セッションに登録
		$_SESSION['name'] = $name;
		$_SESSION['password'] = $password;

		//アカウント入力判定
		//パスワード入力判定
		if ($password == ''):
			$errors['password'] = "パスワードが入力されていません。";
		else:
			$password_hide = str_repeat('*', strlen($password));
		endif;

		if ($name == ''):
			$errors['name'] = "ユーザーネームが入力されていません。";
		endif;
		
		//DB接続
        require 'db.php';
        
		//DB確認        
           $sql = "SELECT id FROM user WHERE name=:name";
           $stm = $pdo->prepare($sql);
           $stm->bindValue(':name', $name, PDO::PARAM_STR);
           
           $stm->execute();
           $result = $stm->fetch(PDO::FETCH_ASSOC);
           //user テーブルに同じnameがある場合、エラー表示
           if(isset($result["id"])){
    			$errors['user_check'] = "<span>このユーザーネームはすでに利用されております。</span>";
           }
		
	}
	
}

/**
* page_3
* 登録(btn_submit)押した後の処理
*/
if(isset($_POST['btn_submit'])){
	//パスワードのハッシュ化
	$password_hash =  password_hash($_SESSION['password'], PASSWORD_DEFAULT);

	//ここでデータベースに登録する
	try{
		$sql = "INSERT INTO user (name,password,mail,status,created_at,updated_at) VALUES (:name,:password_hash,:mail,1,now(),now())";
       $stm = $pdo->prepare($sql);
		$stm->bindValue(':name', $_SESSION['name'], PDO::PARAM_STR);
		$stm->bindValue(':mail', $_SESSION['mail'], PDO::PARAM_STR);
		$stm->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
		$stm->execute();

		//pre_userのflagを1にする(トークンの無効化)
		$sql = "UPDATE pre_user SET flag=1 WHERE mail=:mail";
		$stm = $pdo->prepare($sql);
		//プレースホルダへ実際の値を設定する
		$stm->bindValue(':mail', $mailadd, PDO::PARAM_STR);
		$stm->execute();
						
		/*
		* 登録ユーザと管理者へ仮登録されたメール送信
       */
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
        $mail->addAddress($mailadd, '利用者様'); //受信者（送信先）を追加する
        //    $mail->addReplyTo('xxxxxxxxxx@xxxxxxxxxx','返信先');
        //    $mail->addCC('xxxxxxxxxx@xxxxxxxxxx'); // CCで追加
        //    $mail->addBcc('xxxxxxxxxx@xxxxxxxxxx'); // BCCで追加
        $mail->Subject = '登録完了'; // メールタイトル
        $mail->isHTML(true);    // HTMLフォーマットの場合はコチラを設定します
        $body = 'ご登録ありがとうございます。<br>サインインは<a href="https://tech-base.net/tb-230372/NishChat/signin_form.php">こちら</a>';
    
        $mail->Body  = $body; // メール本文
        // メール送信の実行
        if(!$mail->send()) {
        	echo 'メール送信失敗';
        	echo 'Mailer Error: ' . $mail->ErrorInfo;
        } 
        
		//データベース接続切断
		$stm = null;

		//セッション変数を全て解除
		$_SESSION = array();
		//セッションクッキーの削除
		if (isset($_COOKIE["PHPSESSID"])) {
				setcookie("PHPSESSID", '', time() - 1800, '/');
		}
		//セッションを破棄する
		session_destroy();

	}catch (PDOException $e){
		//トランザクション取り消し（ロールバック）
		$pdo->rollBack();
		$errors['error'] = "もう一度やりなおして下さい。";
		print('Error:'.$e->getMessage());
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

    			<h1>会員登録画面</h1>
    
    			<!-- page_3 完了画面-->
    			<?php if(isset($_POST['btn_submit']) && count($errors) === 0): ?>
    			<br>
    			<br>
    			<h3>本登録が完了いたしました。<br>サインインページへ移動します。<script>setTimeout(s, 3000)</script></h3>
    
    			<!-- page_2 確認画面-->
    			<?php elseif (isset($_POST['btn_confirm']) && count($errors) === 0): ?>
    				<form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>?urltoken=<?php print $urltoken; ?>" method="post">
    					<p>メールアドレス：<?=h($_SESSION['mail'])?></p>
    					<p>パスワード：<?=$password_hide?></p>
    					<p>ユーザーネーム：<?=h($name)?></p>
    					
    					<input class = "back" type="submit" name="btn_back" value="戻る">
    					<input type="hidden" name="token" value="<?=$_POST['token']?>">
    					<input class = "submit" type="submit" name="btn_submit" value="登録する">
    				</form>
    
    			<?php else: ?>
    				<!-- page_1 登録画面 -->
    				<?php if(count($errors) > 0): ?>
    						<?php
    						foreach($errors as $value){
    								echo "<p class='error'>".$value."</p>";
    						}
    						?>
    				<?php endif; ?>
    					<?php if(!isset($errors['urltoken_timeover'])): ?>
    						<form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>?urltoken=<?php print $urltoken; ?>" method="post">
    							<p>メールアドレス：<?=h($mailadd)?></p>
    							<p>パスワード：<input class = "info-input" type="password" name="password"></p>
    							<p>ユーザーネーム：<input class = "info-input" type="text" name="name" value="<?php if( !empty($_SESSION['name']) ){ echo h($_SESSION['name']); } ?>"></p>
    							<input type="hidden" name="token" value="<?=$token?>">
    							<input class = "submit" type="submit" name="btn_confirm" value="確認する">
    						</form>
    					<?php endif ?>
    			<?php endif; ?>
			</div>
		</main>

</body>
</html>