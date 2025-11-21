=== Zetema SMTP ===
Contributors: turbosmtp, dueclic
Tags: smtp, email, wp mail, gmail, outlook
Requires at least: 5.5
Tested up to: 6.8
Stable tag: 1.6.2
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enhance email deliverability by connecting WordPress to SMTP providers with automatic failover, proactive alerts, advanced analytics, and intelligent routing.

== Description ==

Zetema SMTP is a powerful WordPress plugin that enhances email deliverability by connecting your site to various email service providers. Configure multiple SMTP providers with automatic failover, track email performance with advanced analytics, receive proactive failure alerts, and ensure reliable email delivery with intelligent routing.

**🚨 NEW: Proactive Email Failure Alerts** - Get instant notifications via Slack, Discord, Microsoft Teams, or custom webhooks when emails fail to deliver. Never miss a critical email delivery issue again!

**📊 NEW: Advanced Analytics Dashboard** - Monitor real-time provider performance, view detailed delivery metrics, and track email engagement with comprehensive analytics for Gmail, Mailgun, SendGrid, and more.

**🎯 NEW: Enhanced Email Routing** - Create sophisticated routing rules with advanced conditional logic, regex matching, and source application detection to ensure emails are delivered through the optimal provider.

= 🚀 Features =

* **Multiple Provider Support**:
    * Standard SMTP servers
    * Gmail (with secure OAuth authentication)
    * Microsoft Outlook (with OAuth authentication)
    * Brevo (formerly Sendinblue)
    * Zimbra (SMTP)
    * TurboSMTP
    * SMTP2GO
    * Mailgun
    * SendGrid
    * Postmark
    * SparkPost
    * And more...

* **Smart Email Routing**:
    * Route emails through specific providers based on custom conditions
    * Automatic failover system using priority levels
    * Set conditions based on email type, recipient, or sending plugin
    * Advanced conditional logic with multiple operators (contains, starts with, regex, etc.)
    * Support for source application detection

* **Proactive Alert System** 🚨:
    * Real-time email failure notifications
    * Multi-channel alert support (Slack, Discord, Microsoft Teams, Custom Webhooks)
    * Smart threshold-based alerts to prevent spam
    * Consolidated failure reports when thresholds are reached
    * Individual alerts for immediate critical failures
    * Test alert functionality to verify configurations

* **Comprehensive Logging**:
    * Track email status (sent, delivered, failed)
    * View detailed error messages
    * Configurable log retention
    * Email content inspection
    * Email resend functionality from logs
    * Full email header and body inspection

* **Advanced Analytics Dashboard**:
    * Monitor provider performance metrics
    * View delivery rates and engagement statistics
    * Track email analytics per provider
    * Regular summary reports
    * Date range filtering and pagination
    * Export capabilities for detailed analysis

* **Enhanced Security & Authentication**:
    * OAuth 2.0 authentication for Gmail and Outlook
    * Secure API key management
    * SSL/TLS encryption support
    * Connection validation and testing

* **Advanced Settings**:
    * Custom From Email and From Name per routing condition
    * Fallback to WordPress mail system
    * Easy import from other SMTP plugins (WP Mail SMTP, Easy SMTP)
    * Bulk email management and cleanup tools
    * Multi-attachment support with various file types

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/pro-mail-smtp` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to **Zetema SMTP → Settings** to configure the plugin

== Configuration ==

= General Setup =

1. Navigate to **Zetema SMTP → Settings**
2. Configure your default "From Email" and "From Name"
3. Choose whether to enable email summaries and set your preferred frequency

= Adding Email Providers =

1. Go to **Zetema SMTP → Providers**
2. Click **Add Provider**
3. Select your email service provider
4. Enter your credentials:
    * For SMTP: Server, port, username, password, encryption type
    * For API-based services: API key and required settings
    * For OAuth services: Follow the authentication flow
5. Set a priority level for each provider (lower numbers = higher priority)
6. Test the connection before saving

= Email Routing (Optional) =

1. Navigate to **Zetema SMTP → Email Router**
2. Create rules to route specific emails through particular providers
3. Set conditions based on recipient email, source plugin, or other factors
4. Use advanced operators like "contains", "starts with", "regex match", etc.
5. Configure custom sender email and name for specific routing conditions

= Proactive Email Alerts Setup =

1. Navigate to **Zetema SMTP → Alerts**
2. Click **Add New Alert** to create your first alert configuration
3. Choose your notification channel:
    * **Slack**: Enter your Slack webhook URL
    * **Discord**: Enter your Discord webhook URL
    * **Microsoft Teams**: Enter your Teams webhook URL
    * **Custom Webhook**: Enter any custom webhook endpoint
4. Set your **failure threshold**:
    * `0` = Immediate alerts for every failure
    * `1+` = Consolidated alerts when threshold is reached
5. Configure alert settings and test the connection
6. Enable the alert and save your configuration

**Alert Features:**
* **Smart Thresholds**: Prevent notification spam with configurable failure thresholds
* **Consolidated Reports**: When multiple failures occur, get summarized reports instead of individual alerts
* **Rich Formatting**: Alerts include detailed failure information, provider details, and site context
* **Test Functionality**: Verify your alert configuration with test notifications

== Frequently Asked Questions ==

= Which email services does this plugin support? =

Zetema SMTP supports standard SMTP servers, Gmail (with OAuth), Microsoft Outlook (with OAuth), Brevo, TurboSMTP, SMTP2GO, Mailgun, SendGrid, Postmark, SparkPost, and many other providers.

= Can I use multiple email providers? =

Yes! You can configure multiple providers and set priorities for automatic failover. You can also create rules to route specific emails through particular providers.

= Will this plugin work with contact form plugins? =

Yes, Zetema SMTP works with all major contact form plugins including Contact Form 7, WPForms, Gravity Forms, and more.

= How do the email failure alerts work? =

The plugin can send real-time notifications when emails fail to deliver. You can configure alerts to go to Slack, Discord, Microsoft Teams, or custom webhooks. Set thresholds to receive either immediate notifications or consolidated reports when multiple failures occur.

= Can I see analytics for my email providers? =

Yes! The plugin provides detailed analytics for API-based providers like Gmail, Mailgun, SendGrid, and others. You can view delivery rates, send times, recipient information, and more.

= How does the email routing system work? =

The email routing system allows you to create rules that automatically route emails through specific providers based on conditions like recipient email, subject line, source plugin, or message content. You can use advanced operators like "contains", "regex match", and more.

= Is OAuth authentication secure? =

Yes, the plugin uses OAuth 2.0 for Gmail and Outlook authentication, which is more secure than traditional username/password authentication. Your actual login credentials are never stored in the plugin.

== Screenshots ==

1. Provider configuration screen with OAuth authentication
2. Email logs view with detailed error messages and resend functionality
3. Provider analytics dashboard with real-time metrics
4. Settings page with advanced configuration options
5. Email router configurations with conditional logic
6. Proactive alerts setup with multi-channel support
7. Email failure alerts dashboard

== Third-Party Services ==

Zetema SMTP connects to various third-party email service providers to send your WordPress site's emails. When you configure and use these services, your site will transmit data to these external services. Below is information about each service:

=== Google / Gmail ===
*   **Service Description:** This integration allows your WordPress site to send emails using your Gmail or Google Workspace account via the official Google APIs, typically using OAuth 2.0 for secure authentication.
*   **What data is sent and when:**
    *   **Authentication:** When you authorize the plugin to connect to your Google account, authentication data (such as OAuth 2.0 tokens, Client ID, and Client Secret, if applicable) is exchanged with Google's authentication servers (e.g., `https://accounts.google.com/o/oauth2/v2/auth`, `https://oauth2.googleapis.com/token`) to securely link your account. This happens during the setup process for the Gmail mailer.
    *   **Email Transmission:** When an email is sent from your WordPress site using a configured Gmail connection, the email content (including sender address, recipient(s) address(es), subject, body, headers, and any attachments) is transmitted to Google's email sending servers (e.g., `https://www.googleapis.com/gmail/v1/users/me/messages/send`) for delivery.
*   **Service Provider:** Google LLC
*   **Terms of Service:** You can find Google's Terms of Service here: https://policies.google.com/terms
*   **Privacy Policy:** Google's Privacy Policy is available here: https://policies.google.com/privacy

=== Brevo (formerly Sendinblue) ===
*   **Service Description:** This integration allows your WordPress site to send emails using the Brevo (formerly Sendinblue) email service via their API.
*   **What data is sent and when:**
    *   **Authentication:** When you configure the plugin to use Brevo, your API key is stored by the plugin. This API key is sent to Brevo's API endpoints (e.g., `https://api.brevo.com/v3/...`) for authentication with each email sent from your WordPress site.
    *   **Email Transmission:** When an email is sent from your WordPress site using a configured Brevo connection, the email content (including sender address, recipient(s) address(es), subject, body, headers, and any attachments) is transmitted to Brevo's servers for delivery.
*   **Service Provider:** Brevo
*   **Terms of Service:** You can find Brevo's Terms of Use here: https://www.brevo.com/legal/termsofuse/
*   **Privacy Policy:** Brevo's Privacy Policy is available here: https://www.brevo.com/legal/privacy-policy/

=== Microsoft / Outlook / Office 365 ===
*   **Service Description:** This integration allows your WordPress site to send emails using your Outlook.com or Microsoft 365 account via Microsoft's APIs, typically using OAuth 2.0 for secure authentication.
*   **What data is sent and when:**
    *   **Authentication:** When you authorize the plugin to connect to your Microsoft account, authentication data (such as OAuth 2.0 tokens, Client ID, and Client Secret, if applicable) is exchanged with Microsoft's authentication servers (e.g., `https://login.microsoftonline.com/common/oauth2/v2.0/authorize`, `https://login.microsoftonline.com/common/oauth2/v2.0/token`) to securely link your account. This happens during the setup process for the Outlook/Microsoft mailer.
    *   **Email Transmission:** When an email is sent from your WordPress site using a configured Microsoft connection, the email content (including sender address, recipient(s) address(es), subject, body, headers, and any attachments) is transmitted to Microsoft's email sending servers (e.g., via the Microsoft Graph API) for delivery.
*   **Service Provider:** Microsoft Corporation
*   **Terms of Service:** You can find the Microsoft Services Agreement here: https://www.microsoft.com/en-us/servicesagreement/
*   **Privacy Policy:** Microsoft's Privacy Statement is available here: https://privacy.microsoft.com/en-us/privacystatement

=== Mailgun ===
*   **Service Description:** This integration allows your WordPress site to send emails using the Mailgun email service via their API.
*   **What data is sent and when:**
    *   **Authentication:** When you configure the plugin to use Mailgun, your API key and sending domain are stored by the plugin. The API key is sent to Mailgun's API endpoints (e.g., `https://api.mailgun.net/v3/...`) for authentication with each email sent from your WordPress site.
    *   **Email Transmission:** When an email is sent from your WordPress site using a configured Mailgun connection, the email content (including sender address, recipient(s) address(es), subject, body, headers, and any attachments) is transmitted to Mailgun's servers for delivery.
*   **Service Provider:** Mailgun Technologies, Inc.
*   **Terms of Service:** You can find Mailgun's Terms of Service here: https://www.mailgun.com/terms/
*   **Privacy Policy:** Mailgun's Privacy Policy is available here: https://www.mailgun.com/privacy-policy/

=== Postmark ===
*   **Service Description:** This integration allows your WordPress site to send emails using the Postmark transactional email service via their API.
*   **What data is sent and when:**
    *   **Authentication:** When you configure the plugin to use Postmark, your Server API Token is stored by the plugin. This token is sent to Postmark's API endpoints (e.g., `https://api.postmarkapp.com/email`) for authentication with each email sent from your WordPress site.
    *   **Email Transmission:** When an email is sent from your WordPress site using a configured Postmark connection, the email content (including sender address, recipient(s) address(es), subject, body, headers, and any attachments) is transmitted to Postmark's servers for delivery.
*   **Service Provider:** Wildbit LLC (the company behind Postmark)
*   **Terms of Service:** You can find Postmark's Terms of Service here: https://postmarkapp.com/terms-of-service
*   **Privacy Policy:** Wildbit's Privacy Policy (covering Postmark) is available here: https://wildbit.com/privacy-policy

=== SendGrid ===
*   **Service Description:** This integration allows your WordPress site to send emails using the SendGrid email delivery service via their API.
*   **What data is sent and when:**
    *   **Authentication:** When you configure the plugin to use SendGrid, your API key is stored by the plugin. This API key is sent to SendGrid's API endpoints (e.g., `https://api.sendgrid.com/v3/mail/send`) for authentication with each email sent from your WordPress site.
    *   **Email Transmission:** When an email is sent from your WordPress site using a configured SendGrid connection, the email content (including sender address, recipient(s) address(es), subject, body, headers, and any attachments) is transmitted to SendGrid's servers for delivery.
*   **Service Provider:** Twilio Inc. (the company behind SendGrid)
*   **Terms of Service:** You can find SendGrid's Terms of Service here: https://sendgrid.com/policies/tos/
*   **Privacy Policy:** SendGrid's Privacy Policy is available here: https://sendgrid.com/policies/privacy/

=== SMTP2GO ===
*   **Service Description:** This integration allows your WordPress site to send emails using the SMTP2GO email delivery service via their API or SMTP.
*   **What data is sent and when (API Method):**
    *   **Authentication:** When you configure the plugin to use SMTP2GO via API, your API key is stored by the plugin. This API key is sent to SMTP2GO's API endpoints (e.g., `https://api.smtp2go.com/v3/...`) for authentication with each email sent.
    *   **Email Transmission:** When an email is sent using a configured SMTP2GO API connection, the email content (including sender address, recipient(s) address(es), subject, body, headers, and any attachments) is transmitted to SMTP2GO's servers.
*   **What data is sent and when (SMTP Method):**
    *   **Authentication:** If configured via SMTP, your SMTP credentials (hostname, port, username, password) are sent to SMTP2GO's SMTP servers for authentication with each email.
    *   **Email Transmission:** When an email is sent using a configured SMTP2GO SMTP connection, the email content is transmitted to SMTP2GO's SMTP servers.
*   **Service Provider:** SMTP2GO
*   **Terms of Service:** You can find SMTP2GO's Terms of Service here: https://www.smtp2go.com/terms-of-service/
*   **Privacy Policy:** SMTP2GO's Privacy Policy is available here: https://www.smtp2go.com/privacy-policy/

=== SparkPost ===
*   **Service Description:** This integration allows your WordPress site to send emails using the SparkPost email delivery service via their API.
*   **What data is sent and when:**
    *   **Authentication:** When you configure the plugin to use SparkPost, your API key is stored by the plugin. This API key is sent to SparkPost's API endpoints (e.g., `https://api.sparkpost.com/api/v1/...`) for authentication with each email sent from your WordPress site.
    *   **Email Transmission:** When an email is sent from your WordPress site using a configured SparkPost connection, the email content (including sender address, recipient(s) address(es), subject, body, headers, and any attachments) is transmitted to SparkPost's servers for delivery.
*   **Service Provider:** Message Systems, Inc. (SparkPost)
*   **Terms of Service:** You can find SparkPost's Terms of Use here: https://www.sparkpost.com/policies/tou/
*   **Privacy Policy:** SparkPost's Privacy Policy is available here: https://www.sparkpost.com/policies/privacy/

=== TurboSMTP ===
*   **Service Description:** This integration allows your WordPress site to send emails using the TurboSMTP transactional email service.
*   **What data is sent and when:**
    *   **Authentication:** When you configure the plugin to use TurboSMTP, your API key (and potentially username/password) is stored by the plugin and sent to TurboSMTP's API endpoint (e.g., `https://api.turbo-smtp.com/api/v2/mail/send`) for authentication with each email sent.
    *   **Email Transmission:** When an email is sent from your WordPress site using a configured TurboSMTP connection, the email content (including sender address, recipient(s) address(es), subject, body, headers, and any attachments) is transmitted to TurboSMTP's servers for delivery.
*   **Service Provider:** Delivery Media S.R.L. (the company behind TurboSMTP)
*   **Terms of Service:** You can find TurboSMTP's Terms and Conditions here: https://www.serversmtp.com/terms-and-conditions/
*   **Privacy Policy:** TurboSMTP's Privacy Policy is available here: https://www.serversmtp.com/privacy-policy/

=== Other SMTP Servers (Generic SMTP) ===
*   **Service Description:** This integration allows your WordPress site to send emails using any standard SMTP server you configure (e.g., your web host's mail server, or other third-party SMTP providers not explicitly listed above).
*   **What data is sent and when:**
    *   **Authentication:** When you configure a generic SMTP connection, your SMTP credentials (hostname, port, username, password, encryption type) are stored by the plugin. These credentials are sent to your configured SMTP server for authentication each time an email is sent from your WordPress site. We recommend using constants defined in `wp-config.php` for sensitive data like passwords where possible.
    *   **Email Transmission:** When an email is sent from your WordPress site using a configured generic SMTP connection, the email content (including sender address, recipient(s) address(es), subject, body, headers, and any attachments) is transmitted to your configured SMTP server for delivery.
*   **Service Provider:** This will be the specific SMTP provider you have chosen to configure.
*   **Terms of Service & Privacy Policy:** You are responsible for obtaining and reviewing the terms of service and privacy policy of the specific SMTP provider you configure. These documents will be provided by that third-party service.

**User Responsibility:**
It is your responsibility as the user of this plugin to choose your email sending service, configure it correctly, and to review and agree to the terms and privacy policies of any third-party email provider you decide to use. Zetema SMTP facilitates the connection to these services based on the information and credentials you provide; it does not control and is not responsible for the practices of these third-party services.
This plugin does not collect or share any data with these services beyond what is necessary to send emails as per your configuration. Your email content and recipient information are only sent to the services you explicitly configure in the plugin settings.

== Changelog ==

= 1.6.2 =
* **New Feature**: Proactive Email Failure Alerts System
  * Multi-channel alert support (Slack, Discord, Microsoft Teams, Custom Webhooks)
  * Smart threshold-based alerts to prevent notification spam
  * Consolidated failure reports with detailed analytics
  * Test alert functionality for configuration verification
* **New Feature**: Email Resend Functionality
  * Resend failed emails directly from logs with different providers
  * Full email header and body inspection
  * Improved error message handling
* **Enhancement**: Enhanced security with better API key management
* **Fix**: Various bug fixes and performance improvements

= 1.1.1 =
* Fix Default PHPMailer issue
* Fix OtherSMTP Provider issue
* New About page

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release of Zetema SMTP
