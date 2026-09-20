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

$ownerStmt = db()->prepare('SELECT username, avatar FROM users WHERE id = ?');
$ownerStmt->execute([$targetId]);
$owner = $ownerStmt->fetch();

$title = 'マイページ | vNet';
require __DIR__ . '/includes/header.php';
?>
<?php if (!empty($_SESSION['profile_flash'])):
  $flash = $_SESSION['profile_flash'];
  unset($_SESSION['profile_flash']); ?>
  <div class="flash <?= h($flash['type']) ?>"><?= h($flash['msg']) ?></div>
<?php endif; ?>
<aside class="rightrail">
  <section class="card profile-card">
    <h2>プロフィール更新</h2>
    <div class="profile-current">
      <?= avatarTag($owner['avatar'] ?? null, (string) $owner['username'], 'avatar avatar-lg') ?>
    </div>
    <form method="post" action="profile_update.php" enctype="multipart/form-data">
      <input type="hidden" name="_token" value="<?= h(csrfToken()) ?>">
      <label>
        ユーザーネーム
        <input type="text" name="username" maxlength="50" value="<?= h((string) $owner['username']) ?>" required>
      </label>
      <label>
        アイコン画像（JPEG / PNG・2MBまで）
        <input type="file" name="avatar" accept="image/jpeg,image/png">
      </label>
      <button type="submit">更新する</button>
    </form>
  </section>
</aside>

<div class="page-head">
  <h1>マイページ</h1>
</div>

<section class="card">
  <h2>新規投稿</h2>
  <form method="post" action="post_create.php">
    <!-- CSRF対策 -->
    <!-- <input type="hidden" name="_token" value="<?= h(csrfToken()) ?>"> -->
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