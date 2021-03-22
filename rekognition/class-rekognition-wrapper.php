<?php
/**
 * Rekognition Interface.
 *
 * @package RekognitionWrapper
 */

namespace FaceRekogCrop;

/**
 * Manager Rekognition API.
 */
class Rekognition_Wrapper {

	/**
	 * AWS client options.
	 *
	 * @var array
	 */
	protected $client_options = array(
		'access_key'    => 'THEKEY',
		'access_secret' => 'THESECRET',
		'region'        => 'us-east-1', // LocalStack default region for development.
		'host'          => '',
		'use_https'     => true,
	);

	/**
	 * Constructor for the wrapper.
	 *
	 * @param array $options Client options.
	 */
	public function __construct( $options = array() ) {

		$this->client_options = wp_parse_args( $options, $this->client_options );

		if ( array_key_exists( 'host', $options ) && ! empty( $options['host'] ) ) {
			$this->client_options['host'] = $options['host'];
		} else {
			$this->client_options['host'] = sprintf( 'rekognition.%s.amazonaws.com', $this->client_options['region'] );
		}
	}

	/**
	 * Executes DetectFaces on Rekognition.
	 *
	 * @param string $image_source The source image to analyze.
	 * @param string $mode Can be 'DEFAULT' or 'ALL'.
	 *
	 * @see https://docs.aws.amazon.com/rekognition/latest/dg/API_DetectFaces.html
	 *
	 * @return array|\WP_Error
	 */
	public function detect_faces( $image_source, $mode = 'DEFAULT' ) {

		$bytes = Utilities\get_bytes( $image_source );

		if ( is_wp_error( $bytes ) ) {
			return $bytes;
		}

		$params = array(
			'Image'      => array(
				'Bytes' => $bytes,
			),
			'Attributes' => array( $mode ), // or ALL.
		);

		return $this->call( 'DetectFaces', $params );
	}

	/**
	 * Executes DetectLabels on Rekognition.
	 *
	 * @param string $image_source The source image to analyze.
	 * @param int    $max_labels Maximum number of labels you want the service to return in the response.
	 * @param float  $min_confidence Specifies the minimum confidence level for the labels to return.
	 *
	 * @see https://docs.aws.amazon.com/rekognition/latest/dg/API_DetectLabels.html
	 *
	 * @return array|\WP_Error
	 */
	public function detect_labels( $image_source, $max_labels = 0, $min_confidence = 55.0 ) {

		$bytes = Utilities\get_bytes( $image_source );

		if ( is_wp_error( $bytes ) ) {
			return $bytes;
		}

		$params = array(
			'Image'         => array(
				'Bytes' => $bytes,
			),
			'MaxLabels'     => $max_labels,
			'MinConfidence' => $min_confidence,
		);

		return $this->call( 'DetectLabels', $params );
	}

	/**
	 * Executes a Rekognition action.
	 *
	 * @param string $operation Rekognition operation to execute.
	 * @param array  $params Payload to send to Rekognition.
	 *
	 * @return array|\WP_Error
	 */
	private function call( $operation, $params ) {

		$request_args = array(
			'method'         => 'POST',
			'body'           => wp_json_encode( $params ),
			'uri'            => '/',
			'client_options' => $this->client_options,
			'headers'        => array(
				'host' => $this->client_options['host'],
			),
			'aws'            => 'rekognition',
			'operation'      => $operation,
		);

		$endpoint = $this->client_options['host'];
		if ( $this->client_options['use_https'] ) {
			$endpoint = 'https://' . $endpoint;
		} else {
			$endpoint = 'http://' . $endpoint; // Usually for local development.
		}

		$request_args['host'] = $endpoint;

		return wp_remote_request(
			$endpoint,
			$request_args
		);
	}
}
