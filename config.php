<?php
/**
 * Application Configuration File
 * Contains global constants and environment settings.
 */

// Site Information
define('SITE_NAME', 'ECollect');
define('SITE_TITLE', 'Barangay Poblacion Waste Collection Scheduling System');
define('SITE_TAGLINE', 'Smart & Sustainable Waste Management Solutions');

// Helper function for asset URLs
function asset($path) {
    return 'assets/' . ltrim($path, '/');
}
?>
