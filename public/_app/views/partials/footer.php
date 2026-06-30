<?php
/**
 * Footer partial - trust strip, footer columns, scripts
 *
 * @var array $packages Available for modal destination dropdown
 */
use Mystical\Bootstrap;
use Mystical\Csrf;

$appName  = Bootstrap::config('BUSINESS_NAME', 'Mystical Expedition');
$phone    = Bootstrap::config('BUSINESS_PHONE', '+91-8219000937');
$email    = Bootstrap::config('BUSINESS_EMAIL', 'info@mysticalexpedition.com');
$address  = Bootstrap::config('BUSINESS_ADDRESS', '');
$whatsapp = Bootstrap::config('BUSINESS_WHATSAPP', '918219000937');
$appUrl   = Bootstrap::config('APP_URL', '');

$destinations = array_map(static fn($p) => $p['title'], $packages ?? []);

// Has flash error from classic form submission?
$flashErr = $_GET['err'] ?? null;
?>

<!-- Footer -->
<footer class="me-footer" aria-label="Footer">
    <div class="container">
        <div class="me-footer__grid">
            <!-- Logo only (vertically centered) -->
            <div class="me-footer__col me-footer__col--brand">
                <a href="/" class="me-footer__logo">
                    <img src="<?php echo htmlspecialchars($appUrl) ?>/assets/img/brand/logo-white.png" alt="<?php echo htmlspecialchars($appName) ?>" loading="lazy">
                </a>
            </div>

            <!-- Our Guarantee -->
            <div class="me-footer__col">
                <h3 class="me-footer__title"><i class="fa-solid fa-shield-halved me-footer__title-icon" aria-hidden="true"></i> Our Guarantee</h3>
                <ul class="me-footer__list">
                    <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> 100% Trust</li>
                    <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> 100% Support</li>
                    <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> 100% Value for Money</li>
                    <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> 100% Online Security</li>
                </ul>
            </div>

            <!-- Approved By -->
            <div class="me-footer__col">
                <h3 class="me-footer__title"><i class="fa-solid fa-certificate me-footer__title-icon" aria-hidden="true"></i> Approved By</h3>
                <div class="me-footer__approved">
                    <img src="<?php echo htmlspecialchars($appUrl) ?>/assets/img/brand/himachal-tourism.png" alt="Himachal Tourism" loading="lazy">
                    <p>Officially recognised by Himachal Tourism for reliable and quality travel services.</p>
                </div>
            </div>

            <!-- Get in Touch -->
            <div class="me-footer__col">
                <h3 class="me-footer__title"><i class="fa-solid fa-headset me-footer__title-icon" aria-hidden="true"></i> Get in Touch</h3>
                <ul class="me-footer__list me-footer__list--contact">
                    <li><a href="tel:<?php echo htmlspecialchars($phone) ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i> <?php echo htmlspecialchars($phone) ?></a></li>
                    <li><a href="mailto:<?php echo htmlspecialchars($email) ?>"><i class="fa-solid fa-envelope" aria-hidden="true"></i> <?php echo htmlspecialchars($email) ?></a></li>
                    <li><a href="https://wa.me/<?php echo htmlspecialchars($whatsapp) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Chat on WhatsApp</a></li>
                    <li><span class="me-footer__address"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <?php echo htmlspecialchars($address) ?></span></li>
                </ul>
            </div>
        </div>

        <!-- Bottom row: copyright (left) + payments (right) -->
        <div class="me-footer__bottom">
            <div class="me-footer__copy">© <?php echo date('Y') ?> <?php echo htmlspecialchars($appName) ?> · All Rights Reserved</div>
            <div class="me-footer__payments" aria-label="Accepted payment methods">
                <img src="<?php echo htmlspecialchars($appUrl) ?>/assets/img/payments/visa.png" alt="Visa" loading="lazy">
                <img src="<?php echo htmlspecialchars($appUrl) ?>/assets/img/payments/mastercard.png" alt="MasterCard" loading="lazy">
                <img src="<?php echo htmlspecialchars($appUrl) ?>/assets/img/payments/paypal.png" alt="PayPal" loading="lazy">
                <img src="<?php echo htmlspecialchars($appUrl) ?>/assets/img/payments/american-card.png" alt="American Express" loading="lazy">
                <img src="<?php echo htmlspecialchars($appUrl) ?>/assets/img/payments/visa-1.png" alt="Visa Electron" loading="lazy">
            </div>
        </div>
    </div>
</footer>

<!-- Sticky Mobile CTA Bar (visible only on mobile) -->
<div class="me-sticky-bar" role="region" aria-label="Quick contact">
    <a href="tel:<?php echo htmlspecialchars($phone) ?>" class="me-sticky-bar__btn me-sticky-bar__btn--call">
        <i class="fa-solid fa-phone" aria-hidden="true"></i>
        <span>Call Now</span>
    </a>
    <a href="https://wa.me/<?php echo htmlspecialchars($whatsapp) ?>?text=Hello%20<?php echo urlencode($appName) ?>%2C%20I%20want%20to%20book%20a%20package." target="_blank" rel="noopener" class="me-sticky-bar__btn me-sticky-bar__btn--whatsapp">
        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
        <span>WhatsApp</span>
    </a>
    <button type="button" class="me-sticky-bar__btn me-sticky-bar__btn--enquiry" data-open-modal="enquiryModal">
        <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
        <span>Enquire</span>
    </button>
</div>

<!-- Exit-intent Modal (desktop only) -->
<div class="me-modal" id="exitIntentModal" role="dialog" aria-modal="true" aria-labelledby="exitIntentTitle" aria-hidden="true">
    <div class="me-modal__backdrop" data-close-modal></div>
    <div class="me-modal__dialog me-modal__dialog--sm">
        <button type="button" class="me-modal__close" data-close-modal aria-label="Close">&times;</button>
        <div class="me-modal__body me-modal__body--centered">
            <div class="me-exit-intent">
                <p class="me-exit-intent__eyebrow">⏳ Wait! Before you go...</p>

                <div class="me-modal__offer" id="exitIntentTitle">
                    <i class="fa-solid fa-mountain" aria-hidden="true"></i>
                    Early Bird Offer — Book Spiti Valley &amp; <strong>Save Big</strong> This Season!
                </div>

                <p class="me-exit-intent__sub">Fill in your details and our travel expert will share an exclusive quote within 15 minutes.</p>

                <form method="POST" action="submit.php" class="me-form me-form--stacked" data-ajax-form>
                    <?php echo Csrf::field() ?>
                    <input type="text" name="website" tabindex="-1" autocomplete="off" class="me-honeypot" aria-hidden="true">

                    <div class="me-form__field">
                        <input type="text" name="name" class="me-form__input" placeholder="Your Name" required minlength="2" maxlength="100" autocomplete="name">
                    </div>
                    <div class="me-form__field">
                        <input type="tel" name="phone" class="me-form__input" placeholder="10-digit mobile" required minlength="10" maxlength="10" pattern="[6-9][0-9]{9}" autocomplete="tel" inputmode="numeric">
                    </div>
                    <div class="me-form__field">
                        <input type="email" name="email" class="me-form__input" placeholder="Email" required autocomplete="email">
                    </div>
                    <div class="me-form__field me-form__field--row">
                        <input type="text" name="city" class="me-form__input" placeholder="City" required minlength="2" maxlength="60" autocomplete="address-level2">
                        <select name="destination" class="me-form__input me-form__select" required>
                            <option value="">Destination</option>
                            <?php foreach ($destinations as $d): ?>
                                <option value="<?php echo htmlspecialchars($d) ?>"><?php echo htmlspecialchars($d) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="contact_submit" class="me-btn me-btn--accent me-btn--block">
                        <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                        Claim My Early Bird Deal
                    </button>
                    <div class="me-form__message" data-form-message role="status" aria-live="polite"></div>
                </form>

                <button type="button" class="me-exit-intent__dismiss" data-close-modal>No thanks, I'll pay full price</button>
            </div>
        </div>
    </div>
</div>

<!-- Generic Enquiry Modal (pre-fills destination) -->
<div class="me-modal" id="enquiryModal" role="dialog" aria-modal="true" aria-labelledby="enquiryTitle" aria-hidden="true">
    <div class="me-modal__backdrop" data-close-modal></div>
    <div class="me-modal__dialog">
        <button type="button" class="me-modal__close" data-close-modal aria-label="Close">&times;</button>
        <div class="me-modal__body">
            <div class="me-modal__header">
                <h2 id="enquiryTitle" class="me-modal__title">Get Free Quotes</h2>
                <p class="me-modal__sub">Customised itinerary · Best price guarantee · No hidden costs</p>
            </div>

            <div class="me-modal__offer">
                <i class="fa-solid fa-mountain" aria-hidden="true"></i>
                Early Bird Offer — Book Spiti Valley &amp; <strong>Save Big</strong> This Season!
            </div>

            <form method="POST" action="submit.php" class="me-form me-form--stacked" data-ajax-form>
                <?php echo Csrf::field() ?>
                <input type="text" name="website" tabindex="-1" autocomplete="off" class="me-honeypot" aria-hidden="true">

                <div class="me-form__field me-form__field--row">
                    <input type="text" name="name" class="me-form__input" placeholder="Full Name" required minlength="2" maxlength="100" autocomplete="name">
                    <input type="text" name="city" class="me-form__input" placeholder="City" required minlength="2" maxlength="60" autocomplete="address-level2">
                </div>
                <div class="me-form__field me-form__field--row">
                    <input type="email" name="email" class="me-form__input" placeholder="Email" required autocomplete="email">
                    <input type="tel" name="phone" class="me-form__input" placeholder="10-digit mobile" required minlength="10" maxlength="10" pattern="[6-9][0-9]{9}" autocomplete="tel" inputmode="numeric">
                </div>
                <div class="me-form__field">
                    <select name="destination" class="me-form__input me-form__select" required data-prefill-destination>
                        <option value="">Choose your Destination</option>
                        <?php foreach ($destinations as $d): ?>
                            <option value="<?php echo htmlspecialchars($d) ?>"><?php echo htmlspecialchars($d) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="contact_submit" class="me-btn me-btn--accent me-btn--block me-btn--lg">
                    <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                    Plan My Trip
                </button>
                <p class="me-form__legal">🔒 We respect your privacy. We will never spam you.</p>
                <div class="me-form__message" data-form-message role="status" aria-live="polite"></div>
            </form>
        </div>
    </div>
</div>

<?php if ($flashErr): ?>
<div class="me-flash me-flash--error" role="alert" id="flash-error">
    <span><?php echo htmlspecialchars($flashErr) ?></span>
    <button type="button" onclick="document.getElementById('flash-error').remove()" aria-label="Dismiss">&times;</button>
</div>
<?php endif; ?>

<!-- Scripts -->
<script src="<?php echo htmlspecialchars($appUrl) ?>/assets/js/main.js?v=<?php echo filemtime(dirname(__DIR__, 3).'/assets/js/main.js') ?>" defer></script>
<script src="<?php echo htmlspecialchars($appUrl) ?>/assets/js/validation.js?v=<?php echo filemtime(dirname(__DIR__, 3).'/assets/js/validation.js') ?>" defer></script>

</body>
</html>