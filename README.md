=== CF7 Popup ===
Contributors: yourname
Tags: contact-form-7, popup, cf7
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin that displays custom popup modals after Contact Form 7 form submissions.

**Tested up to:** WordPress 6.5

## Features

- **Custom Popups**: Display beautiful, animated popups after CF7 form submissions
- **Per-Form Configuration**: Enable/disable and customize popups for each Contact Form 7 form individually
- **Customizable Content**:
  - Popup title
  - Success/error messages
  - Button text
- **Visual Feedback**: Animated success checkmark and error X icons
- **Smooth Animations**: Modern CSS transitions with scale and opacity effects
- **Responsive Design**: Works seamlessly on all devices
- **Easy Admin Interface**: Intuitive settings page in WordPress admin panel

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin from WordPress admin
3. Go to **CF7 Popups** menu to configure

## Configuration

1. Navigate to **CF7 Popups** in the WordPress admin menu
2. For each Contact Form 7 form:
   - Check "Enable popup for this form"
   - Add popup title
   - Customize button text
   - Enter success message
   - Enter error message
3. Click **Save Changes**

## How It Works

- Detects Contact Form 7 form submissions
- Displays custom popup with configured message (success or error)
- Hides the default CF7 response output
- Shows appropriate icon (checkmark for success, X for error)
- User can close popup by clicking button, close X, or background overlay

## Browser Support

Works on all modern browsers with CSS3 support.

## Multilingual Support

This plugin supports multiple languages using WPML and Polylang.

### Using WPML (WordPress Multilingual Plugin)

1. Install and activate [WPML](https://wpml.org/)
2. Configure your popup settings in the **CF7 Popups** admin panel
3. Go to **WPML > String Translations**
4. Search for "cf7_popup" to find all registered popup strings
5. Translate each string to your desired languages
6. Save changes

### Using Polylang

1. Install and activate [Polylang](https://polylang.pro/)
2. Configure your popup settings in the **CF7 Popups** admin panel
3. Go to **Languages > Strings translations** (if using Polylang Pro)
4. Search for "cf7_popup" to find all registered popup strings
5. Translate each string to your desired languages
6. Save changes

### How It Works

- When you save popup settings, all strings are automatically registered for translation
- The plugin detects which multilingual plugin is active (WPML or Polylang)
- On the frontend, translated strings are automatically loaded based on the current language
- WPML is checked first; if not active or translation not found, Polylang is used as fallback

## Author

Melazhari

## License

GPLv2 or later

## Version

1.0
