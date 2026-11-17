<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/site.php';
// Issue a per-session token. It deters casual hotlinking of bandwidth-intensive endpoints.
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('linespeed_session');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $secure, 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
if (!isset($_SESSION['speed_test_token'])) {
    $_SESSION['speed_test_token'] = bin2hex(random_bytes(24));
}
$token = (string) $_SESSION['speed_test_token'];
session_write_close();
header('Cache-Control: private, no-store');
pageStart('', 'Free internet speed test. Check download speed, upload speed, ping and jitter in your browser, then save your results as a PNG image.', '', 'test');
?>
<main id="main">
  <section class="hero container" aria-labelledby="hero-title">
    <div class="hero-copy">
      <p class="eyebrow"><span class="eyebrow-rule" aria-hidden="true"></span> INTERNET SPEED TEST, MADE SIMPLE</p>
      <h1 id="hero-title">Know your speed.<br><span class="emphasis">Skip the guesswork.</span></h1>
      <p class="hero-description">A clean, straightforward way to check your internet connection. Get real measurements for download, upload, ping and jitter — right in your browser.</p>
      <div class="hero-proof"><span class="proof-icon" aria-hidden="true">✓</span> No account needed <span class="proof-separator" aria-hidden="true">/</span> No app to install</div>
      <div class="hero-note"><span class="note-number">01 / 03</span><span>TEST YOUR CONNECTION<br>UNDERSTAND THE NUMBERS<br>SAVE YOUR RESULT</span></div>
    </div>

    <section class="test-panel" aria-label="Internet speed tester" data-test-token="<?= esc($token) ?>">
      <div class="panel-heading"><span class="panel-kicker"><span class="status-indicator" id="status-dot"></span> CONNECTION CHECK</span><span class="panel-counter">LIVE TEST / 001</span></div>
      <div class="meter-area">
        <div class="meter-graphic" aria-hidden="true">
          <svg class="speed-arc" viewBox="0 0 440 250" role="presentation">
            <path d="M 70 203 A 150 150 0 0 1 370 203" class="arc-track" pathLength="100" />
            <path id="gauge-progress" d="M 70 203 A 150 150 0 0 1 370 203" class="arc-progress" pathLength="100" />
            <g class="arc-ticks" stroke="currentColor" stroke-linecap="round" stroke-width="2" opacity=".45"><path d="M48 205h12"/><path d="M380 205h12"/><path d="M220 33v11"/></g>
          </svg>
          <div class="meter-reading" aria-live="off"><span class="meter-number" id="speed-value">0.0</span><span class="meter-unit">Mbps</span></div>
        </div>
        <p class="meter-caption" id="test-status" aria-live="polite">Ready when you are</p>
        <p class="meter-subcaption" id="test-detail">Press the button to measure your connection</p>
      </div>
      <div class="measurement-grid" aria-label="Speed test results">
        <div class="measure-cell"><div class="measure-name"><span class="mini-arrow down-arrow">↓</span> DOWNLOAD</div><div class="measure-value"><span id="download-value">—</span><small>Mbps</small></div></div>
        <div class="measure-cell"><div class="measure-name"><span class="mini-arrow up-arrow">↑</span> UPLOAD</div><div class="measure-value"><span id="upload-value">—</span><small>Mbps</small></div></div>
        <div class="measure-cell"><div class="measure-name"><span class="mini-icon" aria-hidden="true">◷</span> PING</div><div class="measure-value"><span id="ping-value">—</span><small>ms</small></div></div>
        <div class="measure-cell"><div class="measure-name"><span class="mini-icon" aria-hidden="true">⌁</span> JITTER</div><div class="measure-value"><span id="jitter-value">—</span><small>ms</small></div></div>
      </div>
      <div class="test-controls"><button id="test-button" class="button button-primary" type="button"><span id="button-label">Start speed test</span><span class="button-arrow" aria-hidden="true">↗</span></button><button id="download-button" class="button button-outline" type="button" disabled><svg width="19" height="19" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0 5-5m-5 5-5-5M4 17v3h16v-3"/></svg> Save result as PNG</button></div>
      <div class="panel-bottom"><span>● Approximate browser-to-server test</span><span id="last-tested">NO TEST YET</span></div>
    </section>
  </section>

  <section class="metrics-explainer" aria-labelledby="measure-title">
    <div class="container">
      <div class="section-top"><span class="section-label">THE ESSENTIALS <span>— 01</span></span><h2 id="measure-title">Four numbers.<br><span>One clearer picture.</span></h2><p>It's not just about how fast a page loads. These four measurements tell you how your connection behaves.</p></div>
      <div class="explain-grid">
        <article class="explain-card"><div class="card-number">01 <span>↙</span></div><h3>Download speed</h3><p>How quickly your connection receives data — useful for watching videos, browsing and downloading files.</p><span class="card-foot">MEASURED IN Mbps</span></article>
        <article class="explain-card"><div class="card-number">02 <span>↗</span></div><h3>Upload speed</h3><p>How quickly you can send data — useful for video calls, cloud backups and sharing large files.</p><span class="card-foot">MEASURED IN Mbps</span></article>
        <article class="explain-card"><div class="card-number">03 <span>◎</span></div><h3>Ping</h3><p>How long a small request takes to reach our test server and return. Lower times usually feel more responsive.</p><span class="card-foot">MEASURED IN ms</span></article>
        <article class="explain-card"><div class="card-number">04 <span>≈</span></div><h3>Jitter</h3><p>How much the response time varies from one request to the next. Lower variation helps calls stay smooth.</p><span class="card-foot">MEASURED IN ms</span></article>
      </div>
    </div>
  </section>

  <section class="knowledge-section container" aria-labelledby="know-title">
    <div class="knowledge-left"><span class="section-label">USEFUL TO KNOW <span>— 02</span></span><h2 id="know-title">Numbers are good.<br><span>Context is better.</span></h2><p>A speed test is a snapshot of your connection to a particular server. Results may change throughout the day.</p><a class="text-link" href="/speed-guide.php">Explore the speed guide <span aria-hidden="true">↗</span></a></div>
    <div class="knowledge-right"><div class="info-row"><span>01</span><div><h3>Use a stable connection</h3><p>Move closer to your Wi-Fi router, or use Ethernet if you want a more consistent reading.</p></div></div><div class="info-row"><span>02</span><div><h3>Pause heavy downloads</h3><p>Streaming, updates and cloud backups can affect the results during your test.</p></div></div><div class="info-row"><span>03</span><div><h3>Test more than once</h3><p>Try different times of day to understand what your connection typically delivers.</p></div></div></div>
  </section>

  <section class="faq-section" aria-labelledby="faq-title"><div class="container faq-layout"><div><span class="section-label">QUICK ANSWERS <span>— 03</span></span><h2 id="faq-title">Good questions.<br><span>Clear answers.</span></h2></div><div class="faq-items">
    <details><summary>Is this internet speed test free?<span aria-hidden="true">+</span></summary><p>Yes. You can run the test without an account or payment. Your internet provider may still count the transferred data toward your data allowance.</p></details>
    <details><summary>Why is my result different from another speed test?<span aria-hidden="true">+</span></summary><p>Different tests can use different servers, connection methods and data sizes. Network congestion, Wi-Fi signal and device performance also make a difference. This tool measures your connection to the server hosting this website.</p></details>
    <details><summary>Does the test work on mobile phones?<span aria-hidden="true">+</span></summary><p>Yes. It works in modern mobile browsers, including on Wi-Fi and mobile data. Keep an eye on data usage if you have a limited plan.</p></details>
    <details><summary>How can I save my speed test result?<span aria-hidden="true">+</span></summary><p>Run a test and select “Save result as PNG.” A shareable image with your measured values and test date will download to your device.</p></details>
    <details><summary>Is ping the same as gaming latency?<span aria-hidden="true">+</span></summary><p>Not exactly. Our ping reading is based on small browser requests to this website's server, not ICMP ping to your game's server. Your gaming latency can be different.</p></details>
  </div></div></section>

  <div class="container ending-banner"><span class="ending-star" aria-hidden="true">✳</span><div><h2>Less guessing. Better connecting.</h2><p>Your connection, your numbers. Whenever you need them.</p></div><a href="#main">Back to speed test <span aria-hidden="true">↑</span></a></div>
</main>
<script src="/assets/js/app.js?v=1.0" defer></script>
<?php pageEnd(); ?>
