<?php

declare(strict_types=1);

require_once __DIR__ . '/api/auth.php';

client_logout();
header('Location: index.php');
exit;
