<?php
function h($value) {
  echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function error($error) {
  if ($error == 'blank') {
    echo 'ユーザーネームが入力されていません';
  } else if ($error == 'length') {
    echo 'ユーザーネームは3文字以上にしてください';
  } else if ($error == 'same'){
    echo 'このユーザーネームは使用できません';
  }
}
