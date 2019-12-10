<?php
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/Like.php');

try {
  $like = new \MyApp\Like();
} catch (Exception $e) {
  echo $e->getMessage();
  exit;
}

$like->verify();
?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="description" content="Ajaxを用いて「いいね！」ボタンを作ってみました">
    <title>Ajaxで「いいね!」ボタン　作ってみた</title>
    <link rel="shortcut icon" href="favicon.ico">
    <link rel="stylesheet" type="text/css" href="styles.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-color/2.1.2/jquery.color.js"></script>
  </head>
  <body>
    <div class="logout">
      <a href="/index.php?action=logout">ログアウト</a>
    </div>
    <p class="count"><?php h($like->readLikesCount()); ?></p>
    <div class="icon">
      <i class="fas fa-thumbs-up <?php if ($_SESSION['btn'] == 'on') { echo 'like'; } ?>"></i>
      <input type="hidden" id="token" value="<?php h($_SESSION['token']); ?>">
    </div>
    <div class="comment" style="<?php if ($_SESSION['btn'] == 'off') { echo 'display: none;'; } ?>">
      <div>
        <p>「いいね！」ありがとうございます！！</p>
      </div>
    </div>
    <script type="text/javascript" src="script.js"></script>
  </body>
</html>
