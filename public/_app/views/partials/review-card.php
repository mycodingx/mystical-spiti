<?php
/**
 * Review card partial - one testimonial.
 *
 * Required: $review array
 */
use Mystical\Bootstrap;

$appUrl = Bootstrap::config('APP_URL', '');
$name    = $review['name'] ?? 'Anonymous';
$loc     = $review['location'] ?? '';
$rating  = max(1, min(5, (int) ($review['rating'] ?? 5)));
$text    = $review['text'] ?? '';

$initials = strtoupper(mb_substr($name, 0, 1));
$words    = explode(' ', $name);
if (count($words) >= 2) {
    $initials .= strtoupper(mb_substr($words[1], 0, 1));
}
?>
<article class="me-review-card">
    <div class="me-review-card__avatar" aria-hidden="true"><?php echo htmlspecialchars($initials) ?></div>
    <h4 class="me-review-card__name"><?php echo htmlspecialchars($name) ?></h4>
    <small class="me-review-card__location"><?php echo htmlspecialchars($loc) ?></small>
    <div class="me-review-card__stars" aria-label="Rating: <?php echo $rating ?> out of 5 stars">
        <?php for ($i = 0; $i < 5; $i++): ?>
            <i class="fa-solid fa-star<?php echo $i < $rating ? '' : ' me-review-card__star--off' ?>" aria-hidden="true"></i>
        <?php endfor; ?>
    </div>
    <p class="me-review-card__text"><?php echo htmlspecialchars($text) ?></p>
</article>