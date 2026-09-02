<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$me = currentUser();

redirect(currentUser() !== null ? 'mypage.php?user_id=' . (int) $me['id'] : 'login.php');
// redirect(currentUser() !== null ? 'mypage.php' : 'login.php');
