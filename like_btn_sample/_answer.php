<?php
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/Like.php');

try {
  $like = new \MyApp\Like();
} catch (Exception $e) {
  echo $e->getMessage();
  exit;
}

try {
  $like->updateLikesCount();
  $likes_count = $like->readLikesCount();
} catch (Exception $e) {
  header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
  echo $e->getMessage();
  exit;
}

header('Content-Type: application/json; charset=UTF-8');
echo json_encode([
  'likes_count' => $likes_count,
  'btn' => $btn
]);
