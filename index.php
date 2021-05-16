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

//投稿するボタンがクリックされた時
if(!empty($_POST)){
  //投稿フォームが空でなければデータをdbに登録
  if($_POST['message'] !== ''){


    $message = $db->prepare('INSERT INTO posts SET member_id=?, message=?, created=NOW()');
    $message->execute(array(
      $member['id'],
      $_POST['message']
    ));

    //POST処理が行われたらもう一度自分自身を呼び出してPOSTの値を初期化する
    header('Location: index.php');
    exit();
  }
}
//投稿されたデータをcreatedが新しい物から順に取得
$posts = $db->query('SELECT m.name, m.user_image, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC');
?>


<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>ひとこと掲示板</title>

	<link rel="stylesheet" href="style.css" />
</head>

<body>
<div id="wrap">
  <div id="head">
    <h1>ひとこと掲示板</h1>
  </div>
  <div id="content">
  	<div style="text-align: right"><a href="logout.php">ログアウト</a></div>
    <form action="" method="post">
      <dl>
        <dt><?php print(htmlspecialchars($member['name'],ENT_QUOTES)); ?>さん、メッセージをどうぞ</dt>
        <dd>
          <textarea name="message" cols="50" rows="5"></textarea>
          <input type="hidden" name="reply_post_id" value="" />
        </dd>
      </dl>
      <div>
        <p>
          <input type="submit" value="投稿する" />
        </p>
      </div>
    </form>

<?php foreach($posts as $post): ?>
    <div class="msg">
    <img src="member_picture/<?php print(htmlspecialchars($post['user_image'], ENT_QUOTES)); ?>" width="48" height="48" alt="<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>" />
    <p><?php print(htmlspecialchars($post['message'], ENT_QUOTES)); ?><span class="name">（<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>）</span>[<a href="index.php?res=">Re</a>]</p>
    <p class="day"><a href="view.php?id="><?php print(htmlspecialchars($post['created'], ENT_QUOTES)); ?></a>
<a href="view.php?id=">
返信元のメッセージ</a>
[<a href="delete.php?id="
style="color: #F33;">削除</a>]
    </p>
    </div>
<?php endforeach; ?>

<ul class="paging">
<li><a href="index.php?page=">前のページへ</a></li>
<li><a href="index.php?page=">次のページへ</a></li>
</ul>
  </div>
</div>
</body>
</html>
