<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

// 意図しない強制ログアウト対策
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  header('Allow: POST');
  exit('Method Not Allowed');
}

verifyCsrf();
logout();
redirect('login.php');
