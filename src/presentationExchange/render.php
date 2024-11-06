<?php
/**
 * PHP file to use when rendering the block type on the server to show on the front end.
 *
 * The following variables are exposed to the file:
 *     $attributes (array): The block attributes.
 *     $content (string): The block default content.
 *     $block (WP_Block): The block instance.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
// do a session a start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$options = new OpenID4VP_Admin_Options();
$openidEndpoint = $options->openidEndpoint;
$authenticationHeaderName = $options->authenticationHeaderName;
$authenticationToken = $options->authenticationToken;
if (!empty($attributes['openidEndpoint'])) {
    $openidEndpoint = $attributes['openidEndpoint'];
    $authenticationHeaderName = $attributes['authenticationHeaderName'];
    $authenticationToken = $attributes['authenticationToken'];
}

$response = wp_remote_post( $openidEndpoint . '/' . $attributes['presentationDefinitionId'], array(
    'headers' => array('Content-Type' => 'application/json', $authenticationHeaderName => $authenticationToken),
    'timeout'     => 45,
    'redirection' => 5,
    'blocking'    => true,
    'body'        => '{}'
));

if (is_wp_error($response)) {
    return 'Error fetching data';
}

$body = wp_remote_retrieve_body($response);
$result = json_decode( $body );

// store the correlation id in the SESSION
$_SESSION['correlationId'] = $result->correlationId;
$_SESSION['presentationStatusUri'] = $result->presentationStatusUri;
$_SESSION['authenticationHeaderName'] = $authenticationHeaderName;
$_SESSION['authenticationToken'] = $authenticationToken;
if (array_key_exists('successUrl', $attributes)) {
    $_SESSION['successUrl'] = wp_sanitize_redirect($attributes['successUrl']);
}
do_action( 'wp_enqueue_script' );


$block_content = '<div ' . get_block_wrapper_attributes() . '><img id="openid4vp_qrImage" src="data:' . $result->qrImage . '"></>or click <a href="' . $result->presentationRequestUri . '">link</a></div>';

echo $block_content;

