<?php
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

$body = array('query_id' => $attributes['queryId']);
if ($attributes['qrCodeEnabled']) {
    $qrCode = (object)[];
    if (array_key_exists('qrSize', $attributes)) {
        $qrCode->size = $attributes['qrSize'];
    }
    if (array_key_exists('qrColorDark', $attributes)) {
        $qrCode->color_dark = $attributes['qrColorDark'];
    }
    if (array_key_exists('qrColorLight', $attributes)) {
        $qrCode->color_light = $attributes['qrColorLight'];
    }
    if (array_key_exists('qrPadding', $attributes)) {
        $qrCode->padding = $attributes['qrPadding'];
    }
    $body['qr_code'] = $qrCode;
}

$response = wp_remote_post( $openidEndpoint . '/oid4vp/backend/auth/requests', array(
    'headers' => array('Content-Type' => 'application/json', $authenticationHeaderName => $authenticationToken),
    'timeout'     => 45,
    'redirection' => 5,
    'blocking'    => true,
    'body'        => json_encode($body)
));

if (is_wp_error($response)) {
    return 'Error fetching data';
}

$body = wp_remote_retrieve_body($response);
$result = json_decode( $body );

// store the correlation id in the SESSION
$_SESSION['correlationId'] = $result->correlation_id;
$_SESSION['presentationStatusUri'] = $result->status_uri;
$_SESSION['authenticationHeaderName'] = $authenticationHeaderName;
$_SESSION['authenticationToken'] = $authenticationToken;
if (array_key_exists('successUrl', $attributes)) {
    $_SESSION['successUrl'] = wp_sanitize_redirect($attributes['successUrl']);
}
do_action( 'wp_enqueue_script' );


$block_content = '<div ' . get_block_wrapper_attributes() . '><img id="openid4vp_qrImage" src="data:' . $result->qr_uri . '"></>or click <a href="' . $result->request_uri . '">link</a></div>';

echo $block_content;

