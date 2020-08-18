<?php


class HashManager {

	/**
	 * Generates a hash using the ImageHasher() library which generates the hash based
	 * the images visual profile. Then the image hash is saved to the corresponding
	 * attachment as post meta
	 *
	 * @param bool $image_id
	 *
	 * @return bool
	 */
	public static function processPostMeta( $image_id = false ) {

		if ( ! $image_id ) {
			return false;
		}

		$file_path = get_attached_file( $image_id );

		if ( ! file_exists( $file_path ) ) {
			// cleanup if file does not exist
			delete_post_meta( $image_id, ATTACHMENT_FILE_HASH );

			return false;
		}

		$md5_hash_of_file = ImageHasher::generate( $file_path );

		if ( $md5_hash_of_file ) {
			update_post_meta( $image_id, ATTACHMENT_FILE_HASH, $md5_hash_of_file );
		}

		return true;
	}
}
