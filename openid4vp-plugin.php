<?php
/**
 * Plugin Name:       OpenID4VP
 * Description:       Retrieve verifiable presentations
 * Version:           0.3.0
 * Requires at least: 6.6
 * Requires PHP:      7.2
 * Author:            Credenco
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       openid4vp-exchange
 *
 * @package           create-block
 */

if ( ! defined( 'ABSPATH' ) ) {
   exit; // Exit if accessed directly.
}

if ( ! defined( 'OPENID4VP_PLUGIN_URL' ) ) {
   define( 'OPENID4VP_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
}
if (!defined('OPENID4VP_PLUGIN_DIR')) {
    define('OPENID4VP_PLUGIN_DIR', trailingslashit(plugin_dir_path(__FILE__)));
}

require_once(OPENID4VP_PLUGIN_DIR . 'build/OpenID4VP.php');

$openid4vp = new OpenID4VP();

add_action('admin_menu', [$openid4vp, 'plugin_init']);
add_action('wp_logout', [$openid4vp, 'logout']);

register_activation_hook(__FILE__, [$openid4vp, 'setup']);
register_activation_hook(__FILE__, [$openid4vp, 'upgrade']);

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_openid4vp_block_init() {
   register_block_type( __DIR__ . '/build/presentationExchange' );
   register_block_type( __DIR__ . '/build/presentationExchangeOrgWallet' );
   register_block_type( __DIR__ . '/build/presentationAttribute' );
    if(!session_id()) {
        session_start();
    }
}

function openid4vp_login_form_button()
{
    $options = new OpenID4VP_Admin_Options();
    if ($options->loginUrl !== '') {
        $login_with_wallet_button = sprintf('
            <div>
                <a style="margin:1em auto;" rel="nofollow" class="button" href="%s">Login with Personal Wallet</a>
                <div style="clear:both;"></div>
            </div>',
            esc_url($options->loginUrl)
        );

        echo wp_kses_post($login_with_wallet_button);
    }
}

/**
 * Enqueues our scripts
 */
function enqueue_my_scripts() {
    // Enqueue our script, using the jQuery dependency
    wp_enqueue_script( 'ajax-script',OPENID4VP_PLUGIN_URL . '/build/pollStatus.js', array( 'jquery' ));
    wp_localize_script(
        'ajax-script',
        'my_ajax_obj',
        array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
        )
    );
}

function enqueue_org_wallet_scripts() {
    // Enqueue our script, using the jQuery dependency
    wp_enqueue_script( 'ajax-script', OPENID4VP_PLUGIN_URL . '/build/presentationExchangeOrgWallet/submitPresentationRequest.js', array( 'jquery' ));
    wp_localize_script(
        'ajax-script',
        'my_ajax_obj',
        array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
        )
    );
}

add_action( 'init', 'create_block_openid4vp_block_init' );
// Display the Login button at the top of the WP Login form
add_action('login_message', 'openid4vp_login_form_button');
// Add an action to call our script enqueuing function
add_action( 'wp_enqueue_script', 'enqueue_my_scripts' );

add_action( 'wp_ajax_nopriv_poll_status_ajax', 'ajax_poll_status' );
add_action( 'wp_ajax_poll_status_ajax', 'ajax_poll_status' );

add_action( 'wp_ajax_nopriv_presentation_exchange_ajax', 'ajax_org_wallet_presentation_exchange');
add_action( 'wp_ajax_presentation_exchange_ajax', 'ajax_org_wallet_presentation_exchange');

/**
 * Gets the number of votes from the database, and sends it
 * back to the client script as JSON.
 */
function ajax_poll_status() {
    // Get the 'current' data that the AJAX call sent
    if ( isset( $_POST['current'] ) ) {
        $current = $_POST['current'];
    }

    $options = new OpenID4VP_Admin_Options();

    $response = wp_remote_get( $_SESSION['presentationStatusUri'], array(
        'headers' => array('Content-Type' => 'application/json', $_SESSION['authenticationHeaderName'] => $_SESSION['authenticationToken'] ),
        'timeout'     => 45,
        'redirection' => 5,
        'blocking'    => true
    ));

    $body = wp_remote_retrieve_body($response);
    //$result = json_decode( $body );
    $successUrl = null;
    if ( json_decode( $body ) != null ) {
        $successUrl = $_SESSION['successUrl'];

        $presentationResponse = json_decode( $body, true);

        error_log($body);

        $credentialClaims = $presentationResponse['verified_data']['credential_claims'];
        foreach ($credentialClaims as $credential) {
            if (empty($_SESSION['presentationResponse'])) {
                $_SESSION['presentationResponse'] = (object)[];
            }
            $_SESSION['presentationResponse'][$credential['id']] = $credential;
        }

        if ($options->loginUrl == $current) {
            $jsonAttributeNames = explode(".", $options->usernameAttribute);

            $result = $_SESSION['presentationResponse'];
            foreach ($jsonAttributeNames as &$name) {
                $result = $result[$name];
            }
            // $arr is now array(2, 4, 6, 8)
            unset($name);

            if (username_exists($result) == true) {
                $user = get_user_by('login', $result);
                $user_id = $user->ID;
            }

            if (!empty($user_id)) {
                // set current user session
                wp_clear_auth_cookie();
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);

                if (is_user_logged_in()) {
                    $successUrl = admin_url();
                }
            }
        }

        $_SESSION['authenticationHeaderName'] = null;
        $_SESSION['authenticationToken'] = null;
        $_SESSION['successUrl'] = null;
    }

    // Prepare the data to sent back to Javascript
    $data = array(
        'presentationStatusUri'    =>    $_SESSION['presentationStatusUri'],
        'configuredSuccessUrl' => $_SESSION['successUrl'],
        'successUrl' => $successUrl
    );

    // Encode it as JSON and send it back
    echo json_encode( $data );
    die();
}

/**
 * Gets the number of votes from the database, and sends it
 * back to the client script as JSON.
 */
function ajax_org_wallet_presentation_exchange() {
    // Get the 'walletUrl' data that the AJAX call sent
    if ( isset( $_POST['walletUrl'] ) ) {
        $walletUrl = $_POST['walletUrl'];
    }
    $openidEndpoint = $_SESSION['openidEndpoint'];
    $authenticationHeaderName = $_SESSION['authenticationHeaderName'];
    $authenticationToken = $_SESSION['authenticationToken'];
    $attributes = $_SESSION['queryAttributes'];

    $body = array('query_id' => $attributes['queryId'], 'request_uri_base' => $walletUrl);
    if (array_key_exists('requestUriMethod', $attributes)) {
        $body['request_uri_method'] = $attributes['requestUriMethod'];
    }
    if (array_key_exists('clientId', $attributes)) {
        $body['client_id'] = $attributes['clientId'];
    }
    if (array_key_exists('responseType', $attributes)) {
        $body['response_type'] = $attributes['responseType'];
    }
    if (array_key_exists('responseMode', $attributes)) {
        $body['response_mode'] = $attributes['responseMode'];
    }
    if (array_key_exists('successUrl', $attributes)) {
        $body['direct_post_response_redirect_uri'] = $attributes['successUrl'];
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

   $_SESSION['correlationId'] = $result->correlation_id;
   $_SESSION['presentationStatusUri'] = $result->status_uri;

   echo $body;

   die();
}

add_action( 'wp_ajax_my_tag_count', 'my_ajax_handler' );
function my_ajax_handler() {
   check_ajax_referer( 'title_example' );

   $title = wp_unslash( $_POST['title'] );

   update_user_meta( get_current_user_id(), 'title_preference', $title );

   $args = array(
      'tag' => $title,
   );

   $the_query = new WP_Query( $args );

   echo esc_html( $title ) . ' (' . $the_query->post_count . ') ';

   wp_die(); // all ajax handlers should die when finished
}
