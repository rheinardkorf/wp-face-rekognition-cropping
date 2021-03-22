<?php
/**
 * Signing AWS requests.
 *
 * @package   FaceRekogCrop
 */

namespace FaceRekogCrop;

/**
 * Hooks http_request_args.
 *
 * If $args['aws'] and $args['client_options'] is provided it will add
 * the AWS Signature v4 headers to the request.
 *
 * $aws_services filters the signature specifically for the 'rekognition' request.
 * This can be expanded to allow for other AWS services.
 *
 * @param array $args Arguments.
 * @return mixed
 */
function aws_request_args( $args ) {

	// Supported AWS services.
	$aws_services = array(
		'rekognition' => 'RekognitionService',
	);

	// Check for AWS request.
	if ( ! array_key_exists( 'aws', $args ) || ! in_array( $args['aws'], array_keys( $aws_services ), true ) || ! array_key_exists( 'client_options', $args ) ) {
		return $args;
	}

	$client_options = $args['client_options'];

	// Get content body.
	$body = isset( $args['body'] ) ? $args['body'] : '';
	if ( is_array( $body ) ) {
		$body = implode( '', $body );
	}

	$timestamp  = gmdate( 'Ymd\THis\Z' );
	$short_date = gmdate( 'Ymd' );

	$headers = array(
		'host'       => $client_options['host'],
		'x-amz-date' => $timestamp, // Long date.
	);

	if ( strlen( $body ) > 0 ) {
		$headers['x-amz-content-sha256'] = create_hash( $body );
	}

	// Add Rekognition headers.
	// Different services have different headers.
	// The below works for DynamoDB too.
	if ( 'rekognition' === $args['aws'] && array_key_exists( 'operation', $args ) ) {
		$headers['content-type'] = 'application/x-amz-json-1.1';
		$headers['x-amz-target'] = sprintf( '%s.%s', $aws_services[ $args['aws'] ], (string) $args['operation'] );
	}

	// Merge headers added in the request.
	if ( array_key_exists( 'headers', $args ) && is_array( $args['headers'] ) ) {
		$headers = array_merge( $headers, $args['headers'] );
	}

	// Sort headers for signing.
	ksort( $headers );

	// Uri components.
	$uri_parts = explode( '?', $args['uri'] );
	$uri       = array_shift( $uri_parts );

	// Canonical headers for signature.
	$canonical_headers = '';
	foreach ( $headers as $key => $value ) {
		$canonical_headers .= sprintf( '%s:%s' . PHP_EOL, strtolower( $key ), $value );
	}

	// Used to verify which headers are signed.
	$signed_headers = implode( ';', array_map( 'strtolower', array_keys( $headers ) ) );

	// Convert the request into a canonical string as per https://docs.aws.amazon.com/general/latest/gr/sigv4-create-canonical-request.html.
	$canonical_request  = isset( $args['method'] ) ? $args['method'] : 'POST';
	$canonical_request .= PHP_EOL;
	$canonical_request .= '/' . ltrim( $uri, '/' ) . PHP_EOL;
	if ( count( $uri_parts ) > 0 ) {
		$canonical_request .= $uri_parts;
	}
	$canonical_request .= PHP_EOL;
	$canonical_request .= $canonical_headers . PHP_EOL;
	$canonical_request .= $signed_headers . PHP_EOL;
	$canonical_request .= $headers['x-amz-content-sha256'] ?? 'UNSIGNED-PAYLOAD';
	$hashed_request     = create_hash( $canonical_request );

	$scope = implode(
		'/',
		array(
			$short_date,
			$client_options['region'],
			$args['aws'],
			'aws4_request',
		)
	);

	// Create the string to sign.
	$string_to_sign = "AWS4-HMAC-SHA256\n{$timestamp}\n{$scope}\n{$hashed_request}";

	/**
	 * So much hashing of hashes.
	 *
	 * Thanks to @carlalexander and his ymirapp/wordpress-plugin for
	 * helping me figure this one out.
	 *
	 * @see https://github.com/ymirapp/wordpress-plugin/blob/master/src/CloudProvider/Aws/AbstractClient.php
	 * @see https://docs.aws.amazon.com/general/latest/gr/sigv4-calculate-signature.html
	 */
	$signature = create_hash( $string_to_sign, create_hash( 'aws4_request', create_hash( $args['aws'], create_hash( $client_options['region'], create_hash( $short_date, 'AWS4' . $client_options['access_secret'], true ), true ), true ), true ) );

	// Get Auth Header.
	$headers['authorization'] = 'AWS4-HMAC-SHA256 ' . implode(
		',',
		array(
			'Credential=' . $client_options['access_key'] . '/' . $scope,
			'SignedHeaders=' . $signed_headers,
			'Signature=' . $signature,
		)
	);

	// Sort headers for request.
	ksort( $headers );

	// Signature v4 signed request.
	$args['headers'] = $headers;

	return $args;
}

/**
 * Hashing function.
 *
 * @see https://github.com/ymirapp/wordpress-plugin/blob/master/src/CloudProvider/Aws/AbstractClient.php#L271
 *
 * @param string|null $data Data to hash.
 * @param string      $key Key to hash with.
 * @param boolean     $raw Use raw hash.
 * @return string
 */
function create_hash( ?string $data, string $key = '', bool $raw = false ): string {
	$algorithm = 'sha256';

	return empty( $key ) ? hash( $algorithm, (string) $data, $raw ) : hash_hmac( $algorithm, (string) $data, $key, $raw );
}

add_filter(
	'http_request_args',
	'\\FaceRekogCrop\\aws_request_args'
);
