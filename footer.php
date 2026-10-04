<!-- Site Footer -->
    <footer id="contact" class="site-footer">
        <div class="container footer-container">
            <div class="footer-col footer-brand-col">
                <a href="#home" class="logo logo-footer">
                    <svg class="logo-icon" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M7 19a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H7z" />
                        <path d="M12 7v10M9 10l3-3 3 3M9 14l3 3 3-3" />
                    </svg>
                    <span class="brand-name"><?= SITE_NAME; ?></span>
                </a>
                <p class="footer-desc">
                    Providing sustainable waste management logistics and modern scheduling solutions to build cleaner, smarter communities.
                </p>
                <div class="social-links">
                    <a href="#" aria-label="Facebook" class="social-btn">FB</a>
                    <a href="#" aria-label="Twitter" class="social-btn">TW</a>
                    <a href="#" aria-label="Instagram" class="social-btn">IG</a>
                    <a href="#" aria-label="LinkedIn" class="social-btn">LN</a>
                </div>
            </div>

            <div class="footer-col">
                <h4 class="footer-title">Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#contact" class="nav-link">Contact</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4 class="footer-title">Services</h4>
                <ul class="footer-links">
                    <li><a href="#">Residential Pickup</a></li>
                    <li><a href="#">Commercial Waste</a></li>
                    <li><a href="#">Bulk Removal</a></li>
                    <li><a href="#">E-Waste Recycling</a></li>
                    <li><a href="#">Hazardous Disposal</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4 class="footer-title">Contact Us</h4>
                <address class="footer-contact-info">
                    <p>📍 Poblacion, Iligan City</p>
                    <p>📞 09358158491</p>
                    <p>✉️ juswarey@gmail.com</p>
                    <p>⏰ Mon - Fri: 8:00 AM - 5:00 PM</p>
                </address>
            </div>
        </div>

        <div class="footer-bottom text-center">
            <div class="container">
                <p>&copy; <?= date('Y'); ?> <?= SITE_NAME; ?> Iligan City Waste System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="back-to-top" aria-label="Back to top">
        ↑
    </button>

    <!-- AUTHENTICATION MODAL -->
    <div id="auth-modal" class="modal-overlay" aria-hidden="true">
        <div class="modal-container">
            <button id="modal-close" class="modal-close" aria-label="Close modal">&times;</button>
            
            <!-- Modal Header / Tabs -->
            <div class="modal-tabs">
                <button id="tab-login-btn" class="modal-tab active">Log In</button>
                <button id="tab-register-btn" class="modal-tab">Register Resident</button>
            </div>

            <div class="modal-body">
                <!-- 1. LOGIN FORM (Supports 4 User Roles) -->
                <form id="login-form" class="auth-form active" action="process_login.php" method="POST">
                    <div id="login-error-alert" class="alert alert-error" style="display:none;"></div>

                    <div class="form-group">
                        <label for="login-role">Account Type</label>
                        <select id="login-role" name="role" class="form-control" required>
                            <option value="resident">Resident</option>
                            <option value="barangay">Barangay Official</option>
                            <option value="garbage_collector">Garbage Collector / Driver</option>
                            <option value="lgu_admin">LGU Admin</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="login-email">Email Address</label>
                        <input type="email" id="login-email" name="email" class="form-control" placeholder="e.g. user@iligan.gov.ph" required>
                    </div>

                    <div class="form-group">
                        <label for="login-password">Password</label>
                        <input type="password" id="login-password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                </form>

                <!-- 2. RESIDENT REGISTRATION FORM -->
                <form id="register-form" class="auth-form" action="process_register.php" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reg-firstname">First Name</label>
                            <input type="text" id="reg-firstname" name="first_name" class="form-control" placeholder="Juan" required>
                        </div>
                        <div class="form-group">
                            <label for="reg-lastname">Last Name</label>
                            <input type="text" id="reg-lastname" name="last_name" class="form-control" placeholder="Dela Cruz" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="reg-email">Email Address</label>
                        <input type="email" id="reg-email" name="email" class="form-control" placeholder="juan@example.com" required>
                    </div>

                    <div class="form-group">
                        <label for="reg-password">Password</label>
                        <input type="password" id="reg-password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <div class="form-group">
                        <label for="reg-contact">Contact Number</label>
                        <input type="tel" id="reg-contact" name="contact_number" class="form-control" placeholder="09123456789">
                    </div>

                    <div class="form-group">
                        <label for="reg-barangay">Assigned Barangay</label>
                        <select id="reg-barangay" name="barangay_id" class="form-control" required>
                            <option value="" disabled selected>Select your Barangay...</option>
                            <!-- Dynamically populate options from `barangay` table -->
                            <option value="1">Poblacion</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="reg-address">Home Address</label>
                        <textarea id="reg-address" name="home_address" class="form-control" rows="2" placeholder="Purok / Street, House No."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Create Resident Account</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Vanilla JavaScript -->
    <script src="assets/js/admin.js"></script>

    <!-- Show login errors coming back from process_login.php -->
    <script>
    (function () {
        const modal = document.getElementById('auth-modal');
        const loginTabBtn = document.getElementById('tab-login-btn');
        const registerTabBtn = document.getElementById('tab-register-btn');
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        const closeBtn = document.getElementById('modal-close');

        function openModal(tab) {
            if (!modal) return;
            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            showTab(tab || 'login');
        }

        function closeModal() {
            if (!modal) return;
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        function showTab(tab) {
            const isLogin = tab !== 'register';
            if (loginTabBtn) loginTabBtn.classList.toggle('active', isLogin);
            if (registerTabBtn) registerTabBtn.classList.toggle('active', !isLogin);
            if (loginForm) loginForm.classList.toggle('active', isLogin);
            if (registerForm) registerForm.classList.toggle('active', !isLogin);
        }

        // Any element with class="js-open-login" opens the modal.
        // Optional data-tab="register" opens straight to the register tab.
        document.querySelectorAll('.js-open-login').forEach(function (trigger) {
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                openModal(trigger.getAttribute('data-tab') || 'login');
            });
        });

        if (loginTabBtn) loginTabBtn.addEventListener('click', function () { showTab('login'); });
        if (registerTabBtn) registerTabBtn.addEventListener('click', function () { showTab('register'); });

        if (closeBtn) closeBtn.addEventListener('click', closeModal);

        // Click on the dark overlay (outside the modal box) closes it.
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeModal();
            });
        }

        // Escape key closes it.
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
                closeModal();
            }
        });

        // Show login errors coming back from process_login.php
        const params = new URLSearchParams(window.location.search);
        const loginError = params.get('login_error');
        const loginRole = params.get('login_role');

        if (loginError) {
            const errorBox = document.getElementById('login-error-alert');
            const roleSelect = document.getElementById('login-role');

            openModal('login');

            if (errorBox) {
                errorBox.textContent = loginError;
                errorBox.style.display = 'block';
            }

            if (roleSelect && loginRole) {
                roleSelect.value = loginRole;
            }

            // Clean the query string so refreshing doesn't re-show the error.
            const cleanUrl = window.location.pathname + window.location.hash;
            window.history.replaceState({}, document.title, cleanUrl);
        }
    })();
    </script>
</body>
</html>