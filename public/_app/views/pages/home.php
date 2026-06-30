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
                <span>10,000+ Travellers Served</span>
            </div>
            <div class="me-trust-strip__item">
                <i class="fa-solid fa-bolt" aria-hidden="true"></i>
                <span>Reply in 5–15 Minutes</span>
            </div>
        </div>
    </div>
</section>

<!-- Packages -->
<section class="me-packages" id="packages" aria-label="Tour packages">
    <div class="container">
        <div class="me-section-head">
            <p class="me-section-head__eyebrow">Curated for You</p>
            <h2 class="me-section-head__title">Top <span class="me-section-head__title-accent">Himachal Tour Packages</span></h2>
            <p class="me-section-head__sub">Choose from our best-selling curated Himachal holiday packages.</p>
        </div>

        <div class="me-packages__grid">
            <?php foreach ($packages as $package): ?>
                <?php include __DIR__ . '/../partials/package-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Reviews -->
<section class="me-reviews" aria-label="Customer reviews">
    <div class="container">
        <div class="me-section-head">
            <p class="me-section-head__eyebrow">Happy Travellers</p>
            <h2 class="me-section-head__title">What <span class="me-section-head__title-accent">Travelers Say</span></h2>
            <p class="me-section-head__sub">Real experiences from our Himachal explorers</p>
        </div>

        <div class="me-reviews__grid">
            <?php foreach ($reviews as $review): ?>
                <?php include __DIR__ . '/../partials/review-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- About -->
<section class="me-about" aria-label="About <?php echo htmlspecialchars($appName) ?>">
    <div class="container">
        <div class="me-about__grid">
            <div class="me-about__media">
                <div class="me-about__media-wrap">
                    <img src="<?php echo htmlspecialchars(Bootstrap::config('APP_URL', '')) ?>/assets/img/himachal/about-1.jpg" alt="Himachal mountains" loading="lazy" decoding="async" width="600" height="400" class="me-about__img me-about__img--main">
                    <img src="<?php echo htmlspecialchars(Bootstrap::config('APP_URL', '')) ?>/assets/img/himachal/about-2.jpg" alt="Himachal scenery" loading="lazy" decoding="async" width="240" height="200" class="me-about__img me-about__img--float">
                </div>
            </div>
            <div class="me-about__content">
                <p class="me-section-head__eyebrow">About Us</p>
                <h2 class="me-section-head__title">About <span class="me-section-head__title-accent"><?php echo htmlspecialchars($appName) ?></span></h2>
                <p>
                    <strong><?php echo htmlspecialchars($appName) ?></strong> is a trusted tour and travel company based in
                    <strong>Shoghi, Shimla</strong>, offering customised tour packages to <strong>Shimla, Manali, Kullu,
                    Agra, Mathura, Amritsar (Golden Temple), Kinnaur–Spiti</strong>, and <strong>Delhi Volvo Tours</strong>.
                </p>
                <p>
                    We provide <strong>car and taxi rental services in Shimla</strong> along with <strong>honeymoon,
                    family, group, trekking, adventure, and river rafting packages</strong>. We arrange
                    <strong>deluxe hotels, cottages, and resorts</strong>, ensuring quality service, economical
                    transportation, and friendly support.
                </p>
                <p>
                    Customer satisfaction is our priority. We are committed to quality service, courteous behaviour,
                    and cost-effective travel solutions. <strong>Mystical Expedition</strong> — where quality travel
                    meets genuine care.
                </p>
                <button type="button" class="me-btn me-btn--primary me-btn--lg" data-open-modal="enquiryModal" data-scroll-to-form>
                    Get In Touch <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>