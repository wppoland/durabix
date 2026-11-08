<?php
/**
 * Uninstall routine: remove plugin settings and product meta.
 *
 * @package Durabix
 */

declare(strict_types=1);

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Remove this plugin's option and product meta from the current site.
 */
function durabix_uninstall_site(): void {
	delete_option( 'durabix_settings' );
	foreach ( array( '_durabix_durability_years', '_durabix_durability_producer', '_durabix_model_identifier', '_durabix_repair_score', '_durabix_exempt_guarantee' ) as $durabix_meta_key ) {
		delete_post_meta_by_key( $durabix_meta_key );
	}
	// GARAN label files written for order emails.
	$durabix_dir = wp_upload_dir()['basedir'] . '/durabix';
	foreach ( (array) glob( $durabix_dir . '/garan-*.svg' ) as $durabix_file ) {
		wp_delete_file( $durabix_file );
	}
	if ( is_dir( $durabix_dir ) ) {
		rmdir( $durabix_dir ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- empty plugin directory.
	}
}

// Options are per site, so a network uninstall has to visit every site.
if ( is_multisite() ) {
	$durabix_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);
	foreach ( $durabix_site_ids as $durabix_site_id ) {
		switch_to_blog( (int) $durabix_site_id );
		durabix_uninstall_site();
		restore_current_blog();
	}
	unset( $durabix_site_ids, $durabix_site_id );
} else {
	durabix_uninstall_site();
}
