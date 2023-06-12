<?php
/**
 * Plugin Name: Events
 * Plugin URI: https://sourcecity.ir/
 * Description: This is a plugin.
 * Version: 1.0.0
 * Author: Mojtaba	Ghasemi
 * Author URI: https://sourcecity.ir/
 * License: GPL2
 */

// Add a filter to modify the title of the blog
add_filter( 'wp_title', 'my_plugin_modify_title', 10, 1 );

function my_plugin_modify_title( $title ) {
    return $title . ' | رویدادها';
}

add_action( 'init', 'events_post_type' );

function events_post_type() {
    $labels = array(
        'name'               => _x( 'سمینار/وبینار/کارگاه', 'post type general name', 'textdomain' ),
        'singular_name'      => _x( 'سمینار/وبینار/کارگاه', 'post type singular name', 'textdomain' ),
        'menu_name'          => _x( 'سمینار/وبینار/کارگاه', 'admin menu', 'textdomain' ),
        'name_admin_bar'     => _x( 'سمینار/وبینار/کارگاه', 'add new on admin bar', 'textdomain' ),
        'add_new'            => _x( 'ایجاد جدید', 'My Custom Post Type', 'textdomain' ),
        'add_new_item'       => __( 'ایجاد جدید', 'textdomain' ),
       'new_item'           => __( 'ایجاد جدید', 'textdomain' ),
        'edit_item'          => __( 'ویرایش', 'textdomain' ),
        'view_item'          => __( 'مشاهده', 'textdomain' ),
        'all_items'          => __( 'همه', 'textdomain' ),
        'search_items'       => __( 'جستجو', 'textdomain' ),
        'parent_item_colon'  => __( 'والد:', 'textdomain' ),
        'not_found'          => __( 'موردی از سمینار/وبینار/کارگاه پیدا نشد.', 'textdomain' ),
        'not_found_in_trash' => __( 'موردی از سمینار/وبینار/کارگاه پیدا نشد..', 'textdomain' )
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'event' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array( 'title', 'editor','author', 'thumbnail', 'excerpt', 'comments' )
    );

    register_post_type( 'event', $args );
  
  register_taxonomy( 'categories', array('event'), array(
        'hierarchical' => true, 
        'label' => 'دسته بندی ها', 
        'singular_label' => 'دسته بندی ها', 
        'rewrite' => array( 'slug' => 'categories', 'with_front'=> false )
        )
    );

    register_taxonomy_for_object_type( 'categories', 'event' );
}

function event_settings_page() {
    // تنظیمات صفحه
    $page_title = __( 'تنظیمات رویدادها', 'text-domain' );
    $menu_title = __( 'تنظیمات', 'text-domain' );
    $capability = 'manage_options';
    $menu_slug = 'event-settings';
    $callback = 'event_settings_callback';

    add_submenu_page(
        'edit.php?post_type=event',
        $page_title,
        $menu_title,
        $capability,
        $menu_slug,
        $callback
    );
}
add_action( 'admin_menu', 'event_settings_page' );
function event_merchant_code_callback() {
    $merchant_code = get_option( 'event_merchant_code', '' );
    ?>
    <input type="text" name="event_merchant_code" value="<?php echo esc_attr( $merchant_code ); ?>" />
    <?php
}

function event_enable_gateway_callback() {
    $enable_gateway = get_option( 'event_enable_gateway', false );
    ?>
    <label>
        <input type="checkbox" name="event_enable_gateway" value="1" <?php checked( $enable_gateway ); ?> />
        <?php _e( 'فعالسازی درگاه', 'text-domain' ); ?>
    </label>
    <?php
}

function event_settings_callback() {
    ?>
    <div class="wrap">
        <h1><?php _e( 'Event Settings', 'text-domain' ); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'event_settings_group' ); ?>
            <?php do_settings_sections( 'event_settings_page' ); ?>
            <?php submit_button( __( 'ذخیره تنظیمات', 'text-domain' ), 'primary', 'submit', true ); ?>
        </form>
    </div>
    <script>
        jQuery( document ).ready( function( $ ) {
            $( '#event_disable_button' ).on( 'click', function( event ) {
                event.preventDefault();
                $( 'input[name="event_enable_gateway"]' ).prop( 'checked', false );
            } );
            $( '#event_enable_button' ).on( 'click', function( event ) {
                event.preventDefault();
                $( 'input[name="event_enable_gateway"]' ).prop( 'checked', true );
            } );
        } );
    </script>
    <?php
}

function event_settings_init() {
    register_setting(
        'event_settings_group',
        'event_merchant_code',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );
    register_setting(
        'event_settings_group',
        'event_enable_gateway',
        array(
            'type' => 'boolean',
            'default' => false
        )
    );

    // اضافه کردن بخش‌های تنظیمات به صفحه تنظیمات
    add_settings_section(
        'event_settings_section',
        __( 'تنظیمات رویدادها', 'text-domain' ),
        'event_settings_section_callback',
        'event_settings_page'
    );
    add_settings_field(
        'event_merchant_code',
        __( 'مرچنت کد', 'text-domain' ),
        'event_merchant_code_callback',
        'event_settings_page',
        'event_settings_section'
    );
    add_settings_field(
        'event_enable_gateway',
        __( 'وضعیت', 'text-domain' ),
        'event_enable_gateway_callback',
        'event_settings_page',
        'event_settings_section'
    );
}
add_action( 'admin_init', 'event_settings_init' );