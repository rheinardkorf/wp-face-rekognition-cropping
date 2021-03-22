<?php
/**
 * Face Rekognition Cropping
 *
 * @package   FaceRekogCrop
 * @copyright Copyright(c) 2021, Rheinard Korf
 * @licence http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2 (GPL-2.0)
 *
 * Plugin Name: Face Rekognition Cropping
 * Plugin URI: https://rheinardkorf.com
 * Description: Uses AWS Rekognition to crop faces (preserving the original files).
 * Version: 0.1-alpha
 * Author: Rheinard Korf
 * Author URI: https://rheinardkorf.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: face-rekog-crop
 * Domain Path: /languages
 */

use function FaceRekogCrop\Utilities\add_box_as_attachment;
use function FaceRekogCrop\Utilities\get_raw_bounding_area;
use function FaceRekogCrop\Utilities\get_translated_bounding_area;
use function FaceRekogCrop\Utilities\save_box_as_file;

require_once __DIR__ . '/rekognition/rekognition-request.php';
require_once __DIR__ . '/rekognition/class-rekognition-wrapper.php';
require_once __DIR__ . '/utilities/file.php';
require_once __DIR__ . '/utilities/crop.php';
require_once __DIR__ . '/utilities/attachments.php';
require_once __DIR__ . '/admin/options.php';

/**
 * Performance hack for wp_*_request functions.
 *
 * @see: https://gist.github.com/carlalexander/c779b473f62dcd1a4ca26fcaa637ec59
 */
add_filter(
	'http_request_args',
	function ( array $arguments ) {
		$body = $arguments['body'];

		if ( is_array( $body ) ) {
			$body = implode( '', $body );
		}

		$arguments['headers']['expect'] = ! empty( $body ) && strlen( $body ) > 1048576 ? '100-Continue' : '';

		return $arguments;
	}
);


add_filter(
	'wp_handle_upload',
	function( $upload ) {
		$plugin_options = get_option( 'face_rekognition_cropping_options' );

		$rekog = new \FaceRekogCrop\Rekognition_Wrapper(
			array(
				'access_key'    => $plugin_options['access_key'],
				'access_secret' => $plugin_options['access_secret'],
				'region'        => $plugin_options['aws_region'],
			)
		);

		$file = $upload['file'];

		list( $orig_width, $orig_height ) = getimagesize( $file );

		$parts    = explode( '/', $file );
		$filename = array_pop( $parts );

		// Actual Rekognition results.
		$results = $rekog->detect_faces( $file );
		$body    = json_decode( $results['body'], true );
		$faces   = $body['FaceDetails'];

		// Exit early if no faces detected.
		if ( 0 === count( $faces ) ) {
			return $upload;
		}

		$filename_prefix = array_shift( explode( '.', $filename ) );

		$file = str_replace( $filename_prefix, $filename_prefix . '_centered', $file );

		$max_faces_group = (int) $plugin_options['max_faces_group'];
		$box             = get_translated_bounding_area( $orig_width, $orig_height, get_raw_bounding_area( $faces, $max_faces_group ) );

		// Save box within constraints of original image. Cropping will occur.
		save_box_as_file( $upload['file'], $box, $orig_width, $orig_height, $file );
		add_box_as_attachment( $file, $filename_prefix . ' ' . __( '(centered)', 'face-rekog-crop' ) );

			// Create a file for each face.
		if ( count( $faces ) > 0 && $plugin_options['individual_crops_enabled'] ) {
			$max_faces = (int) $plugin_options['max_faces_single'];

			$processed = 0;
			foreach ( $faces as $face ) {

				if ( $processed === $max_faces ) {
					break;
				}

				$file = $upload['file'];

				$box = get_translated_bounding_area( $orig_width, $orig_height, get_raw_bounding_area( array( $face ) ) );

				$suffix = sprintf( '-%d-%d-%d-%d', $box['left'], $box['top'], $box['right'], $box['bottom'] );
				$file   = str_replace( $filename_prefix, $filename_prefix . '_face' . $suffix, $file );

				$b_width  = (int) ( $box['right'] - $box['left'] ) * 1.75;
				$b_height = (int) ( $box['bottom'] - $box['top'] ) * 1.75;

				// Save box within constraints of original image. Cropping will occur.
				save_box_as_file( $upload['file'], $box, $b_width, $b_height, $file );
				$box_as_string = sprintf( ' (%d,%d), (%d,%d)', $box['left'], $box['top'], $box['right'], $box['bottom'] );
				add_box_as_attachment( $file, __( 'face: ', 'face-rekog-crop' ) . $box_as_string );

				++$processed;
			}
		}

		/**
		 * Future Options:
		 * * Box shape for individual faces; dynamic | square.
		 * * Zoom level for individual faces.
		 */

		return $upload;
	}
);
