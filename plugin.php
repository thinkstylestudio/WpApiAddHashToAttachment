<?php defined( 'ABSPATH' ) or exit;
/**
 * Plugin Name: WP REST API Media File Hash
 * Description: This plugin adds an hash parameter to the media query.
 * Author: Theron Smith - Think Style Studio
 * Author URI: http://thinkstylestudio.com
 * Version: 0.1
 * License: GPL2+
 **/

require __DIR__ . '/vendor/autoload.php';

const ATTACHMENT_FILE_HASH = '_attachment_file_hash';


/**
 * @param      $post_id
 * @param null $post_after
 * @param null $post_before
 *
 * @return bool
 */
function api_media_hash_update_md5_on_add_update_attachment( $post_id, $post_after = null, $post_before = null ) {
	if ( ! $post_id ) {
		return false;
	}

	return HashManager::processPostMeta( $post_id );
}

/**
 * @param $attachment
 * @param $request
 * @param $true
 *
 * @return bool
 */
function api_media_hash_update_md5_on_rest_insert_attachment( $attachment, $request, $true ) {

	if ( ( ! $attachment ) && ( ! is_object( $attachment ) ) ) {
		return false;
	}

	return HashManager::processPostMeta( $attachment->id );
}

function api_media_hash_rest_api_init() {
	register_rest_field( 'attachment', 'media_file_md5', [
		'get_callback' => function ( $comment_arr ) {
			return get_post_meta( $comment_arr['id'], ATTACHMENT_FILE_HASH, true );
		},
		'schema'       => [
			'description' => __( 'Will return an md5 hash/fingerprint of image files in library. Each string can be used to compare if duplicates are present. Hash value changes only when an images height to width ratio change. Scaling the image will not generate a new md5 hash' ),
			'type'        => 'string'
		]
	] );
}

/**
 *
 */
function api_media_hash_deactivation() {

	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
	check_admin_referer( "deactivate-plugin_{$plugin}" );

	delete_post_meta_by_key( ATTACHMENT_FILE_HASH );
}

/**
 * On plug-in activation apply hashes to entire media library
 */
function rest_api_media_hash_activation() {

	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
	check_admin_referer( "activate-plugin_{$plugin}" );

	$query_images_args = [
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'post_status'    => 'inherit',
		'posts_per_page' => - 1, // -1 could lead to performance problems but
		// due to the perceived max size of the media library this should
		// not pose a problem
	];

	$query_images = new WP_Query( $query_images_args );

	foreach ( $query_images->posts as $image ) {
		HashManager::processPostMeta( $image->ID );
	}
}

/**
 *
 */
function rest_api_media_hash_uninstall() {

	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	check_admin_referer( 'bulk-plugins' );

	// Important: Check if the file is the one
	// that was registered during the uninstall hook.
	if ( __FILE__ !== WP_UNINSTALL_PLUGIN ) {
		return;
	}
}

add_action( 'rest_api_init', 'api_media_hash_rest_api_init' );
add_action( 'rest_insert_attachment', 'api_media_hash_update_md5_on_rest_insert_attachment', 10, 3 );
add_action( 'add_attachment', 'api_media_hash_update_md5_on_add_update_attachment' );
add_action( 'attachment_updated', 'api_media_hash_update_md5_on_add_update_attachment' );

register_activation_hook( __FILE__, 'rest_api_media_hash_activation' );
register_deactivation_hook( __FILE__, 'api_media_hash_deactivation' );
register_uninstall_hook( __FILE__, 'rest_api_media_hash_uninstall' );


