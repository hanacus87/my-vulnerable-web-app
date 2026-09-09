<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

// session_set_cookie_params([
//   'httponly' => true,
//   'samesite' => 'Lax',
// ]);
session_start();

/** 認証 */
function login(string $username, string $password): bool
{
  // SQLi可能(文字列連結)
  $sql = "SELECT id, password_hash FROM users WHERE username = '{$username}'";
  $user = db()->query($sql)->fetch();

  if ($user === false || !password_verify($password, $user['password_hash'])) {
    return false;
  }

  // セッション固定攻撃対策(セッションID再生成)
  session_regenerate_id(true);

  $_SESSION['user_id'] = (int) $user['id'];
  $_SESSION['logged_in_at'] = time();

  return true;
}
// function login(string $username, string $password): bool
// {
//     // SQLi対策(プレースホルダ)
//     $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE username = ?');
//     $stmt->execute([$username]);
//     $user = $stmt->fetch();

//     if ($user === false || !password_verify($password, $user['password_hash'])) {
//         return false;
//     }

//     // セッション固定攻撃対策(セッションIDの再生成)
//     session_regenerate_id(true);

//     $_SESSION['user_id'] = (int) $user['id'];
//     $_SESSION['logged_in_at'] = time();

//     return true;
// }

/** セッション破棄 */
function logout(): void
{
  // メモリを上書き
  $_SESSION = [];

  // ブラウザのCookieを上書き
  if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', [
      'expires' => time() - 42000,
      'path' => $p['path'],
      'domain' => $p['domain'],
      'secure' => $p['secure'],
      'httponly' => $p['httponly'],
      'samesite' => $p['samesite'],
    ]);
  }

  // サーバのセッションストアから削除
  session_destroy();
}

/** ログインユーザ取得 */
function currentUser(): ?array
{
  static $cached = false;

  if ($cached !== false) {
    return $cached;
  }

  if (empty($_SESSION['user_id'])) {
    return $cached = null;
  }

  $stmt = db()->prepare('SELECT id, username, avatar, created_at FROM users WHERE id = ?');
  $stmt->execute([$_SESSION['user_id']]);
  $user = $stmt->fetch();

  return $cached = ($user === false ? null : $user);
}

/** ログイン画面へリダイレクト */
function requireLogin(): void
{
  if (currentUser() === null) {
    redirect('login.php');
  }
}

/** CSRFトークン返却 */
function csrfToken(): string
{
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }

  return $_SESSION['csrf_token'];
}

/** CSRFトークン検証 */
function verifyCsrf(): void
{
  $sent = $_POST['_token'] ?? '';

  if (!is_string($sent) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sent)) {
    http_response_code(400);
    exit('Invalid CSRF token');
  }
}
