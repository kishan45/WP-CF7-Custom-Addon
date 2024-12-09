=== WP CF7 Custom Addon ===

Contributors: kishankotharri
Tags: cf7, form, contactform
Requires at least: 5.6
Tested up to: 5.6
Stable Tag: 1.0.0
Requires PHP: 5.6
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

== Description ==

1. Custom Validation with Pattern Matching and Error Messaging
* Introduce advanced validation rules by allowing regular expression (regex) pattern matching for form fields.
Provide user-friendly, customizable error messages when a validation rule fails.
* Example: Validate a phone number field to match a specific format like +1 (555) 123-4567. If it doesn’t match, show an error like “Please enter a valid phone number in the required format.”
* Ensure compatibility with CF7's native validation system for seamless integration.

2. Custom Hook for Pushing Form Data to Third-Party APIs
* Implement a flexible custom action hook that developers can utilize to send form submissions to external APIs.
* Include error handling and retry mechanisms for failed API calls.
* Allow developers to customize the payload structure via filter hooks to meet specific API requirements.
* Example: After form submission, send data to a CRM system or email marketing platform like HubSpot or Mailchimp.

3. Saving Form Data in the WordPress Database
* Provide a robust mechanism for saving all form submissions to the WordPress database.
* Create a dedicated database table to store submissions, ensuring scalability and security.

4. Email Notification Toggle
* Add an option to enable or disable email notifications for specific forms directly from the admin panel.
