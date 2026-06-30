<?php
/**
 * Public entry point - thank you page
 */
declare(strict_types=1);

// Load Composer autoloader (needed for Dotenv, PHPMailer, etc.)
require __DIR__ . '/_app/vendor/autoload.php';

require __DIR__ . '/_app/views/pages/thanks.php';