<?php
/**
 * Public entry point - landing page
 * Resolves to views/pages/home.php
 */
declare(strict_types=1);

// Load Composer autoloader (needed for Dotenv, PHPMailer, etc.)
require __DIR__ . '/_app/vendor/autoload.php';

require __DIR__ . '/_app/views/pages/home.php';