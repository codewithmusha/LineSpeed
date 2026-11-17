<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config.php';

function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function siteBase(): string
{
    $configured = rtrim(trim(SITE_URL), '/');
    if ($configured !== '' && preg_match('~^https?://[a-z0-9.-]+(?::\d+)?$~i', $configured)) {
        return $configured;
    }
    $rawHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $host = preg_match('/^[a-z0-9.-]+(?::\d+)?$/iD', $rawHost) ? $rawHost : 'localhost';
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    return ($https ? 'https://' : 'http://') . $host;
}

function siteUrl(string $path = ''): string
{
    return siteBase() . '/' . ltrim($path, '/');
}

function pageStart(string $title, string $description, string $path = '', string $active = ''): void
{
    $canonical = siteUrl($path);
    $wholeTitle = $title === '' ? SITE_NAME . ' — Free Internet Speed Test' : $title . ' | ' . SITE_NAME;
    $structured = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => SITE_NAME,
        'url' => siteUrl(),
        'description' => 'Measure approximate download, upload, ping and jitter speeds from your browser.',
    ];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f6f1">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta name="description" content="<?= esc($description) ?>">
    <title><?= esc($wholeTitle) ?></title>
    <link rel="canonical" href="<?= esc($canonical) ?>">
    <link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css?v=1.0">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= esc(SITE_NAME) ?>">
    <meta property="og:title" content="<?= esc($wholeTitle) ?>">
    <meta property="og:description" content="<?= esc($description) ?>">
    <meta property="og:url" content="<?= esc($canonical) ?>">
    <meta name="twitter:card" content="summary">
    <script type="application/ld+json"><?= json_encode($structured, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="/" aria-label="<?= esc(SITE_NAME) ?> home">
            <span class="brand-mark" aria-hidden="true"><svg width="28" height="28" viewBox="0 0 28 28" fill="none"><path d="M5 20.5a12 12 0 0 1 18 0" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/><path d="M9 16.5a7 7 0 0 1 10 0" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/><circle cx="14" cy="21" r="2.5" fill="currentColor"/></svg></span>
            <span><?= esc(SITE_NAME) ?><span class="brand-period">.</span></span>
        </a>
        <nav class="nav-links" aria-label="Main navigation">
            <a class="<?= $active === 'test' ? 'active' : '' ?>" href="/">Speed test</a>
            <a class="<?= $active === 'guide' ? 'active' : '' ?>" href="/speed-guide.php">Speed guide</a>
            <a class="<?= $active === 'how' ? 'active' : '' ?>" href="/how-it-works.php">How it works</a>
        </nav>
        <a class="header-action" href="/">Test connection <span aria-hidden="true">↗</span></a>
    </div>
</header>
<?php
}

function pageEnd(): void
{
    ?>
<footer class="site-footer">
  <div class="container">
    <div class="footer-main">
      <div>
        <a class="footer-logo" href="/"><?= esc(SITE_NAME) ?><span>.</span></a>
        <p>Internet speed, explained simply.<br>Built to help you make sense of your connection.</p>
      </div>
      <nav aria-label="Footer navigation" class="footer-links">
        <a href="/">Speed test</a>
        <a href="/speed-guide.php">Speed guide</a>
        <a href="/how-it-works.php">How it works</a>
        <a href="/about.php">About</a>
        <a href="/contact.php">Contact</a>
        <a href="/privacy.php">Privacy</a>
        <a href="/terms.php">Terms</a>
      </nav>
    </div>
    <div class="footer-bottom"><span>© <?= date('Y') ?> <?= esc(SITE_NAME) ?>. All rights reserved.</span><span>Measurements are estimates to this website's test server.</span></div>
  </div>
</footer>
</body>
</html>
<?php
}
