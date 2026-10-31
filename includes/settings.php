<?php
/**
 * "Réglages → Cookies" page: the values that change from one site to another.
 *
 * @package Adeldev_Cookie_Consent
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings merged with their defaults.
 *
 * @return array{actif: string, ga4_id: string, google_ads: string, page_confidentialite: int}
 */
function adeldev_cc_settings(): array {
	$stored = get_option( ADELDEV_CC_OPTION, array() );

	return wp_parse_args(
		is_array( $stored ) ? $stored : array(),
		array(
			'actif'                => '1',
			'ga4_id'               => '',
			'google_ads'           => '0',
			'page_confidentialite' => (int) get_option( 'wp_page_for_privacy_policy' ),
		)
	);
}

add_action( 'admin_init', 'adeldev_cc_register_setting' );

/**
 * Register the option and its fields.
 */
function adeldev_cc_register_setting(): void {
	register_setting(
		'adeldev_cookie_consent',
		ADELDEV_CC_OPTION,
		array(
			'type'              => 'object',
			'sanitize_callback' => 'adeldev_cc_sanitize',
			'default'           => array(),
		)
	);

	add_settings_section( 'adeldev_cc_main', '', '__return_null', 'adeldev-cookie-consent' );

	$fields = array(
		'actif'                => __( 'Bandeau', 'adeldev-cookie-consent' ),
		'ga4_id'               => __( 'ID de mesure GA4', 'adeldev-cookie-consent' ),
		'google_ads'           => __( 'Google Ads', 'adeldev-cookie-consent' ),
		'page_confidentialite' => __( 'Page de confidentialité', 'adeldev-cookie-consent' ),
	);
	foreach ( $fields as $key => $label ) {
		add_settings_field(
			$key,
			$label,
			'adeldev_cc_render_field',
			'adeldev-cookie-consent',
			'adeldev_cc_main',
			array(
				'key'       => $key,
				'label_for' => 'adeldev-cc-' . $key,
			)
		);
	}
}

/**
 * Sanitize submitted settings.
 *
 * @param mixed $input Submitted values.
 */
function adeldev_cc_sanitize( $input ): array {
	$input = is_array( $input ) ? wp_unslash( $input ) : array();
	$ga4   = strtoupper( trim( (string) ( $input['ga4_id'] ?? '' ) ) );

	if ( '' !== $ga4 && ! preg_match( '/^G-[A-Z0-9]+$/', $ga4 ) ) {
		add_settings_error( ADELDEV_CC_OPTION, 'ga4_id', __( 'L’ID de mesure GA4 doit avoir la forme G-XXXXXXXXXX.', 'adeldev-cookie-consent' ) );
		$ga4 = adeldev_cc_settings()['ga4_id'];
	}

	return array(
		'actif'                => empty( $input['actif'] ) ? '0' : '1',
		'ga4_id'               => $ga4,
		'google_ads'           => empty( $input['google_ads'] ) ? '0' : '1',
		'page_confidentialite' => absint( $input['page_confidentialite'] ?? 0 ),
	);
}

/**
 * Render one field.
 *
 * @param array $args Field arguments.
 */
function adeldev_cc_render_field( array $args ): void {
	$settings = adeldev_cc_settings();
	$key      = $args['key'];
	$id       = $args['label_for'];
	$name     = ADELDEV_CC_OPTION . '[' . $key . ']';

	switch ( $key ) {
		case 'actif':
			printf(
				'<label><input type="checkbox" id="%s" name="%s" value="1" %s> %s</label>',
				esc_attr( $id ),
				esc_attr( $name ),
				checked( $settings[ $key ], '1', false ),
				esc_html__( 'Afficher le bandeau et appliquer le consentement', 'adeldev-cookie-consent' )
			);
			break;

		case 'ga4_id':
			printf(
				'<input type="text" class="regular-text code" id="%s" name="%s" value="%s" placeholder="G-XXXXXXXXXX"><p class="description">%s</p>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $settings[ $key ] ),
				esc_html__( 'Le même que dans la balise GA4 de GTM. Il sert à supprimer les bons cookies en cas de refus. Laisser vide si le site n’utilise pas GA4.', 'adeldev-cookie-consent' )
			);
			break;

		case 'google_ads':
			printf(
				'<label><input type="checkbox" id="%s" name="%s" value="1" %s> %s</label>',
				esc_attr( $id ),
				esc_attr( $name ),
				checked( $settings[ $key ], '1', false ),
				esc_html__( 'Le conteneur GTM contient des balises Google Ads', 'adeldev-cookie-consent' )
			);
			break;

		case 'page_confidentialite':
			wp_dropdown_pages(
				array(
					'id'                => esc_attr( $id ),
					'name'              => esc_attr( $name ),
					'selected'          => (int) $settings[ $key ],
					'show_option_none'  => esc_html__( '— Aucune —', 'adeldev-cookie-consent' ),
					'option_none_value' => '0',
				)
			);
			break;
	}
}

add_action( 'admin_menu', 'adeldev_cc_admin_menu' );

/**
 * Add the page under Settings.
 */
function adeldev_cc_admin_menu(): void {
	add_options_page(
		__( 'Cookies et consentement', 'adeldev-cookie-consent' ),
		__( 'Cookies', 'adeldev-cookie-consent' ),
		'manage_options',
		'adeldev-cookie-consent',
		'adeldev_cc_render_page'
	);
}

/**
 * Render the settings page.
 */
function adeldev_cc_render_page(): void {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Cookies et consentement', 'adeldev-cookie-consent' ); ?></h1>
		<p><?php esc_html_e( 'Google Tag Manager doit être chargé après ce plugin (par exemple par un extrait WPCode dans l’en-tête) : le consentement est refusé par défaut, puis mis à jour selon le choix du visiteur.', 'adeldev-cookie-consent' ); ?></p>
		<p>
			<?php
			printf(
				/* translators: %s: link anchor. */
				esc_html__( 'Pour permettre aux visiteurs de modifier leur choix, ajoutez un lien vers %s, par exemple dans le pied de page.', 'adeldev-cookie-consent' ),
				'<code>#gerer-cookies</code>'
			);
			?>
		</p>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'adeldev_cookie_consent' );
			do_settings_sections( 'adeldev-cookie-consent' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}
