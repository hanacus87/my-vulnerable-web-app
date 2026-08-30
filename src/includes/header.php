<?php

declare(strict_types=1);

$title = $title ?? 'My App';
$user = $user ?? null;
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
  <main class="container">