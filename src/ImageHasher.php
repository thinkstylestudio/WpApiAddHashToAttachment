<?php

use Jenssegers\ImageHash\Implementations\DifferenceHash;
use Jenssegers\ImageHash\ImageHash;

/**
 * Class ImageHasher
 */
class ImageHasher {

	/**
	 * @var ImageHash
	 */
	public $hasher;
	/**
	 * @var
	 */
	public $imageLocation;

	/**
	 * ImageHasher constructor.
	 */
	public function __construct() {

		$this->hasher = new ImageHash();

	}

	/**
	 * Given an image location generate a hash of that image.
	 *
	 * @param $imageLocation
	 *
	 * @return int
	 */
	public function generate( $imageLocation ) {

		$this->imageLocation = $imageLocation;

		return $this->hasher->hash( $this->imageLocation );

	}

}
