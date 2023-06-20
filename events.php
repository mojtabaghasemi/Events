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
include "Payment.php";

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

function event_merchant_id_callback() {
    $value = get_option('event_merchant_id');
    echo '<input type="text" name="event_merchant_id" value="' . esc_attr($value) . '">';
}

function event_terminal_id_callback() {
    $value = get_option('event_terminal_id');
    echo '<input type="text" name="event_terminal_id" value="' . esc_attr($value) . '">';
}

function event_transaction_key_callback() {
    $value = get_option('event_transaction_key');
    echo '<input type="text" name="event_transaction_key" value="' . esc_attr($value) . '">';
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
        <h1><?php _e( 'تنظیمات رویدادها', 'text-domain' ); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'event_settings_group' ); ?>
            <?php do_settings_sections( 'event_settings_page' ); ?>
            <?php submit_button( __( 'ذخیره تنظیمات', 'text-domain' ), 'primary', 'submit', true ); ?>
        </form>
    </div>
    <?php
}

function event_settings_init() {
    register_setting(
        'event_settings_group',
        'event_transaction_key',
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

    // ثبت تنظیمات شماره پذیرنده
    register_setting(
        'event_settings_group',
        'event_merchant_id',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

// ثبت تنظیمات شماره ترمینال
    register_setting(
        'event_settings_group',
        'event_terminal_id',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    // اضافه کردن بخش‌های تنظیمات به صفحه تنظیمات
    add_settings_section(
        'event_settings_section',
        __( 'تنظیمات حساب بانک ملی', 'text-domain' ),
        'event_settings_section_callback',
        'event_settings_page'
    );
    add_settings_field(
        'event_enable_gateway',
        __( 'وضعیت', 'text-domain' ),
        'event_enable_gateway_callback',
        'event_settings_page',
        'event_settings_section'
    );

    add_settings_field(
        'merchant_id',
        __( 'شماره پذیرنده', 'text-domain' ),
        'event_merchant_id_callback',
        'event_settings_page',
        'event_settings_section'
    );

    add_settings_field(
        'terminal_id',
        __( 'شماره ترمینال', 'text-domain' ),
        'event_terminal_id_callback',
        'event_settings_page',
        'event_settings_section'
    );

    add_settings_field(
        'event_merchant_code',
        __( 'کلید تراکنش', 'text-domain' ),
        'event_transaction_key_callback',
        'event_settings_page',
        'event_settings_section'
    );

}
add_action( 'admin_init', 'event_settings_init' );


//function create_event_orders_table() {
//    global $wpdb;
//    $table_name = $wpdb->prefix . 'event_orders';
//    $charset_collate = $wpdb->get_charset_collate();
//
//    $sql = "CREATE TABLE $table_name (
//        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
//        user_id BIGINT(20) UNSIGNED NOT NULL,
//        event_id BIGINT(20) UNSIGNED NOT NULL,
//        amount BIGINT(20) UNSIGNED NULL,
//        status VARCHAR(50) NULL,
//        order_date DATETIME NULL,
//        PRIMARY KEY (id),
//        FOREIGN KEY (event_id) REFERENCES {$wpdb->prefix}posts(ID),
//        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID)
//    ) $charset_collate;";
//
//    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
//    dbDelta( $sql );
//}
//
//add_action('init', 'create_event_orders_table');
//
//function create_event_payments_table() {
//    global $wpdb;
//    $table_name = $wpdb->prefix . 'event_payments';
//    $charset_collate = $wpdb->get_charset_collate();
//
//    $sql = "CREATE TABLE $table_name (
//        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
//        user_id BIGINT(20) UNSIGNED NOT NULL,
//        order_id BIGINT(20) UNSIGNED NOT NULL,
//        amount BIGINT(20) UNSIGNED NULL,
//        system_trace_no VARCHAR(50) NULL,
//        retrival_ref_no VARCHAR(50) NULL,
//        status VARCHAR(50) NULL,
//        payment_date DATETIME NULL,
//        PRIMARY KEY (id),
//        FOREIGN KEY (order_id) REFERENCES {$wpdb->prefix}event_orders(ID),
//        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID)
//    ) $charset_collate;";
//
//    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
//    dbDelta( $sql );
//}
//add_action('init', 'create_event_payments_table');

add_action( 'admin_post_event_process_payment', 'event_process_payment' );
add_action( 'admin_post_nopriv_event_process_payment', 'event_process_payment' );
function event_process_payment() {
    // Verify nonce
    check_admin_referer( 'event_process_payment', 'event_process_payment_nonce' );

    // Get post ID and payment amount
    $post_id = intval( $_POST['post_id'] );
    $amount = floatval( $_POST['amount'] );

    $user_id = get_current_user_id();

    if ( $user_id == 0 ) {
        wp_redirect( '/my-account' );
        exit;
    } else {
        global $wpdb;
        $order_status = 'pending';
        $table_name = $wpdb->prefix.'event_orders';

        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d AND status = %s AND event_id = %d",
            $user_id,
            $order_status,
            $post_id
        );
        $result = $wpdb->get_row($query);
        $order_id = $result->id;

        if (!$result) {
            $data = [
                'user_id' => $user_id,
                'event_id' => $post_id,
                'amount' => $amount,
                'status' => $order_status,
                'order_date' => current_time('mysql')
            ];
            $result = $wpdb->insert($table_name, $data);
            $order_id = $wpdb->insert_id;
        }

        if ($result) {
            $gateway_enabled = get_option( 'event_enable_gateway' );
            if ($gateway_enabled) {
                $gateway_transaction_key = get_option('event_transaction_key');
                $gateway_merchant_id = get_option('event_merchant_id');
                $gateway_terminal_id = get_option('event_terminal_id');
                $redirect_url =  plugin_dir_url( __FILE__ ) . 'callback-payment.php';

                $payment_class = new Payment();
                $payment_class
                    ->transaction_key($gateway_transaction_key)
                    ->merchant_id($gateway_merchant_id)
                    ->terminal_id($gateway_terminal_id)
                    ->redirect_url($redirect_url);

                $payment_class->redirect_to_bank($amount, $order_id);
            }else{
                echo "درگاه پرداخت غیرفعال میباشد";
            }
        } else {
            echo 'Error adding record.';
        }
    }
}
// Add new tab to My Account menu
function order_events_endpoint() {
    add_rewrite_endpoint( 'order-events', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'order_events_endpoint' );

function order_events_endpoint_link( $items ) {
    $icon_color = get_option( 'primary_color' );
    $items['order-events'] = ' رویدادهای من';
    return $items;
}
add_filter( 'woocommerce_account_menu_items', 'order_events_endpoint_link' );

function order_events_display() {
    require 'Template/account-events.php';
}
add_action( 'woocommerce_account_order-events_endpoint', 'order_events_display' );

add_action( 'wp_head', 'customstyle' ,99999);

function customstyle(){
    echo '<style>li.woocommerce-MyAccount-navigation-link--order-events a:before {content: "\f005";font-family: FontAwesome;color: #1597e5;margin-left: 2px}</style>';
}


// افزودن یک دکمه جدید به زیر منو رویدادها
function event_payments_submenu_page() {
    add_submenu_page(
        'edit.php?post_type=event',
        __('سفارشات', 'textdomain'),
        __('سفارشات', 'textdomain'),
        'manage_options',
        'event-payments',
        'event_payments_callback'
    );
}
add_action('admin_menu', 'event_payments_submenu_page');

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class Event_Payments_Table extends WP_List_Table {
    private $transactions;

    public function set_transactions($transactions) {
        $this->transactions = $transactions;
    }

    public function get_columns() {
        return array(
            'id' => __('ID', 'textdomain'),
            'display_name' => __('نام و نام خانوادگی', 'textdomain'),
            'post_title' => __('عنوان رویداد', 'textdomain'),
            'order_id' => __('شماره سفارش', 'textdomain'),
            'system_trace_no' => __('شماره پیگیری', 'textdomain'),
            'amount' => __('مبلغ', 'textdomain'),
            'payment_date' => __('تاریخ', 'textdomain'),
            'status' => __('وضعیت سفارش', 'textdomain')
        );
    }

    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $per_page = 20;
        $current_page = $this->get_pagenum();
        $total_items = count($this->transactions);

        $this->items = array_slice($this->transactions, ($current_page-1)*$per_page, $per_page);

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items/$per_page)
        ));
    }

    public function get_sortable_columns() {
        return array(
            'id' => array('id', false),
            'post_title' => array('post_title', false),
            'order_id' => array('order_id', false),
            'display_name' => array('display_name', false),
            'system_trace_no' => array('system_trace_no', false),
            'amount' => array('amount', false),
            'payment_date' => array('payment_date', false),
            'status' => array('status', false)
        );
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'id':
            case 'order_id':
            case 'post_title':
            case 'display_name':
            case 'system_trace_no':
                return $item->$column_name;
            case 'amount':
                return number_format($item->amount) . ' ریال ';
            case 'payment_date':
                return date('F j, Y', strtotime($item->payment_date));
            case 'status':
                return $item->status == "pending" ? "در انتضار پرداخت" : "پرداخت شده";
            default:
                return '';
        }
    }
}

function event_payments_callback() {

    global $wpdb;
    $table_payments = $wpdb->prefix . 'event_payments';
    $table_orders = $wpdb->prefix . 'event_orders';
    $table_users = $wpdb->prefix . 'users';
    $table_posts = $wpdb->prefix . 'posts';

    $transactions = $wpdb->get_results(
        "SELECT o.*, p.*, u.display_name, pst.post_title
    FROM $table_orders o
    JOIN $table_payments p ON p.order_id = o.id
    JOIN $table_users u ON o.user_id = u.ID
    JOIN $table_posts pst ON o.event_id = pst.ID"
    );

    // Display payments table
    $payments_table = new Event_Payments_Table();
    $payments_table->set_transactions($transactions);
    $payments_table->prepare_items();
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php esc_html_e('سفارشات و پرداخت ها', 'textdomain'); ?></h1>
        <hr class="wp-header-end">
        <form method="get">
            <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>">
            <?php $payments_table->search_box(__('Search', 'textdomain'), 'event-payments-search'); ?>
            <?php $payments_table->display(); ?>
        </form>
    </div>
    <?php
}