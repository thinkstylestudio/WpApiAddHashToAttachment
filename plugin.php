<?php defined( 'ABSPATH' ) OR exit;
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
/*
 * Register WP-API endpoint and attach it to an attachment post meta field with
 *
 * */
add_action( 'rest_api_init', function () {
	register_rest_field( 'attachment', 'media_file_md5', [
		'get_callback' => function ( $comment_arr ) {

			return get_post_meta( $comment_arr['id'], ATTACHMENT_FILE_HASH, true );
		},
		'schema'       => [
			'description' => __( 'Attachment image hash.' ),
			'type'        => 'string'
		]
	] );
} );

/**
 * @param      $post_id
 * @param null $post_after
 * @param null $post_before
 *
 * @return bool
 */
function insert_custom_default_caption( $post_id, $post_after = null, $post_before = null ) {
	if ( ! $post_id ) {
		return false;
	}
	add_hash_to_attachment_post_meta( $post_id );
}

/**
 * @param $attachment
 * @param $request
 * @param $true
 *
 * @return bool
 */
function action_rest_insert_attachment( $attachment, $request, $true ) {

	if ( ( ! $attachment ) && ( ! is_object( $attachment ) ) ) {
		return false;
	}
	add_hash_to_attachment_post_meta( $attachment->id );
	// make action magic happen here...
}

// add the action
add_action( 'rest_insert_attachment', 'action_rest_insert_attachment', 10, 3 );


add_action( 'add_attachment', 'insert_custom_default_caption' );
add_action( 'attachment_updated', 'insert_custom_default_caption' );
if ( ! function_exists( 'write_log' ) ) {
	/**
	 * @param $log
	 */
	function write_log( $log ) {
		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( print_r( $log, true ) );
		} else {
			error_log( $log );
		}
	}
}

/**
 *
 */
function rest_api_media_hash_deactivation() {

	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
	check_admin_referer( "deactivate-plugin_{$plugin}" );

	delete_post_meta_by_key( ATTACHMENT_FILE_HASH );


	# Uncomment the following line to see the function in action
	# exit( var_dump( $_GET ) );
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

	# Uncomment the following line to see the function in action
	# exit( var_dump( $_GET ) );

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
		add_hash_to_attachment_post_meta( $image->ID );
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

	# Uncomment the following line to see the function in action
	# exit( var_dump( $_GET ) );
}

/**
 * Generates a hash using the ImageHasher() library which generates the hash based
 * the images visual profile. Then the image hash is saved to the corresponding
 * attachment as post meta
 *
 * @param bool $image_id
 *
 * @return bool
 */
function add_hash_to_attachment_post_meta( $image_id = false ) {
	if ( ! $image_id ) {
		return false;
	}
	$file_path = get_attached_file( $image_id );
	if ( ! file_exists( $file_path ) ) {
		return false;
	}

	$image_hasher     = new ImageHasher();
	$image_hash       = $image_hasher->generate( $file_path );
	$md5_hash_of_file = $image_hash;
	if ( $image_id && $md5_hash_of_file ) {
		update_post_meta( $image_id, '' . ATTACHMENT_FILE_HASH . '', $md5_hash_of_file );
	}

	return true;
}


register_activation_hook( __FILE__, 'rest_api_media_hash_activation' );
register_deactivation_hook( __FILE__, 'rest_api_media_hash_deactivation' );
register_uninstall_hook( __FILE__, 'rest_api_media_hash_uninstall' );


