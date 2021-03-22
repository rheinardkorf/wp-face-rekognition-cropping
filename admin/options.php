<?php
/**
 * Face Rekognition Crop Options Page.
 *
 * @package FaceRekogCrop
 */

namespace FaceRekogCrop\Admin;

if ( ! is_admin() ) {
	return;
}

add_action( 'admin_menu', '\\FaceRekogCrop\\Admin\\add_plugin_page' );
add_action( 'admin_init', '\\FaceRekogCrop\\Admin\\page_init' );

/**
 * Adds a new menu page for plugin options.
 *
 * @return void
 */
function add_plugin_page() {
	add_menu_page(
		__( 'Face Rekognition Cropping', 'face-rekog-crop' ), // page_title.
		__( 'Face Rekognition Cropping', 'face-rekog-crop' ), // menu_title.
		'manage_options', // capability.
		'face-rekognition-cropping', // menu_slug.
		'\\FaceRekogCrop\\Admin\\create_admin_page',
		'dashicons-id'
	);
}

/**
 * Renders the settings page.
 *
 * @return void
 */
function create_admin_page() {
	?>
		<div class="wrap">
			<h2><?php echo esc_html__( 'Face Rekognition Cropping', 'face-rekog-crop' ); ?></h2>
			<p><?php echo esc_html__( 'Face Rekognition Cropping relies on the AWS Rekognition service. Only JPG and PNG files up to 5MB is supported by Rekognition.', 'face-rekog-crop' ); ?></p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'face_rekognition_cropping_option_group' );
				do_settings_sections( 'face-rekognition-cropping-admin' );
				submit_button();
				?>
			</form>
		</div>
		<?php
}

/**
 * Register settings and fields before they can be rendered.
 *
 * @return void
 */
function page_init() {
	register_setting(
		'face_rekognition_cropping_option_group', // option_group.
		'face_rekognition_cropping_options', // option_name.
		'\\FaceRekogCrop\\Admin\\sanitize'  // sanitize_callback.
	);

	/*
	 * Credential settings.
	 */

	add_settings_section(
		'face_rekognition_cropping_credentials_section', // id.
		__( 'AWS Rekognition Credentials', 'face-rekog-crop' ), // title.
		'\\FaceRekogCrop\\Admin\\credentials_section_info', // callback.
		'face-rekognition-cropping-admin' // page.
	);

	add_settings_field(
		'access_key', // id.
		__( 'Access Key', 'face-rekog-crop' ), // title.
		'\\FaceRekogCrop\\Admin\\access_key_callback', // callback.
		'face-rekognition-cropping-admin', // page.
		'face_rekognition_cropping_credentials_section' // section.
	);

	add_settings_field(
		'access_secret',
		__( 'Access Secret', 'face-rekog-crop' ),
		'\\FaceRekogCrop\\Admin\\access_secret_callback',
		'face-rekognition-cropping-admin',
		'face_rekognition_cropping_credentials_section'
	);

	add_settings_field(
		'aws_region',
		__( 'AWS Region', 'face-rekog-crop' ),
		'\\FaceRekogCrop\\Admin\\aws_region_callback',
		'face-rekognition-cropping-admin',
		'face_rekognition_cropping_credentials_section'
	);

	/*
	 * Cropping settings.
	 */

	add_settings_section(
		'face_rekognition_cropping_settings_section', // id.
		__( 'Cropping Settings', 'face-rekog-crop' ), // title.
		'\\FaceRekogCrop\\Admin\\settings_section_info', // callback.
		'face-rekognition-cropping-admin' // page.
	);

	add_settings_field(
		'max_faces_group', // id.
		__( 'Max Faces (Aggregate Crop)', 'face-rekog-crop' ), // title.
		'\\FaceRekogCrop\\Admin\\max_faces_group_callback', // callback.
		'face-rekognition-cropping-admin', // page.
		'face_rekognition_cropping_settings_section' // section.
	);

	add_settings_field(
		'individual_crops_enabled',
		__( 'Perform individual face crops', 'face-rekog-crop' ),
		'\\FaceRekogCrop\\Admin\\individual_crops_enabled_callback',
		'face-rekognition-cropping-admin',
		'face_rekognition_cropping_settings_section'
	);

	add_settings_field(
		'max_faces_single',
		__( 'Max Faces (Individual Crops)', 'face-rekog-crop' ),
		'\\FaceRekogCrop\\Admin\\max_faces_single_callback',
		'face-rekognition-cropping-admin',
		'face_rekognition_cropping_settings_section'
	);
}

/**
 * Sanitize plugin settings on save.
 *
 * @param array $input The input array.
 * @return array
 */
function sanitize( $input ) {
	$sanitary_values = array();
	if ( isset( $input['access_key'] ) ) {
		$sanitary_values['access_key'] = sanitize_text_field( $input['access_key'] );
	}

	if ( isset( $input['access_secret'] ) ) {
		$sanitary_values['access_secret'] = sanitize_text_field( $input['access_secret'] );
	}

	if ( isset( $input['aws_region'] ) ) {
		$sanitary_values['aws_region'] = sanitize_text_field( $input['aws_region'] );
	}

	if ( isset( $input['max_faces_group'] ) ) {
		$sanitary_values['max_faces_group'] = (int) $input['max_faces_group'];
		$sanitary_values['max_faces_group'] = empty( $sanitary_values['max_faces_group'] ) ? 5 : $sanitary_values['max_faces_group'];
	}

	if ( isset( $input['max_faces_single'] ) ) {
		$sanitary_values['max_faces_single'] = (int) $input['max_faces_single'];
		$sanitary_values['max_faces_single'] = empty( $sanitary_values['max_faces_single'] ) ? 5 : $sanitary_values['max_faces_single'];
	}

	$sanitary_values['individual_crops_enabled'] = (int) isset( $input['individual_crops_enabled'] );

	return $sanitary_values;
}

/**
 * Show info under credentials settings title.
 *
 * @return void
 */
function credentials_section_info() {
	?>
	<p><?php echo esc_html__( 'Please ensure you have an API user configured with the correct permissions.', 'face-rekog-crop' ); ?></p>
	<?php
}

/**
 * Show info under credentials settings title.
 *
 * @return void
 */
function settings_section_info() {
	?>
	<p><?php echo esc_html__( 'The following settings are used to describe how cropped versions of the faces should be generated.', 'face-rekog-crop' ); ?></p>
	<?php
}


/**
 * Render field.
 *
 * @return void
 */
function access_key_callback() {
	$plugin_options = get_option( 'face_rekognition_cropping_options' );
	printf(
		'<input class="regular-text" type="text" name="face_rekognition_cropping_options[access_key]" id="access_key" value="%s">',
		isset( $plugin_options['access_key'] ) ? esc_attr( $plugin_options['access_key'] ) : ''
	);
}

/**
 * Render field.
 *
 * @return void
 */
function access_secret_callback() {
	$plugin_options = get_option( 'face_rekognition_cropping_options' );
	printf(
		'<input class="regular-text" type="password" name="face_rekognition_cropping_options[access_secret]" id="access_secret" value="%s">',
		isset( $plugin_options['access_secret'] ) ? esc_attr( $plugin_options['access_secret'] ) : ''
	);
}

/**
 * Render field.
 *
 * @return void
 */
function aws_region_callback() {
	$plugin_options = get_option( 'face_rekognition_cropping_options' );
	printf(
		'<input class="regular-text" type="text" name="face_rekognition_cropping_options[aws_region]" id="aws_region" value="%s">',
		isset( $plugin_options['aws_region'] ) ? esc_attr( $plugin_options['aws_region'] ) : ''
	);
}

/**
 * Render field.
 *
 * @return void
 */
function max_faces_group_callback() {
	$plugin_options = get_option( 'face_rekognition_cropping_options' );
	printf(
		'<input class="small-text" type="number" name="face_rekognition_cropping_options[max_faces_group]" id="max_faces_group" value="%s">',
		isset( $plugin_options['max_faces_group'] ) ? esc_attr( $plugin_options['max_faces_group'] ) : 5
	);
}

/**
 * Render field.
 *
 * @return void
 */
function max_faces_single_callback() {
	$plugin_options = get_option( 'face_rekognition_cropping_options' );
	printf(
		'<input class="small-text" type="number" name="face_rekognition_cropping_options[max_faces_single]" id="max_faces_single" value="%s">',
		isset( $plugin_options['max_faces_single'] ) ? esc_attr( $plugin_options['max_faces_single'] ) : 5
	);
}

/**
 * Render field.
 *
 * @return void
 */
function individual_crops_enabled_callback() {
	$plugin_options = get_option( 'face_rekognition_cropping_options' );

	printf(
		'<input type="checkbox" name="face_rekognition_cropping_options[individual_crops_enabled]" id="individual_crops_enabled" %s>',
		checked( 1, $plugin_options['individual_crops_enabled'], false )
	);
}

/*
 * Retrieve this value with:
 * $plugin_options = get_option( 'face_rekognition_cropping_options' );
 */
