<?php
/**
 * Header partial - opens HTML, renders <head>, top-bar, nav, hero, schema.org
 *
 * @var array $packages Available for destination dropdown
 */
use Mystical\Bootstrap;
use Mystical\Csrf;

$appName   = Bootstrap::config('BUSINESS_NAME', 'Mystical Expedition');
$phone     = Bootstrap::config('BUSINESS_PHONE', '+91-8219000937');
$email     = Bootstrap::config('BUSINESS_EMAIL', 'info@mysticalexpedition.com');
$address   = Bootstrap::config('BUSINESS_ADDRESS', '');
$whatsapp  = Bootstrap::config('BUSINESS_WHATSAPP', '918219000937');
$appUrl    = Bootstrap::config('APP_URL', '');

// Pass packages list to <select> in hero form
$destinations = array_map(static fn($p) => $p['title'], $packages ?? []);

// Pre-select destination from ?package=... query param
$prefillDest = $_GET['package'] ?? '';

// JSON-LD Schema.org TravelAgency
$schema = [
    '@context' => 'https://schema.org',
    '@type'    => 'TravelAgency',
    'name'     => $appName,
    'image'    => $appUrl . '/assets/img/brand/logo-dark.png',
    'url'      => $appUrl,
    'telephone' => $phone,
    'email'    => $email,
    'address'  => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => 'NH-22, opposite SBI Bank',
        'addressLocality' => 'Shoghi',
        'addressRegion'   => 'Himachal Pradesh',
        'postalCode'      => '171219',
        'addressCountry'  => 'IN',
    ],
    'priceRange'        => 'INR INR-INR',
    'aggregateRating'   => [
        '@type'       => 'AggregateRating',
        'ratingValue' => '4.9',
        'reviewCount' => '200',
    ],
    'openingHours' => 'Mo-Su 09:00-21:00',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a1422">

    <title>Spiti Valley Tour Packages | <?php echo htmlspecialchars($appName) ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($appName) ?> - Customised Spiti Valley tour packages. Kaza, Chandratal, Tabo, Chitkul, Kalpa, Dhankar & Kinnaur circuit. Free quotes in 5 minutes.">
    <meta name="keywords" content="Spiti Valley tour packages, Spiti Valley trip, Chandratal Lake, Kaza tour, Tabo Monastery, Chitkul, Kibber, Key Monastery, Kunzum Pass, Kinnaur Spiti circuit, <?php echo htmlspecialchars($appName) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo htmlspecialchars($appUrl) ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Spiti Valley Tour Packages | <?php echo htmlspecialchars($appName) ?>">
    <meta property="og:description" content="Customised Spiti Valley tour packages. Free quotes in 5 minutes.">
    <meta property="og:image" content="<?php echo htmlspecialchars($appUrl) ?>/assets/img/himachal/spiti-valley.jpg">
    <meta property="og:url" content="<?php echo htmlspecialchars($appUrl) ?>">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($appName) ?>">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Spiti Valley Tour Packages | <?php echo htmlspecialchars($appName) ?>">
    <meta name="twitter:description" content="Customised Spiti Valley tour packages. Free quotes in 5 minutes.">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($appUrl) ?>/assets/img/himachal/spiti-valley.jpg">

    <!-- Favicon -->
    <link rel="icon" href="<?php echo htmlspecialchars($appUrl) ?>/assets/img/brand/logo-white.png" type="image/png">

    <!-- Preload critical assets -->
    <link rel="preload" as="image" href="<?php echo htmlspecialchars($appUrl) ?>/assets/img/himachal/spiti-valley.jpg">

    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars($appUrl) ?>/assets/css/variables.css?v=<?php echo filemtime(dirname(__DIR__, 3).'/assets/css/variables.css') ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($appUrl) ?>/assets/css/main.css?v=<?php echo filemtime(dirname(__DIR__, 3).'/assets/css/main.css') ?>">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Google Fonts: Poppins + Cinzel (Spiti – ancient monastic feel) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
    <?php echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
    </script>
</head>
<body class="me-page">

<!-- Floating WhatsApp + Phone (always visible) -->
<div class="me-floating-cta" aria-label="Quick contact">
    <a href="https://wa.me/<?php echo htmlspecialchars($whatsapp) ?>?text=Hello%20<?php echo urlencode($appName) ?>%2C%20I%20want%20to%20book%20a%20package."
       class="me-floating-cta__btn me-floating-cta__btn--whatsapp"
       target="_blank" rel="noopener"
       aria-label="Chat on WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>
    <a href="tel:<?php echo htmlspecialchars($phone) ?>"
       class="me-floating-cta__btn me-floating-cta__btn--phone"
       aria-label="Call us">
        <i class="fa-solid fa-phone"></i>
    </a>
</div>

<!-- Sticky wrapper: announcement bar + nav stick together -->
<div class="me-sticky-wrap">

<!-- Announcement strip -->
<div class="me-top-bar" role="complementary" aria-label="Announcement">
    <div class="container">
        <p class="me-top-bar__text">
            <i class="fa-solid fa-mountain" aria-hidden="true"></i>
            Early Bird Offer — Book Your Spiti Valley Package &amp; Save Big This Season!
            <a href="#get-quote" class="me-top-bar__cta">Get Free Quote &rarr;</a>
        </p>
    </div>
</div>

<!-- Top header bar -->
<header class="me-top-header">
    <div class="container">
        <div class="me-top-header__inner">
            <a href="/" class="me-top-header__logo">
                <img src="<?php echo htmlspecialchars($appUrl) ?>/assets/img/brand/logo-white.png" alt="<?php echo htmlspecialchars($appName) ?>" height="52">
            </a>
            <div class="me-top-header__right">
                <ul class="me-top-header__contacts">
                    <li>
                        <a href="https://wa.me/<?php echo htmlspecialchars($whatsapp) ?>?text=Hello%20<?php echo urlencode($appName) ?>" class="me-top-header__link me-top-header__link--wa" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
                            <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                            <span class="me-top-header__link-text">WhatsApp</span>
                        </a>
                    </li>
                    <li class="me-top-header__contact--email">
                        <a href="mailto:<?php echo htmlspecialchars($email) ?>" class="me-top-header__link">
                            <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                            <span class="me-top-header__link-text"><?php echo htmlspecialchars($email) ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="tel:<?php echo htmlspecialchars($phone) ?>" class="me-top-header__link me-top-header__link--phone">
                            <i class="fa-solid fa-phone" aria-hidden="true"></i>
                            <span class="me-top-header__link-text"><?php echo htmlspecialchars($phone) ?></span>
                        </a>
                    </li>
                </ul>
                <a href="#get-quote" class="me-btn me-btn--accent me-btn--sm me-top-header__quote-btn">
                    <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                    <span>Free Quote</span>
                </a>
            </div>
        </div>
    </div>
</header>

</div><!-- /.me-sticky-wrap -->

<!-- Hero section -->
<?php if (empty($hideHero)): ?>
<section class="me-hero" aria-label="Hero - get free quotes">
    <div class="me-hero__overlay" aria-hidden="true"></div>
    <div class="container position-relative">
        <div class="row align-items-center me-hero__row">
            <!-- Left content -->
            <div class="col-lg-7 col-12 text-white me-hero__content">
                <p class="me-hero__eyebrow"><span class="me-hero__eyebrow-badge"><i class="fa-solid fa-mountain" aria-hidden="true"></i> Early Bird Offer — Book Spiti Valley &amp; Save Big This Season!</span></p>
                <h1 class="me-hero__title">
                    Explore <span class="me-hero__title-accent">Spiti Valley Tour Packages</span>
                </h1>
                <p class="me-hero__subtitle">Journey to the roof of the world &mdash; <strong>Kaza, Chandratal, Tabo, Chitkul, Kibber &amp; beyond.</strong></p>
                <div class="me-hero__locations">
                    <span class="me-hero__loc-pill">Kaza</span>
                    <span class="me-hero__loc-pill">Chandratal</span>
                    <span class="me-hero__loc-pill">Tabo</span>
                    <span class="me-hero__loc-pill">Chitkul</span>
                    <span class="me-hero__loc-pill">Kibber</span>
                    <span class="me-hero__loc-pill">Kunzum Pass</span>
                </div>

                <div class="me-hero__chips" role="list">
                    <div class="me-hero__chip" role="listitem">
                        <img src="<?php echo htmlspecialchars($appUrl) ?>/assets/img/icons/mountain.png" alt="" width="48" height="48" loading="lazy">
                        <span>High Altitude</span>
                    </div>
                    <div class="me-hero__chip" role="listitem">
                        <img src="<?php echo htmlspecialchars($appUrl) ?>/assets/img/icons/adventure.png" alt="" width="48" height="48" loading="lazy">
                        <span>Adventure</span>
                    </div>
                    <div class="me-hero__chip" role="listitem">
                        <img src="<?php echo htmlspecialchars($appUrl) ?>/assets/img/icons/camping.png" alt="" width="48" height="48" loading="lazy">
                        <span>Monasteries</span>
                    </div>
                </div>

                <a href="#packages" class="me-btn me-btn--primary me-btn--lg me-hero__cta">
                    Explore Packages
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>

                <!-- Trust strip -->
                <div class="me-hero__trust">
                    <div class="me-hero__trust-item">
                        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                        <span>Himachal Tourism Approved</span>
                    </div>
                    <div class="me-hero__trust-item">
                        <i class="fa-solid fa-star" aria-hidden="true"></i>
                        <span>4.9/5 on Google</span>
                    </div>
                    <div class="me-hero__trust-item">
                        <i class="fa-solid fa-users" aria-hidden="true"></i>
                        <span>5,000+ Spiti Travellers</span>
                    </div>
                </div>
                <a href="#packages" class="me-hero__scroll" aria-label="Scroll to packages">
                    <span>Explore packages</span>
                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                </a>
            </div>

            <!-- Right form (Hero CTA) -->
            <div class="col-lg-5 col-12 mt-4 mt-lg-0">
                <div class="me-form-card" id="get-quote">
                    <span class="me-form-card__badge"><i class="fa-solid fa-tag" aria-hidden="true"></i> Free Quote</span>
                    <h2 class="me-form-card__title">Get Free Quotes</h2>
                    <p class="me-form-card__sub">Reply in 5-15 minutes</p>

                    <form method="POST" action="submit.php" class="me-form" data-ajax-form>
                        <?php echo Csrf::field() ?>

                        <!-- Honeypot - hidden from humans, blocks bots -->
                        <input type="text" name="website" tabindex="-1" autocomplete="off" class="me-honeypot" aria-hidden="true">

                        <div class="me-form__field">
                            <label for="hero-name" class="me-form__label">Full Name</label>
                            <input id="hero-name" type="text" name="name" class="me-form__input" placeholder="Your Name" required minlength="2" maxlength="100" autocomplete="name">
                        </div>

                        <div class="me-form__field">
                            <label for="hero-city" class="me-form__label">City</label>
                            <input id="hero-city" type="text" name="city" class="me-form__input" placeholder="Your City" required minlength="2" maxlength="60" autocomplete="address-level2">
                        </div>

                        <div class="me-form__field">
                            <label for="hero-email" class="me-form__label">Email</label>
                            <input id="hero-email" type="email" name="email" class="me-form__input" placeholder="you@email.com" required autocomplete="email">
                        </div>

                        <div class="me-form__field">
                            <label for="hero-phone" class="me-form__label">Mobile No.</label>
                            <input id="hero-phone" type="tel" name="phone" class="me-form__input" placeholder="10-digit mobile" required minlength="10" maxlength="10" pattern="[6-9][0-9]{9}" autocomplete="tel" inputmode="numeric">
                        </div>

                        <div class="me-form__field">
                            <label for="hero-destination" class="me-form__label">Destination</label>
                            <select id="hero-destination" name="destination" class="me-form__input me-form__select" required>
                                <option value="">Choose your Destination</option>
                                <?php foreach ($destinations as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d) ?>" <?php echo $prefillDest === $d ? 'selected' : '' ?>>
                                        <?php echo htmlspecialchars($d) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" name="contact_submit" class="me-btn me-btn--accent me-btn--block me-form__submit">
                            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                            Send Enquiry
                        </button>

                        <p class="me-form__legal">We respect your privacy. No spam.</p>

                        <div class="me-form__message" data-form-message role="status" aria-live="polite"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="me-hero__wave" aria-hidden="true">
        <svg viewBox="0 0 1440 80" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,60 L80,30 L160,50 L260,10 L370,45 L480,5 L580,38 L680,12 L780,42 L880,8 L980,35 L1080,15 L1180,44 L1280,20 L1360,50 L1440,28 L1440,80 L0,80 Z" fill="#0a1422"/>
        </svg>
    </div>
</section>
<?php endif; ?>