<?php

/**
 * Plugin Name: CPT Demo
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: A brief description of the Plugin.
 * Version: The Plugin's Version Number, e.g.: 1.0
 * Author: Name Of The Plugin Author
 * Author URI: http://URI_Of_The_Plugin_Author
 * License: A "Slug" license name e.g. GPL2
 */
add_action('init', 'register_plugin_cpt');

function register_plugin_cpt() {
    $label = array(
        'name' => __('CPT Generator'),
        'singular_name' => __('CPT Generator')
    );

    register_post_type('cpt_plugin', array(
        'labels' => $label,
        'public' => false,
        'show_ui' => true,
        'menu_position' => 80,
        'exclude_from_search' => true,
            )
    );
}
