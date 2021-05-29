<?php
session_start();
require('dbconnect.php');


//ログインしてから1時間経つとログアウトする
if(isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()){
    //初めのログインしてから1時間以内にアクセスするとセッションを更新する
    $_SESSION['time'] = time();

    //ログインしているユーザーのデータを取得
    $members = $db->prepare('SELECT * FROM members WHERE id=?');
    $members->execute(array($_SESSION['id']));
    $member = $members->fetch();
  }else{
    header('Location: login.php');
    exit();
  }

//フォームの内容が送信されたかをチェック
if(!empty($_POST)){
		//バリデーションチェック
		if($_POST['title'] === ''){
			$error['title'] = 'blank';
		}

		$fileName = $_FILES['sound_data']['name'];
		// ファイルがアップロードされているか
        if($_FILES['sound_data']['name'] === ''){
			$error['sound_data'] = 'blank';
		}

		if(!empty($fileName)){
			//拡張子をチェック
			$ext = substr($fileName, -3);
			if($ext != 'mp3'){
				$error['sound_data'] = 'type';
			}
		}

		if(empty($error)){
			$sound_data = date('YmdHis') . mt_rand();
			// $_SESSION['join'] = $_POST;
			// $_SESSION['join']['sound_data'] = $sound_data;
            $statement = $db->prepare('INSERT INTO sounds SET title=?, sound_data=?, member_id=?, created=NOW()');
            echo $statement->execute(array(
                $_POST['title'],
                $sound_data,
                $member['id'],
            ));
			move_uploaded_file($_FILES['sound_data']['tmp_name'], 'sound_data/' . $sound_data . ".mp3");
			header('Location: index.php');
			exit();
		}
}


?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>新規投稿</title>

	<link rel="stylesheet" href="style.css" />
</head>
<body>
<div id="wrap">
<div id="head">
<h1>楽曲投稿ページ</h1>
</div>

<div id="content">
<form action="" method="post" enctype="multipart/form-data">
	<dl>
		<dt>タイトル<span class="required">必須</span></dt>
		<dd>
        	<input type="text" name="title" size="35" maxlength="255" value="<?php print(htmlspecialchars($_POST['title'],ENT_QUOTES)); ?>" />
			<?php if($error['title'] === 'blank'): ?>
				<p class="error">*タイトルを入力してください</p>
			<?php endif; ?>
		</dd>

		<dt>楽曲</dt>
		<dd>
        	<input type="file" name="sound_data" size="35" value="test"  />
			<?php if($error['sound_data'] === 'type'): ?>
				<p class="error">*拡張子はmp3でお願いします</p>
			<?php endif; ?>
            <?php if($error['sound_data'] === 'blank'): ?>
				<p class="error">*ファイルを選択してください</p>
			<?php endif; ?>
        </dd>
	</dl>
	<div><input type="submit" value="投稿" /></div>
</form>
</div>

</body>
</html>

