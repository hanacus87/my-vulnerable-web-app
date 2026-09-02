<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

verifyCsrf();
requireLogin();
$me = currentUser();

$postTitle = trim((string) filter_input(INPUT_POST, 'title'));
$postContent = trim((string) filter_input(INPUT_POST, 'content'));
$isPublic = isset($_POST['is_public']) ? 1 : 0;

if ($postTitle === '' || $postContent === '') {
  redirect('mypage.php?user_id=' . (int) $me['id']);
  // redirect('mypage.php');
}

// user_idは必ずセッション由来（作成側にIDORを作らない）
$stmt = db()->prepare(
  'INSERT INTO posts (user_id, title, content, is_public) VALUES (?, ?, ?, ?)'
);
$stmt->execute([(int) $me['id'], $postTitle, $postContent, $isPublic]);

redirect('mypage.php?user_id=' . (int) $me['id']);
// redirect('mypage.php');
