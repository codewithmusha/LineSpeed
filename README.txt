LINESPEED — HOSTINGER-READY INTERNET SPEED TESTER
================================================

WHAT IS INCLUDED
- Responsive professional website (Poppins font; large text)
- HTML (inside PHP page templates), CSS, JavaScript and PHP
- Real browser-to-server download, upload and HTTP-latency/jitter tests
- PNG result download generated in the browser
- Search-friendly page titles, descriptions, canonicals and schema
- Useful guide, FAQs, about, how-it-works, privacy, terms and contact pages
- Dynamic sitemap at /sitemap.php and robots.txt
- No database and no API key required

IMPORTANT — BEFORE YOU GO LIVE
1. Open config.php and set CONTACT_EMAIL to your real contact email.
2. Set SITE_URL to your final HTTPS domain (example: https://example.com).
3. Change SITE_NAME if you want a different brand. Also update the
   LineSpeed text inside assets/js/app.js used on saved PNG reports.
4. Check that PHP 8.1+ is enabled on Hostinger and HTTPS/SSL is active.
5. Review privacy/terms content for your actual operation and jurisdiction.
6. If you later add Google AdSense, update the privacy policy and any
   required consent notices; approval is NOT guaranteed by this starter kit.

HOSTINGER INSTALLATION
1. In Hostinger, open Websites > Manage > File Manager.
2. Open your site's public_html folder (regular Web Hosting, NOT Website Builder).
3. Back up any old site files first if your public_html is not empty.
4. Upload the supplied ZIP into public_html and extract it there.
   The ZIP contains files at its root, so public_html/index.php
   should appear directly (not inside another folder).
5. Ensure .htaccess was extracted (hidden files may be hidden).
6. Visit https://your-domain.com/ and run a real test on your phone/PC.
7. Visit https://your-domain.com/sitemap.php and submit this URL to
   Google Search Console. Inspect the site's pages before indexing.

FILE STRUCTURE
index.php                Home page + speed test
config.php               Site name, domain, contact email, limits
includes/site.php        Shared header, footer and SEO metadata
assets/css/style.css     Responsive design
assets/js/app.js         Measurements and PNG export
assets/images/favicon.svg Logo/favicon
api/bootstrap.php        Session token and basic per-session rate limit
api/ping.php             Tiny HTTP response for latency testing
api/download.php         Streams random test bytes (max 16 MB/request)
api/upload.php           Reads and discards temporary test data (max 6 MB)
speed-guide.php          Educational SEO guide
how-it-works.php         Transparent test explanation
about.php, contact.php, privacy.php, terms.php
sitemap.php, robots.txt, .htaccess

HOW THE TEST WORKS
- Several HTTP requests estimate ping and jitter.
- A 1 MB warm-up download chooses a 2–16 MB download sample.
- A 1 MB warm-up upload chooses a 2–5 MB upload sample.
- JavaScript measures approximate transfer Mbps.
- On completion, "Save result as PNG" draws and saves an image locally.
- No result history database, and test uploads are NOT saved as files.

LIMITATIONS YOU SHOULD UNDERSTAND
- The measurements are browser-to-YOUR-HOSTINGER-SERVER estimates.
  They are not guaranteed to match Speedtest.net or a user's ISP plan.
- A shared Hostinger plan may impose CPU, I/O, upload-size, request-time,
  bandwidth or connection limits. The site's reported speed can reflect
  those limits instead of the visitor's connection maximum.
- Fast internet often needs bigger tests and multiple dedicated servers
  for high accuracy. This starter intentionally limits data to reduce cost.
- Every visitor transfers several MB of actual data. High traffic can
  consume significant server bandwidth and hosting resources.
- The built-in per-session throttle is NOT protection against determined
  abuse. Add hosting/CDN-level protection before scaling up.
- The browser cannot run this PHP test by opening index.php via file://.
  Upload it to a PHP web server first.
- Hosting with a page builder product may not support these PHP files.
- Poppins loads from Google Fonts; if unavailable the system font is used.

SECURITY & MAINTENANCE
- Keep HTTPS enabled.
- Avoid adding tracking/advertising scripts without updating disclosures.
- Use strong Hostinger account credentials and update PHP when necessary.
- If your Hostinger plan has post_max_size below 6 MB, reduce the maximum
  JavaScript upload chunk (in assets/js/app.js) accordingly.
- Configure sensible rate limits through your CDN or web host for /api/*.

FINAL CHECKLIST
[ ] Homepage loads correctly with CSS and Poppins
[ ] Test completes on mobile and desktop
[ ] Save as PNG produces an image with real completed results
[ ] All footer links and contact email work
[ ] SITE_URL and canonical tags use correct HTTPS domain
[ ] Sitemap loads and is submitted to Google Search Console
[ ] Privacy and consent disclosures match your actual practices
[ ] Hosting bandwidth usage and error logs are monitored

This kit does not include AdSense code or promise traffic, revenue or approval.
