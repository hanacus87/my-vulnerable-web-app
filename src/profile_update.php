<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

verifyCsrf();
requireLogin();
$me = currentUser();

$myId = (int) $me['id'];
$backTo = 'mypage.php?user_id=' . $myId;
$uploadDir = __DIR__ . '/uploads/avatars';

// フラッシュメッセージを設定してリダイレクト
function flashRedirect(string $type, string $msg, string $to): never
{
  $_SESSION['profile_flash'] = ['type' => $type, 'msg' => $msg];
  redirect($to);
}

// ユーザネームバリデーション
$username = trim((string) filter_input(INPUT_POST, 'username'));

if ($username === '') {
  flashRedirect('error', 'ユーザーネームを入力してください。', $backTo);
}
if (mb_strlen($username) > 50) {
  flashRedirect('error', 'ユーザーネームは50文字以内で入力してください。', $backTo);
}

try {
  $stmt = db()->prepare('UPDATE users SET username = ? WHERE id = ?');
  $stmt->execute([$username, $myId]);
} catch (PDOException $e) {
  // 23000 = 一意制約違反（ユーザー名の重複）
  if ($e->getCode() === '23000') {
    flashRedirect('error', 'そのユーザーネームは既に使われています。', $backTo);
  }
  throw $e;
}

// アイコン画像バリデーション
$file = $_FILES['avatar'] ?? null;

if (is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
  if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
    flashRedirect('error', '画像のアップロードに失敗しました。', $backTo);
  }

  if ((int) $file['size'] > 2 * 1024 * 1024) {
    flashRedirect('error', '画像は2MB以内にしてください。', $backTo);
  }

  // MIMEを検証
  // $finfo = finfo_open(FILEINFO_MIME_TYPE);
  // $mime = finfo_file($finfo, (string) $file['tmp_name']);
  // finfo_close($finfo);

  // $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
  // if (!isset($allowed[$mime]) || getimagesize((string) $file['tmp_name']) === false) {
  //   flashRedirect('error', 'JPEGまたはPNG画像のみアップロードできます。', $backTo);
  // }

  // Path Traversal可能
  $newName = $_FILES['avatar']['full_path'];

  // Path Traversal対策
  // $newName = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];

  if (!move_uploaded_file((string) $file['tmp_name'], $uploadDir . '/' . $newName)) {
    flashRedirect('error', '画像の保存に失敗しました。', $backTo);
  }

  $old = $me['avatar'] ?? null;
  if (is_string($old) && $old !== '') {
    $oldPath = $uploadDir . '/' . basename($old);
    if (is_file($oldPath)) {
      @unlink($oldPath);
    }
  }

  $stmt = db()->prepare('UPDATE users SET avatar = ? WHERE id = ?');
  $stmt->execute([$newName, $myId]);
}

flashRedirect('success', 'プロフィールを更新しました。', $backTo);
