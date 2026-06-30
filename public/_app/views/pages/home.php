<?php
/**
 * Home page - composes the landing page
 */
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/Bootstrap.php';

use Mystical\Bootstrap;

Bootstrap::init();

// Load packages and reviews from JSON
$packagesJson = Bootstrap::projectRoot() . '/data/packages.json';
$reviewsJson  = Bootstrap::projectRoot() . '/data/reviews.json';

$packages = json_decode((string) file_get_contents($packagesJson), true) ?: [];
$reviews  = json_decode((string) file_get_contents($reviewsJson), true) ?: [];

$appName = Bootstrap::config('BUSINESS_NAME', 'Mystical Expedition');
$address = Bootstrap::config('BUSINESS_ADDRESS', '');

include __DIR__ . '/../partials/header.php';
?>

<!-- Trust Strip -->
<section class="me-trust-strip" aria-label="Trust signals">
    <div class="container">
        <div class="me-trust-strip__grid">
            <div class="me-trust-strip__item">
                <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                <span>Himachal Tourism Approved</span>
            </div>
            <div class="me-trust-strip__item">
                <i class="fa-solid fa-star" aria-hidden="true"></i>
                <span>4.9/5 Google Rating</span>
            </div>
            <div class="me-trust-strip__item">
                <i class="fa-solid fa-users" aria-hidden="true"></i>
                <span>5,000+ Spiti Travellers</span>
            </div>
            <div class="me-trust-strip__item">
                <i class="fa-solid fa-bolt" aria-hidden="true"></i>
                <span>Reply in 5–15 Minutes</span>
            </div>
        </div>
    </div>
</section>

<!-- Spiti Stats Bar -->
<section class="me-spiti-stats" aria-label="Spiti Valley highlights">
    <div class="container">
        <div class="me-spiti-stats__grid">
            <div class="me-spiti-stats__item">
                <span class="me-spiti-stats__number">4,550<span class="me-spiti-stats__unit">m</span></span>
                <span class="me-spiti-stats__label">Max Altitude</span>
            </div>
            <div class="me-spiti-stats__item">
                <span class="me-spiti-stats__number">6</span>
                <span class="me-spiti-stats__label">Curated Routes</span>
            </div>
            <div class="me-spiti-stats__item">
                <span class="me-spiti-stats__number">500<span class="me-spiti-stats__unit">+</span></span>
                <span class="me-spiti-stats__label">Spiti Explorers</span>
            </div>
            <div class="me-spiti-stats__item">
                <span class="me-spiti-stats__number">15<span class="me-spiti-stats__unit">+</span></span>
                <span class="me-spiti-stats__label">Years Experience</span>
            </div>
        </div>
    </div>
</section>

<!-- Packages -->
<section class="me-packages" id="packages" aria-label="Tour packages">
    <div class="container">
        <div class="me-section-head">
            <p class="me-section-head__eyebrow">Curated for You</p>
            <h2 class="me-section-head__title">Spiti Valley <span class="me-section-head__title-accent">Tour Packages</span></h2>
            <p class="me-section-head__sub">Choose from our handcrafted Spiti Valley circuits — from 7-day escapes to full 10-day Kinnaur–Spiti odysseys.</p>
        </div>

        <div class="me-packages__grid">
            <?php foreach ($packages as $package): ?>
                <?php include __DIR__ . '/../partials/package-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Mountain divider -->
<div aria-hidden="true" style="background:#0a1422;line-height:0;">
    <svg viewBox="0 0 1440 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" style="display:block;width:100%;height:60px;">
        <path d="M0,45 L90,15 L180,38 L300,5 L420,32 L540,8 L660,28 L780,4 L900,25 L1020,6 L1140,30 L1260,8 L1440,35 L1440,60 L0,60 Z" fill="#0d1828"/>
    </svg>
</div>

<!-- Why Choose Us -->
<section class="me-why" aria-label="Why choose us for Spiti">
    <div class="container">
        <div class="me-section-head">
            <p class="me-section-head__eyebrow">Why Travel With Us</p>
            <h2 class="me-section-head__title">Built for <span class="me-section-head__title-accent">High-Altitude Travel</span></h2>
            <p class="me-section-head__sub">Spiti is unlike any other destination — and we've spent years mastering it</p>
        </div>
        <div class="me-why__grid">
            <div class="me-why__card">
                <div class="me-why__icon"><i class="fa-solid fa-file-shield"></i></div>
                <h3>Inner Line Permits</h3>
                <p>We handle all Inner Line Permits for restricted Spiti zones — zero paperwork stress for you.</p>
            </div>
            <div class="me-why__card">
                <div class="me-why__icon"><i class="fa-solid fa-heart-pulse"></i></div>
                <h3>Altitude Acclimatisation</h3>
                <p>Every itinerary is engineered with acclimatisation stops to keep you safe above 4,000m.</p>
            </div>
            <div class="me-why__card">
                <div class="me-why__icon"><i class="fa-solid fa-truck-monster"></i></div>
                <h3>High-Altitude Vehicles</h3>
                <p>Sturdy 4x4 SUVs with experienced mountain drivers who know every road in Spiti.</p>
            </div>
            <div class="me-why__card">
                <div class="me-why__icon"><i class="fa-solid fa-headset"></i></div>
                <h3>24/7 Trip Support</h3>
                <p>Our team is reachable round the clock — even when you're offline at Chandratal Lake.</p>
            </div>
        </div>
    </div>
</section>

<!-- Reviews -->
<section class="me-reviews" aria-label="Customer reviews">
    <div class="container">
        <div class="me-section-head">
            <p class="me-section-head__eyebrow">Happy Travellers</p>
            <h2 class="me-section-head__title">What <span class="me-section-head__title-accent">Spiti Explorers Say</span></h2>
            <p class="me-section-head__sub">Real experiences from travellers who conquered the high Himalayas with us</p>
        </div>

        <div class="me-reviews__grid">
            <?php foreach ($reviews as $review): ?>
                <?php include __DIR__ . '/../partials/review-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Mountain divider -->
<div aria-hidden="true" style="background:#0d1828;line-height:0;">
    <svg viewBox="0 0 1440 70" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" style="display:block;width:100%;height:70px;">
        <path d="M0,50 L100,20 L200,45 L340,5 L460,38 L580,8 L700,35 L820,2 L940,30 L1060,10 L1180,40 L1300,12 L1440,38 L1440,70 L0,70 Z" fill="var(--me-color-bg-alt)"/>
    </svg>
</div>

<!-- About -->
<section class="me-about" aria-label="About <?php echo htmlspecialchars($appName) ?>">
    <div class="container">
        <div class="me-about__grid">
            <div class="me-about__media">
                <div class="me-about__media-wrap">
                    <img src="<?php echo htmlspecialchars(Bootstrap::config('APP_URL', '')) ?>/assets/img/himachal/kinnaur-spiti-valley.webp" alt="Spiti Valley landscape" loading="lazy" decoding="async" width="600" height="400" class="me-about__img me-about__img--main">
                    <img src="<?php echo htmlspecialchars(Bootstrap::config('APP_URL', '')) ?>/assets/img/himachal/pin-dhaba.jpg" alt="Spiti Valley monastery" loading="lazy" decoding="async" width="240" height="200" class="me-about__img me-about__img--float">
                </div>
            </div>
            <div class="me-about__content">
                <p class="me-section-head__eyebrow">About Us</p>
                <h2 class="me-section-head__title">About <span class="me-section-head__title-accent"><?php echo htmlspecialchars($appName) ?></span></h2>
                <p>
                    <strong><?php echo htmlspecialchars($appName) ?></strong> is a trusted tour and travel company based in
                    <strong>Shoghi, Shimla</strong>, specialising in <strong>Spiti Valley circuits, Kinnaur–Spiti tours,
                    Chandratal Lake expeditions</strong>, and all Himachal Pradesh destinations.
                </p>
                <p>
                    We handle everything — acclimatisation-aware itineraries, <strong>inner-line permits for Spiti</strong>,
                    high-altitude accommodation, reliable 4×4 transfers, and expert local guides who know every pass,
                    monastery, and hidden village in the Spiti River valley.
                </p>
                <p>
                    From the ancient murals of <strong>Tabo Monastery</strong> to the turquoise waters of
                    <strong>Chandratal Lake</strong> at 4,250m, we craft journeys that go beyond tourism.
                    Customer safety, honest pricing, and genuine care are at the heart of everything we do.
                </p>
                <button type="button" class="me-btn me-btn--primary me-btn--lg" data-open-modal="enquiryModal" data-scroll-to-form>
                    Plan My Spiti Trip <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Footer mountain transition -->
<div aria-hidden="true" style="background:var(--me-color-bg-alt);line-height:0;">
    <svg viewBox="0 0 1440 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" style="display:block;width:100%;height:60px;">
        <path d="M0,42 L120,18 L240,40 L380,8 L500,35 L620,12 L740,38 L860,6 L980,32 L1100,10 L1220,36 L1340,14 L1440,30 L1440,60 L0,60 Z" fill="#060e18"/>
    </svg>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>