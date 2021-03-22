<?php
/**
 * File utilities.
 *
 * @package   FaceRekogCrop
 */

namespace FaceRekogCrop\Utilities;

/**
 * Adds a new attachment to the Media library.
 *
 * @param string $file File to attach (in uploads folder).
 * @param string $title Title to associate with Attachment post.
 * @return void
 */
function add_box_as_attachment( $file, $title ) {
	$wp_filetype = wp_check_filetype( $file, null );

	$attachment = array(
		'post_mime_type' => $wp_filetype['type'],
		'post_title'     => sanitize_text_field( $title ),
		'post_content'   => '',
		'post_status'    => 'inherit',
	);

	$attach_id = wp_insert_attachment( $attachment, $file );
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
	wp_update_attachment_metadata( $attach_id, $attach_data );
}
