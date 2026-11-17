<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/site.php';
pageStart('Terms of Use', 'Read the terms of use for the LineSpeed internet speed testing website.', 'terms.php');
?>
<main id="main"><header class="page-hero container"><span class="section-label">SITE POLICIES</span><h1>Terms of use.</h1><p>A few simple guidelines for using this speed testing website. Last updated: 17 November 2026.</p></header><div class="article-shell container"><aside class="article-aside">Site policies</aside><article class="article-body">
<h2>1. Purpose of the service</h2><p><?= esc(SITE_NAME) ?> provides an informational internet speed test and related educational content. The results are estimates based on your device's connection to our test server, not certifications of service quality or your internet provider's advertised rate.</p>
<h2>2. No guarantee of accuracy or uptime</h2><p>Results can vary due to Wi-Fi, device conditions, browser overhead, server capacity, location and network congestion. The tool is provided on an as-available basis. Do not rely on a single result for contractual, financial or safety-critical decisions.</p>
<h2>3. Responsible use</h2><p>Please do not automate repeated tests, misuse endpoints, bypass limits, or intentionally overload the server. We may restrict access when necessary to protect availability and bandwidth.</p>
<h2>4. Data usage</h2><p>Testing sends and receives real data. Your internet or mobile provider may charge for transferred data according to your plan. You are responsible for your own internet charges.</p>
<h2>5. Content and changes</h2><p>Information is provided for general educational use. We may update the website, its features and these terms as needed.</p>
<h2>6. Questions</h2><p>Use our <a href="/contact.php">contact page</a> for questions about the website. The website owner must configure a contact address before publication.</p>
</article></div></main>
<?php pageEnd(); ?>
