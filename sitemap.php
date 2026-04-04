<?php

declare(strict_types=1);

require __DIR__ . '/api/seo.php';

header('Content-Type: application/xml; charset=UTF-8');

$pages = [
    ['path' => '/', 'file' => __DIR__ . '/index.php', 'changefreq' => 'weekly', 'priority' => '1.0'],
    ['path' => '/services.php', 'file' => __DIR__ . '/services.php', 'changefreq' => 'weekly', 'priority' => '0.8'],
];

$formatLastmod = static function (string $filePath): string {
    $timestamp = @filemtime($filePath);
    if (!$timestamp) {
        return gmdate('Y-m-d\TH:i:s\Z');
    }

    return gmdate('Y-m-d\TH:i:s\Z', $timestamp);
};

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

foreach ($pages as $page) {
    $loc = seo_url((string) $page['path']);
    $lastmod = $formatLastmod((string) $page['file']);
    $changefreq = (string) $page['changefreq'];
    $priority = (string) $page['priority'];

    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars($loc, ENT_QUOTES, 'UTF-8') . "</loc>\n";
    echo "    <lastmod>{$lastmod}</lastmod>\n";
    echo "    <changefreq>{$changefreq}</changefreq>\n";
    echo "    <priority>{$priority}</priority>\n";
    echo "  </url>\n";
}

echo "</urlset>\n";
