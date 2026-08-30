<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

requireLogin();
$user = currentUser();

$userCount = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$loggedInAt = date('Y-m-d H:i:s', $_SESSION['logged_in_at'] ?? time());

$title = 'マイページ | vNet';
require __DIR__ . '/includes/header.php';
?>
<h1>マイページ</h1>

<div class="card">
  <p>ようこそ、<strong><?= h($user['username']) ?></strong> さん</p>
  <dl>
    <dt>ユーザー ID</dt>
    <dd><?= h((string) $user['id']) ?></dd>
    <dt>登録日時</dt>
    <dd><?= h($user['created_at']) ?></dd>
    <dt>ログイン日時</dt>
    <dd><?= h($loggedInAt) ?></dd>
    <dt>登録ユーザー数</dt>
    <dd><?= h((string) $userCount) ?></dd>
  </dl>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>