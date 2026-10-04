<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Smart Waste Collection Scheduling System for Iligan City residents and municipal government. Schedule pickups, view collection calendars, and track trucks in real time.">
    <meta name="theme-color" content="#2E7D32">
    <title><?= SITE_TITLE; ?> | Clean & Green Barangay Poblacion</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div id="preloader" class="preloader">
        <div class="spinner"></div>
    </div>
    <header class="site-header" id="header">
        <div class="header-container container">
            <a href="#home" class="logo">
                <div class="header-logos">
                    <img 
                        src="<?= asset('images/iligan.jpg'); ?>" alt="Iligan City Official Seal" class="city-logo"
                    />

                    <!-- LGU Iligan Logo Placeholder -->
                    <!-- Replace 'src' with your actual image path later, e.g., assets/images/lgu-iligan-logo.png -->
                    <img 
                        src="<?= asset('images/lgu_iligan.jpg'); ?>" alt="LGU Iligan Logo" class="city-logo"
                    />

                    <img 
                        src="<?= asset('images/poblogo.png'); ?>" alt="Iligan City Official Seal" class="city-logo"
                    />
                </div>

                <div class="logo-text">
                    <span class="brand-name"><?= SITE_NAME; ?></span>
                    <span class="brand-sub">Barangay Poblacion Waste System</span>
                </div>
            </a>

            <!-- Primary Navigation -->
            <nav class="main-nav" id="main-nav" aria-label="Main Navigation">
                <ul class="nav-list">
                    <li><a href="#home" class="nav-link active">Home</a></li>
                    <li><a href="#about" class="nav-link">About</a></li>
                    <li><a href="#services" class="nav-link">Services</a></li>
                    <li><a href="#contact" class="nav-link">Contact</a></li>
                </ul>
            </nav>

            <!-- Actions & Toggles -->
            <div class="header-actions">
                <div class="topbar-clock-wrap">
                    <span id="clock" class="digital-clock">00:00:00 AM</span>
                    <span class="header-date"><?= date('F d, Y'); ?></span>
                </div>
                <button id="theme-toggle" class="btn-icon" aria-label="Toggle Dark Mode">
                    <svg class="sun-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                    </svg>
                    <svg class="moon-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    </svg>
                </button>
                <a href="#" class="btn btn-primary btn-sm js-open-login" data-tab="login">Login</a>
                <button id="hamburger" class="hamburger-menu" aria-label="Toggle Menu" aria-expanded="false">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>
            </div>

        </div>
    </header>
    <script src="assets/js/landing.js"></script>
</body>
