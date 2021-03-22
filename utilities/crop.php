<?php
/**
 * Cropping utilities.
 *
 * @package   FaceRekogCrop
 */

namespace FaceRekogCrop\Utilities;

/**
 * Given an array of FaceDetails find the max bounding area.
 *
 * @param array $faces Array of FaceDetails.
 * @param int   $max_faces Max number of faces to use. Rekognition maxes at 100 by default.
 *
 * @return array
 */
function get_raw_bounding_area( $faces, $max_faces = 100 ) {
	$top    = 100.0;
	$left   = 100.0;
	$right  = 0.0;
	$bottom = 0.0;

	$processed = 0;

	foreach ( $faces as $face ) {

		// Only process the number of faces specified.
		if ( $processed === $max_faces ) {
			break;
		}

		$ft = (float) $face['BoundingBox']['Top'];
		$fl = (float) $face['BoundingBox']['Left'];
		$fr = (float) $face['BoundingBox']['Width'] + $fl;
		$fb = (float) $face['BoundingBox']['Height'] + $ft;

		if ( $ft < $top ) {
			$top = $ft;
		}

		if ( $fl < $left ) {
			$left = $fl;
		}

		if ( $fr > $right ) {
			$right = $fr;
		}

		if ( $fb > $bottom ) {
			$bottom = $fb;
		}

		++$processed;
	}

	return array(
		'top'    => $top,
		'left'   => $left,
		'right'  => $right,
		'bottom' => $bottom,
	);
}

/**
 * Translate raw bounds into actual pixel positions.
 *
 * @param int   $width Original width.
 * @param int   $height Original height.
 * @param array $bounds Input bounds.
 * @return array Output bounds.
 */
function get_translated_bounding_area( $width, $height, $bounds ) {
	return array(
		'top'    => (int) ( $height * $bounds['top'] ),
		'left'   => (int) ( $width * $bounds['left'] ),
		'bottom' => (int) ( $height * $bounds['bottom'] ),
		'right'  => (int) ( $width * $bounds['right'] ),
	);
}

/**
 * Center a given box of (top, left, right, bottom) on destination area.
 *
 * @param int     $width  Width of destination area.
 * @param int     $height Height of destination area.
 * @param array   $box Box with array('top' => [int], 'right' => [int], 'bottom' => [int], 'left' => [int]) array.
 * @param boolean $fit Scale box to fit on destination.
 * @param float   $zoom Zoom in or out the box before centering to include more details around the box.
 *
 * @return array  Return array of array('x' => [int], 'y' => [int], 'scale' => [float]).
 */
function center_box_at_destination( $width, $height, $box, $fit = true, $zoom = 1.0 ) {

	$scale_precision = pow( 10, 5 );

	// Get center position.
	$bw    = (int) ( ( $box['right'] - $box['left'] ) * $zoom );
	$bh    = (int) ( ( $box['bottom'] - $box['top'] ) * $zoom );
	$x     = (int) ( ( $width - $bw ) / 2 );
	$y     = (int) ( ( $height - $bh ) / 2 );
	$scale = 1.0;

	// If box is larger than width and height.
	if ( ( $x < 0 || $y < 0 ) && $fit ) {
		// Which side is larger?
		if ( $x <= $y ) {
			$w1    = abs( $x ) * 2;
			$bw1   = $bw - $w1; // New box width.
			$scale = (int) ( $bw1 / $bw * $scale_precision ) / $scale_precision;
			$bh1   = (int) ( $bh * $scale );
			$x     = 0;
			$y     = (int) ( ( $height - $bh1 ) / 2 );
		} else {
			$h1    = abs( $y ) * 2;
			$bh1   = $bh - $h1; // New box height.
			$scale = (int) ( $bh1 / $bh * $scale_precision ) / $scale_precision;
			$bw1   = (int) ( $bw * $scale );
			$x     = (int) ( ( $width - $bw1 ) / 2 );
			$y     = 0;
		}
	}

	return array(
		'x'     => $x,
		'y'     => $y,
		'scale' => $scale,
	);

}
