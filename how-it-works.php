<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/site.php';
pageStart('How Our Internet Speed Test Works', 'Learn how LineSpeed estimates download speed, upload speed, browser ping and jitter using a web browser and a server.', 'how-it-works.php', 'how');
?>
<main id="main"><header class="page-hero container"><span class="section-label">BEHIND THE NUMBERS — 02</span><h1>Simple test.<br>Real data transfers.</h1><p>Here's what happens after you press Start. No invented speed scores, and no separate mobile app required.</p></header>
<div class="article-shell container"><aside class="article-aside">How it works<br>Browser-based testing</aside><article class="article-body">
<h2>1. Your browser checks responsiveness</h2><p>We send several small requests to the server hosting this website and measure how long each request takes. The median measured round-trip time is shown as ping. We also calculate average variation between consecutive readings, shown as jitter.</p><p>This is an <strong>HTTP-based browser latency estimate</strong>, not a traditional network ICMP ping.</p>
<h2>2. It downloads test data</h2><p>Our server generates random, hard-to-compress bytes. Your browser downloads a small warm-up sample, followed by a larger sample chosen based on the first reading. We calculate approximate download Mbps from the amount received and the elapsed time.</p><h2>3. It uploads test data</h2><p>Your browser prepares random test data and sends it back to our server. The server reads the data without storing it as a file. We estimate upload speed from the amount transferred and the time measured in the browser.</p>
<h2>4. It makes a PNG report</h2><p>Once all measurements finish, you can save a shareable PNG image showing download, upload, ping, jitter and the date. The image is made directly in your browser.</p>
<h2>What does this test actually measure?</h2><p>It measures a browser connection <strong>between your device and the server hosting this website</strong>. Your true internet-plan capacity may be higher or lower. Performance can be limited by your device, browser, Wi-Fi, server location or hosting limits. Professional multi-server test networks may give different results.</p>
<div class="article-callout">A complete test transfers a variable amount of data, typically a few megabytes up to about 23 MB, depending on the connection. On mobile data, this can count toward your allowance. Frequent tests also consume website-hosting bandwidth.</div>
<h2>Is your result stored?</h2><p>The test data is not saved as uploaded files. The result image is created locally in your browser. Hosting access logs may still record ordinary request metadata such as IP address and request times. See our <a href="/privacy.php">privacy policy</a> for more information.</p>
<a class="content-back" href="/">← Run a speed test</a>
</article></div></main>
<?php pageEnd(); ?>
