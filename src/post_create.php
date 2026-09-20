<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

// CSRF対策
// verifyCsrf();
requireLogin();
$me = currentUser();

$postTitle = trim((string) filter_input(INPUT_POST, 'title'));
$postContent = trim((string) filter_input(INPUT_POST, 'content'));
$isPublic = isset($_POST['is_public']) ? 1 : 0;

if ($postTitle === '' || $postContent === '') {
  redirect('mypage.php?user_id=' . (int) $me['id']);
  // redirect('mypage.php');
}

$stmt = db()->prepare(
  'INSERT INTO posts (user_id, title, content, is_public) VALUES (?, ?, ?, ?)'
);
$stmt->execute([(int) $me['id'], $postTitle, $postContent, $isPublic]);

redirect('mypage.php?user_id=' . (int) $me['id']);
// redirect('mypage.php');
