<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = universal_openid4vp_sendVpRequest($attributes);

if ($response["success"] === false) {
  echo $response["error"];
  return;
}

$result = $response["result"];

do_action( 'wp_enqueue_script' );

$qr_content = $attributes['qrCodeEnabled'] ? '<img id="openid4vp_qrImage" src="data:' . $result->qr_uri . '"></>or ' : '';
$block_content = '<div ' . get_block_wrapper_attributes() . '>' . $qr_content . 'click <a href="' . $result->request_uri . '">link</a></div>';

echo $block_content;

