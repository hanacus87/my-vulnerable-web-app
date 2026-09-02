<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

verifyCsrf();
requireLogin();
$me = currentUser();

$postId = (int) filter_input(INPUT_POST, 'post_id');

$stmt = db()->prepare('DELETE FROM posts WHERE id = ? AND user_id = ?');
$stmt->execute([$postId, (int) $me['id']]);

redirect('mypage.php?user_id=' . (int) $me['id']);
