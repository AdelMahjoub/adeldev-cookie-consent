<?php
/**
 * Updates from GitHub releases. The plugin header's "Update URI" points to
 * github.com, so WordPress asks the update_plugins_github.com filter instead
 * of WordPress.org. Each release must carry an adeldev-cookie-consent.zip
 * asset (built by .github/workflows/release.yml).
 *
 * @package Adeldev_Cookie_Consent
 */

defined( 'ABSPATH' ) || exit;

/**
 * Latest release, cached for 6 hours (1 hour after a failed request).
 *
 * @return array{version: string, package: string, url: string}|null
 */
function adeldev_cc_latest_release(): ?array {
	$cached = get_site_transient( 'adeldev_cc_release' );
	if ( is_array( $cached ) ) {
		return $cached['version'] ? $cached : null;
	}

	$release  = array(
		'version' => '',
		'package' => '',
		'url'     => '',
	);
	$response = wp_remote_get(
		'https://api.github.com/repos/' . ADELDEV_CC_REPOSITORY . '/releases/latest',
		array(
			'timeout' => 10,
			'headers' => array( 'Accept' => 'application/vnd.github+json' ),
		)
	);

	if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		foreach ( (array) ( $data['assets'] ?? array() ) as $asset ) {
			if ( 'adeldev-cookie-consent.zip' === ( $asset['name'] ?? '' ) ) {
				$release = array(
					'version' => ltrim( (string) ( $data['tag_name'] ?? '' ), 'v' ),
					'package' => (string) $asset['browser_download_url'],
					'url'     => (string) ( $data['html_url'] ?? '' ),
				);
				break;
			}
		}
	}

	set_site_transient( 'adeldev_cc_release', $release, $release['version'] ? 6 * HOUR_IN_SECONDS : HOUR_IN_SECONDS );

	return $release['version'] ? $release : null;
}

add_filter( 'update_plugins_github.com', 'adeldev_cc_update', 10, 3 );

/**
 * Offer the latest release to WordPress' update check.
 *
 * @param array|false $update      Update data from earlier filters.
 * @param array       $plugin_data Plugin headers.
 * @param string      $plugin_file Plugin basename.
 * @return array|false
 */
function adeldev_cc_update( $update, array $plugin_data, string $plugin_file ) {
	if ( plugin_basename( ADELDEV_CC_FILE ) !== $plugin_file ) {
		return $update;
	}

	$release = adeldev_cc_latest_release();
	if ( ! $release ) {
		return $update;
	}

	return array(
		'id'      => $plugin_data['UpdateURI'],
		'slug'    => 'adeldev-cookie-consent',
		'version' => $release['version'],
		'url'     => $release['url'],
		'package' => $release['package'],
	);
}

add_action( 'upgrader_process_complete', 'adeldev_cc_clear_release_cache' );

/**
 * Forget the cached release once an update has run.
 */
function adeldev_cc_clear_release_cache(): void {
	delete_site_transient( 'adeldev_cc_release' );
}
