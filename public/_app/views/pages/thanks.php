<?php
/**
 * Thanks page - shown after successful lead submission
 */
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/Bootstrap.php';

use Mystical\Bootstrap;

Bootstrap::init();

$appName = Bootstrap::config('BUSINESS_NAME', 'Mystical Expedition');
$phone   = Bootstrap::config('BUSINESS_PHONE', '+91-8219000937');
$email   = Bootstrap::config('BUSINESS_EMAIL', 'info@mysticalexpedition.com');
$appUrl  = Bootstrap::config('APP_URL', '');

$packages = [];
$packagesJson = Bootstrap::projectRoot() . '/data/packages.json';
if (file_exists($packagesJson)) {
    $packages = json_decode((string) file_get_contents($packagesJson), true) ?: [];
}

$hideHero = true;
include __DIR__ . '/../partials/header.php';
?>

<section class="me-thanks" aria-label="Thank you">
    <div class="container">
        <div class="me-thanks__card">

            <!-- Check icon -->
            <div class="me-thanks__icon" aria-hidden="true">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <h1 class="me-thanks__title">You're All Set! 🎉</h1>
            <p class="me-thanks__sub">Your enquiry has been received. Our Spiti travel expert will get in touch with you shortly with a personalised itinerary and the best rates.</p>

            <!-- Urgency strip -->
            <div class="me-thanks__urgency">
                <i class="fa-solid fa-clock" aria-hidden="true"></i>
                Expected callback within <strong>5–15 minutes</strong>
            </div>

            <!-- Next steps -->
            <div class="me-thanks__steps">
                <div class="me-thanks__step">
                    <span class="me-thanks__step-num">1</span>
                    <div>
                        <strong>Enquiry Received</strong>
                        <span>We have your details and are reviewing your request right now.</span>
                    </div>
                </div>
                <div class="me-thanks__step">
                    <span class="me-thanks__step-num">2</span>
                    <div>
                        <strong>Expert Calls You</strong>
                        <span>A dedicated travel expert will call you within 5–15 minutes.</span>
                    </div>
                </div>
                <div class="me-thanks__step">
                    <span class="me-thanks__step-num">3</span>
                    <div>
                        <strong>Custom Itinerary</strong>
                        <span>We craft a personalised package with the best rates — no hidden costs.</span>
                    </div>
                </div>
            </div>

            <!-- CTAs -->
            <div class="me-thanks__actions">
                <a href="https://wa.me/<?php echo htmlspecialchars(Bootstrap::config('BUSINESS_WHATSAPP','918219000937')) ?>?text=Hi%2C%20I%20just%20submitted%20an%20enquiry%20on%20your%20website." target="_blank" rel="noopener" class="me-btn me-btn--wa me-btn--lg">
                    <i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Chat on WhatsApp
                </a>
                <a href="tel:<?php echo htmlspecialchars($phone) ?>" class="me-btn me-btn--primary me-btn--lg">
                    <i class="fa-solid fa-phone" aria-hidden="true"></i> Call Us Now
                </a>
            </div>

            <a href="/" class="me-thanks__back">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to Home
            </a>

            <!-- Trust strip -->
            <div class="me-thanks__trust">
                <span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Govt. Approved Operator</span>
                <span><i class="fa-solid fa-star" aria-hidden="true"></i> 4.9/5 on Google</span>
                <span><i class="fa-solid fa-users" aria-hidden="true"></i> 500+ Spiti Explorers</span>
            </div>

        </div>
    </div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>