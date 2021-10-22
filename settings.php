<?php
    session_start();
    
    $username = $_SESSION['name'];
    //DB接続
    require 'db.php';
    
    //コメント保存用テーブルを作成
    $sql = 'CREATE TABLE IF NOT EXISTS NishChat_thumbnail (
        name char(32),
        thumbnail char(255)
        )
        DEFAULT CHARACTER SET utf8mb4;';
        $stmt = $pdo->query($sql);
    
    //画像アップロード(新規投稿と統合できる？)
    if (isset($_POST['thumbnail_set'])) {//送信ボタンが押された場合
        $image = uniqid(mt_rand(), true);//ファイル名をユニーク化
        $image .= '.' . substr(strrchr($_FILES['image']['name'], '.'), 1);//アップロードされたファイルの拡張子を取得
        $file = "images/$image";
        $sql = "INSERT INTO NishChat_thumbnail(name, thumbnail) VALUES (:name, :thumbnail)";
        $stmt = $pdo->prepare($sql);
        $stmt -> bindParam(':name', $username, PDO::PARAM_STR);
        $stmt->bindValue(':thumbnail', $image, PDO::PARAM_STR);
        if (!empty($_FILES['image']['name'])) {//ファイルが選択されていれば$imageにファイル名を代入
            move_uploaded_file($_FILES['image']['tmp_name'], './thumbnail/' . $image);//imagesディレクトリにファイル保存
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
  <title>Settings</title>
  <link rel="stylesheet" href="css/stylesheet.css">
</head>
<body>
    <header class ="userhome-header">
        <div class = "header-left">
            <a href="index.html"><img class = "headerlogo" src="images for WS/NishChat logo.png"></a> 
        </div>
    </header>
    <main>
        <div class="settings-container">
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
                                <input class = "submit" type="submit" name="thumbnail_set" value="設定する">
                            </form>
                        </div>
                        <div class="prev-con">
                            <div class="preview">
                                <img id="preview" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" style="max-width:100px;">
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </main>
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
    </script>
</body>
</html>