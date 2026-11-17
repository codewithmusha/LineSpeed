<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/site.php';
header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');
$paths = ['', 'speed-guide.php', 'how-it-works.php', 'about.php', 'contact.php', 'privacy.php', 'terms.php'];
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($paths as $path) {
    echo '  <url><loc>' . esc(siteUrl($path)) . '</loc></url>' . "\n";
}
echo '</urlset>';
