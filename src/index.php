<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

redirect(currentUser() !== null ? 'mypage.php' : 'login.php');
