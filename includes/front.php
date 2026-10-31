<?php
/**
 * Front end: Google Consent Mode v2 defaults to "denied" before any Google
 * tag, then tarteaucitron (self-hosted in assets/vendor) asks the visitor and
 * sends the consent updates (assets/consent.js). GTM itself is loaded
 * elsewhere; this only has to run before it, hence wp_head priority 1.
 *
 * @package Adeldev_Cookie_Consent
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the banner is output on the current request.
 */
function adeldev_cc_enabled(): bool {
	$enabled = '1' === adeldev_cc_settings()['actif'] && ! is_admin();

	/**
	 * Filters whether the consent banner and Consent Mode defaults are output.
	 *
	 * @param bool $enabled Whether consent handling is enabled.
	 */
	return (bool) apply_filters( 'adeldev_cc_enabled', $enabled );
}

/**
 * Attributes that keep LiteSpeed Cache (and Cloudflare Rocket Loader) from
 * delaying or combining a script: consent must be set before GTM runs.
 */
function adeldev_cc_script_attributes(): array {
	return array(
		'data-no-optimize' => '1',
		'data-no-defer'    => '1',
		'data-no-minify'   => '1',
		'data-cfasync'     => 'false',
	);
}

/**
 * Configuration passed to assets/consent.js.
 */
function adeldev_cc_config(): array {
	$settings = adeldev_cc_settings();
	$services = array();

	if ( '' !== $settings['ga4_id'] ) {
		$services['ga4'] = array(
			'cookies' => array( '_ga', '_ga_' . substr( $settings['ga4_id'], 2 ) ),
		);
	}
	if ( '1' === $settings['google_ads'] ) {
		$services['ads'] = array(
			'cookies' => array( '_gcl_au' ),
		);
	}

	$privacy     = $settings['page_confidentialite'] ? get_permalink( $settings['page_confidentialite'] ) : '';
	$orientation = array(
		'barre-bas'  => 'bottom',
		'barre-haut' => 'top',
		'carte'      => 'popup',
		'fenetre'    => 'middle',
	);
	$icon        = $settings['bulle_image'] ? wp_get_attachment_image_url( (int) $settings['bulle_image'], 'thumbnail' ) : '';

	return array(
		'services'     => $services,
		'tarteaucitron' => array(
			'privacyUrl'              => $privacy ? $privacy : '',
			'hashtag'                 => '#gerer-cookies',
			'cookieName'              => 'adeldev_cookie_consent',
			'orientation'             => $orientation[ $settings['format'] ] ?? 'bottom',
			'groupServices'           => false,
			'showDetailsOnClick'      => true,
			'serviceDefaultState'     => 'wait',
			'showAlertSmall'          => false,
			'cookieslist'             => true,
			'showIcon'                => '1' === $settings['bulle'],
			'iconPosition'            => 'droite' === $settings['cote'] ? 'BottomRight' : 'BottomLeft',
			'iconSrc'                 => $icon ? $icon : '',
			'adblocker'               => false,
			'DenyAllCta'              => true,
			'AcceptAllCta'            => true,
			'highPrivacy'             => true,
			'handleBrowserDNTRequest' => false,
			'removeCredit'            => true,
			'moreInfoLink'            => true,
			'useExternalCss'          => false,
			'mandatory'               => true,
			'mandatoryCta'            => false,
			// Consent Mode is driven by our services, not by the library.
			'googleConsentMode'       => false,
			'partnersList'            => false,
		),
	);
}

add_action( 'wp_head', 'adeldev_cc_head', 1 );

/**
 * Consent Mode defaults, then tarteaucitron and the site's services.
 */
function adeldev_cc_head(): void {
	if ( ! adeldev_cc_enabled() ) {
		return;
	}

	$attributes = adeldev_cc_script_attributes();

	// Defaults follow the choice already stored by tarteaucitron, so a
	// returning visitor who accepted is measured from the first hit.
	$defaults = <<<'JS'
var tarteaucitronForceLanguage = 'fr';
var tarteaucitronForceExpire = 183;
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
(function () {
	var m = document.cookie.match(/(?:^|; )adeldev_cookie_consent=([^;]*)/);
	var choix = m ? decodeURIComponent(m[1]) : '';
	var stats = choix.indexOf('!cc_ga4=true') !== -1 ? 'granted' : 'denied';
	var pub = choix.indexOf('!cc_ads=true') !== -1 ? 'granted' : 'denied';
	gtag('consent', 'default', {
		ad_storage: pub,
		ad_user_data: pub,
		ad_personalization: pub,
		analytics_storage: stats,
		wait_for_update: 500
	});
	gtag('set', 'ads_data_redaction', true);
})();
JS;
	wp_print_inline_script_tag(
		$defaults . "\nvar adeldevCookieConsent = " . wp_json_encode( adeldev_cc_config(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . ';',
		$attributes
	);

	$scripts = array(
		'assets/vendor/tarteaucitron/tarteaucitron.min.js?ver=' . ADELDEV_CC_TARTEAUCITRON,
		'assets/consent.js?ver=' . ADELDEV_CC_VERSION,
	);
	foreach ( $scripts as $script ) {
		wp_print_script_tag( array_merge( array( 'src' => ADELDEV_CC_URL . $script ), $attributes ) );
	}
}

add_action( 'wp_enqueue_scripts', 'adeldev_cc_assets' );

/**
 * Banner styles, with the colors and offsets from the settings as CSS
 * variables (the library injects its own stylesheet at runtime).
 */
function adeldev_cc_assets(): void {
	if ( ! adeldev_cc_enabled() ) {
		return;
	}

	$settings  = adeldev_cc_settings();
	$variables = array(
		'--adeldev-cc-offset'        => (int) $settings['decalage'] . 'px',
		'--adeldev-cc-offset-mobile' => (int) $settings['decalage_mobile'] . 'px',
	);
	$colors    = array(
		'couleur_fond'         => '--adeldev-cc-background',
		'couleur_texte'        => '--adeldev-cc-text',
		'couleur_bouton'       => '--adeldev-cc-accent',
		'couleur_bouton_texte' => '--adeldev-cc-accent-text',
	);
	foreach ( $colors as $key => $variable ) {
		if ( sanitize_hex_color( $settings[ $key ] ) ) {
			$variables[ $variable ] = $settings[ $key ];
		}
	}

	$css = '';
	foreach ( $variables as $variable => $value ) {
		$css .= $variable . ':' . $value . ';';
	}

	wp_enqueue_style( 'adeldev-cookie-consent', ADELDEV_CC_URL . 'assets/consent.css', array(), ADELDEV_CC_VERSION );
	wp_add_inline_style( 'adeldev-cookie-consent', ':root{' . $css . '}' );
}

add_filter( 'body_class', 'adeldev_cc_body_class' );

/**
 * Side of the card and bubble, used by assets/consent.css.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function adeldev_cc_body_class( array $classes ): array {
	if ( adeldev_cc_enabled() ) {
		$classes[] = 'adeldev-cc-' . ( 'droite' === adeldev_cc_settings()['cote'] ? 'droite' : 'gauche' );
	}
	return $classes;
}
