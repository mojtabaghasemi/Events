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