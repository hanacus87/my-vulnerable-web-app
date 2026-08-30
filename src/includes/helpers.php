<?php

declare(strict_types=1);

/** HTMLエスケープ */
function h(?string $value): string
{
  return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/** リダイレクト */
function redirect(string $path): never
{
  header('Location: ' . $path);
  exit;
}
