<?php
declare(strict_types=1);

function load_json(string $filename): array
{
  $path = __DIR__ . '/data/' . $filename;
  if (!file_exists($path))
    return [];
  $data = json_decode(file_get_contents($path), true);
  return is_array($data) ? $data : [];
}

$config = load_json('settings.json');
if (empty($config)) {
  // Запасной вариант — если data/settings.json ещё нет
  $config = require __DIR__ . '/config.php';
}
$services = load_json('services.json');
$reviews = load_json('reviews.json');

function h(string $s): string
{
  return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

$baseUrl = rtrim((string) $config['site']['url'], '/') . '/';
$ogImage = (string) $config['og_image'];
$schema = [
  '@context' => 'https://schema.org',
  '@type' => 'BeautySalon',
  'name' => $config['site']['name_legal'],
  'description' => $config['salon']['schema_description'],
  'url' => $baseUrl,
  'telephone' => $config['phone']['e164'],
  'image' => $ogImage,
  'address' => [
    '@type' => 'PostalAddress',
    'streetAddress' => $config['salon']['address_street'],
    'addressLocality' => $config['salon']['address_city'],
    'addressCountry' => $config['salon']['address_country'],
  ],
  'openingHoursSpecification' => [
    '@type' => 'OpeningHoursSpecification',
    'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
    'opens' => $config['salon']['opens'],
    'closes' => $config['salon']['closes'],
  ],
  'priceRange' => '$$',
];
$schemaJson = json_encode(
  $schema,
  JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
if ($schemaJson === false) {
  $schemaJson = '{}';
}

$mapEmbed = sprintf(
  'https://www.google.com/maps?q=%s,%s&z=%s&output=embed',
  rawurlencode((string) $config['salon']['map_lat']),
  rawurlencode((string) $config['salon']['map_lng']),
  rawurlencode((string) $config['salon']['map_zoom'])
);
$mapOpen = sprintf(
  'https://maps.google.com/?q=%s,%s',
  rawurlencode((string) $config['salon']['map_lat']),
  rawurlencode((string) $config['salon']['map_lng'])
);

$siteJson = json_encode(
  [
    'phoneDigits' => $config['phone']['digits'],
    'phoneDisplay' => $config['phone']['display'],
    'waMessage' => $config['whatsapp']['message'],
  ],
  JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
);
if ($siteJson === false) {
  $siteJson = '{}';
}
?>
<!doctype html>
<html lang="ru">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo h($config['site']['title']); ?></title>
  <meta name="description" content="<?php echo h($config['site']['description']); ?>" />

  <link rel="canonical" href="<?php echo h($baseUrl); ?>" />
  <meta property="og:type" content="website" />
  <meta property="og:locale" content="ru_RU" />
  <meta property="og:site_name" content="<?php echo h($config['site']['name_legal']); ?>" />
  <meta property="og:title" content="<?php echo h($config['site']['title']); ?>" />
  <meta property="og:description" content="<?php echo h($config['site']['description']); ?>" />
  <meta property="og:url" content="<?php echo h($baseUrl); ?>" />
  <meta property="og:image" content="<?php echo h($ogImage); ?>" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="630" />
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="<?php echo h($config['site']['title']); ?>" />
  <meta name="twitter:description" content="<?php echo h($config['site']['description']); ?>" />
  <meta name="twitter:image" content="<?php echo h($ogImage); ?>" />

  <script type="application/ld+json"><?php echo $schemaJson; ?></script>

  <style>
    :root {
      --bg: #fffdf4;
      --surface: #ffffff;
      --text: #1f1a1d;
      --muted: #6f646a;
      --brand: #ffb300;
      --brand-dark: #e6a100;
      --accent: #ffecb3;
      --line: #ffe082;
      --ok: #2f9f74;
      --radius: 18px;
      --shadow: 0 14px 30px rgba(230, 161, 0, 0.16);
      --shadow-sm: 0 4px 18px rgba(230, 161, 0, 0.08);
      --ring: 0 0 0 3px rgba(255, 179, 0, 0.22);
      --max: 1160px;
    }

    * {
      box-sizing: border-box;
    }

    html {
      scroll-behavior: smooth;
    }

    @media (prefers-reduced-motion: reduce) {
      html {
        scroll-behavior: auto;
      }
    }

    html,
    body {
      margin: 0;
      padding: 0;
    }

    body {
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      color: var(--text);
      line-height: 1.55;
      -webkit-font-smoothing: antialiased;
      text-rendering: optimizeLegibility;
      background-color: var(--bg);
      background-image:
        radial-gradient(ellipse 120% 70% at 100% -15%, rgba(255, 235, 160, 0.42) 0%, transparent 52%),
        radial-gradient(ellipse 90% 55% at 0% 100%, rgba(255, 245, 200, 0.55) 0%, transparent 48%),
        radial-gradient(circle at 50% 0%, rgba(255, 250, 230, 0.9) 0%, transparent 42%);
    }

    a {
      color: inherit;
      text-decoration: none;
    }

    img {
      max-width: 100%;
      display: block;
    }

    .container {
      width: min(100% - 32px, var(--max));
      margin-inline: auto;
    }

    .btn {
      display: inline-block;
      border: 0;
      border-radius: 999px;
      padding: 13px 22px;
      font-weight: 700;
      cursor: pointer;
      transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .btn:focus-visible {
      outline: 2px solid var(--brand);
      outline-offset: 3px;
    }

    .btn-primary {
      background: linear-gradient(165deg, #ffca28 0%, var(--brand) 45%, var(--brand-dark) 100%);
      color: #fff;
      box-shadow: 0 10px 24px rgba(255, 179, 0, 0.35);
    }

    .btn-primary:hover {
      background: linear-gradient(165deg, var(--brand) 0%, var(--brand-dark) 100%);
      transform: translateY(-1px);
      box-shadow: 0 12px 28px rgba(230, 161, 0, 0.38);
    }

    .btn-ghost {
      background: rgba(255, 255, 255, 0.92);
      border: 1px solid var(--line);
    }

    .btn-ghost:hover {
      border-color: #ffe082;
      background: #fff;
      box-shadow: var(--shadow-sm);
    }

    @media (prefers-reduced-motion: reduce) {
      .btn-primary:hover {
        transform: none;
      }
    }

    .topbar {
      position: sticky;
      top: 0;
      z-index: 15;
      background: rgba(255, 255, 255, .86);
      backdrop-filter: blur(8px);
      border-bottom: 1px solid rgba(255, 236, 179, .8);
    }

    .topbar-inner {
      min-height: 70px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
    }

    .logo {
      font-size: 24px;
      font-weight: 800;
      letter-spacing: .06em;
      color: #e6a100;
    }

    .nav {
      display: flex;
      gap: 18px;
      color: var(--muted);
      font-weight: 600;
      font-size: 15px;
    }

    .nav a {
      position: relative;
      padding: 6px 2px;
      transition: color 0.25s ease;
    }

    .nav a::after {
      content: "";
      position: absolute;
      left: 0;
      bottom: 0;
      width: 100%;
      height: 2px;
      background: linear-gradient(90deg, var(--brand), var(--brand-dark));
      border-radius: 2px;
      transform: scaleX(0);
      transform-origin: left center;
      transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
    }

    .nav a:hover {
      color: var(--brand-dark);
    }

    .nav a:hover::after,
    .nav a.is-active::after {
      transform: scaleX(1);
    }

    .nav a.is-active {
      color: var(--brand-dark);
    }

    .nav a:focus-visible {
      outline: 2px solid var(--brand);
      outline-offset: 4px;
      border-radius: 4px;
    }

    @media (prefers-reduced-motion: reduce) {
      .nav a::after {
        transition: none;
      }
    }

    section[id] {
      scroll-margin-top: 88px;
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 12px;
      font-weight: 700;
    }

    .phone {
      color: #e6a100;
      white-space: nowrap;
      transition: color 0.2s ease;
    }

    .phone:hover {
      color: var(--brand-dark);
    }

    .menu-toggle {
      display: none;
      align-items: center;
      justify-content: center;
      width: 46px;
      height: 46px;
      padding: 0;
      border: 1px solid var(--line);
      border-radius: 12px;
      background: #fff;
      cursor: pointer;
      color: var(--brand-dark);
      transition: border-color 0.2s ease, background 0.2s ease;
    }

    .menu-toggle:hover {
      border-color: #ffe082;
      background: #fff8e1;
    }

    .menu-toggle:focus-visible {
      outline: 2px solid var(--brand);
      outline-offset: 3px;
    }

    .menu-toggle svg {
      width: 24px;
      height: 24px;
    }

    .nav-panel {
      position: fixed;
      inset: 0;
      z-index: 1000;
      visibility: hidden;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.25s ease, visibility 0.25s ease;
    }

    .nav-panel.is-open {
      visibility: visible;
      opacity: 1;
      pointer-events: auto;
    }

    .nav-panel__backdrop {
      position: absolute;
      inset: 0;
      z-index: 0;
      background: rgba(31, 26, 29, 0.5);
      backdrop-filter: blur(4px);
    }

    .nav-panel__sheet {
      position: absolute;
      top: 0;
      right: 0;
      z-index: 1;
      width: min(320px, 92vw);
      height: 100%;
      min-height: 100dvh;
      background-color: #ffffff;
      box-shadow: -8px 0 32px rgba(230, 161, 0, 0.25);
      padding: 20px 20px 28px;
      display: flex;
      flex-direction: column;
      gap: 8px;
      transform: translateX(100%);
      transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
      isolation: isolate;
    }

    .nav-panel.is-open .nav-panel__sheet {
      transform: translateX(0);
    }

    @media (prefers-reduced-motion: reduce) {

      .nav-panel,
      .nav-panel__sheet {
        transition: none;
      }
    }

    .nav-panel__head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 12px;
      padding-bottom: 12px;
      border-bottom: 1px solid var(--line);
      background-color: #ffffff;
      flex-shrink: 0;
    }

    .nav-panel__title {
      font-weight: 800;
      letter-spacing: 0.06em;
      color: #e6a100;
    }

    .nav-panel__close {
      width: 44px;
      height: 44px;
      border: 0;
      border-radius: 10px;
      background: #fff8e1;
      cursor: pointer;
      font-size: 22px;
      line-height: 1;
      color: var(--brand-dark);
    }

    .nav-panel__close:focus-visible {
      outline: 2px solid var(--brand);
      outline-offset: 2px;
    }

    .nav-panel__links {
      display: flex;
      flex-direction: column;
      gap: 4px;
      flex: 1;
      background-color: #ffffff;
      min-height: 0;
    }

    .nav-panel__links a {
      padding: 14px 12px;
      border-radius: 12px;
      font-weight: 600;
      color: var(--text);
    }

    .nav-panel__links a:hover,
    .nav-panel__links a.is-active {
      background: #fff8e1;
      color: var(--brand-dark);
    }

    .nav-panel__cta {
      margin-top: auto;
      text-align: center;
    }

    .hero {
      padding: 74px 0 58px;
    }

    .hero-grid {
      display: grid;
      grid-template-columns: 1.1fr .9fr;
      gap: 30px;
      align-items: center;
    }

    .badge {
      display: inline-block;
      background: linear-gradient(135deg, #fff8e1 0%, var(--accent) 100%);
      color: #cc8e00;
      border-radius: 999px;
      padding: 7px 14px;
      font-size: 13px;
      font-weight: 700;
      margin-bottom: 16px;
      border: 1px solid rgba(255, 255, 255, 0.75);
      box-shadow: 0 2px 14px rgba(255, 179, 0, 0.12);
    }

    h1 {
      margin: 0 0 14px;
      font-size: clamp(32px, 5vw, 54px);
      line-height: 1.1;
      letter-spacing: -0.02em;
      color: #1a1418;
    }

    .hero p {
      margin: 0 0 24px;
      color: var(--muted);
      font-size: 18px;
      max-width: 58ch;
    }

    .hero-cta {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 20px;
    }

    .proof {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      color: #5c4d54;
      font-weight: 600;
      font-size: 14px;
    }

    .proof span {
      padding: 8px 14px;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.72);
      border: 1px solid rgba(255, 243, 204, 0.95);
      box-shadow: 0 1px 8px rgba(230, 161, 0, 0.06);
    }

    .hero-card {
      background: linear-gradient(165deg, #ffffff 0%, #fff8e1 42%, #fffdf4 100%);
      border-radius: var(--radius);
      border: 1px solid rgba(255, 243, 204, 0.95);
      box-shadow: var(--shadow), 0 0 0 1px rgba(255, 255, 255, 0.6) inset;
      padding: 24px;
    }

    .hero-card h3 {
      margin: 0 0 12px;
    }

    .hero-list {
      margin: 0;
      padding-left: 20px;
      color: #67565e;
    }

    .hero-list li {
      margin-bottom: 8px;
    }

    section {
      padding: 56px 0;
    }

    #services {
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.55) 0%, rgba(255, 243, 204, 0.35) 50%, transparent 100%);
      border-top: 1px solid rgba(255, 243, 204, 0.6);
      border-bottom: 1px solid rgba(255, 243, 204, 0.35);
    }

    .section-title {
      font-size: clamp(28px, 4vw, 42px);
      margin: 0 0 20px;
      line-height: 1.15;
      letter-spacing: -0.015em;
      position: relative;
      padding-bottom: 14px;
      color: #1a1418;
    }

    .section-title::after {
      content: "";
      position: absolute;
      left: 0;
      bottom: 0;
      width: 56px;
      height: 4px;
      border-radius: 999px;
      background: linear-gradient(90deg, var(--brand), var(--accent));
      opacity: 0.9;
    }

    .section-sub {
      margin: 0 0 26px;
      color: var(--muted);
      max-width: 70ch;
    }

    .cards {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
    }

    .card {
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      padding: 22px 20px;
      box-shadow: var(--shadow-sm);
      transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.35s ease, border-color 0.25s ease;
    }

    @media (prefers-reduced-motion: no-preference) and (hover: hover) {
      .card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow);
        border-color: #ffca28;
      }
    }

    .card h4 {
      margin: 0 0 8px;
      font-size: 20px;
    }

    .card p {
      margin: 0;
      color: var(--muted);
    }

    .price {
      margin-top: 12px;
      display: inline-block;
      font-weight: 800;
      color: #cc8e00;
      background: #fff3e0;
      border-radius: 999px;
      padding: 6px 12px;
      font-size: 14px;
    }

    .reviews {
      background: #fff8e1;
      border-top: 1px solid #ffecb3;
      border-bottom: 1px solid #ffecb3;
    }

    .google-head {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      margin-bottom: 18px;
    }

    .google-rating {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 700;
    }

    .google-rating>span:first-child {
      font-size: 13px;
      font-weight: 800;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      color: #5f6368;
      padding: 5px 10px;
      border-radius: 8px;
      background: #fff;
      border: 1px solid #e8eaed;
      box-shadow: 0 1px 3px rgba(60, 64, 67, 0.08);
    }

    .stars {
      color: #f8b400;
      letter-spacing: .08em;
    }

    .review-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 14px;
    }

    .review {
      background: #fff;
      border: 1px solid #ffecb3;
      border-radius: 14px;
      padding: 18px 16px;
      box-shadow: var(--shadow-sm);
      transition: box-shadow 0.3s ease, border-color 0.25s ease;
    }

    @media (prefers-reduced-motion: no-preference) and (hover: hover) {
      .review:hover {
        box-shadow: 0 8px 26px rgba(177, 88, 126, 0.12);
        border-color: #e5c2d0;
      }
    }

    .review-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      margin-bottom: 8px;
    }

    .review-name {
      font-weight: 700;
    }

    .review-text {
      color: #584d52;
      margin: 0;
    }

    .review small {
      color: #8e7b84;
    }

    .offer {
      background: linear-gradient(120deg, #ffecb3 0, #ffffff 60%);
      border: 1px solid #ffe082;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 26px;
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 16px;
      align-items: center;
    }

    .offer h3 {
      margin: 0 0 8px;
      font-size: 30px;
    }

    .offer p {
      margin: 0;
      color: var(--muted);
    }

    .contact-wrap {
      display: grid;
      grid-template-columns: .95fr 1.05fr;
      gap: 18px;
      align-items: stretch;
    }

    .contact-card {
      border: 1px solid var(--line);
      border-radius: var(--radius);
      padding: 22px;
      background: #fff;
    }

    .contact-list {
      list-style: none;
      padding: 0;
      margin: 0 0 18px;
      color: #5f5258;
    }

    .contact-list li {
      margin-bottom: 8px;
    }

    .contact-form {
      position: relative;
      display: grid;
      gap: 10px;
    }

    .contact-form label {
      font-size: 14px;
      font-weight: 600;
      color: #4a3f44;
    }

    .contact-form .consent-label {
      font-weight: 500;
      display: flex;
      align-items: flex-start;
      gap: 10px;
      cursor: pointer;
    }

    .contact-form .consent-label input {
      margin-top: 3px;
      flex-shrink: 0;
      width: 18px;
      height: 18px;
      accent-color: var(--brand);
    }

    .contact-form .consent-label a {
      color: var(--brand-dark);
      text-decoration: underline;
      text-underline-offset: 2px;
    }

    .honeypot {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
      border: 0;
    }

    .form-status {
      min-height: 1.25em;
      margin: 0;
      font-size: 14px;
      font-weight: 600;
    }

    .form-status.is-ok {
      color: var(--ok);
    }

    .form-status.is-err {
      color: #c43d5c;
    }

    .field {
      border: 1px solid #ffca28;
      border-radius: 12px;
      padding: 12px 14px;
      font: inherit;
      background: #fffefd;
      transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .field:hover {
      border-color: #ffe082;
    }

    .field:focus {
      outline: none;
      border-color: var(--brand);
      background: #fff;
      box-shadow: var(--ring);
    }

    .map-box {
      border: 1px solid #ffecb3;
      border-radius: var(--radius);
      overflow: hidden;
      min-height: 420px;
      background: #fff;
    }

    .map-box iframe {
      width: 100%;
      height: 100%;
      border: 0;
      min-height: 420px;
    }

    .map-note {
      font-size: 12px;
      color: #8b7a82;
      margin-top: 8px;
    }

    footer {
      padding: 28px 0 36px;
      color: #6f646a;
      font-size: 14px;
      border-top: 1px solid rgba(241, 221, 229, 0.85);
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.65) 0%, rgba(255, 249, 251, 0.5) 100%);
    }

    footer a {
      color: var(--brand-dark);
      text-decoration: underline;
      text-underline-offset: 2px;
    }

    @media (max-width: 1024px) {
      .nav {
        display: none;
      }

      .menu-toggle {
        display: flex;
      }

      .header-book {
        display: none !important;
      }

      .cards {
        grid-template-columns: repeat(2, 1fr);
      }

      .hero-grid,
      .contact-wrap {
        grid-template-columns: 1fr;
      }

      .review-grid {
        grid-template-columns: 1fr 1fr;
      }

      .offer {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 640px) {
      .topbar-inner {
        min-height: 62px;
      }

      .phone {
        display: none;
      }

      .cards,
      .review-grid {
        grid-template-columns: 1fr;
      }

      .hero p {
        font-size: 16px;
      }

      .btn {
        width: 100%;
        text-align: center;
      }

      .hero-cta {
        width: 100%;
      }
    }

    /* Появление блоков при скролле */
    .reveal {
      opacity: 0;
      transform: translateY(1.75rem);
      transition:
        opacity 0.75s cubic-bezier(0.22, 1, 0.36, 1),
        transform 0.75s cubic-bezier(0.22, 1, 0.36, 1);
      transition-delay: var(--reveal-delay, 0s);
      will-change: opacity, transform;
    }

    .reveal.is-visible {
      opacity: 1;
      transform: translateY(0);
      will-change: auto;
    }

    .reveal-delay-1 {
      --reveal-delay: 0.1s;
    }

    .reveal-delay-2 {
      --reveal-delay: 0.2s;
    }

    .reveal-delay-3 {
      --reveal-delay: 0.3s;
    }

    .cards>.reveal:nth-child(1) {
      --reveal-delay: 0ms;
    }

    .cards>.reveal:nth-child(2) {
      --reveal-delay: 0.07s;
    }

    .cards>.reveal:nth-child(3) {
      --reveal-delay: 0.14s;
    }

    .cards>.reveal:nth-child(4) {
      --reveal-delay: 0.21s;
    }

    .cards>.reveal:nth-child(n + 5) {
      --reveal-delay: 0.28s;
    }

    .review-grid>.reveal:nth-child(1) {
      --reveal-delay: 0ms;
    }

    .review-grid>.reveal:nth-child(2) {
      --reveal-delay: 0.09s;
    }

    .review-grid>.reveal:nth-child(3) {
      --reveal-delay: 0.18s;
    }

    .review-grid>.reveal:nth-child(n + 4) {
      --reveal-delay: 0.27s;
    }

    @media (prefers-reduced-motion: reduce) {
      .reveal {
        opacity: 1;
        transform: none;
        transition: none;
        will-change: auto;
      }
    }
  </style>
</head>

<body>
  <header class="topbar">
    <div class="container topbar-inner">
      <a class="logo" href="#top"><?php echo h($config['site']['name_short']); ?></a>
      <nav class="nav" aria-label="Основное меню">
        <a class="js-nav-anchor" href="#services">Услуги</a>
        <a class="js-nav-anchor" href="#prices">Цены</a>
        <a class="js-nav-anchor" href="#reviews">Отзывы</a>
        <a class="js-nav-anchor" href="#contacts">Контакты</a>
      </nav>
      <div class="header-actions">
        <a class="phone" href="#" data-phone-link><?php echo h($config['phone']['display']); ?></a>
        <button type="button" class="menu-toggle" id="menu-toggle" aria-controls="nav-panel" aria-expanded="false"
          aria-label="Открыть меню">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M4 7h16M4 12h16M4 17h16" />
          </svg>
        </button>
        <a class="btn btn-primary header-book" href="#contacts" data-book-online>Записаться</a>
      </div>
    </div>
  </header>

  <!-- Вне header: иначе backdrop-filter у .topbar ломает слои и fixed-оверлей «просвечивает» -->
  <div id="nav-panel" class="nav-panel" aria-hidden="true">
    <div class="nav-panel__backdrop" data-close-panel tabindex="-1"></div>
    <div class="nav-panel__sheet" role="dialog" aria-modal="true" aria-labelledby="nav-panel-title">
      <div class="nav-panel__head">
        <span id="nav-panel-title" class="nav-panel__title">Меню</span>
        <button type="button" class="nav-panel__close" aria-label="Закрыть меню" data-close-panel>&times;</button>
      </div>
      <nav class="nav-panel__links" aria-label="Разделы сайта">
        <a class="js-nav-anchor" href="#services">Услуги</a>
        <a class="js-nav-anchor" href="#prices">Цены</a>
        <a class="js-nav-anchor" href="#reviews">Отзывы</a>
        <a class="js-nav-anchor" href="#contacts">Контакты</a>
      </nav>
      <a class="btn btn-primary nav-panel__cta" href="#contacts" data-book-online>Записаться</a>
    </div>
  </div>

  <main id="top">
    <section class="hero">
      <div class="container hero-grid">
        <div class="reveal">
          <span class="badge"><?php echo h($config['hero']['badge']); ?></span>
          <h1><?php echo $config['hero']['title']; // С тегами em ?></h1>
          <p><?php echo h($config['hero']['description']); ?></p>
          <div class="hero-cta">
            <a class="btn btn-primary" href="#contacts" data-book-online>Записаться онлайн</a>
            <a class="btn btn-ghost" href="#prices">Смотреть прайс</a>
          </div>
          <div class="proof">
            <span>⭐ 4.9 рейтинг</span>
            <span>2 000+ довольных клиентов</span>
            <span>Ежедневно: 09:00 - 21:00</span>
          </div>
        </div>
        <aside class="hero-card reveal reveal-delay-2">
          <h3><?php echo h($config['hero']['benefits_title']); ?></h3>
          <ul class="hero-list">
            <?php foreach ($config['hero']['benefits'] as $benefit): ?>
              <li><?php echo h($benefit); ?></li>
            <?php endforeach; ?>
          </ul>
        </aside>
      </div>
    </section>

    <section id="services">
      <div class="container">
        <h2 class="section-title reveal">Популярные услуги</h2>
        <p class="section-sub reveal reveal-delay-1">Подберем образ под ваш стиль, график и бюджет. Ниже — демо-примеры
          услуг для лендинга.</p>
        <div class="cards">
          <?php foreach ($services as $svc): ?>
            <article class="card reveal">
              <h4><?php echo h($svc['title']); ?></h4>
              <p><?php echo h($svc['description']); ?></p>
              <span class="price"><?php echo h($svc['price']); ?></span>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <?php if (!empty($config['offer']['show'])): ?>
      <section id="prices">
        <div class="container">
          <div class="offer reveal">
            <div>
              <h3><?php echo h($config['offer']['title'] ?? ''); ?></h3>
              <p><?php echo h($config['offer']['description'] ?? ''); ?></p>
            </div>
            <a class="btn btn-primary" href="#contacts" data-book-online>Забронировать время</a>
          </div>
        </div>
      </section>
    <?php endif; ?>

    <section id="reviews" class="reviews">
      <div class="container">
        <h2 class="section-title reveal">Отзывы клиентов</h2>
        <p class="section-sub reveal reveal-delay-1">Блок стилизован под отзывы из Google. Сейчас используются
          демо-данные, позже можно подключить реальные отзывы через Google Business API / виджет.</p>
        <div class="google-head reveal reveal-delay-2">
          <div class="google-rating">
            <span>Google</span>
            <span class="stars">★★★★★</span>
            <span>4.9 (327 отзывов)</span>
          </div>
          <a class="btn btn-ghost" href="<?php echo h($config['salon']['google_maps_url']); ?>" target="_blank"
            rel="noopener">Открыть в Google Maps</a>
        </div>
        <div class="review-grid">
          <?php foreach ($reviews as $rev): ?>
            <article class="review reveal">
              <div class="review-top">
                <span class="review-name"><?php echo h($rev['name']); ?></span>
                <span class="stars"><?php echo h($rev['stars']); ?></span>
              </div>
              <p class="review-text"><?php echo h($rev['text']); ?></p>
              <small><?php echo h($rev['date']); ?></small>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section id="contacts">
      <div class="container">
        <h2 class="section-title reveal">Мы рядом — приходите в гости</h2>
        <p class="section-sub reveal reveal-delay-1">Ниже встроена Google-карта. Адрес и контакты — демо, замените на
          свои перед запуском рекламы.</p>
        <div class="contact-wrap">
          <div class="contact-card reveal reveal-delay-2">
            <ul class="contact-list">
              <li><strong>Адрес:</strong> <?php echo h($config['salon']['address_line']); ?></li>
              <li><strong>Телефон:</strong> <a href="#" data-phone-link><span
                    data-phone-text><?php echo h($config['phone']['display']); ?></span></a></li>
              <li><strong>WhatsApp:</strong> <span data-phone-text><?php echo h($config['phone']['display']); ?></span>
              </li>
              <li><strong>График:</strong> <?php echo h($config['salon']['hours_label']); ?></li>
            </ul>
            <form id="contact-form" class="contact-form" action="send-contact.php" method="post" novalidate>
              <input type="text" name="website" class="honeypot" tabindex="-1" autocomplete="off" aria-hidden="true" />
              <label for="cf-name">Ваше имя</label>
              <input id="cf-name" class="field" type="text" name="name" autocomplete="name" required maxlength="120" />
              <label for="cf-phone">Телефон</label>
              <input id="cf-phone" class="field" type="tel" name="phone" autocomplete="tel" required maxlength="32" />
              <label class="consent-label">
                <input type="checkbox" name="consent" value="1" required />
                <span>Согласен(на) на <a href="privacy.html" target="_blank" rel="noopener">обработку персональных
                    данных</a></span>
              </label>
              <p id="form-status" class="form-status" role="status" aria-live="polite"></p>
              <button class="btn btn-primary" type="submit">Получить консультацию</button>
            </form>
          </div>
          <div class="map-box reveal reveal-delay-3">
            <iframe title="Google Map" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
              src="<?php echo h($mapEmbed); ?>">
            </iframe>
          </div>
        </div>
        <p class="map-note reveal">Если карта не отображается, откройте ссылку в новом окне: <a
            href="<?php echo h($mapOpen); ?>" target="_blank" rel="noopener">Google Maps</a>.</p>
      </div>
    </section>
  </main>

  <footer>
    <div class="container">
      <div>© <?php echo h((string) $config['footer']['year']); ?> <?php echo h($config['site']['name_legal']); ?>. С
        любовью к деталям. Разработка <a href="https://master-jenya.com" target="_blank"
          rel="noopener">Master-Jenya.com</a></div>
    </div>
  </footer>

  <script>
    (function () {
      var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      if (reduceMotion) return;

      var nodes = document.querySelectorAll('.reveal');
      if (!nodes.length) return;

      function showAll() {
        nodes.forEach(function (el) {
          el.classList.add('is-visible');
        });
      }

      if (!('IntersectionObserver' in window)) {
        showAll();
        return;
      }

      var io = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            io.unobserve(entry.target);
          });
        },
        { root: null, rootMargin: '0px 0px -32px 0px', threshold: 0.06 }
      );

      nodes.forEach(function (el) {
        io.observe(el);
      });
    })();

    (function () {
      var SITE = <?php echo $siteJson; ?>;

      var telHref = 'tel:+' + SITE.phoneDigits;

      document.querySelectorAll('[data-phone-link]').forEach(function (a) {
        a.href = telHref;
      });
      document.querySelectorAll('[data-phone-text]').forEach(function (el) {
        el.textContent = SITE.phoneDisplay;
      });

      document.querySelectorAll('[data-book-online]').forEach(function (a) {
        a.href = 'https://t.me/demo_rec_bot';
        a.target = '_blank';
        a.rel = 'noopener noreferrer';
      });
    })();

    (function () {
      var panel = document.getElementById('nav-panel');
      var toggle = document.getElementById('menu-toggle');
      if (!panel || !toggle) return;

      function setOpen(open) {
        panel.classList.toggle('is-open', open);
        panel.setAttribute('aria-hidden', open ? 'false' : 'true');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.style.overflow = open ? 'hidden' : '';
        if (open) {
          var closeBtn = panel.querySelector('.nav-panel__close');
          if (closeBtn) {
            closeBtn.focus();
          }
        } else {
          toggle.focus();
        }
      }

      toggle.addEventListener('click', function () {
        setOpen(!panel.classList.contains('is-open'));
      });

      panel.querySelectorAll('[data-close-panel]').forEach(function (el) {
        el.addEventListener('click', function () {
          setOpen(false);
        });
      });

      panel.querySelectorAll('.nav-panel__links a[href^="#"]').forEach(function (a) {
        a.addEventListener('click', function () {
          setOpen(false);
        });
      });

      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && panel.classList.contains('is-open')) {
          setOpen(false);
        }
      });
    })();

    (function () {
      var navLinks = document.querySelectorAll('.js-nav-anchor[href^="#"]');
      var sections = document.querySelectorAll('section[id]');
      var headerOffset = 88;

      function updateActiveNav() {
        var y = window.scrollY + headerOffset + 2;
        var activeId = '';
        sections.forEach(function (section) {
          var top = section.getBoundingClientRect().top + window.scrollY;
          if (top <= y) {
            activeId = section.id;
          }
        });
        navLinks.forEach(function (link) {
          var href = link.getAttribute('href');
          var match = href === '#' + activeId;
          var on = match && activeId !== '';
          link.classList.toggle('is-active', on);
          if (on) {
            link.setAttribute('aria-current', 'true');
          } else {
            link.removeAttribute('aria-current');
          }
        });
      }

      var ticking = false;
      function onScroll() {
        if (!ticking) {
          requestAnimationFrame(function () {
            updateActiveNav();
            ticking = false;
          });
          ticking = true;
        }
      }

      window.addEventListener('scroll', onScroll, { passive: true });
      window.addEventListener('resize', onScroll, { passive: true });
      window.addEventListener('load', updateActiveNav);
      window.addEventListener('hashchange', updateActiveNav);
      updateActiveNav();
    })();

    (function () {
      var form = document.getElementById('contact-form');
      var statusEl = document.getElementById('form-status');
      if (!form || !statusEl) return;

      form.addEventListener('submit', function (e) {
        e.preventDefault();
        statusEl.textContent = '';
        statusEl.classList.remove('is-ok', 'is-err');

        if (!form.consent.checked) {
          statusEl.textContent = 'Нужно согласие на обработку персональных данных.';
          statusEl.classList.add('is-err');
          return;
        }

        var fd = new FormData(form);
        var btn = form.querySelector('button[type="submit"]');
        if (btn) {
          btn.disabled = true;
        }

        fetch(form.action, {
          method: 'POST',
          body: fd,
          headers: { Accept: 'application/json' }
        })
          .then(function (res) {
            return res.text().then(function (text) {
              var data = null;
              try {
                data = text ? JSON.parse(text) : null;
              } catch (e) {
                data = null;
              }
              return { ok: res.ok, data: data };
            });
          })
          .then(function (r) {
            if (r.ok && r.data && r.data.ok) {
              statusEl.textContent = r.data.message || 'Спасибо! Мы свяжемся с вами.';
              statusEl.classList.add('is-ok');
              form.reset();
            } else {
              statusEl.textContent =
                (r.data && r.data.error) || 'Не удалось отправить. Попробуйте позже или позвоните нам.';
              statusEl.classList.add('is-err');
            }
          })
          .catch(function () {
            statusEl.textContent = 'Ошибка сети. Проверьте подключение или позвоните нам.';
            statusEl.classList.add('is-err');
          })
          .finally(function () {
            if (btn) {
              btn.disabled = false;
            }
          });
      });
    })();
  </script>
</body>

</html>