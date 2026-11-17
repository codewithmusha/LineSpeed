<?php
/**
 * LineSpeed settings. Edit this file before publishing.
 * PHP 8.1+ required. No database or paid API is required.
 */
declare(strict_types=1);

define('SITE_NAME', 'LineSpeed'); // Working brand; update if you prefer another name.
define('SITE_TAGLINE', 'A clearer picture of your internet.');
define('SITE_URL', rtrim((string) (getenv('RENDER_EXTERNAL_URL') ?: ''), '/')); // Automatically uses your free Render HTTPS URL; override when moving to a custom domain.
define('CONTACT_EMAIL', ''); // Set your real contact email before applying for AdSense.

define('MAX_DOWNLOAD_MB', 16);
define('MAX_UPLOAD_MB', 6);

// Optional: set IPINFO_TOKEN in Render > Environment to your free IPinfo Lite
// API token for unlimited free ASN/ISP and country lookups (with attribution).
// The site will still attempt the rate-limited public endpoint without a token.
