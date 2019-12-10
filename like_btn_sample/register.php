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
$error = $like->validateName($_POST['name']);
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
    <form action="" method="post">
      <h2>ユーザー登録</h2>
      <h4>ユーザーネームを入力してください</h4>
      <div><input class="name" type="text" name="name" value="<?php h($_POST['name']); ?>"></div>
      <p class="error">
        <?php error($error); ?>
      </p>
      <div><input class="submit" type="submit" value="登録する"></div>
      <div><a href="login.php">ログイン</a></div>
    </form>
    <script type="text/javascript" src="script.js"></script>
  </body>
</html>
