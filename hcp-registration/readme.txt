=== HCP Registration ===
Contributors: developer
Tags: registration, healthcare, approval, user-management
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

Healthcare Professional registration with admin approval workflow.

== Description ==

This plugin adds a complete Healthcare Professional (HCP) registration system
to your WordPress site with the following features:

* **Registration Form** – Use the `[hcp_registration_form]` shortcode on any
  page or post to display the form.
* **Admin Approval Dashboard** – All submissions land in a "Pending" queue
  visible only to administrators under the **HCP Registrations** menu.
* **Custom User Role** – Approved users receive the "Healthcare Professional"
  role with basic read capabilities.
* **Email Notifications** – On approval the user receives an HTML email with a
  secure link to set their password. On rejection a plain-text courtesy
  notification is sent. The site admin is also notified of every new submission.

== Form Fields ==

* First Name
* Last Name
* Phone
* Email
* Practice / Clinic Name
* Healthcare Professional Type (Doctor, Nurse, Pharmacist, Dentist,
  Physiotherapist, Psychologist, Other)
* HCP Registration Number

== Installation ==

1. Upload the `hcp-registration` folder to `wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu.
3. Create a page and add the shortcode `[hcp_registration_form]`.
4. Review incoming requests from **HCP Registrations** in the admin sidebar.
