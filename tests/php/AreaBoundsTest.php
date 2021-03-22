<?php

use function FaceRekogCrop\Utilities\center_box_at_destination;
use function FaceRekogCrop\Utilities\get_raw_bounding_area;
use function FaceRekogCrop\Utilities\get_translated_bounding_area;

require_once dirname( __FILE__ ) . '/../../utilities/crop.php';

/**
 * AreaBoundsTest
 */
class AreaBoundsTest extends PHPUnit\Framework\TestCase {

	/**
	 * @dataProvider facesRawAreaProvider
	 */
	public function test_get_raw_bounding_area( $faces, $max_faces, $expected ) {
		$area = get_raw_bounding_area( $faces, $max_faces );
		$this->assertEquals( $expected, $area );
	}

	/**
	 * @dataProvider rawBoundingAreasProvider
	 */
	public function test_get_translated_bounding_area( $input, $expected ) {
		$width  = 1000;
		$height = 1000;
		$area   = get_translated_bounding_area( $width, $height, $input );
		$this->assertEquals( $expected, $area );
	}

	/**
	 * @dataProvider centerBoundingBoxProvider
	 */
	public function test_center_box_at_destination( $width, $height, $box, $fit, $zoom, $expected ) {
		$position = center_box_at_destination( $width, $height, $box, $fit, $zoom );
		$this->assertEquals( $expected, $position );
	}

	public function centerBoundingBoxProvider(): array {
		return array(
			array(
				'width'    => 500,
				'height'   => 500,
				'area'     => array(
					'top'    => 200,
					'left'   => 200,
					'right'  => 400,
					'bottom' => 400,
				),
				'fit'      => true,
				'zoom'     => 1.0,
				'Expected' => array(
					'x'     => 150,
					'y'     => 150,
					'scale' => 1.0,
				),
			),
			array(
				'width'    => 500,
				'height'   => 500,
				'area'     => array(
					'top'    => 200,
					'left'   => 200,
					'right'  => 700,
					'bottom' => 400,
				),
				'fit'      => true,
				'zoom'     => 1.0,
				'Expected' => array(
					'x'     => 0,
					'y'     => 150,
					'scale' => 1.0,
				),
			),
			array(
				'width'    => 500,
				'height'   => 500,
				'area'     => array(
					'top'    => 200,
					'left'   => 200,
					'right'  => 900,
					'bottom' => 400,
				),
				'fit'      => true,
				'zoom'     => 1.0,
				'Expected' => array(
					'x'     => 0,
					'y'     => 179, // ( 500 - 143 ) / 2.
					'scale' => 0.71428,
				),
			),
			array(
				'width'    => 500,
				'height'   => 500,
				'area'     => array(
					'top'    => 200,
					'left'   => 200,
					'right'  => 400,
					'bottom' => 900,
				),
				'fit'      => true,
				'zoom'     => 1.0,
				'Expected' => array(
					'x'     => 179,
					'y'     => 0,
					'scale' => 0.71428,
				),
			),
			array(
				'width'    => 500,
				'height'   => 500,
				'area'     => array(
					'top'    => 200,
					'left'   => 200,
					'right'  => 900,
					'bottom' => 900,
				),
				'fit'      => true,
				'zoom'     => 1.0,
				'Expected' => array(
					'x'     => 0,
					'y'     => 0,
					'scale' => 0.71428,
				),
			),
			array(
				'width'    => 550,
				'height'   => 550,
				'area'     => array(
					'top'    => 200,
					'left'   => 200,
					'right'  => 900,
					'bottom' => 900,
				),
				'fit'      => true,
				'zoom'     => 1.0,
				'Expected' => array(
					'x'     => 0,
					'y'     => 0,
					'scale' => 0.78571,
				),
			),
			array(
				'width'    => 500,
				'height'   => 550,
				'area'     => array(
					'top'    => 200,
					'left'   => 200,
					'right'  => 900,
					'bottom' => 900,
				),
				'fit'      => true,
				'zoom'     => 1.0,
				'Expected' => array(
					'x'     => 0,
					'y'     => 25,
					'scale' => 0.71428,
				),
			),
			array(
				'width'    => 500,
				'height'   => 500,
				'area'     => array(
					'top'    => 200,
					'left'   => 200,
					'right'  => 900,
					'bottom' => 400,
				),
				'fit'      => true,
				'zoom'     => 0.5,
				'Expected' => array(
					'x'     => 75,
					'y'     => 200,
					'scale' => 1.0,
				),
			),
			array(
				'width'    => 500,
				'height'   => 500,
				'area'     => array(
					'top'    => 200,
					'left'   => 200,
					'right'  => 900,
					'bottom' => 900,
				),
				'fit'      => false,
				'zoom'     => 1.0,
				'Expected' => array(
					'x'     => -100,
					'y'     => -100,
					'scale' => 1.0,
				),
			),
		);
	}

	public function rawBoundingAreasProvider(): array {
		return array(
			array(
				'BoundingArea' => array(
					'top'    => 0.0,
					'left'   => 0.0,
					'right'  => 0.1,
					'bottom' => 0.1,
				),
				'Expected'     => array(
					'top'    => 0,
					'left'   => 0,
					'right'  => 100,
					'bottom' => 100,
				),
			),
			array(
				'BoundingArea' => array(
					'top'    => 0.1,
					'left'   => 0.1,
					'right'  => 0.3,
					'bottom' => 0.3,
				),
				'Expected'     => array(
					'top'    => 100,
					'left'   => 100,
					'right'  => 300,
					'bottom' => 300,
				),
			),
		);
	}

	public function facesRawAreaProvider(): array {
		return array(
			// Face top left edge.
			array(
				'FaceDetails' => array(
					array(
						'BoundingBox' => array(
							'Top'    => 0.0,
							'Left'   => 0.0,
							'Width'  => 0.1,
							'Height' => 0.1,
						),
					),
				),
				'max_faces'   => 100,
				'Expected'    => array(
					'top'    => 0.0,
					'left'   => 0.0,
					'right'  => 0.1,
					'bottom' => 0.1,
				),
			),
			// Face at (100,100) and (300,300).
			array(
				'FaceDetails' => array(
					array(
						'BoundingBox' => array(
							'Top'    => 0.1,
							'Left'   => 0.1,
							'Width'  => 0.2,
							'Height' => 0.2,
						),
					),
				),
				'max_faces'   => 100,
				'Expected'    => array(
					'top'    => 0.1,
					'left'   => 0.1,
					'right'  => 0.3,
					'bottom' => 0.3,
				),
			),
			// Faces at (100,100;300,300) and (600,600;900,900).
			array(
				'FaceDetails' => array(
					array(
						'BoundingBox' => array(
							'Top'    => 0.1,
							'Left'   => 0.1,
							'Width'  => 0.2,
							'Height' => 0.2,
						),
					),
					array(
						'BoundingBox' => array(
							'Top'    => 0.6,
							'Left'   => 0.6,
							'Width'  => 0.3,
							'Height' => 0.3,
						),
					),
				),
				'max_faces'   => 100,
				'Expected'    => array(
					'top'    => 0.1,
					'left'   => 0.1,
					'right'  => 0.9,
					'bottom' => 0.9,
				),
			),
			// Faces at (0,0;300,300) and (600,600;900,900).
			array(
				'FaceDetails' => array(
					array(
						'BoundingBox' => array(
							'Top'    => 0.0,
							'Left'   => 0.0,
							'Width'  => 0.3,
							'Height' => 0.3,
						),
					),
					array(
						'BoundingBox' => array(
							'Top'    => 0.6,
							'Left'   => 0.6,
							'Width'  => 0.3,
							'Height' => 0.3,
						),
					),
				),
				'max_faces'   => 100,
				'Expected'    => array(
					'top'    => 0.0,
					'left'   => 0.0,
					'right'  => 0.9,
					'bottom' => 0.9,
				),
			),
			// Faces at (100,100;300,300) and (600,600;1000,1000).
			array(
				'FaceDetails' => array(
					array(
						'BoundingBox' => array(
							'Top'    => 0.1,
							'Left'   => 0.1,
							'Width'  => 0.2,
							'Height' => 0.2,
						),
					),
					array(
						'BoundingBox' => array(
							'Top'    => 0.6,
							'Left'   => 0.6,
							'Width'  => 0.4,
							'Height' => 0.4,
						),
					),
				),
				'max_faces'   => 100,
				'Expected'    => array(
					'top'    => 0.1,
					'left'   => 0.1,
					'right'  => 1.0,
					'bottom' => 1.0,
				),
			),
			// Faces at (0,0;300,300) and (600,600;1000,1000).
			array(
				'FaceDetails' => array(
					array(
						'BoundingBox' => array(
							'Top'    => 0.0,
							'Left'   => 0.0,
							'Width'  => 0.3,
							'Height' => 0.3,
						),
					),
					array(
						'BoundingBox' => array(
							'Top'    => 0.6,
							'Left'   => 0.6,
							'Width'  => 0.4,
							'Height' => 0.4,
						),
					),
				),
				'max_faces'   => 100,
				'Expected'    => array(
					'top'    => 0.0,
					'left'   => 0.0,
					'right'  => 1.0,
					'bottom' => 1.0,
				),
			),
			// Faces at (100,100;300,300); (600,600;900,900) and (100,100;200,950).
			array(
				'FaceDetails' => array(
					array(
						'BoundingBox' => array(
							'Top'    => 0.1,
							'Left'   => 0.1,
							'Width'  => 0.2,
							'Height' => 0.2,
						),
					),
					array(
						'BoundingBox' => array(
							'Top'    => 0.6,
							'Left'   => 0.6,
							'Width'  => 0.3,
							'Height' => 0.3,
						),
					),
					array(
						'BoundingBox' => array(
							'Top'    => 0.1,
							'Left'   => 0.1,
							'Width'  => 0.85,
							'Height' => 0.2,
						),
					),
				),
				'max_faces'   => 100,
				'Expected'    => array(
					'top'    => 0.1,
					'left'   => 0.1,
					'right'  => 0.95,
					'bottom' => 0.9,
				),
			),
			// Faces at (100,100;300,300); (600,600;900,900) and (100,100;200,950).
			// with Max Faces set to 2.
			array(
				'FaceDetails' => array(
					array(
						'BoundingBox' => array(
							'Top'    => 0.1,
							'Left'   => 0.1,
							'Width'  => 0.2,
							'Height' => 0.2,
						),
					),
					array(
						'BoundingBox' => array(
							'Top'    => 0.6,
							'Left'   => 0.6,
							'Width'  => 0.3,
							'Height' => 0.3,
						),
					),
					array(
						'BoundingBox' => array(
							'Top'    => 0.1,
							'Left'   => 0.1,
							'Width'  => 0.85,
							'Height' => 0.2,
						),
					),
				),
				'max_faces'   => 2,
				'Expected'    => array(
					'top'    => 0.1,
					'left'   => 0.1,
					'right'  => 0.9,
					'bottom' => 0.9,
				),
			),
            // Faces at (100,100;300,300); (600,600;900,900) and (100,100;200,950).
			// with Max Faces set to 1.
			array(
				'FaceDetails' => array(
					array(
						'BoundingBox' => array(
							'Top'    => 0.1,
							'Left'   => 0.1,
							'Width'  => 0.2,
							'Height' => 0.2,
						),
					),
					array(
						'BoundingBox' => array(
							'Top'    => 0.6,
							'Left'   => 0.6,
							'Width'  => 0.3,
							'Height' => 0.3,
						),
					),
					array(
						'BoundingBox' => array(
							'Top'    => 0.1,
							'Left'   => 0.1,
							'Width'  => 0.85,
							'Height' => 0.2,
						),
					),
				),
				'max_faces'   => 1,
				'Expected'    => array(
					'top'    => 0.1,
					'left'   => 0.1,
					'right'  => 0.3,
					'bottom' => 0.3,
				),
			),
		);
	}
}
