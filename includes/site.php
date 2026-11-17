<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config.php';
function esc(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function siteBase(): string {
    $configured = rtrim(trim(SITE_URL), '/');
    if ($configured !== '' && preg_match('~^https?://[a-z0-9.-]+(?::\d+)?$~iD', $configured)) return $configured;
    $rawHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $host = preg_match('/^[a-z0-9.-]+(?::\d+)?$/iD', $rawHost) ? $rawHost : 'localhost';
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    return ($https ? 'https://' : 'http://') . $host;
}
function siteUrl(string $path = ''): string { return siteBase() . '/' . ltrim($path, '/'); }
function pageStart(string $title, string $description, string $path = '', string $active = ''): void {
    $canonical = siteUrl($path);
    $wholeTitle = $title === '' ? SITE_NAME . ' — Free Internet Speed Test' : $title . ' | ' . SITE_NAME;
    $structured = [
        '@context' => 'https://schema.org',
        '@type' => $active === 'test' ? 'WebApplication' : 'WebSite',
        'name' => SITE_NAME,
        'url' => siteUrl(),
        'description' => 'Browser-based internet speed measurements of download, upload, ping and jitter.',
    ];
    if ($active === 'test') { $structured['applicationCategory'] = 'UtilitiesApplication'; $structured['operatingSystem'] = 'Any modern web browser'; $structured['offers'] = ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD']; }
    ?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#181d1d">
<meta name="robots" content="index, follow, max-image-preview:large">
<meta name="description" content="<?= esc($description) ?>">
<title><?= esc($wholeTitle) ?></title>
<link rel="canonical" href="<?= esc($canonical) ?>">
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css?v=3.0">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= esc(SITE_NAME) ?>">
<meta property="og:title" content="<?= esc($wholeTitle) ?>">
<meta property="og:description" content="<?= esc($description) ?>">
<meta property="og:url" content="<?= esc($canonical) ?>">
<meta name="twitter:card" content="summary">
<script type="application/ld+json"><?= json_encode($structured, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script>try{const s=localStorage.getItem('ls-theme');document.documentElement.dataset.theme=s==='light'||s==='dark'?s:(matchMedia('(prefers-color-scheme: light)').matches?'light':'dark')}catch(e){}</script>
<script src="/assets/js/i18n.js?v=3.0" defer></script>
<script src="/assets/js/shell.js?v=3.0" defer></script>
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>
<header class="site-header">
  <div class="container header-inner">
    <a class="brand" href="/" aria-label="LineSpeed home"><span class="brand-graphic" aria-hidden="true"><span></span><span></span><span></span></span><span><?= esc(SITE_NAME) ?><b>.</b></span></a>
    <nav class="main-nav" aria-label="Main navigation">
      <a class="nav-start <?= $active === 'test' ? 'active' : '' ?>" href="/" data-i18n="navTest">Speed test</a>
      <details class="explore-menu" id="explore-menu">
        <summary><span data-i18n="navExplore">Explore</span><span class="chevron" aria-hidden="true">⌄</span></summary>
        <div class="menu-content">
          <p class="menu-group-label" data-i18n="menuLearn">LEARN & UNDERSTAND</p>
          <a href="/speed-guide.php">Internet speed guide <span>↗</span></a>
          <a href="/how-it-works.php">How testing works <span>↗</span></a>
          <a href="/faq.php">Common questions <span>↗</span></a>
          <a href="/devices.php">Supported devices <span>↗</span></a>
          <p class="menu-group-label" data-i18n="menuCompany">ABOUT THE SITE</p>
          <a href="/about.php">About LineSpeed <span>↗</span></a>
          <a href="/contact.php">Contact <span>↗</span></a>
          <a href="/privacy.php">Privacy policy <span>↗</span></a>
          <a href="/terms.php">Terms <span>↗</span></a>
        </div>
      </details>
    </nav>
    <div class="header-controls">
      <label class="language-wrap"><span class="sr-only">Choose interface language</span><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18"/></svg><select id="site-language" aria-label="Choose language"><option value="en">English</option><option value="hi">हिन्दी</option><option value="es">Español</option><option value="fr">Français</option><option value="pt">Português</option><option value="ar">العربية</option><option value="bn">বাংলা</option><option value="de">Deutsch</option><option value="id">Indonesia</option><option value="it">Italiano</option><option value="ru">Русский</option><option value="ja">日本語</option><option value="zh">中文</option><option value="ko">한국어</option><option value="tr">Türkçe</option><option value="ur">اردو</option><option value="ta">தமிழ்</option><option value="vi">Tiếng Việt</option><option value="th">ไทย</option><option value="nl">Nederlands</option></select></label>
      <button type="button" id="theme-toggle" class="theme-toggle" aria-label="Toggle light and dark appearance" title="Toggle light and dark appearance"><svg class="sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg><svg class="moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.4 15.5A8.5 8.5 0 0 1 8.5 3.6a8.5 8.5 0 1 0 11.9 11.9Z"/></svg></button>
    </div>
  </div>
</header>
<?php }
function pageEnd(): void { ?>
<footer class="site-footer"><div class="container footer-inner">
  <div class="footer-top"><div><a href="/" class="footer-brand"><?= esc(SITE_NAME) ?><b>.</b></a><p data-i18n="footerDesc">A useful answer to a simple question: how fast is my connection?</p></div><nav class="footer-links" aria-label="Footer navigation"><a href="/speed-guide.php">Speed guide</a><a href="/how-it-works.php">How it works</a><a href="/faq.php">FAQ</a><a href="/devices.php">Devices</a><a href="/about.php">About</a><a href="/contact.php">Contact</a><a href="/privacy.php">Privacy</a><a href="/terms.php">Terms</a></nav></div>
  <div class="footer-bottom"><span>© <?= date('Y') ?> <?= esc(SITE_NAME) ?>. <span data-i18n="copyright">All rights reserved.</span></span><span data-i18n="footerNote">Your speed is measured against our server, not your ISP's maximum speed.</span></div>
</div></footer>
</body></html>
<?php }
