<?php

/**
 * Plugin Name:  CPT and CT Generator
 * Plugin URI: https://github.com/PareshRadadiya/cpt
 * Description: CPT and CT lets you create Custom Post Types and custom Taxonomies in a user friendly way
 * Version: 1.0
 * Author: rtCamp
 * Author URI: https://rtcamp.com/
 * License: A "Slug" license name e.g. GPL2
 * Text Domain: cpt-generator
 */
if (is_admin()) {
    require_once(plugin_dir_path(__FILE__) . 'lib/class-admin.php');
    require_once(plugin_dir_path(__FILE__) . 'lib/class-helper.php');
    $cpt_helper=new Helper();
    $admin = new Admin();
}
