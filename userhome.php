<?php
session_start();
//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');
//XSS対策
function h($s) {
  return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}
$mailadd = $_SESSION['mail'];
$username = $_SESSION['name'];

if (isset($_SESSION['id'])) {//ログインしているとき
    $link = '<a href="signout.php">サインアウト</a>';
} else {//ログインしていない時
    $link = '<a href="signin_form.php">サインイン</a>';
}

    //DB接続
    require 'db.php';
    
    //コメント保存用テーブルを作成
    $sql = 'CREATE TABLE IF NOT EXISTS NishChat (
        id INT auto_increment primary key,
        name char(32),
        comment text,
        time timestamp,
        image_name char(255),
        edited INT(1) default 0
        )
        DEFAULT CHARACTER SET utf8mb4;';
        $stmt = $pdo->query($sql);
        
    
    //変数代入
    
    $time = date("Y/m/d H:i:s");
    
    if(!empty($_POST['submit'])) {
        $comment = $_POST['comment'];
        $hiddennum = $_POST['hiddennum'];
    }
    
    if(!empty($_POST['delete'])){
        $deleditnum = $_POST['deleditnum'];
    }
    
     if(!empty($_POST['edit'])){
        $deleditnum = $_POST['deleditnum'];
    }
    
    //新規投稿
    if(!empty($_POST['comment']) && isset($_POST['submit']) && empty($_POST['hiddennum'])) {
        $sql = $pdo -> prepare("INSERT INTO NishChat (name, comment, time) VALUES (:name, :comment, :time)");
        $sql -> bindParam(':name', $username, PDO::PARAM_STR);
        $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
        $sql -> bindParam(':time', $time, PDO::PARAM_STR);
        $sql -> execute();

    //再表示後編集
    }elseif(!empty($_POST['comment']) && isset($_POST['submit']) && !empty($_POST['hiddennum'])) { 
        $id = $hiddennum;
        $edited = 1;
        $sql = 'UPDATE NishChat SET comment=:comment,time=:time, edited=:edited WHERE id=:id and name=:name';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':time', $time, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $username, PDO::PARAM_STR);
        $stmt->bindParam(':edited', $edited, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    //削除
    if(isset($_POST['delete'])) {
        $id = $deleditnum;
        $sql = 'delete from NishChat where id=:id and name=:name';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $username, PDO::PARAM_STR);
        $stmt->execute();
    }
    
    //再表示
    if(isset($_POST['edit']) ) {
        $id = $deleditnum;
        $sql = 'SELECT * FROM NishChat where id=:id and name=:name' ;
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $username, PDO::PARAM_STR);
        $stmt->execute();
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            //$rowの中にはテーブルのカラム名が入る
            $edit_comment = $row['comment'];
        }
    }
    
    //画像アップロード(新規投稿と統合できる？)
    if (isset($_POST['upload'])) {//送信ボタンが押された場合
        $image = uniqid(mt_rand(), true);//ファイル名をユニーク化
        $image .= '.' . substr(strrchr($_FILES['image']['name'], '.'), 1);//アップロードされたファイルの拡張子を取得
        $file = "images/$image";
        $sql = "INSERT INTO NishChat(name, time, image_name) VALUES (:name, :time, :image)";
        $stmt = $pdo->prepare($sql);
        $stmt -> bindParam(':name', $username, PDO::PARAM_STR);
        $stmt -> bindParam(':time', $time, PDO::PARAM_STR);
        $stmt->bindValue(':image', $image, PDO::PARAM_STR);
        if (!empty($_FILES['image']['name'])) {//ファイルが選択されていれば$imageにファイル名を代入
            move_uploaded_file($_FILES['image']['tmp_name'], './images/' . $image);//imagesディレクトリにファイル保存
            if (exif_imagetype($file)) {//画像ファイルかのチェック
                $stmt->execute();
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
    <title>NishChat UserHome</title>
    <link rel="stylesheet" href="css/stylesheet.css">
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
</head>
<body>
    <header class ="userhome-header">
        <div class = "header-left">
            <a href="index.html"><img class = "headerlogo" src="images for WS/NishChat logo.png"></a> 
        </div>
        <a class="settings" href="settings.php" target="_blank"><i class="fas fa-cog"></i></a>
        <div class = "header-right">
            <?php echo $link; ?>
        </div>
    </header>
    <?php if (isset($_SESSION['id'])): //サインインしているときのみ表示 ?>
        <main>
            
          <!--投稿フォーム-->
            <div class = "userform">
                <!--画像アップロード-->
                <div class = "image-form-con">
                    <div class = "image-form">
                        <div class="imgf-con">
                            <form method="post" enctype="multipart/form-data"> 
                                <label>
                                    <span class = "image-upload">
                                        
                                        <i class="fas fa-images"></i>
                                        選択
                                    </span>
                                    <input class = "image-input" type="file" accept='image/*' name="image" onchange="previewImage(this);"><br>
                                </label>
                                <input class = "submit" type="submit" name="upload" value="送信">
                            </form>
                        </div>
                        <div class="prev-con">
                            <div class="preview">
                                <p>Preview:</p>
                                <img id="preview" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" style="max-width:100px;">
                            </div>
                        </div>
                    </div>
                </div>
                    
                <!--コメント投稿-->
                <form method = "post">
                    <div class = "toko-container">
                        <div class = "toko">
                            <textarea name = "comment" placeholder = "コメント"><?php if (!empty($edit_comment)) { echo $edit_comment; } ?></textarea>
                            <br>
                            <input class = "submit"  type = "submit" name = "submit" value = "送信">
                        </div>
                    </div>
                    <input type = "hidden" name = "hiddennum" value = "<?php if(!empty($_POST['deleditnum'])){ echo $_POST['deleditnum']; } ?>">
                </form>
            </div>
            
            <!--投稿-->
            <div class = "posts">
                <?php
                $sql = 'SELECT * FROM NishChat';
                $stmt = $pdo->query($sql);
                $results = $stmt->fetchAll(); ?>
                
                <?php foreach ($results as $row):?>
                    <div class = "each-post">
                        <div class="post-label">
                            <?php 
                            echo $row['id'].' ';
                            echo $row['name'].' ';
                            echo $row['time'].' ';
                            if ($row['edited'] != 0){
                                echo "(編集済み)";
                            }
                            ?>
                        </div>
                            <!--消去、編集-->
                        <?php if ($username == $row['name']): ?>
                        <form method = "post">
                            <div class = "deledit-container">
                                <div class = "deledit">
                                    <label>
                                        <i class="fas fa-trash"></i>
                                        <input class = "delete"  type = "submit" name = "delete" value = "削除">
                                    </label>
                                     <?php if ($row['comment']!=null): ?>
                                        <label>
                                            <i class="far fa-edit"></i>
                                            <input class = "edit"  type = "submit" name = "edit" value = "編集">
                                        </label>
                                    <?php endif;?>
                                    
                                    <input type = "hidden" name = "deleditnum"  value = "<?php echo $row['id']; ?>">
                                    
                                </div>
                            </div>
                        </form>
                        <?php endif;?>
                        <!--コメント欄だけ色変える-->
                        <div class = "comment-box">  
                            <?php echo nl2br(h($row['comment'])); ?>
                            <?php if ($row['image_name']!=null): ?>
                                <img class = "post-img" src="images/<?php echo $row['image_name']; ?>" width="20%" height="20%">
                            <?php endif;?>
                        </div>
                    </div>
                <?php endforeach;?>
            </div>
        </main>
    <?php endif; ?>
    <!--JavaScript-->
    <script>
        // 画像プレビュー機能
        function previewImage(obj)
        {
        	var fileReader = new FileReader();
        	fileReader.onload = (function() {
        		document.getElementById('preview').src = fileReader.result;
        	});
        	fileReader.readAsDataURL(obj.files[0]);
        }
        // ページの開始位置を最下部にする
        //htmlの高さ- スクロールバーを除くビューポートの高さとすることで､
        //画面下部に移動します｡アニメーション無し｡
        let scrollHeight = Math.max(
          document.body.scrollHeight, document.documentElement.scrollHeight,
          document.body.offsetHeight, document.documentElement.offsetHeight,
          document.body.clientHeight
        );
        scrollTo(0, scrollHeight - document.documentElement.clientHeight);
    </script>
</body>
</html>
