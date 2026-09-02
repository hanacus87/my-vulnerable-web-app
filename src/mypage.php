<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

requireLogin();
$me = currentUser();
$user = $me; // ヘッダのサイドバー/ナビ表示用

// IDOR可能(本人確認なし)
$targetId = (int) ($_GET['user_id'] ?? 0);

// IDOR対策(セッションの自分のidを使う)
// $targetId = (int) $me['id'];

$stmt = db()->prepare(
  'SELECT id, title, content, is_public, created_at FROM posts WHERE user_id = ? ORDER BY created_at DESC'
);
$stmt->execute([$targetId]);
$posts = $stmt->fetchAll();

$ownerStmt = db()->prepare('SELECT username FROM users WHERE id = ?');
$ownerStmt->execute([$targetId]);
$ownerName = $ownerStmt->fetchColumn();

$title = 'マイページ | vNet';
require __DIR__ . '/includes/header.php';
?>
<h1><?= $ownerName !== false ? h((string) $ownerName) . ' さんの投稿' : '投稿' ?></h1>

<section class="card">
  <h2>新規投稿</h2>
  <form method="post" action="post_create.php">
    <input type="hidden" name="_token" value="<?= h(csrfToken()) ?>">
    <label>
      タイトル
      <input type="text" name="title" maxlength="200" required>
    </label>
    <label>
      内容
      <textarea name="content" rows="4" required></textarea>
    </label>
    <label class="checkbox">
      <input type="checkbox" name="is_public" value="1"> 公開する
    </label>
    <button type="submit">投稿する</button>
  </form>
</section>

<?php if (empty($posts)): ?>
  <p>投稿はありません。</p>
<?php else: ?>
  <?php foreach ($posts as $post): ?>
    <article class="card post">
      <h2><?= h($post['title']) ?></h2>
      <p class="post-meta">
        <?php if ($post['is_public']): ?>
          <span class="badge">公開</span>
        <?php else: ?>
          <span class="badge badge-private">非公開</span>
        <?php endif; ?>
        <?= h($post['created_at']) ?>
      </p>
      <div class="post-content"><?= nl2br(h($post['content'])) ?></div>
      <form method="post" action="post_delete.php" class="post-delete" onsubmit="return confirm('この投稿を削除しますか？');">
        <input type="hidden" name="_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
        <button type="submit" class="danger">削除</button>
      </form>
    </article>
  <?php endforeach; ?>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>