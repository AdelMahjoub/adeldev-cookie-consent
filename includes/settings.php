<?php
/**
 * "Réglages → Cookies" page: the values that change from one site to another.
 *
 * @package Adeldev_Cookie_Consent
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default value of each setting.
 */
function adeldev_cc_defaults(): array {
	return array(
		'actif'                => '1',
		'ga4_id'               => '',
		'google_ads'           => '0',
		'page_confidentialite' => (int) get_option( 'wp_page_for_privacy_policy' ),
		'format'               => 'barre-bas',
		'cote'                 => 'gauche',
		'decalage'             => 0,
		'decalage_mobile'      => 0,
		'bulle'                => '0',
		'bulle_image'          => 0,
		'couleur_fond'         => '',
		'couleur_texte'        => '',
		'couleur_bouton'       => '',
		'couleur_bouton_texte' => '',
	);
}

/**
 * Settings merged with their defaults.
 */
function adeldev_cc_settings(): array {
	$stored = get_option( ADELDEV_CC_OPTION, array() );

	return wp_parse_args( is_array( $stored ) ? $stored : array(), adeldev_cc_defaults() );
}

/**
 * Choices for the select fields.
 */
function adeldev_cc_choices(): array {
	return array(
		'format' => array(
			'barre-bas'  => __( 'Barre en bas de l’écran', 'adeldev-cookie-consent' ),
			'barre-haut' => __( 'Barre en haut de l’écran', 'adeldev-cookie-consent' ),
			'carte'      => __( 'Carte dans un coin', 'adeldev-cookie-consent' ),
			'fenetre'    => __( 'Fenêtre au centre', 'adeldev-cookie-consent' ),
		),
		'cote'   => array(
			'gauche' => __( 'À gauche', 'adeldev-cookie-consent' ),
			'droite' => __( 'À droite', 'adeldev-cookie-consent' ),
		),
	);
}

/**
 * Field definitions, grouped by section.
 */
function adeldev_cc_fields(): array {
	return array(
		'mesure'    => array(
			'title'  => __( 'Mesure et publicité', 'adeldev-cookie-consent' ),
			'fields' => array(
				'actif'                => array(
					'label' => __( 'Bandeau', 'adeldev-cookie-consent' ),
					'type'  => 'checkbox',
					'text'  => __( 'Afficher le bandeau et appliquer le consentement', 'adeldev-cookie-consent' ),
				),
				'ga4_id'               => array(
					'label'       => __( 'ID de mesure GA4', 'adeldev-cookie-consent' ),
					'type'        => 'ga4',
					'description' => __( 'Le même que dans la balise GA4 de GTM. Il sert à supprimer les bons cookies en cas de refus. Laisser vide si le site n’utilise pas GA4.', 'adeldev-cookie-consent' ),
				),
				'google_ads'           => array(
					'label' => __( 'Google Ads', 'adeldev-cookie-consent' ),
					'type'  => 'checkbox',
					'text'  => __( 'Le conteneur GTM contient des balises Google Ads', 'adeldev-cookie-consent' ),
				),
				'page_confidentialite' => array(
					'label' => __( 'Page de confidentialité', 'adeldev-cookie-consent' ),
					'type'  => 'page',
				),
			),
		),
		'apparence' => array(
			'title'  => __( 'Apparence', 'adeldev-cookie-consent' ),
			'fields' => array(
				'format'               => array(
					'label'       => __( 'Format du bandeau', 'adeldev-cookie-consent' ),
					'type'        => 'select',
					'description' => __( 'La carte laisse le reste de la page utilisable. La fenêtre au centre bloque la page jusqu’au choix du visiteur.', 'adeldev-cookie-consent' ),
				),
				'cote'                 => array(
					'label'       => __( 'Côté', 'adeldev-cookie-consent' ),
					'type'        => 'select',
					'description' => __( 'Pour la carte et la bulle. Choisissez le côté opposé aux boutons flottants du site (appel, WhatsApp, chat…).', 'adeldev-cookie-consent' ),
				),
				'decalage'             => array(
					'label'       => __( 'Décalage depuis le bas (ordinateur)', 'adeldev-cookie-consent' ),
					'type'        => 'pixels',
					'description' => __( 'Remonte la barre du bas, la carte et la bulle, par exemple au-dessus d’une barre fixe.', 'adeldev-cookie-consent' ),
				),
				'decalage_mobile'      => array(
					'label'       => __( 'Décalage depuis le bas (mobile et tablette)', 'adeldev-cookie-consent' ),
					'type'        => 'pixels',
					'description' => __( 'Écrans de moins de 1024 px, par exemple la hauteur d’une barre d’appel fixe en bas.', 'adeldev-cookie-consent' ),
				),
				'bulle'                => array(
					'label' => __( 'Bulle', 'adeldev-cookie-consent' ),
					'type'  => 'checkbox',
					'text'  => __( 'Afficher une bulle permanente pour modifier son choix', 'adeldev-cookie-consent' ),
				),
				'bulle_image'          => array(
					'label'       => __( 'Image de la bulle', 'adeldev-cookie-consent' ),
					'type'        => 'image',
					'description' => __( 'Image carrée, affichée en 48 × 48 px. Sans image, l’icône de cookie par défaut est utilisée.', 'adeldev-cookie-consent' ),
				),
				'couleur_fond'         => array(
					'label' => __( 'Couleur de fond', 'adeldev-cookie-consent' ),
					'type'  => 'color',
				),
				'couleur_texte'        => array(
					'label' => __( 'Couleur du texte', 'adeldev-cookie-consent' ),
					'type'  => 'color',
				),
				'couleur_bouton'       => array(
					'label' => __( 'Couleur des boutons', 'adeldev-cookie-consent' ),
					'type'  => 'color',
				),
				'couleur_bouton_texte' => array(
					'label'       => __( 'Couleur du texte des boutons', 'adeldev-cookie-consent' ),
					'type'        => 'color',
					'description' => __( 'Couleurs laissées vides : celles du thème (accent-1 pour les boutons, contrast pour le fond), sinon bleu et noir.', 'adeldev-cookie-consent' ),
				),
			),
		),
	);
}

add_action( 'admin_init', 'adeldev_cc_register_setting' );

/**
 * Register the option, its sections and fields.
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

	foreach ( adeldev_cc_fields() as $section => $group ) {
		add_settings_section( 'adeldev_cc_' . $section, $group['title'], '__return_null', 'adeldev-cookie-consent' );

		foreach ( $group['fields'] as $key => $field ) {
			add_settings_field(
				$key,
				$field['label'],
				'adeldev_cc_render_field',
				'adeldev-cookie-consent',
				'adeldev_cc_' . $section,
				array(
					'key'       => $key,
					'field'     => $field,
					'label_for' => 'adeldev-cc-' . $key,
				)
			);
		}
	}
}

/**
 * Sanitize submitted settings.
 *
 * @param mixed $input Submitted values.
 */
function adeldev_cc_sanitize( $input ): array {
	$input   = is_array( $input ) ? wp_unslash( $input ) : array();
	$current = adeldev_cc_settings();
	$choices = adeldev_cc_choices();
	$clean   = array();

	foreach ( adeldev_cc_fields() as $group ) {
		foreach ( $group['fields'] as $key => $field ) {
			$value = $input[ $key ] ?? '';

			switch ( $field['type'] ) {
				case 'checkbox':
					$clean[ $key ] = empty( $value ) ? '0' : '1';
					break;

				case 'ga4':
					$value = strtoupper( trim( (string) $value ) );
					if ( '' !== $value && ! preg_match( '/^G-[A-Z0-9]+$/', $value ) ) {
						add_settings_error( ADELDEV_CC_OPTION, $key, __( 'L’ID de mesure GA4 doit avoir la forme G-XXXXXXXXXX.', 'adeldev-cookie-consent' ) );
						$value = $current[ $key ];
					}
					$clean[ $key ] = $value;
					break;

				case 'select':
					$clean[ $key ] = isset( $choices[ $key ][ $value ] ) ? $value : adeldev_cc_defaults()[ $key ];
					break;

				case 'pixels':
					$clean[ $key ] = min( 400, absint( $value ) );
					break;

				case 'color':
					$clean[ $key ] = (string) sanitize_hex_color( (string) $value );
					break;

				case 'page':
				case 'image':
					$clean[ $key ] = absint( $value );
					break;
			}
		}
	}

	return $clean;
}

/**
 * Render one field.
 *
 * @param array $args Field arguments.
 */
function adeldev_cc_render_field( array $args ): void {
	$settings = adeldev_cc_settings();
	$key      = $args['key'];
	$field    = $args['field'];
	$id       = $args['label_for'];
	$name     = ADELDEV_CC_OPTION . '[' . $key . ']';
	$value    = $settings[ $key ];

	switch ( $field['type'] ) {
		case 'checkbox':
			printf(
				'<label><input type="checkbox" id="%s" name="%s" value="1" %s> %s</label>',
				esc_attr( $id ),
				esc_attr( $name ),
				checked( $value, '1', false ),
				esc_html( $field['text'] )
			);
			break;

		case 'ga4':
			printf(
				'<input type="text" class="regular-text code" id="%s" name="%s" value="%s" placeholder="G-XXXXXXXXXX">',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value )
			);
			break;

		case 'page':
			wp_dropdown_pages(
				array(
					'id'                => esc_attr( $id ),
					'name'              => esc_attr( $name ),
					'selected'          => (int) $value,
					'show_option_none'  => esc_html__( '— Aucune —', 'adeldev-cookie-consent' ),
					'option_none_value' => '0',
				)
			);
			break;

		case 'select':
			printf( '<select id="%s" name="%s">', esc_attr( $id ), esc_attr( $name ) );
			foreach ( adeldev_cc_choices()[ $key ] as $option => $label ) {
				printf( '<option value="%s" %s>%s</option>', esc_attr( $option ), selected( $value, $option, false ), esc_html( $label ) );
			}
			echo '</select>';
			break;

		case 'pixels':
			printf(
				'<input type="number" class="small-text" id="%s" name="%s" value="%d" min="0" max="400" step="1"> px',
				esc_attr( $id ),
				esc_attr( $name ),
				(int) $value
			);
			break;

		case 'color':
			printf(
				'<input type="text" class="adeldev-cc-color" id="%s" name="%s" value="%s">',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value )
			);
			break;

		case 'image':
			$url = $value ? wp_get_attachment_image_url( (int) $value, 'thumbnail' ) : '';
			printf(
				'<div class="adeldev-cc-image"><input type="hidden" id="%1$s" name="%2$s" value="%3$s"><img src="%4$s" alt="" width="48" height="48" style="%5$s"> <button type="button" class="button adeldev-cc-image-choose">%6$s</button> <button type="button" class="button-link adeldev-cc-image-remove" style="%7$s">%8$s</button></div>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( (string) $value ),
				esc_url( (string) $url ),
				$url ? 'vertical-align:middle;border-radius:50%;object-fit:cover' : 'display:none',
				esc_html__( 'Choisir une image', 'adeldev-cookie-consent' ),
				$url ? '' : 'display:none',
				esc_html__( 'Retirer', 'adeldev-cookie-consent' )
			);
			break;
	}

	if ( ! empty( $field['description'] ) ) {
		printf( '<p class="description">%s</p>', esc_html( $field['description'] ) );
	}
}

add_action( 'admin_menu', 'adeldev_cc_admin_menu' );

/**
 * Add the page under Settings, with its color pickers and media frame.
 */
function adeldev_cc_admin_menu(): void {
	$hook = add_options_page(
		__( 'Cookies et consentement', 'adeldev-cookie-consent' ),
		__( 'Cookies', 'adeldev-cookie-consent' ),
		'manage_options',
		'adeldev-cookie-consent',
		'adeldev_cc_render_page'
	);

	add_action(
		'admin_enqueue_scripts',
		static function ( string $current ) use ( $hook ): void {
			if ( $current !== $hook ) {
				return;
			}
			wp_enqueue_media();
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'adeldev-cc-admin', ADELDEV_CC_URL . 'assets/admin.js', array( 'jquery', 'wp-color-picker' ), ADELDEV_CC_VERSION, true );
		}
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
				esc_html__( 'Pour permettre aux visiteurs de modifier leur choix, ajoutez un lien vers %s (par exemple dans le pied de page) ou affichez la bulle.', 'adeldev-cookie-consent' ),
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
