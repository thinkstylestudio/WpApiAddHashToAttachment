<?php




	use Jenssegers\ImageHash\Implementations\DifferenceHash;
use Jenssegers\ImageHash\ImageHash;



	class ImageHasher {

            public $hasher;
            public $imageLocation;

		public function __construct() {


            $this->hasher = new ImageHash();


	 }

       public function generate( $imageLocation) {

	       $this->imageLocation = $imageLocation;
            $hash = $this->hasher->hash($this->imageLocation);
            return $hash;

       }


	}


