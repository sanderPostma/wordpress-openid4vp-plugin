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

// store the correlation id in the SESSION
$presentationResponse = $_SESSION['presentationResponse'];
$jsonAttributeNames = explode(".", $attributes['attributeName']);

$result = $presentationResponse[$attributes['credentialType']];
foreach ($jsonAttributeNames as &$name) {
    $result = $result[$name];
}
// $arr is now array(2, 4, 6, 8)
unset($name);

$block_content = '<p ' . get_block_wrapper_attributes() . '>' . $attributes['attributeLabel'] . ': ' . $result . '</p>';

echo $block_content;

