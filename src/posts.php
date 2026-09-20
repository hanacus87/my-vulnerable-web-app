<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

requireLogin();
$me = currentUser();

$stmt = db()->query(
  'SELECT p.id, p.title, p.content, p.created_at, u.username, u.avatar
    FROM posts p
    JOIN users u ON u.id = p.user_id
    WHERE p.is_public = 1
    ORDER BY p.created_at DESC'
);
$posts = $stmt->fetchAll();

$title = 'ポスト | vNet';
require __DIR__ . '/includes/header.php';
?>
<h1>ポスト</h1>

<?php if (empty($posts)): ?>
  <p>公開されている投稿はありません。</p>
<?php else: ?>
  <?php foreach ($posts as $post): ?>
    <article class="card post">
      <h2><?= h($post['title']) ?></h2>
      <p class="post-meta">
        <?= avatarTag($post['avatar'] ?? null, (string) $post['username'], 'avatar avatar-sm') ?>
        <span><?= h($post['username']) ?> ・ <?= h($post['created_at']) ?></span>
      </p>
      <!-- XSS可能 -->
      <div class="post-content"><?= nl2br($post['content']) ?></div>

      <!-- XSS対策 -->
      <!-- <div class="post-content"><?= nl2br(h($post['content'])) ?></div> -->
    </article>
  <?php endforeach; ?>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>