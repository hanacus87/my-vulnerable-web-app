<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (currentUser() !== null) {
  redirect('mypage.php');
}

$error = null;
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verifyCsrf();

  $username = trim((string) filter_input(INPUT_POST, 'username'));
  $password = (string) filter_input(INPUT_POST, 'password');

  if ($username !== '' && $password !== '' && login($username, $password)) {
    redirect('mypage.php');
  }

  $error = 'ユーザー名またはパスワードが違います';
}

$title = 'ログイン | vNet';
require __DIR__ . '/includes/header.php';
?>
<h1>ログイン</h1>

<?php if ($error !== null): ?>
  <p class="error"><?= h($error) ?></p>
<?php endif; ?>

<form method="post" action="login.php" class="card">
  <input type="hidden" name="_token" value="<?= h(csrfToken()) ?>">
  <label>
    ユーザー名
    <input type="text" name="username" value="<?= h($username) ?>" required autofocus>
  </label>
  <label>
    パスワード
    <input type="password" name="password" required>
  </label>
  <button type="submit">ログイン</button>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>