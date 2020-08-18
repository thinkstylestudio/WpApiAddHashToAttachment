<?php

use Jenssegers\ImageHash\ImageHash;

/**
 * Image Hash Wrapper Class
 */
class ImageHasher {
	/**
	 * @var ImageHash
	 */
	public $hasher;
	/**
	 * @var string
	 */
	public $file_path;

	/**
	 * ImageHasher constructor.
	 */
	public function __construct() {
		$this->hasher = new ImageHash();
	}

	/**
	 * Given an image location generate a hash of that image.
	 *
	 * @param $file_path
	 *
	 * @return int
	 */
	public function hash( $file_path ) {
		$this->file_path = $file_path;

		return $this->hasher->hash( $this->file_path );
	}

	/**
	 * @param bool $file_path
	 *
	 * @return int
	 */
	public static function generate( bool $file_path ): int {
		$image_hasher = new ImageHasher();

		return $image_hasher->hash( $file_path );
	}
}
