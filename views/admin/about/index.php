<?php defined('ABSPATH') || exit; ?>

<div class="wrap">
    <div class="plugin-header">
        <span class="plugin-logo"></span>
        <h1><span><?php esc_html_e('PRO', 'pro-mail-smtp'); ?> </span><?php esc_html_e(' MAIL SMTP', 'pro-mail-smtp'); ?></h1>
    </div>

    <p class="description"><?php esc_html_e('Learn more about Zetema SMTP and discover our other WordPress plugins.', 'pro-mail-smtp'); ?></p>

    <nav class="pro-mail-smtp-nav-tab-wrapper">
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-providers')); ?>" class="pro-mail-smtp-nav-tab"><?php esc_html_e('Providers', 'pro-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-logs')); ?>" class="pro-mail-smtp-nav-tab"><?php esc_html_e('Email Logs', 'pro-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-analytics')); ?>" class="pro-mail-smtp-nav-tab"><?php esc_html_e('Providers Logs', 'pro-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-email-router')); ?>" class="pro-mail-smtp-nav-tab"><?php esc_html_e('Email Router', 'pro-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-alerts')); ?>" class="pro-mail-smtp-nav-tab">Alerts</a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-settings')); ?>" class="pro-mail-smtp-nav-tab"><?php esc_html_e('Settings', 'pro-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-about')); ?>" class="pro-mail-smtp-nav-tab pro-mail-smtp-nav-tab-active"><?php esc_html_e('About', 'pro-mail-smtp'); ?></a>
    </nav>

    <?php settings_errors('pro_mail_smtp_messages'); ?>

    <!-- Small Review Request Banner -->
    <div class="review-banner">
        <span class="review-stars">★★★★★</span>
        <span class="review-text"><?php esc_html_e('Loving Zetema SMTP? Help us spread the word!', 'pro-mail-smtp'); ?></span>
        <a href="https://wordpress.org/support/plugin/turbosmtp/reviews/#new-post" target="_blank" class="review-link"><?php esc_html_e('Leave a Review', 'pro-mail-smtp'); ?></a>
        <button type="button" class="review-dismiss" onclick="this.parentElement.style.display='none'" aria-label="<?php esc_attr_e('Dismiss review request', 'pro-mail-smtp'); ?>">&times;</button>
    </div>

    <div class="tabset-content">
        <!-- About Section -->
        <div class="about-section">
            <div class="about-content">
                <div class="about-main">
                    <h2><?php esc_html_e('About Zetema SMTP', 'pro-mail-smtp'); ?></h2>
                    <p><?php esc_html_e('A powerful WordPress plugin that enhances email deliverability by connecting your site to various email service providers. Configure multiple SMTP providers with automatic failover, track email performance, and ensure reliable email delivery.', 'pro-mail-smtp'); ?></p>
                    
                    <h3><?php esc_html_e('Key Features', 'pro-mail-smtp'); ?></h3>
                    <ul class="feature-list">
                        <li><?php esc_html_e('Support for 10+ Popular Email Providers (Gmail, Outlook, SendGrid, Mailgun, etc.)', 'pro-mail-smtp'); ?></li>
                        <li><?php esc_html_e('Intelligent Email Routing with Priority System', 'pro-mail-smtp'); ?></li>
                        <li><?php esc_html_e('Comprehensive Email Logging and Analytics', 'pro-mail-smtp'); ?></li>
                        <li><?php esc_html_e('OAuth Authentication for Secure Connections', 'pro-mail-smtp'); ?></li>
                        <li><?php esc_html_e('Easy Migration from Other SMTP Plugins', 'pro-mail-smtp'); ?></li>
                        <li><?php esc_html_e('Professional Support and Regular Updates', 'pro-mail-smtp'); ?></li>
                    </ul>

                    <div class="action-buttons">
                        <a href="https://wpromailsmtp.com/" target="_blank" class="button button-primary button-hero">
                            <span class="action-icon dashicons dashicons-external"></span>
                            <?php esc_html_e('Visit Our Website', 'pro-mail-smtp'); ?>
                        </a>
                        <a href="https://wpromailsmtp.com/documentation/" target="_blank" class="button button-secondary button-hero">
                            <span class="action-icon dashicons dashicons-book-alt"></span>
                            <?php esc_html_e('Documentation', 'pro-mail-smtp'); ?>
                        </a>
                        <a href="https://wpromailsmtp.com/support/" target="_blank" class="button button-secondary button-hero">
                            <span class="action-icon dashicons dashicons-sos"></span>
                            <?php esc_html_e('Get Support', 'pro-mail-smtp'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recommended Plugins Section -->
        <div class="recommended-plugins-section">
            <h2><?php esc_html_e('Our Other WordPress Plugins', 'pro-mail-smtp'); ?></h2>
            <p><?php esc_html_e('Discover our other premium WordPress plugins available on WordPress.org.', 'pro-mail-smtp'); ?></p>
            
            <div class="wp-list-table widefat plugin-install-php">
                <div class="the-list">
                    <!-- TurboSMTP Email Validator Plugin -->
                    <div class="plugin-card plugin-card-turbosmtp-email-validator">
                        <div class="plugin-card-top">
                            <div class="name column-name">
                                <h3>
                                    <a href="https://wordpress.org/plugins/turbosmtp-email-validator/" target="_blank">
                                        <div class="plugin-icon turbosmtp-validator-icon"></div>
                                        <?php esc_html_e('TurboSMTP Email Validator', 'pro-mail-smtp'); ?>
                                    </a>
                                </h3>
                            </div>
                            <div class="action-links">
                                <ul>
                                    <li><a class="install-now button" href="https://wordpress.org/plugins/turbosmtp-email-validator/" target="_blank" aria-label="<?php esc_attr_e('View TurboSMTP Email Validator on WordPress.org', 'pro-mail-smtp'); ?>"><?php esc_html_e('View Plugin', 'pro-mail-smtp'); ?></a></li>
                                </ul>
                            </div>
                            <div class="desc column-description">
                                <p><?php esc_html_e('Validate email addresses in real-time to improve your email deliverability. Clean your email lists and reduce bounce rates with our advanced validation service.', 'pro-mail-smtp'); ?></p>
                                <p class="authors"> <cite><?php esc_html_e('By TurboSMTP', 'pro-mail-smtp'); ?></cite></p>
                            </div>
                        </div>
                        <div class="plugin-card-bottom">
                            <div class="vers column-rating">
                                <span class="num-ratings" aria-hidden="true">        <span class="review-stars">★★★★★</span> <br>(<?php esc_html_e('Email Validation Tool', 'pro-mail-smtp'); ?>)</span>
                            </div>
                            <div class="column-updated">
                                <strong><?php esc_html_e('Features:', 'pro-mail-smtp'); ?></strong> <?php esc_html_e('Real-time Validation, List Cleaning', 'pro-mail-smtp'); ?>
                            </div>
                            <div class="column-downloaded">
                                <?php esc_html_e('Improve deliverability rates', 'pro-mail-smtp'); ?>
                            </div>
                            <div class="column-compatibility">
                                <span class="compatibility-compatible"><strong><?php esc_html_e('Compatible', 'pro-mail-smtp'); ?></strong> <?php esc_html_e('with your version of WordPress', 'pro-mail-smtp'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Company Info Section -->
    </div>
</div>

<style>
/* About Page Specific Styles */
.wrap .about-section {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    padding: 40px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,.08);
    border-radius: 12px;
    border: 1px solid #e9ecef;
    position: relative;
    overflow: hidden;
}

.about-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #ff6e79, #ff9a9e, #fad0c4);
    border-radius: 12px 12px 0 0;
}

.about-content {
    max-width: 900px;
}

.about-section h2 {
    color: #2c3e50;
    font-size: 28px;
    font-weight: 600;
    margin-bottom: 20px;
    position: relative;
}

.about-section h3 {
    color: #34495e;
    font-size: 22px;
    font-weight: 500;
    margin: 30px 0 15px 0;
}

.about-section p {
    color: #5a6c7d;
    line-height: 1.7;
    font-size: 16px;
    margin-bottom: 20px;
}

.feature-list {
    list-style: none;
    padding: 0;
    margin: 25px 0;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 15px;
}

.feature-list li {
    padding: 15px 20px 15px 50px;
    position: relative;
    color: #34495e;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #ff6e79;
    font-weight: 500;
    transition: all 0.3s ease;
}

.feature-list li:hover {
    background: #ffffff;
    box-shadow: 0 2px 8px rgba(0,0,0,.1);
    transform: translateX(5px);
}

.feature-list li:before {
    content: "✓";
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: #27ae60;
    font-weight: bold;
    font-size: 18px;
}

.action-buttons {
    margin-top: 40px;
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    justify-content: flex-start;
}

.button-hero {
    height: auto !important;
    font-size: 15px !important;
    font-weight: 600 !important;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    border-radius: 6px !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 2px 8px rgba(0,0,0,.1) !important;
    border: none !important;
    position: relative;
}

.button-hero .action-icon {
    font-size: 16px !important;
    width: 16px !important;
    height: 16px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    vertical-align: middle !important;
    line-height: 1 !important;
    margin-top: -2px !important;
}

.button-hero.button-primary {
    background: linear-gradient(135deg, #ff6e79, #ff5252) !important;
    color: #ffffff !important;
}

.button-hero.button-secondary {
    background: linear-gradient(135deg, #ffffff, #f8f9fa) !important;
    color: #ff6e79 !important;
    border: 2px solid #ff6e79 !important;
}

.button-hero:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 15px rgba(0,0,0,.2) !important;
}

.button-hero.button-primary:hover {
    background: linear-gradient(135deg, #ff5252, #e57373) !important;
}

.button-hero.button-secondary:hover {
    background: #ff6e79 !important;
    color: #ffffff !important;
}

.recommended-plugins-section {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    padding: 40px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,.08);
    border-radius: 12px;
    border: 1px solid #e9ecef;
    position: relative;
    overflow: hidden;
}

.recommended-plugins-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #6c5ce7, #a29bfe, #74b9ff);
    border-radius: 12px 12px 0 0;
}

.recommended-plugins-section h2 {
    color: #2c3e50;
    font-size: 28px;
    font-weight: 600;
    margin-bottom: 15px;
    text-align: center;
}

.recommended-plugins-section > p {
    color: #5a6c7d;
    font-size: 16px;
    text-align: center;
    margin-bottom: 40px;
}

/* WordPress Plugin Card Styles */
.recommended-plugins-section .wp-list-table.widefat.plugin-install-php {
    border: none !important;
    box-shadow: none !important;
    background: transparent !important;
    width: 100% !important;
}

.recommended-plugins-section .wp-list-table .the-list {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)) !important;
    gap: 20px !important;
    margin-top: 0 !important;
    width: 100% !important;
}

.recommended-plugins-section .plugin-card {
    background: #fff !important;
    border: 1px solid #dcdcde !important;
    border-radius: 4px !important;
    margin: 0 !important;
    padding: 0 !important;
    position: relative !important;
    overflow: hidden !important;
    box-shadow: 0 1px 1px rgba(0,0,0,.04) !important;
    transition: box-shadow .3s ease-in-out !important;
    width: 100% !important;
    max-width: none !important;
    min-width: 0 !important;
}

.recommended-plugins-section .plugin-card:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,.1) !important;
}

.recommended-plugins-section .plugin-card .plugin-card-top {
    position: relative !important;
    padding: 20px 20px 0 !important;
    min-height: 135px !important;
}

.recommended-plugins-section .plugin-card .name {
    margin: 0 0 12px !important;
}

.recommended-plugins-section .plugin-card .name h3 {
    font-size: 18px !important;
    line-height: 1.3 !important;
    margin: 0 !important;
    word-wrap: break-word !important;
}

.recommended-plugins-section .plugin-card .name h3 a {
    color: #1d2327 !important;
    text-decoration: none !important;
    display: block !important;
}

.recommended-plugins-section .plugin-card .name h3 a:hover,
.recommended-plugins-section .plugin-card .name h3 a:focus {
    color: #135e96 !important;
}

.recommended-plugins-section .plugin-card .plugin-icon {
    position: relative !important;
    display: inline-block !important;
    width: 44px !important;
    height: 44px !important;
    border-radius: 4px !important;
    background: #f6f7f7 !important;
    border: 1px solid #dcdcde !important;
    margin-right: 20px !important;
    vertical-align: middle !important;
    margin-left: -21px;
    top: 1px;
}

.recommended-plugins-section .plugin-card .action-links {
    position: absolute !important;
    top: 20px !important;
    right: 20px !important;
}

.recommended-plugins-section .plugin-card .action-links ul {
    margin: 0 !important;
    list-style: none !important;
}

.recommended-plugins-section .plugin-card .action-links li {
    margin: 0 !important;
    padding: 0 !important;
}

.recommended-plugins-section .plugin-card .action-links .button {
    margin: 0 !important;
    text-decoration: none !important;
    font-size: 13px !important;
    line-height: 2.15384615 !important;
    min-height: 30px !important;
    padding: 0 12px !important;
}

.recommended-plugins-section .plugin-card .desc {
    margin: 0 120px 0 0 !important;
}

.recommended-plugins-section .plugin-card .desc p {
    margin: 0 0 12px !important;
    color: #50575e !important;
    font-size: 13px !important;
    line-height: 1.5 !important;
}

.recommended-plugins-section .plugin-card .desc .authors {
    margin: 0 !important;
}

.recommended-plugins-section .plugin-card .desc .authors cite {
    font-style: normal !important;
    font-weight: 600 !important;
    color: #646970 !important;
}

.recommended-plugins-section .plugin-card-bottom {
    border-top: 1px solid #dcdcde !important;
    padding: 12px 20px !important;
    background: #f6f7f7 !important;
    overflow: hidden !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    flex-wrap: wrap !important;
    gap: 10px !important;
}

.recommended-plugins-section .plugin-card-bottom > div {
    color: #646970 !important;
    font-size: 13px !important;
    line-height: 1.5 !important;
}

.recommended-plugins-section .plugin-card .star-rating {
    display: inline-block !important;
    vertical-align: middle !important;
    margin-right: 8px !important;
}

.recommended-plugins-section .plugin-card .star {
    display: inline-block !important;
    width: 16px !important;
    height: 16px !important;
    margin-right: 2px !important;
    background: url('data:image/svg+xml;charset=utf-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill="%23ddd" d="m10 1 3 6 6 .75-4.12 4.62L16 19l-6-3-6 3 1.13-6.63L1 7.75 7 7z"/></svg>') no-repeat center !important;
    background-size: 16px 16px !important;
}

.recommended-plugins-section .plugin-card .star.star-full {
    background-image: url('data:image/svg+xml;charset=utf-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill="%23ffb900" d="m10 1 3 6 6 .75-4.12 4.62L16 19l-6-3-6 3 1.13-6.63L1 7.75 7 7z"/></svg>') !important;
}

.recommended-plugins-section .plugin-card .num-ratings {
    color: #646970 !important;
    font-size: 12px !important;
}

.recommended-plugins-section .plugin-card .column-compatibility .compatibility-compatible {
    color: #00a32a !important;
    font-weight: 600 !important;
}

/* Plugin Icon Styles */
.recommended-plugins-section .plugin-card .turbosmtp-icon,
.recommended-plugins-section .plugin-card .turbosmtp-validator-icon {
    background-image: url('<?php echo isset($plugin_icons['turbosmtp']) && $plugin_icons['turbosmtp']['exists'] ? esc_url($plugin_icons['turbosmtp']['url']) : 'data:image/svg+xml;base64,' . base64_encode('<svg viewBox="0 0 189.73 189.7" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="g1" x1="41.7175" y1="2.0" x2="148.7186" y2="187.3" gradientUnits="userSpaceOnUse"><stop offset="0.1672" style="stop-color:#497EBD"/><stop offset="0.8374" style="stop-color:#325490"/></linearGradient></defs><path fill="url(#g1)" d="M119.6,104.55c-0.5-0.5-1.1-1.1-1.7-1.7c-6,4.6-12,9.1-17.9,13.7c-4.4,3.3-8.9,7.9-14.7,4.1c-6.8-5.1-13.5-10.3-20.3-15.4c-1-0.8-2-1.6-3.1-2.3c-0.5,0.5-1.1,1.1-1.6,1.7c-13.8,14.7-27.5,29.3-41.3,44c-2.3,2.4-4.6,4.9-6.9,7.3l124,33.2c8.6,2.3,17.4-2.8,19.7-11.4l7.2-26.9c-8.3-8.8-16.6-17.7-24.9-26.5C132,117.75,125.8,111.15,119.6,104.55z M91.6,81.65c2.1,1.6,4.1,3.1,6.2,4.7c0.7,0.5,2.3,1.3,1.7,1.8c24.4-18.6,48.8-37.1,73.3-55.7L53.6,0.55c-8.6-2.3-17.4,2.8-19.7,11.4l-5.8,21.5c14.2,10.8,28.5,21.7,42.8,32.5C77.7,71.05,84.7,76.35,91.6,81.65z M189.7,48.35c-0.6,0.5-1.2,0.9-1.8,1.4c-6.4,4.8-12.7,9.7-19,14.5c-7.8,5.9-15.6,11.9-23.4,17.8c1.3,1.4,2.6,2.7,3.8,4.1c8.1,8.6,16.2,17.2,24.2,25.8l15.7-58.5C189.6,51.75,189.8,50.05,189.7,48.35z M45.9,90.75c-9.2-7-18.4-14-27.6-21l-17.8,66.3c-0.3,1.2-0.5,2.4-0.5,3.6c0.5-0.5,1-1,1.5-1.6c9.2-9.8,18.5-19.6,27.7-29.5C34.7,102.65,40.3,96.65,45.9,90.75z"/></svg>'); ?>') !important;
    background-size: contain !important;
    background-repeat: no-repeat !important;
    background-position: center !important;
}

/* Small Review Banner Styles */
.review-banner {
    background: linear-gradient(135deg, #fff9e6 0%, #fffbf0 100%);
    border: 1px solid #ffd700;
    border-radius: 6px;
    padding: 12px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 8px rgba(255, 215, 0, 0.1);
    position: relative;
}

.review-stars {
    font-size: 16px;
    line-height: 1;
    color: #ffbc00;
}

.review-text {
    flex: 1;
    color: #5a5a5a;
    font-size: 14px;
    font-weight: 500;
}

.review-link {
    color: #0073aa;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    padding: 4px 12px;
    background: #ffffff;
    border: 1px solid #ddd;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.review-link:hover {
    background: #f0f6fc;
    border-color: #0073aa;
    text-decoration: none;
}

.review-dismiss {
    background: none;
    border: none;
    color: #999;
    font-size: 18px;
    line-height: 1;
    cursor: pointer;
    padding: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.review-dismiss:hover {
    background: rgba(0,0,0,0.1);
    color: #666;
}

.company-info-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 50px 40px;
    box-shadow: 0 4px 20px rgba(0,0,0,.15);
    border-radius: 12px;
    color: #ffffff;
    position: relative;
    overflow: hidden;
}

.company-info-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 300px;
    height: 300px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    transform: rotate(45deg);
}

.company-info-section h2 {
    color: #ffffff;
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 30px;
    text-align: center;
    position: relative;
    z-index: 2;
}

.company-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 50px;
    align-items: center;
    position: relative;
    z-index: 2;
}

.company-description p {
    color: rgba(255,255,255,0.95);
    line-height: 1.7;
    margin-bottom: 20px;
    font-size: 16px;
}

.company-stats {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.stat-item {
    text-align: center;
    padding: 25px 20px;
    background: rgba(255,255,255,0.15);
    border-radius: 12px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
    transition: all 0.3s ease;
}

.stat-item:hover {
    background: rgba(255,255,255,0.25);
    transform: translateY(-3px);
}

.stat-item strong {
    display: block;
    font-size: 36px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 8px;
}

.stat-item span {
    color: rgba(255,255,255,0.9);
    font-size: 14px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .recommended-plugins-section .wp-list-table .the-list {
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)) !important;
        gap: 15px !important;
    }
    
    .company-content {
        gap: 30px;
    }
}

@media (max-width: 768px) {
    .about-section,
    .recommended-plugins-section,
    .company-info-section {
        padding: 30px 20px;
    }
    
    .review-banner {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
        text-align: left;
    }
    
    .review-banner .review-stars {
        align-self: center;
    }
    
    .action-buttons {
        flex-direction: column;
        align-items: stretch;
    }
    
    .button-hero {
        text-align: center !important;
        justify-content: center !important;
    }
    
    .company-content {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .company-stats {
        flex-direction: row;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .stat-item {
        flex: 1;
        min-width: 140px;
    }
    
    .feature-list {
        grid-template-columns: 1fr;
    }
    
    .recommended-plugins-section .wp-list-table .the-list {
        grid-template-columns: 1fr !important;
        gap: 20px !important;
    }
    
    .recommended-plugins-section .plugin-card .plugin-card-top {
        min-height: auto !important;
        padding: 15px !important;
    }
    
    .recommended-plugins-section .plugin-card .desc {
        margin-right: 130px !important;
    }
    
    .recommended-plugins-section .plugin-card .action-links {
        right: 20px !important;
    }
    
    .recommended-plugins-section .plugin-card-bottom {
        padding: 10px 15px !important;
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 5px !important;
    }
}

@media (max-width: 480px) {
    .about-section h2,
    .recommended-plugins-section h2 {
        font-size: 24px;
    }
    
    .company-info-section h2 {
        font-size: 28px;
    }
    
    .button-hero {
        padding: 14px 18px !important;
        font-size: 14px !important;
    }
    
    .stat-item strong {
        font-size: 28px;
    }
    
    .recommended-plugins-section .plugin-card .name h3 {
        font-size: 16px !important;
    }
    
    .recommended-plugins-section .plugin-card .plugin-card-top {
        padding: 12px !important;
    }
    
    .recommended-plugins-section .plugin-card .desc {
        margin-right: 130px !important;
    }
    
    .recommended-plugins-section .plugin-card .action-links {
        right: 20px !important;
    }
    
    .recommended-plugins-section .plugin-card-bottom {
        padding: 8px 12px !important;
    }
}
</style>
