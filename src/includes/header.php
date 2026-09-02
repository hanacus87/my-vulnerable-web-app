<?php

declare(strict_types=1);

$title = $title ?? 'vNet';
$user = $me ?? null;
$current = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title) ?></title>
  <link rel="stylesheet" href="assets/style.css">
</head>

<body>
  <header class="site-header">
    <a class="brand" href="index.php">vNet</a>
    <?php if ($user !== null): ?>
      <nav>
        <span><?= h($user['username']) ?></span>
        <form method="post" action="logout.php" class="inline">
          <input type="hidden" name="_token" value="<?= h(csrfToken()) ?>">
          <button type="submit">ログアウト</button>
        </form>
      </nav>
    <?php endif; ?>
  </header>
  <?php if ($user !== null): ?>
    <div class="app">
      <aside class="sidebar">
        <nav>
          <a href="mypage.php?user_id=<?= (int) $user['id'] ?>" class="<?= $current === 'mypage.php' ? 'active' : '' ?>">マイページ</a>
          <!-- <a href="mypage.php" class="<?= $current === 'mypage.php' ? 'active' : '' ?>">マイページ</a> -->
          <a href="posts.php" class="<?= $current === 'posts.php' ? 'active' : '' ?>">ポスト</a>
        </nav>
      </aside>
      <main class="content">
      <?php else: ?>
        <main class="container">
        <?php endif; ?>