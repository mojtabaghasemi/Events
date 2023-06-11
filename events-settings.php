<?php
// Add a settings page for the plugin
add_action( 'admin_menu', 'events_add_settings_page' );

function events_add_settings_page() {
    add_options_page( 'My Plugin Settings', 'My Plugin Settings', 'manage_options', 'my-plugin-settings', 'events_settings_page' );
}

function events_settings_page() {
    ?>
    <div class="wrap">
        <h2>My Plugin Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields( 'my-plugin-settings-group' ); ?>
            <?php do_settings_sections( 'my-plugin-settings-group' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Some Setting</th>
                    <td><input type="text" name="events_some_setting" value="<?php echo esc_attr( get_option( 'events_some_setting' ) ); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register the settings for the plugin
add_action( 'admin_init', 'events_register_settings' );

function events_register_settings() {
    register_setting( 'my-plugin-settings-group', 'events_some_setting' );
}