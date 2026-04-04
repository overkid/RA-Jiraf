<?php

declare(strict_types=1);

function seo_base_url(): string
{
    $https = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443);

    $scheme = $https ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $host = preg_replace('/[^a-z0-9\.\-:]/i', '', $host) ?: 'localhost';

    return $scheme . '://' . $host;
}

function seo_url(string $path): string
{
    return rtrim(seo_base_url(), '/') . '/' . ltrim($path, '/');
}

