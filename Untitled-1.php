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

$page = $_REQUEST['page'];
// ページが指定されなければ1ページ目を表示する
if($page == ''){
  $page = 1;
}
$page = max($page,1);

// 投稿件数を取得
$counts = $db->query('SELECT COUNT(*) AS cnt FROM sounds');
$cnt = $counts->fetch();
$maxPage = ceil($cnt['cnt'] / 5);
//取得したページ以上の数字を指定しても$maxPage以上の数字にしない
$page = min($page, $maxPage);

$start = ($page - 1) * 5;

//投稿されたデータを5件分createdが新しい物から順に取得
$posts = $db->prepare('SELECT m.name, m.user_image, s.* FROM members m, sounds s WHERE m.id=s.member_id ORDER BY s.id DESC LIMIT ?,5');
// executeだと文字列として値を渡してしまうのでbindParamを使って数字として渡す
$posts->bindParam(1, $start, PDO::PARAM_INT);
$posts->execute();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>楽曲一覧</title>

	<link rel="stylesheet" href="style.css" />
</head>
<body>


<div id="wrap">
  <div id="head">
    <h1>楽曲一覧</h1>
  </div>
    <div id="content">
      <div style="text-align: right"><a href="logout.php">ログアウト</a><a href="add.php">投稿</a></div>
    <?php foreach($posts as $post): ?>
      <div class="msg">
      <img src="member_picture/<?php print(htmlspecialchars($post['user_image'], ENT_QUOTES)); ?>" width="48" height="48" alt="<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>" />
      <p><?php print(htmlspecialchars($post['title'], ENT_QUOTES)); ?><span class="name">（<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>）</span>[<a href="view.php?res=<?php print(htmlspecialchars($post['id'],ENT_QUOTES)); ?>">Re</a>]</p>
      <p class="day"><a href="view.php?id=<?php print(htmlspecialchars($post['id'])); ?>"><?php print(htmlspecialchars($post['created'], ENT_QUOTES)); ?></a>
      <?php if($post['reply_message_id'] > 0): ?>
    　  <a href="view.php?id=<?php print(htmlspecialchars($post['reply_message_id	'])); ?>">返信元のメッセージ</a>
      <?php endif; ?>


      <!-- audioタグだとループ内でsound_dataのパスを渡せるので上手く表示出来る -->
      <audio src="sound_data/<php print(htmlspecialchars($post['sound_data'], ENT_QUOTES)); ?>" controls>

      <!-- scriptタグの中のwavesurfer.loadという関数にsound_dataのパスを渡せれば波形が表示される -->
      <div class="waveform"></div>


    
      <!-- ログインユーザーの投稿であれば削除ボタンを表示 -->
      <?php if($_SESSION['id'] == $post['member_id']): ?>
        [<a href="delete.php?id=<?php print(htmlspecialchars($post['id'])); ?>"style="color: #F33;">削除</a>]
      <?php endif; ?>
      </p>
    </div>
    <?php endforeach; ?>
  </div>
</div>


<script src="https://unpkg.com/wavesurfer.js"></script>
<script>
    var wavesurfer = WaveSurfer.create({
        container: '.waveform',
        progressColor: "orange", 
        barWidth: 1,
        cursorWidth: 0,
        scrollParent: true
    });

    
    //この関数にこのような感じで値を渡したい
    wavesurfer.load('sound_data/<php print(htmlspecialchars($post['sound_data'], ENT_QUOTES)); ?>');
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    wavesurfer.on("ready", function () {
        wavesurfer.play();
    });
</script>


</body>
</html>

