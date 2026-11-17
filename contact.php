<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/site.php';
pageStart('Contact', 'Contact the LineSpeed website team for questions, feedback or website enquiries.', 'contact.php');
?>
<main id="main"><header class="page-hero container"><span class="section-label">GET IN TOUCH</span><h1>Questions or feedback?<br>We'd like to hear it.</h1><p>If you have a question about the site, would like to report a problem or want to share feedback, you can reach out using the contact details below.</p></header><div class="article-shell container"><aside class="article-aside">Contact</aside><article class="article-body"><h2>Website enquiries</h2>
<?php if (CONTACT_EMAIL !== '' && filter_var(CONTACT_EMAIL, FILTER_VALIDATE_EMAIL)): ?>
<p>Write to our contact address and include a short description of your question. For technical issues, mention your browser and device where useful.</p><p><a class="contact-email" href="mailto:<?= esc(CONTACT_EMAIL) ?>"><?= esc(CONTACT_EMAIL) ?> ↗</a></p>
<?php else: ?>
<p class="soft-warning"><strong>Site owner setup required:</strong> Add your real email address to <code>CONTACT_EMAIL</code> in <code>config.php</code> before publishing this page. The website cannot receive messages until a working contact address is provided.</p>
<?php endif; ?>
<p>For your privacy, please avoid sending passwords, account numbers or other sensitive information.</p><a class="content-back" href="/">← Back to speed test</a></article></div></main>
<?php pageEnd(); ?>
