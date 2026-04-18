<?php

return [
    'users' => [
        'admin' => [
            'role' => 'admin',
            // Хэш для пароля "123" (формат pbkdf2_sha256).
            'password_hash' => 'pbkdf2_sha256$210000$AFnbetcc26FbyBN28RU8JA==$P4ZtEvKaayfU5tvLJtewDlD7APhw+b0oRapHG2pVnMA=',
        ],
        'manager' => [
            'role' => 'manager',
            // Хэш для пароля "123" (формат pbkdf2_sha256).
            'password_hash' => 'pbkdf2_sha256$210000$RwK5wJ0pg+huHzChUNxerw==$7I1AiOxO9s8F8plJucXeW0n4eex1/lVTCkoTQ8A/C0s=',
        ],
    ],
];
