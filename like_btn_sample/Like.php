<?php
namespace MyApp;

class Like {
  private $db;

  public function __construct() {
    $this->connectDB();
    $this->createToken();
  }

  private function connectDB() {
    try {
      $this->db = new \PDO(PDO_DSN, DB_USERNAME, DB_PASSWORD);
      $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    } catch (\PDOException $e) {
      throw new \Exception('データベースの接続に失敗しました');
    }
  }

  private function createToken() {
    if (!isset($_SESSION['token'])) {
      $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(16));
    }
  }

  private function validateToken() {
    if (
      !isset($_SESSION['token']) ||
      !isset($_POST['token']) ||
      $_SESSION['token'] !== $_POST['token']
    ) {
      throw new \Exception('invalid token!');
    }
  }

  public function validateName($name) {
    // リロード時はエラーを出さない
    if (!isset($name)) {
      return false;
    }

    // 名前が入力されていなかったらエラー
    if (empty($name)) {
      return 'blank';
    }

    // 名前が３文字以上でなかったらエラー
    if (mb_strlen($name) < 3) {
      return 'length';
    }

    if ($_SERVER['REQUEST_URI'] == '/register.php') {
      $sql = 'SELECT * FROM users';
      $users = $this->db->query($sql);

      // 同じ名前が見つかったらエラー
      while ($user = $users->fetch()) {
        if ($user['name'] == $name) {
          return 'same';
        }
      }
    }

    if ($_SERVER['REQUEST_URI'] == '/register.php') {
      // 登録処理
      $this->register($name);
    } else {
      // ログイン処理
      $this->login($name);
    }
  }

  private function register($name) {
    // usersテーブルにデータを追加
    $sql = 'INSERT INTO users SET name=?';
    $statement = $this->db->prepare($sql);
    $statement->execute([$name]);

    header('Location: http://' . $_SERVER['HTTP_HOST'] . '/login.php');
    exit;
  }

  private function login($name) {
    $sql = 'SELECT * FROM users';
    $users = $this->db->query($sql);

    foreach ($users->fetchAll() as $user) {
      // usersテーブルにフォームから入力した名前があるか
      if ($user['name'] == $name) {
        // ログイン情報を保存
        $this->createCookieAndSession($user['id'], $name);

        header('Location: http://' . $_SERVER['HTTP_HOST'] . '/index.php');
        exit;
      }
    }

    // error message(its username not founded)

    header('Location: http://' . $_SERVER['HTTP_HOST'] . '/login.php');
    exit;
  }

  public function verify() {
    $this->checkLogout();
    $this->checkLogin();
  }

  private function checkLogin() {
    if (!empty($_COOKIE['user_id']) && !empty($_COOKIE['name'])) {
      // ログイン済みの時の処理
      // ログインを記憶する
      $this->createCookieAndSession($_COOKIE['user_id'], $_COOKIE['name']);

      if ($_SERVER['REQUEST_URI'] != '/index.php') {
        header('Location: http://' . $_SERVER['HTTP_HOST'] . '/index.php');
        exit;
      }
    } else {
      // 未ログインの時の処理
      if (
        $_SERVER['REQUEST_URI'] != '/register.php' &&
        $_SERVER['REQUEST_URI'] != '/login.php'
      ) {
        header('Location: http://' . $_SERVER['HTTP_HOST'] . '/login.php');
        exit;
      }
    }
  }

  private function checkLogout() {
    if ($_GET['action'] == 'logout') {
      setcookie('user_id', ' ', time() - 60);
      setcookie('name', ' ', time() - 60);
      setcookie('post_id', ' ', time() - 60);
      unset($_COOKIE);

      $_SESSION = [];
      session_destroy();
    }
  }

  private function createCookieAndSession($id, $name) {
    setcookie('user_id', $id, time() + 60 * 10);
    setcookie('name', $name, time() + 60 * 10);

    $sql = 'SELECT * FROM posts ORDER BY id ASC LIMIT 1';
    $posts = $this->db->query($sql);
    $post = $posts->fetch();
    setcookie('post_id', $post['id'], time() + 60 * 10);

    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['post_id'] = $_COOKIE['post_id'];

    $sql = 'SELECT * FROM likes WHERE user_id=? AND post_id=?';
    $like = $this->db->prepare($sql);
    $like->bindParam(1, $_SESSION['user_id'], \PDO::PARAM_INT);
    $like->bindParam(2, $_SESSION['post_id'], \PDO::PARAM_INT);
    $like->execute();

    if ($like->rowCount() > 0) {
      $_SESSION['btn'] = 'on';
    } else {
      $_SESSION['btn'] = 'off';
    }
  }

  public function readLikesCount() {
    $sql = 'SELECT * FROM posts ORDER BY id ASC LIMIT 1';
    $posts = $this->db->query($sql);
    $post = $posts->fetch();
    return $post['likes_count'];
  }

  public function updateLikesCount() {
    $this->validateToken();

    // likesテーブルからデータを取り出す
    $sql = 'SELECT * FROM likes WHERE user_id=? AND post_id=?';
    $like = $this->db->prepare($sql);
    $like->bindParam(1, $_SESSION['user_id'], \PDO::PARAM_INT);
    $like->bindParam(2, $_SESSION['post_id'], \PDO::PARAM_INT);
    $like->execute();

    // postsテーブルから1つのデータのlikes_countを取り出す
    $sql = 'SELECT * FROM posts ORDER BY id ASC LIMIT 1';
    $posts = $this->db->query($sql);
    $post = $posts->fetch();
    $likes_count = $post['likes_count'];

    // likesテーブル内のデータを更新or削除
    // postsテーブル内のデータのlikes_countを調整
    if ($like->rowCount() > 0) {
      $sql = 'DELETE FROM likes WHERE user_id=? AND post_id=?';
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(1, $_SESSION['user_id'], \PDO::PARAM_INT);
      $stmt->bindParam(2, $_SESSION['post_id'], \PDO::PARAM_INT);
      $stmt->execute();

      $likes_count--;
    } else {
      $sql = 'INSERT INTO likes (user_id, post_id) VALUES(?, ?)';
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(1, $_SESSION['user_id'], \PDO::PARAM_INT);
      $stmt->bindParam(2, $_SESSION['post_id'], \PDO::PARAM_INT);
      $stmt->execute();

      $likes_count++;
    }

    // postsテーブルの1つのデータのlikes_countを更新
    $sql = 'UPDATE posts SET likes_count=? WHERE id=?';
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(1, $likes_count, \PDO::PARAM_INT);
    $stmt->bindParam(2, $_SESSION['post_id'], \PDO::PARAM_INT);
    $stmt->execute();
  }
}
