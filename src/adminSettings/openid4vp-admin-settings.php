<?php

// ABSPATH prevent public user to directly access your .php files through URL.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class OpenID4VP_Admin_Settings {
    private $admin_options;

    private $option_name;

    private $admin_settings_fields = array(
        'openidEndpoint',
        'authenticationHeaderName',
        'authenticationToken',
        'loginUrl',
        'usernameAttribute',
        'redirectUserOrigin'
    );

    public function __construct(OpenID4VP_Admin_Options $admin_options) {
        $this->admin_options = $admin_options;
        $this->option_name = $this->admin_options->get_option_name();
    }

    public static function init(OpenID4VP_Admin_Options $admin_options) {
        $admin_settings = new self($admin_options);
        add_action('admin_init', [$admin_settings, 'admin_init']);
        add_action('admin_menu', [$admin_settings, 'add_page']);
    }

    public function get_admin_settings_field() {
        return $this->admin_settings_fields;
    }

    public function admin_init() {
        register_setting('openid4vp_options', $this->option_name, [$this, 'validate']);
    }

    public function add_page() {
        add_options_page('Universal OID4VP', 'Universal OID4VP', 'manage_options', 'openid4vp_settings', [$this, 'create_settings_page']);
    }

    public function create_settings_page() {
        ?>
        <div class="openid4vp-login-settings container-fluid">
            <div class="admin-settings-header">
                <h1>Universal OID4VP Settings</h1>
            </div>
            <div class="admin-settings-inside">
                <p>This plugin is provides the Universal OID4VP flow both to login and to request information from users</p>
                <div id="accordion">
                    <h3 id="sso-configuration">Configuration</h3>
                    <div class="row">
                        <form method="post" action="options.php">
                            <?php settings_fields('openid4vp_options'); ?>
                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row">OpenID4VP Endpoint</th>
                                    <td>
                                        <input type="text" class="regular-text" name="<?php echo esc_html($this->option_name); ?>[openidEndpoint]" min="10"
                                            value="<?php echo esc_html($this->admin_options->openidEndpoint); ?>"/>
                                        <p class="description">Example: https://wallet.acc.credenco.com</p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Authentication header</th>
                                    <td>
                                        <input type="text" class="regular-text" name="<?php echo esc_html($this->option_name); ?>[authenticationHeaderName]" min="10"
                                            value="<?php echo esc_html($this->admin_options->authenticationHeaderName); ?>"/>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Authentication token</th>
                                    <td>
                                        <input type="text" class="regular-text" name="<?php echo esc_html($this->option_name); ?>[authenticationToken]" min="10"
                                            value="<?php echo esc_html($this->admin_options->authenticationToken); ?>"/>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Login url</th>
                                    <td>
                                        <input type="text" class="regular-text" name="<?php echo esc_html($this->option_name); ?>[loginUrl]" min="10"
                                            value="<?php echo esc_html($this->admin_options->loginUrl); ?>"/>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Username attribute</th>
                                    <td>
                                        <input type="text" class="regular-text" name="<?php echo esc_html($this->option_name); ?>[usernameAttribute]" min="10"
                                            value="<?php echo esc_html($this->admin_options->usernameAttribute); ?>"/>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Redirect user to original page</th>
                                    <td>
                                        <input type="checkbox"
                                            name="<?php echo esc_html($this->option_name); ?>[redirectUserOrigin]"
                                            value="1" <?php echo esc_html($this->admin_options->redirectUserOrigin) == 1 ? 'checked="checked"' : ''; ?> />
                                        <p class="description">By default, users will be redirected to Homepage. If the user used the <em>wp-login.php</em> form, they will be redirected to Dashboard.</p>
                                    </td>
                                </tr>
                            </table>
                    </div>
                    <hr />
                    <p class="submit">
                        <input type="submit" class="button-primary" value="<?php esc_html_e('Save Changes', 'openid4vp-plugin') ?>"/>
                    </p>
                    </form>
                </div>
            </div>
        </div>
        <div style="clear:both;"></div>
        <?php
    }

    public function validate(array $input) {
        $options = array();
        $admin_settings = $this->get_admin_settings_field();

		foreach ( $admin_settings as $admin_settings_field ) {
			if ( isset( $input[ $admin_settings_field ] ) ) {
				$options[ $admin_settings_field ] = sanitize_text_field( trim( $input[ $admin_settings_field ] ) );
			} else {
				$options[ $admin_settings_field ] = '';
			}
		}

		return $options;
    }
}

OpenID4VP_Admin_Settings::init(new OpenID4VP_Admin_Options());
