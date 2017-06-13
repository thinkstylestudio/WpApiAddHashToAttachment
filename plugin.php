<?php
	/**
	 * Plugin Name: WP REST API Media File Hash
	 * Description: This plugin adds an MD5 hash parameter to the media query.
	 * Author: Theron Smith - Think Style Studio
	 * Author URI: http://thinkstylestudio.com
	 * Version: 0.1
	 * License: GPL2+
	 **/

	add_action( 'rest_api_init', function () {
		register_rest_field( 'attachment', 'media_file_md5', array(
			'get_callback' => function ( $comment_arr ) {

				return get_post_meta( $comment_arr['id'], '_attachment_file_hash', true );
			},
			'schema'       => array(
				'description' => __( 'Comment media_file_md5.' ),
				'type'        => 'string'
			),
		) );
	} );

	function insert_custom_default_caption( $post_id, $post_after = null, $post_before = null ) {
		if (! $post_id) {
			return false;
		}
		add_hash_to_attachment_post_meta( $post_id );
	}
function action_rest_insert_attachment( $attachment, $request, $true ) {
	
	if ((! $attachment) && (!is_object( $attachment))) {
		return false;
	}
	add_hash_to_attachment_post_meta( $attachment->id);
    // make action magic happen here...
};

// add the action
add_action( 'rest_insert_attachment', 'action_rest_insert_attachment', 10, 3 );


	add_action( 'add_attachment', 'insert_custom_default_caption' );
	add_action( 'attachment_updated', 'insert_custom_default_caption' );
	if ( ! function_exists( 'write_log' ) ) {
		function write_log( $log ) {
			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else {
				error_log( $log );
			}
		}
	}

	function rest_api_media_hash_activation() {
		$query_images_args = array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'post_status'    => 'inherit',
			'posts_per_page' => - 1,
		);
		$query_images = new WP_Query( $query_images_args );
		$images = array();
		foreach ( $query_images->posts as $image ) {
			add_hash_to_attachment_post_meta( $image->ID );
		}
	}

	function add_hash_to_attachment_post_meta( $post_id = false ) {
		if ( ! $post_id ) {
			return false;
		}
		$file_path = get_attached_file( $post_id );
		if ( ! file_exists( $file_path ) ) {
			return false;
		}
		$md5_hash_of_file = md5_file( $file_path );
		if ( $post_id && $md5_hash_of_file ) {
			update_post_meta( $post_id, '_attachment_file_hash', $md5_hash_of_file );
		}
		return true;
	}


	register_activation_hook( __FILE__, 'rest_api_media_hash_activation' );
