<?php
/**
 * Package card partial - renders one tour package.
 *
 * Required: $package array
 */
use Mystical\Bootstrap;

$appUrl = Bootstrap::config('APP_URL', '');
$img    = $package['image'] ?? '';
$title  = $package['title'] ?? 'Untitled Package';
$slug   = $package['slug'] ?? '';
$duration = $package['duration'] ?? '';
$price  = $package['price'] ?? 'On Request';
$destinations = $package['destinations'] ?? [];
$inclusions = $package['inclusions'] ?? ['Flight', 'Hotel', 'Sightseeing', 'Meals', 'Transfers'];
$short = $package['itinerary_short'] ?? [];
$more  = $package['itinerary_more'] ?? [];
?>
<article class="me-package-card" data-package-slug="<?php echo htmlspecialchars($slug) ?>">
    <a href="#get-quote" class="me-package-card__media" data-package-link="<?php echo htmlspecialchars($title) ?>" aria-label="<?php echo htmlspecialchars($title) ?>">
        <img src="<?php echo htmlspecialchars($appUrl) ?>/assets/img/himachal/<?php echo htmlspecialchars($img) ?>" alt="<?php echo htmlspecialchars($title) ?>" loading="lazy" decoding="async" width="400" height="260">
        <span class="me-package-card__badge"><?php echo htmlspecialchars($duration) ?></span>
    </a>
    <div class="me-package-card__body">
        <h3 class="me-package-card__title"><?php echo htmlspecialchars($title) ?></h3>

        <p class="me-package-card__price-row">
            <i class="fa-solid fa-indian-rupee-sign" aria-hidden="true"></i>
            <strong>Offer Price:</strong>
            <span class="me-package-card__price"><?php echo htmlspecialchars($price) ?></span>
        </p>

        <p class="me-package-card__destinations">
            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
            <strong>Destinations:</strong>
            <span><?php echo htmlspecialchars(implode(', ', $destinations)) ?></span>
        </p>

        <!-- Inclusions -->
        <ul class="me-package-card__inclusions" aria-label="Package inclusions">
            <?php foreach ($inclusions as $inc): ?>
                <li title="<?php echo htmlspecialchars($inc) ?>">
                    <?php
                    $icon = match (strtolower($inc)) {
                        'flight' => 'airplane',
                        'hotel' => 'hotel',
                        'sightseeing' => 'tourism',
                        'meals' => 'food',
                        'transfers' => 'car',
                        default => 'check',
                    };
                    ?>
                    <img src="<?php echo htmlspecialchars($appUrl) ?>/assets/img/icons/<?php echo $icon ?>.png" alt="<?php echo htmlspecialchars($inc) ?>" loading="lazy" width="28" height="28">
                    <span><?php echo htmlspecialchars($inc) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Itinerary -->
        <details class="me-package-card__itinerary">
            <summary>View Day-by-Day Itinerary</summary>
            <ul>
                <?php foreach ($short as $day): ?>
                    <li>
                        <strong>Day <?php echo (int) $day['day'] ?> — <?php echo htmlspecialchars($day['title']) ?></strong>
                        <span><?php echo htmlspecialchars($day['desc']) ?></span>
                    </li>
                <?php endforeach; ?>
                <?php foreach ($more as $day): ?>
                    <li>
                        <strong>Day <?php echo (int) $day['day'] ?> — <?php echo htmlspecialchars($day['title']) ?></strong>
                        <span><?php echo htmlspecialchars($day['desc']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </details>

        <div class="me-package-card__footer">
            <div class="me-package-card__contact">
                <a href="https://wa.me/<?php echo htmlspecialchars(Bootstrap::config('BUSINESS_WHATSAPP', '918219000937')) ?>?text=Hi%2C%20I%20want%20to%20book%20<?php echo urlencode($title) ?>" target="_blank" rel="noopener" aria-label="WhatsApp about <?php echo htmlspecialchars($title) ?>">
                    <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                </a>
                <a href="tel:<?php echo htmlspecialchars(Bootstrap::config('BUSINESS_PHONE', '+91-8219000937')) ?>" aria-label="Call about <?php echo htmlspecialchars($title) ?>">
                    <i class="fa-solid fa-phone" aria-hidden="true"></i>
                </a>
            </div>
            <button type="button" class="me-btn me-btn--primary me-btn--sm" data-open-modal="enquiryModal" data-prefill="<?php echo htmlspecialchars($title) ?>">
                Get Free Quote
            </button>
        </div>
    </div>
</article>