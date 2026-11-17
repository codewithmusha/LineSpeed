<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/site.php';
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_name('linespeed_session');
  session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
  session_start();
}
if (!isset($_SESSION['speed_test_token'])) $_SESSION['speed_test_token']=bin2hex(random_bytes(24));
$token=(string)$_SESSION['speed_test_token'];
session_write_close();
header('Cache-Control: private, no-store');
pageStart('Free Internet Speed Test – Check Wi-Fi & Mobile Data Speed', 'Test download speed, upload speed, ping and jitter. See your approximate network provider, get an easy-to-read connection report and save a PNG result.', '', 'test');
?>
<main id="main">
<section class="hero" aria-labelledby="hero-title">
<div class="container">
  <div class="hero-intro"><p class="eyebrow"><span class="eyebrow-dash"></span><span data-i18n="eyebrow">YOUR CONNECTION, EXPLAINED</span></p><h1 id="hero-title" data-i18n="heroTitle">Know your speed.<br>Know your connection.</h1><p data-i18n="heroSubtitle">One tap. Real measurements. A clearer picture of your internet.</p></div>
  <div class="hero-shell">
    <div class="test-panel" data-test-token="<?= esc($token) ?>" data-phase="idle" aria-label="Internet speed test">
      <div class="test-top"><span class="live-status"><i id="status-dot" aria-hidden="true"></i><span id="connection-label" data-i18n="ready">READY TO TEST</span></span><span class="secure-label"><span aria-hidden="true">●</span> <span data-i18n="noSignup">NO SIGN-UP NEEDED</span></span></div>
      <div class="meter-layout">
        <div class="circle-wrap"><div class="circle-meter">
          <svg class="circular-gauge" viewBox="0 0 320 320" aria-hidden="true"><circle class="gauge-track" cx="160" cy="160" r="141" pathLength="100"/><circle class="gauge-progress" id="gauge-progress" cx="160" cy="160" r="141" pathLength="100"/><circle class="gauge-inside" cx="160" cy="160" r="124"/></svg>
          <button class="go-button" id="test-button" type="button" aria-describedby="test-detail"><span class="go-word" id="go-word">GO</span><span class="live-reading" id="speed-value" hidden>0.0</span><span class="live-unit" id="speed-unit" hidden>Mbps</span><span class="go-caption" id="button-label" data-i18n="startTest">START TEST</span></button>
        </div></div>
        <div class="test-message"><div class="test-steps" aria-label="Testing progress"><span class="test-step" data-step="ping"><span class="step-check">1</span><span data-i18n="ping">Ping</span></span><span class="line"></span><span class="test-step" data-step="download"><span class="step-check">2</span><span data-i18n="download">Download</span></span><span class="line"></span><span class="test-step" data-step="upload"><span class="step-check">3</span><span data-i18n="upload">Upload</span></span></div><h2 id="test-status" role="status" aria-live="polite" data-i18n="readyWhen">Ready when you are</h2><p id="test-detail" data-i18n="tapGo">Tap GO to test your connection</p></div>
      </div>
      <div class="measurement-grid" aria-label="Your speed test results">
        <div class="measure-cell"><span class="measure-name"><b>↓</b> <span data-i18n="download">DOWNLOAD</span></span><div class="measure-value"><span id="download-value">—</span> <small>Mbps</small></div></div>
        <div class="measure-cell"><span class="measure-name"><b>↑</b> <span data-i18n="upload">UPLOAD</span></span><div class="measure-value"><span id="upload-value">—</span> <small>Mbps</small></div></div>
        <div class="measure-cell"><span class="measure-name"><b>↔</b> <span data-i18n="ping">PING</span></span><div class="measure-value"><span id="ping-value">—</span> <small>ms</small></div></div>
        <div class="measure-cell"><span class="measure-name"><b>≋</b> <span data-i18n="jitter">JITTER</span></span><div class="measure-value"><span id="jitter-value">—</span> <small>ms</small></div></div>
      </div>
      <div class="test-footer"><button class="download-result" id="download-button" type="button" disabled><span aria-hidden="true">↓</span> <span data-i18n="saveImage">Save result as image</span></button><span id="last-tested" data-i18n="notTested">NOT TESTED YET</span></div>
      <p class="test-disclaimer" data-i18n="testNote">Results measure your browser's connection to this website's server and can vary. Testing uses internet data.</p>
    </div>
    <aside class="passport" aria-labelledby="passport-title">
      <div class="passport-head"><div><span class="small-label" data-i18n="passportLabel">CONNECTION PASSPORT</span><h2 id="passport-title" data-i18n="passportTitle">Your network, at a glance.</h2></div><span class="passport-icon" aria-hidden="true">↗</span></div>
      <div class="passport-main"><span class="passport-caption" data-i18n="provider">Network provider</span><strong id="isp-name" aria-live="polite" data-i18n="detecting">Detecting…</strong><p class="pass-sub" data-i18n="ispApprox">Approximate name from your public IP address.</p></div>
      <div class="passport-row"><div><span data-i18n="location">Country</span><strong id="country-name">—</strong></div><div><span data-i18n="device">Device</span><strong id="device-name">—</strong></div></div>
      <div class="passport-footer"><span class="passport-live"><i></i><span data-i18n="browserTest">BROWSER-BASED TEST</span></span><button id="network-refresh" class="refresh-network" type="button" data-i18n="refresh">Refresh</button></div>
      <p class="passport-caution" data-i18n="providerNote">VPNs and network routing can change the provider shown.</p>
      <p class="credit" id="network-credit" hidden>Network lookup by <a href="https://ipinfo.io" target="_blank" rel="noopener noreferrer">IPinfo</a></p>
    </aside>
  </div>
  <div class="hero-bottom"><span>01 / 03</span><span data-i18n="heroFoot">MADE FOR REAL CONNECTIONS</span><span>↓</span></div>
</div>
</section>
<section class="insight-section" id="connection-report" aria-labelledby="insight-title"><div class="container insight-layout">
  <div class="insight-heading"><span class="section-index">01 — <span data-i18n="understand">UNDERSTAND</span></span><h2 id="insight-title" data-i18n="reportTitle">Numbers are good.<br>Answers are better.</h2><p data-i18n="reportDesc">After your test, get a simple guide to what your connection may handle.</p><a href="/speed-guide.php" class="text-link"><span data-i18n="speedGuide">Explore the speed guide</span><span aria-hidden="true">↗</span></a></div>
  <div class="insight-cards">
    <div class="insight-card"><div><span class="activity-symbol">▶</span><span data-i18n="streaming">Streaming</span></div><strong id="streaming-verdict" data-i18n="testFirst">Run a test first</strong><p id="streaming-detail" data-i18n="streamingDesc">Get a guide for watching your favourite shows.</p></div>
    <div class="insight-card"><div><span class="activity-symbol">▣</span><span data-i18n="calls">Video calls</span></div><strong id="calls-verdict" data-i18n="testFirst">Run a test first</strong><p id="calls-detail" data-i18n="callsDesc">See whether calls may feel smooth.</p></div>
    <div class="insight-card"><div><span class="activity-symbol">◎</span><span data-i18n="gaming">Gaming</span></div><strong id="gaming-verdict" data-i18n="testFirst">Run a test first</strong><p id="gaming-detail" data-i18n="gamingDesc">Check response times for online play.</p></div>
    <p class="insight-disclaimer" data-i18n="guidanceNote">These are simple guidelines, not guaranteed performance ratings.</p>
  </div>
</div></section>
<section class="devices-section" aria-labelledby="device-title"><div class="container"><div class="devices-heading"><div><span class="section-index">02 — <span data-i18n="anyDevice">ANY DEVICE</span></span><h2 id="device-title" data-i18n="devicesTitle">Wherever you're connected.</h2></div><p data-i18n="devicesDesc">No app needed. Use LineSpeed in a modern browser on almost any screen.</p></div>
<div class="device-grid"><div><span aria-hidden="true">▯</span><h3 data-i18n="phones">Phones</h3><p data-i18n="phoneDetail">Android & iPhone</p></div><div><span aria-hidden="true">▭</span><h3 data-i18n="tablets">Tablets</h3><p data-i18n="tabletDetail">iPad & Android tablets</p></div><div><span aria-hidden="true">⌁</span><h3 data-i18n="laptops">Laptops</h3><p data-i18n="laptopDetail">MacBook & Windows</p></div><div><span aria-hidden="true">▣</span><h3 data-i18n="desktops">Desktops</h3><p data-i18n="desktopDetail">PC, Mac & Linux</p></div></div>
<a href="/devices.php" class="device-link"><span data-i18n="deviceHelp">Read device compatibility</span> ↗</a>
</div></section>
</main>
<script src="/assets/js/app.js?v=3.0" defer></script>
<?php pageEnd(); ?>
