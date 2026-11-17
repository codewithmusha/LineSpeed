<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/site.php';
pageStart('Privacy Policy', 'Read the LineSpeed privacy policy: how speed testing works, temporary session cookies, hosting logs and result image downloads.', 'privacy.php');
?>
<main id="main"><header class="page-hero container"><span class="section-label">YOUR INFORMATION</span><h1>Privacy policy.</h1><p>Clear information about what happens when you use this website. Last updated: 17 November 2026.</p></header><div class="article-shell container"><aside class="article-aside">Site policies</aside><article class="article-body">
<h2>1. What this website does</h2><p><?= esc(SITE_NAME) ?> provides a browser-based internet speed test and educational content. The test exchanges temporary data between your browser and the web server to estimate download speed, upload speed, ping and jitter.</p>
<h2>2. Information used during testing</h2><p>Test requests may reveal technical information ordinarily visible to a server, including your IP address, browser type, request timestamps and test data volume. The web hosting provider may retain standard access and error logs for security and operations. The application does not create user accounts or store a history of finished speed test results.</p>
<h2>3. Session cookies</h2><p>We use a temporary, first-party session cookie to protect testing endpoints from casual misuse. It stores a session identifier in your browser; the session expires according to the server's session settings. There is no advertising or analytics tracking code in this starter version of the website.</p>
<h2>4. Uploaded test data and result images</h2><p>Random upload test data is read by the server and discarded without being saved as an uploaded file. PNG result images are generated in your browser and downloaded to your device. The application does not save those images on the server.</p>
<h2>5. External services</h2><p>This website loads the Poppins font from Google Fonts. Your browser may connect to Google to load font files, which is subject to Google's own service and privacy practices. The website is hosted by your chosen hosting provider, which may also process technical logs.</p>
<h2>6. Advertising if introduced later</h2><p>This starter version does not include Google AdSense. <strong>If advertisements, analytics or other tracking tools are added later, this policy and any necessary consent mechanisms must be updated before enabling them.</strong> Google and advertising partners may use cookies or similar technology under their policies.</p>
<h2>7. Contact</h2><p>For questions about this privacy policy, visit our <a href="/contact.php">contact page</a>. The website owner must supply a working contact address before publication.</p>
</article></div></main>
<?php pageEnd(); ?>
