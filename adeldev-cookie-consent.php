<?php
/**
 * Plugin Name:       Adeldev Cookie Consent
 * Plugin URI:        https://github.com/AdelMahjoub/adeldev-cookie-consent
 * Description:       Bandeau de consentement aux cookies (tarteaucitron, hébergé localement) et Google Consent Mode v2 pour GA4 et Google Ads chargés par Google Tag Manager. Réglages : Réglages → Cookies.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            Adel Mahjoub
 * Author URI:        https://adeldev.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       adeldev-cookie-consent
 * Update URI:        https://github.com/AdelMahjoub/adeldev-cookie-consent
 *
 * @package Adeldev_Cookie_Consent
 */

defined( 'ABSPATH' ) || exit;

define( 'ADELDEV_CC_VERSION', '0.1.0' );
define( 'ADELDEV_CC_TARTEAUCITRON', '1.37.0' );
define( 'ADELDEV_CC_OPTION', 'adeldev_cookie_consent' );
define( 'ADELDEV_CC_FILE', __FILE__ );
define( 'ADELDEV_CC_DIR', plugin_dir_path( __FILE__ ) );
define( 'ADELDEV_CC_URL', plugin_dir_url( __FILE__ ) );
define( 'ADELDEV_CC_REPOSITORY', 'AdelMahjoub/adeldev-cookie-consent' );

require_once ADELDEV_CC_DIR . 'includes/settings.php';
require_once ADELDEV_CC_DIR . 'includes/front.php';
require_once ADELDEV_CC_DIR . 'includes/updater.php';
