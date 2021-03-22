<?php
/**
 * File utilities.
 *
 * @package   FaceRekogCrop
 */

namespace FaceRekogCrop\Utilities;

use WP_Error;

/**
 * Returns source contents.
 *
 * @param string $source The source to get content from.
 * @param bool   $base64 To encode or not.
 * @return mixed
 */
function get_bytes( $source, $base64 = true ) {

	if ( preg_match( '/^https?\:\/\//', $source ) ) {
		$data = wp_safe_remote_get( $source );
		if ( ! is_wp_error( $data ) && array_key_exists( 'body', $data ) ) {
			if ( $base64 ) {
				// Need to base64 encode the bytes to send to Rekognition service.
				$data['body'] = base64_encode( $data['body'] ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			}
			return $data['body'];
		}
	} else {
		if ( file_exists( $source ) ) {
			global $wp_filesystem;

			if ( ! $wp_filesystem ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();
			}
			$image = $wp_filesystem->get_contents( $source );
			if ( $image ) {
				if ( $base64 ) {
					// Need to base64 encode the bytes to send to Rekognition service.
					$image = base64_encode( $image ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				}
				return $image;
			}
		}
	}

	return new WP_Error( 'source_not_found', __( 'Could not find image source.', 'face-rekog-crop' ) );
}

/**
 * Saves a given box (left,rigth,top,bottom) as a new cropped file.
 *
 * This requires a source image as well as new image filename to save to.
 *
 * NOTE: Desired size cannot exceed original image size. Cropping will occur.
 * NOTE: If desired size matches original size cropping is likely to occur
 *       when centereing the box.
 *
 * @param string $input_file Source file image.
 * @param array  $box Box to crop.
 * @param int    $width Desired image width.
 * @param int    $height Desired image height.
 * @param string $new_filename New filename to save.
 * @return void
 */
function save_box_as_file( $input_file, $box, $width, $height, $new_filename ) {

	// Initiate WordPress Editor.
	$editor = wp_get_image_editor( $input_file );
	$editor->set_quality(); // Infer quality of input file.

	// Get size of given input file.
	$size = $editor->get_size();

	// Contrain to input image width.
	if ( $width > $size['width'] ) {
		$width = $size['width'];
	}

	// Contrain to input image height.
	if ( $height > $size['height'] ) {
		$height = $size['height'];
	}

	$w = $width;
	$h = $height;

	// Get the centered position and scale.
	$centered = center_box_at_destination( $width, $height, $box );

	// Identify crop start position.
	$x1 = ( $box['left'] - $centered['x'] ) * $centered['scale'];
	$y1 = ( $box['top'] - $centered['y'] ) * $centered['scale'];

	// Original size provided, banding will occur. Lets try fix it, but it will crop.
	if ( $width === $size['width'] ) {
		if ( ( $x1 + $width ) > $width ) {
			$w = $width - $x1;
		} else {
			$delta = abs( $x1 );
			$x1   += $delta;
			$w    -= $delta;
		}
	}
	if ( $height === $size['height'] ) {
		if ( ( $y1 + $height ) > $height ) {
			$h = $height - $y1;
		} else {
			$delta = abs( $y1 );
			$y1   += $delta;
			$h    -= $delta;
		}
	}

	$editor->resize( $size['width'] * $centered['scale'], $size['height'] * $centered['scale'] );

	// Crop to size and save.
	$editor->crop( $x1, $y1, $w, $h );
	$editor->save( $new_filename );
}
