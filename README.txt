LINESPEED 3.0 — COMPLETE WEBSITE FOR GITHUB + RENDER FREE
======================================================
Built with HTML, Poppins CSS, vanilla JavaScript, PHP and Docker.
No Node.js knowledge, database, custom domain or paid service is required.

WHAT'S INCLUDED
- New original, editorial 3-section homepage (Speed Test, Understanding, Devices)
- One large GO button; real browser-to-server ping/download/upload/jitter
- A Connection Passport with approximate IP-based internet provider and country
- Device information (best-effort browser identification)
- Connection guidance for streaming, calls and gaming after the test
- PNG result download including provider and device
- Dark / light modes (saved per browser)
- Language switcher with 20 major interface languages, including Hindi
- Dropdown navigation for educational, support, privacy and contact pages
- English original information pages: speed guide, how testing works, FAQ,
  supported devices, about, contact, terms and privacy
- Canonical/meta/OG titles, JSON-LD, dynamic sitemap.php and robots.txt

GITHUB + RENDER FREE — EASY DEPLOYMENT
1. Extract the ZIP file on your computer.
2. Open your GitHub LineSpeed repository.
3. Delete or replace old files (if needed), then Add file > Upload files.
4. Upload ALL EXTRACTED files and folders, NOT just the ZIP file itself.
   The repository root MUST contain Dockerfile, index.php, config.php,
   .htaccess, sitemap.php, the api/, assets/, includes/ folders and pages.
5. Commit changes directly to main.
6. Open your Render Web Service linked to that repository.
7. Settings: Language/Runtime = Docker, Dockerfile Path = ./Dockerfile,
   Root Directory empty, Instance Type = Free ($0/month).
8. If not auto-deployed, choose Manual Deploy > Deploy latest commit.
9. Open your https://your-service.onrender.com/ URL and press GO.

ISP / NETWORK PROVIDER DETECTION (AIRTEL, JIO, EXCITEL, ETC.)
- The website requests ASN/company information for the visitor's public IP.
- For dependable free production lookups, obtain a FREE IPinfo Lite token:
  https://ipinfo.io/dashboard/lite
- On Render: Web Service > Environment > Add Environment Variable:
    Key:   IPINFO_TOKEN
    Value: YOUR_PERSONAL_IPINFO_LITE_TOKEN
  Save and redeploy. NEVER commit your token to GitHub.
- IPinfo Lite includes country and ASN name, not the customer's exact SIM
  plan or Wi-Fi provider. The company name can represent an upstream carrier
  or VPN. Provider unavailable is a valid result when no lookup is possible.
- Without a token, the website attempts IPinfo's older public endpoint,
  which is rate-limited and may stop returning information.
- Network lookup requires an external call to IPinfo and is disclosed in
  the Privacy Policy. The website includes a source-credit link.

CHANGES BEFORE A PUBLIC/ADSENSE LAUNCH
- In config.php, set CONTACT_EMAIL to a real monitored email address.
- Make sure the site name, About, Privacy, Contact and Terms are accurate.
- This ZIP intentionally has NO Google AdSense tags or ads.txt publisher ID.
- Apply for AdSense only after you have real, useful content and a stable
  website. SEO pages and legal pages alone do NOT guarantee approval.
- When you do add AdSense, use an appropriate Google-certified CMP for EEA,
  UK and Switzerland audiences, meet Google policy requirements, and update
  the privacy policy and terms as needed.
- Free Render instances sleep after inactivity and have limited resources.
  Speed testing consumes real bandwidth. This is for testing and early use,
  NOT a dependable production speed-testing network or heavy AdSense traffic.

LANGUAGE NOTE
- There are 20 client-side interface languages, selected automatically from
  supported browser languages, or manually by visitor. Unsupported languages
  fall back to English. The English educational and policy pages have NOT been
  translated. For fully translated SEO pages, add professionally reviewed,
  indexable separate localized URLs and hreflang tags in the future.

TEST ACCURACY NOTE
- Download, upload and latency are measured to this website's hosting server.
  They are NOT direct measurements of a contracted ISP's advertised maximum.
- PHP HTTP ping differs from ICMP ping; server distance, load and device
  performance affect readings. Quality guidance is approximate, not a promise.
- Mobile speed tests transfer multiple megabytes of data.

FILES TO KNOW
- index.php                 Homepage
- includes/site.php         Header, dropdown, footer and SEO metadata
- assets/css/style.css      Layout and themes
- assets/js/app.js          Test, network UI, PNG export, activity guidance
- assets/js/i18n.js         Home page and test interface translations
- assets/js/shell.js        Dark/light toggle and navigation interactions
- api/ping.php              Lightweight HTTP response test
- api/download.php          Download byte transfer test
- api/upload.php            Upload byte transfer test
- api/network.php           Public-IP ASN/company lookup via IPinfo
- sitemap.php               XML sitemap at /sitemap.php
- Dockerfile                Required for Render PHP deployment

References (for publisher review):
- IPinfo Lite: https://ipinfo.io/developers/lite-api
- Google AdSense policies: https://support.google.com/adsense/answer/48182
- Google CMP requirement: https://support.google.com/adsense/answer/13554116
