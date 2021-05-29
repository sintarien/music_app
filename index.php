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

<div id="audio_space">
  <?php foreach($posts as $post): ?>
    <!-- <php var_dump($post); ?> -->
      <div class="msg" id="<?php print(htmlspecialchars($post['sound_data'], ENT_QUOTES)); ?>">
      <img src="member_picture/<?php print(htmlspecialchars($post['user_image'], ENT_QUOTES)); ?>" width="48" height="48" alt="<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>" />
      <p><?php print(htmlspecialchars($post['title'], ENT_QUOTES)); ?><span class="name">（<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>）</span>[<a href="view.php?res=<?php print(htmlspecialchars($post['id'],ENT_QUOTES)); ?>">Re</a>]</p>
      <p class="day"><a href="view.php?id=<?php print(htmlspecialchars($post['id'])); ?>"><?php print(htmlspecialchars($post['created'], ENT_QUOTES)); ?></a>
    <?php if($post['reply_message_id'] > 0): ?>
    <a href="view.php?id=<?php print(htmlspecialchars($post['reply_message_id	'])); ?>">
    返信元のメッセージ</a>
    <?php endif; ?>

        <!-- <audio src="sound_data/<php print(htmlspecialchars($post['sound_data'], ENT_QUOTES)); ?>" controls> -->
        <button onClick="ws_play_wave('M<?php print(htmlspecialchars($post['sound_data'], ENT_QUOTES)); ?>')"> 再生/停止 </button>
        <div id="waveform<?php print(htmlspecialchars($post['sound_data'], ENT_QUOTES)); ?>"></div>

    <!-- ログインユーザーの投稿であれば削除ボタンを表示 -->
    <?php if($_SESSION['id'] == $post['member_id']): ?>
      [<a href="delete.php?id=<?php print(htmlspecialchars($post['id'])); ?>"style="color: #F33;">削除</a>]
    <?php endif; ?>
      </p>
      </div>
  <?php endforeach; ?>
</div>



    <ul class="paging">
      <?php if($page > 1): ?>
       <li><a href="index.php?page=<?php print($page-1); ?>">前のページへ</a></li>
      <?php else: ?>
        <li>前のページへ</li>
      <?php endif; ?>
      <?php if($page < $maxPage): ?>
        <li><a href="index.php?page=<?php print($page+1); ?>">次のページへ</a></li>
      <?php else: ?>
        <li>次のページへ</li>
      <?php endif; ?>
    </ul>
  </div>
</div>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>wavesurfer sample</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/wavesurfer.js/3.0.0/wavesurfer.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.4.1.js" integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU=" crossorigin="anonymous"></script>
</head>

<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<script src="https://unpkg.com/wavesurfer.js"></script>
<script>

let ws_arr = [];

window.onload = function() {
// ここに読み込みが完了したら実行したい処理を記述する
ws_show_wave();
}

function ws_show_wave(){
  //id抽出
  $("#audio_space").children().each(function(index, element){
    console.log($(element).attr('id'));

    var wavesurfer = WaveSurfer.create({
        //containerにidを使って波形を出す場所を指定する
        container: '#waveform' + $(element).attr('id'),
        progressColor: "orange", 
        barWidth: 1,
        cursorWidth: 0,
        scrollParent: true
    });

    wavesurfer.load('sound_data/' + $(element).attr('id') + '.mp3');
    ws_arr["M"+$(element).attr('id')] = wavesurfer;
  });
    console.log(ws_arr);
}

function ws_play_wave(music_id){
  // console.log(music_id);
  ws_arr[music_id].playPause();
}



</script>


</body>
</html>

