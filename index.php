<?php
require_once 'header.php';
?>
<style>
        /* =========================================
    SERVICE AREA & CONTACT SECTION
    ========================================= */

    .location-contact-section {
        background: #f8faf9;
        position: relative;
    }

    .location-contact-section .section-header {
        max-width: 720px;
        margin: 0 auto 45px;
    }

    .location-contact-section .section-header h2 {
        margin-bottom: 12px;
    }

    .location-contact-section .section-header p {
        color: #6b7280;
        line-height: 1.7;
    }


    /* =========================================
    MAP + CONTACT GRID
    ========================================= */

    .location-contact-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 30px;
        align-items: stretch;
    }


    /* =========================================
    MAP CARD
    ========================================= */

    .map-card {
        background: #ffffff;
        border-radius: 20px;
        overflow: hidden;
        min-height: 450px;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .map-card iframe {
        display: block;
        width: 100%;
        height: 100%;
        min-height: 450px;
        border: 0;
    }


    /* =========================================
    CONTACT CARD
    ========================================= */

    .contact-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.05);

        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .contact-label {
        display: inline-block;
        color: #2e7d32;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 2px;
        margin-bottom: 12px;
    }

    .contact-card h3 {
        font-size: 32px;
        margin: 0 0 15px;
        color: #1f2937;
    }

    .contact-description {
        color: #6b7280;
        line-height: 1.7;
        margin-bottom: 30px;
    }


    /* =========================================
    CONTACT INFORMATION
    ========================================= */

    .contact-info {
        display: flex;
        flex-direction: column;
        gap: 22px;
        margin-bottom: 30px;
    }

    .contact-item {
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }

    .contact-icon {
        width: 44px;
        height: 44px;
        flex-shrink: 0;

        display: flex;
        align-items: center;
        justify-content: center;

        background: #e8f5e9;
        color: #2e7d32;
        border-radius: 12px;
        font-size: 19px;
    }

    .contact-item span {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #2e7d32;
        margin-bottom: 4px;
    }

    .contact-item p {
        margin: 0;
        color: #4b5563;
        line-height: 1.5;
    }


    /* =========================================
    CONTACT BUTTON
    ========================================= */

    .contact-card .btn {
        align-self: flex-start;
    }

    /* =========================================
    STATISTICS SECTION TEXT
    ========================================= */

    .stats-title {
        color: #ffffff;
        margin-bottom: 0.75rem;
    }

    .stats-description {
        color: rgba(255, 255, 255, 0.85);
        max-width: 650px;
        margin: 0 auto;
    }

</style>
<link rel="stylesheet" href="css/style.css">
<main>
    <section id="home" class="hero-section">
        <div class="container hero-container">
            <div class="hero-content reveal-slide-right">
                <span class="badge">Eco-Friendly & Intelligent</span>
                <h1 class="hero-title">Solid Waste Collection Scheduling</h1>
                <p class="hero-subtitle">
                    Streamlining city waste pickup and recycling for healthier communities. Schedule pickups on demand, monitor real-time routes, and keep your neighborhood green.
                </p>
                <div class="hero-actions">
                    <a href="#" class="btn btn-primary btn-lg js-open-login">Get Started</a>
                </div>
            </div>
            <div class="hero-image-wrapper reveal-slide-left">
                <div class="hero-card-glass">
                    <div class="svg-hero-illustration">
                        <svg viewBox="0 0 500 400" width="100%" height="100%">
                            <defs>
                                <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#2E7D32" stop-opacity="1" />
                                    <stop offset="100%" stop-color="#1976D2" stop-opacity="1" />
                                </linearGradient>
                            </defs>
                            <circle cx="250" cy="200" r="160" fill="url(#grad1)" opacity="0.15" />
                            <rect x="120" y="140" width="260" height="180" rx="16" fill="#FFFFFF" shadow="true" />
                            <rect x="140" y="160" width="220" height="20" rx="6" fill="#E8F5E9" />
                            <rect x="140" y="195" width="160" height="14" rx="4" fill="#E0E0E0" />
                            <rect x="140" y="220" width="180" height="14" rx="4" fill="#E0E0E0" />
                            <circle cx="330" cy="265" r="30" fill="#2E7D32" />
                            <path d="M320 265 l8 8 l16 -16" stroke="#FFFFFF" stroke-width="4" fill="none" stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="about-section section-padding">
        <div class="container">
            <div class="section-header text-center reveal-fade">
                <h2>Organized Collection For Better Living</h2>
                <p>We combine technology and operational logistics to minimize environmental impact and maximize recycling rates.</p>
            </div>

            <div class="about-grid">
                <div class="about-card reveal-fade">
                    <div class="about-icon">
                        <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                    </div>
                    <h3>For Residents</h3>
                    <p>Never miss a pickup day again. Track trucks live in your neighborhood, log bulky item requests, and view recycling guidelines.</p>
                </div>

                <div class="about-card reveal-fade">
                    <div class="about-icon">
                        <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="3" width="15" height="13"/>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                            <circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                    </div>
                    <h3>For Waste Collectors</h3>
                    <p>Dynamic route optimization reduces fuel consumption and minimizes delays, improving overall team efficiency and safety.</p>
                </div>

                <div class="about-card reveal-fade">
                    <div class="about-icon">
                        <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>
                    <h3>Environmental Impact</h3>
                    <p>Structured scheduled collection prevents landfill overflow, mitigates illegal dumping, and encourages active urban sorting.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. FEATURES SECTION -->
    <section id="services" class="features-section section-padding bg-light">
        <div class="container">
            <div class="section-header text-center reveal-fade">
                <h2>Smart Tools For Smarter Cities</h2>
            </div>

            <div class="features-grid">
                <div class="feature-card reveal-fade">
                    <div class="feature-icon">📅</div>
                    <h3>Online Scheduling</h3>
                    <p>Book special bulk waste or hazardous material pickups directly from your smartphone or desktop.</p>
                </div>

                <div class="feature-card reveal-fade">
                    <div class="feature-icon">🗺️</div>
                    <h3>Collection Calendar</h3>
                    <p>Interactive calendar categorized by organic, recyclable, and general household waste types.</p>
                </div>

                <!-- UPDATED FEATURE CARD HERE -->
                <div class="feature-card reveal-fade">
                    <div class="feature-icon">📍</div>
                    <h3>Real-Time Truck Tracking</h3>
                    <p>Monitor live GPS locations of collection trucks in your zone so you know exactly when to place your bins out.</p>
                </div>

                <div class="feature-card reveal-fade">
                    <div class="feature-icon">🚚</div>
                    <h3>Route Management</h3>
                    <p>Optimized driver navigation pathways ensure timely servicing and reduced carbon emissions across municipal sectors.</p>
                </div>

                <div class="feature-card reveal-fade">
                    <div class="feature-icon">📊</div>
                    <h3>Waste Reports</h3>
                    <p>Access municipal data and personal household metrics to track monthly recycling efficiency over time.</p>
                </div>

                <div class="feature-card reveal-fade">
                    <div class="feature-icon">⚙️</div>
                    <h3>Admin Dashboard</h3>
                    <p>Centralized dispatch system for municipal managers to assign routes and handle citizen requests.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. HOW IT WORKS SECTION -->
    <section class="how-it-works section-padding">
        <div class="container">
            <div class="section-header text-center reveal-fade">
                <h2>How It Works</h2>
            </div>

            <div class="steps-wrapper">
                <div class="step-card reveal-fade">
                    <div class="step-number">1</div>
                    <div class="step-icon">👤</div>
                    <h3>Register Account</h3>
                    <p>Sign up with your residential address and contact information to assign your zone.</p>
                </div>

                <div class="step-card reveal-fade">
                    <div class="step-number">2</div>
                    <div class="step-icon">📆</div>
                    <h3>Schedule Pickup</h3>
                    <p>Select your preferred dates or view fixed recurring collection windows.</p>
                </div>

                <div class="step-card reveal-fade">
                    <div class="step-number">3</div>
                    <div class="step-icon">📍</div>
                    <h3>Track Truck Live</h3>
                    <p>Follow the collection truck’s arrival progress on an interactive live map in real time.</p>
                </div>

                <div class="step-card reveal-fade">
                    <div class="step-number">4</div>
                    <div class="step-icon">♻️</div>
                    <h3>Collection Done</h3>
                    <p>Place bins out as the truck approaches. Our automated crew handles prompt, clean collection.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. STATISTICS SECTION (INITIAL LAUNCH STATE) -->
    <section class="stats-section section-padding">
        <div class="container text-center">
            <h2 class="stats-title">Building a Cleaner Iligan City Together</h2>
            <p class="stats-description">
                Our digital waste scheduling system is officially live! As residents, barangay officials, and collection crews register across Iligan City, real-time statistics and analytics will populate here.
            </p>
        </div>
    </section>

<!-- 7. TESTIMONIALS SECTION (INITIAL LAUNCH STATE) -->
    <section class="testimonials-section section-padding">
        <div class="container">
            <div class="section-header text-center reveal-fade">
                <h2>What Our Residents Say</h2>
                <p>Community feedback and reviews will be featured here as Iligan residents use the system.</p>
            </div>

            <div class="empty-state-card reveal-fade">
                <div class="empty-state-icon">💬</div>
                <h3>Be One of Our First Reviewers</h3>
                <p>We are just getting started! Once collection services begin in your area, you'll be able to rate your service and share feedback to help us keep Iligan City clean and sustainable.</p>
            </div>
        </div>
    </section>

    <!-- 7. SERVICE AREA & CONTACT SECTION -->
<section id="contact" class="location-contact-section section-padding">
    <div class="container">

        <div class="section-header text-center reveal-fade">
            <h2>Our Service Area</h2>
            <p>
                Find us in Barangay Poblacion, Iligan City and get in touch
                with our support team for questions about waste collection services.
            </p>
        </div>

        <div class="location-contact-grid">

            <!-- MAP -->
            <div class="map-card reveal-slide-right">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d987.1840961705204!2d124.23391334638909!3d8.229242766848605!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x325575e7ac009721%3A0x5dd0875c959538f4!2sPoblacion%20Barangay%20Hall!5e0!3m2!1sen!2sph!4v1790219344394!5m2!1sen!2sph"
                    width="100%"
                    height="450"
                    
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="strict-origin-when-cross-origin">
                </iframe>
            </div>

            <!-- CONTACT -->
            <div class="contact-card reveal-slide-left">
                <span class="contact-label">GET IN TOUCH</span>

                <h3>Need Help?</h3>

                <p class="contact-description">
                    Have questions about waste collection schedules, reports,
                    or other services? Our team is here to help.
                </p>

                <div class="contact-info">

                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div>
                            <span>Location</span>
                            <p>Barangay Poblacion<br>Iligan City, Philippines</p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">📧</div>
                        <div>
                            <span>Email</span>
                            <p>support@solidwaste.local</p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div>
                            <span>Contact</span>
                            <p>Contact your Barangay Office</p>
                        </div>
                    </div>

                </div>

                <a href="#" class="btn btn-primary js-open-login">
                    Get Started
                </a>
            </div>

        </div>
    </div>
</section>


</main>

<?php
require_once 'footer.php';
?>
