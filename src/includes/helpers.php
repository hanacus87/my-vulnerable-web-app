<?php

declare(strict_types=1);

/** HTMLエスケープ */
function h(?string $value): string
{
  return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

/** リダイレクト */
function redirect(string $path): never
{
  header('Location: ' . $path);
  exit;
}

/** アバターを表示するHTMLを返す */
function avatarTag(?string $avatar, string $username, string $class = 'avatar'): string
{
  if (is_string($avatar) && $avatar !== '') {
    $src = 'uploads/avatars/' . rawurlencode($avatar);
    return '<img class="' . h($class) . '" src="' . h($src) . '" alt="">';
  }

  $initial = h(mb_substr($username, 0, 1));
  return '<span class="' . h($class) . ' avatar-fallback">' . $initial . '</span>';
}
