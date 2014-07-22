<?php

/**
 * Plugin Name:  CPT and CT Generator
 * Plugin URI: https://github.com/PareshRadadiya/cpt
 * Description: CPT and CT lets you create Custom Post Types and custom Taxonomies in a user friendly way
 * Version: 1.0
 * Author: rtCamp
 * Author URI: https://rtcamp.com/
 * License: A "Slug" license name e.g. GPL2
 */
if (is_admin()) {
    //include class file for custom post type and custom taxonomy
    require_once(plugin_dir_path(__FILE__) . 'class-cpt.php');
    require_once(plugin_dir_path(__FILE__) . 'class-ct.php');
    
    //instance of custom post type class
    $cpt_settings = new CptSettings();
    
    //instance of custom taxonomy class
    $ct_settings = new CtSettings();
}
   