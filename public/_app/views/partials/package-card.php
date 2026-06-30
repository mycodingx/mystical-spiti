<?php
/**
 * Package card partial - renders one tour package.
 *
 * Required: $package array
 */
use Mystical\Bootstrap;

$appUrl       = Bootstrap::config('APP_URL', '');
$img          = $package['image'] ?? '';
$title        = $package['title'] ?? 'Untitled Package';
$slug         = $package['slug'] ?? '';
$duration     = $package['duration'] ?? '';
$price        = $package['price'] ?? 'On Request';
$destinations = $package['destinations'] ?? [];
$inclusions   = $package['inclusions'] ?? ['Hotel', 'Sightseeing', 'Meals', 'Transfers'];
$short        = $package['itinerary_short'] ?? [];
$more         = $package['itinerary_more'] ?? [];
$featured     = !empty($package['featured']);
$allDays      = array_merge($short, $more);
$totalDays    = count($allDays);

// Derive tour type from slug/title
$tourType = 'Circuit';
if (stripos($title, 'loop') !== false) $tourType = 'Loop';
elseif (stripos($title, 'manali') !== false && stripos($title, 'kinnaur') !== false) $tourType = 'Full Circuit';

// Inclusion FA icons map
$inclusionIcons = [
    'flight'      => ['plane', 'Flights'],
    'hotel'       => ['hotel', 'Hotels'],
    'sightseeing' => ['binoculars', 'Sightseeing'],
    'meals'       => ['utensils', 'Meals'],
    'transfers'   => ['van-shuttle', 'Transfers'],
];
?>
<article class="me-package-card<?php echo $featured ? ' me-package-card--featured' : '' ?>" data-package-slug="<?php echo htmlspecialchars($slug) ?>">

    <?php if ($featured): ?>
        <div class="me-package-card__ribbon">⭐ Most Popular</div>
    <?php endif; ?>

    <!-- Image -->
    <a href="#get-quote" class="me-package-card__media" data-package-link="<?php echo htmlspecialchars($title) ?>" aria-label="<?php echo htmlspecialchars($title) ?>">
        <img src="<?php echo htmlspecialchars($appUrl) ?>/assets/img/himachal/<?php echo htmlspecialchars($img) ?>" alt="<?php echo htmlspecialchars($title) ?>" loading="lazy" decoding="async" width="400" height="225">
        <span class="me-package-card__badge"><?php echo htmlspecialchars($duration) ?></span>
        <h3 class="me-package-card__img-title"><?php echo htmlspecialchars($title) ?></h3>
    </a>

    <!-- Quick stats strip -->
    <div class="me-package-card__stats">
        <div class="me-package-card__stat">
            <i class="fa-regular fa-calendar-days"></i>
            <span><?php echo $totalDays ?> Days</span>
        </div>
        <div class="me-package-card__stat">
            <i class="fa-solid fa-mountain-sun"></i>
            <span>4,550m Max</span>
        </div>
        <div class="me-package-card__stat">
            <i class="fa-solid fa-route"></i>
            <span><?php echo $tourType ?></span>
        </div>
    </div>

    <div class="me-package-card__body">

        <!-- Destination pills -->
        <div class="me-package-card__dest-pills" aria-label="Destinations">
            <?php foreach (array_slice($destinations, 0, 5) as $d): ?>
                <span class="me-package-card__dest-pill">
                    <i class="fa-solid fa-location-dot"></i>
                    <?php echo htmlspecialchars($d) ?>
                </span>
            <?php endforeach; ?>
            <?php if (count($destinations) > 5): ?>
                <span class="me-package-card__dest-pill me-package-card__dest-pill--more">+<?php echo count($destinations) - 5 ?> more</span>
            <?php endif; ?>
        </div>

        <!-- Inclusions as icon pills -->
        <div class="me-package-card__inc-pills" aria-label="Inclusions">
            <?php foreach ($inclusions as $inc):
                $key  = strtolower($inc);
                $info = $inclusionIcons[$key] ?? ['check', $inc];
            ?>
                <span class="me-package-card__inc-pill" title="<?php echo htmlspecialchars($inc) ?>">
                    <i class="fa-solid fa-<?php echo $info[0] ?>"></i>
                    <span><?php echo htmlspecialchars($info[1]) ?></span>
                </span>
            <?php endforeach; ?>
        </div>

        <!-- Itinerary accordion -->
        <details class="me-package-card__itinerary">
            <summary>
                <i class="fa-solid fa-map-location-dot"></i>
                <span>Day-by-Day Itinerary</span>
                <i class="fa-solid fa-chevron-down me-package-card__itinerary-arrow"></i>
            </summary>
            <ol class="me-package-card__days">
                <?php foreach ($allDays as $day): ?>
                    <li class="me-package-card__day">
                        <span class="me-package-card__day-num">Day <?php echo (int) $day['day'] ?></span>
                        <div class="me-package-card__day-info">
                            <strong><?php echo htmlspecialchars($day['title']) ?></strong>
                            <span><?php echo htmlspecialchars($day['desc']) ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        </details>

        <!-- Footer -->
        <div class="me-package-card__footer">
            <div class="me-package-card__price-badge">
                <i class="fa-solid fa-tag"></i>
                <span>Price: <strong><?php echo htmlspecialchars($price) ?></strong></span>
            </div>
            <div class="me-package-card__actions">
                <a href="https://wa.me/<?php echo htmlspecialchars(Bootstrap::config('BUSINESS_WHATSAPP', '918219000937')) ?>?text=Hi%2C%20I%20want%20to%20book%20<?php echo urlencode($title) ?>"
                   target="_blank" rel="noopener"
                   class="me-package-card__quick-btn me-package-card__quick-btn--wa"
                   aria-label="WhatsApp">
                    <i class="fa-brands fa-whatsapp"></i>
                </a>
                <a href="tel:<?php echo htmlspecialchars(Bootstrap::config('BUSINESS_PHONE', '+91-8219000937')) ?>"
                   class="me-package-card__quick-btn me-package-card__quick-btn--phone"
                   aria-label="Call">
                    <i class="fa-solid fa-phone"></i>
                </a>
                <button type="button"
                        class="me-btn me-btn--accent me-btn--sm me-package-card__enquire-btn"
                        data-open-modal="enquiryModal"
                        data-prefill="<?php echo htmlspecialchars($title) ?>">
                    <i class="fa-solid fa-paper-plane"></i>
                    Get Quote
                </button>
            </div>
        </div>

    </div>
</article>