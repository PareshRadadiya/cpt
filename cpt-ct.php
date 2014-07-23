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
    require_once(plugin_dir_path(__FILE__) . 'lib/class-cpt.php');
    require_once(plugin_dir_path(__FILE__) . 'lib/class-ct.php');

    $cpt_settings = new CptSettings();
    $ct_settings = new CtSettings();

    $cpt_helper_tabs;

    $cpt_helper_tabs = array(
        'cpt' => array(
            'menu_title' => __('CPT', 'nginx-helper'),
            'menu_slug' => 'cpt'
        ),
        'ct' => array(
            'menu_title' => __('CT', 'nginx-helper'),
            'menu_slug' => 'ct'
    ));
    //include class file for custom post type and custom taxonomy


    add_action('admin_menu', 'add_cpt_plugin_page'); //Add menu inside setting for Generator

    /**
     * Add options page
     */
    function add_cpt_plugin_page() {
        add_options_page('CPT Generator', 'CPT Generator', 'manage_options', 'cpt-generator', 'cpt_create_admin_page');
    }

    //instance of custom post type class
    //instance of custom taxonomy class
    //  $ct_settings = new CtSettings();

    function cpt_create_admin_page() {
        wp_register_style('cpt_switch_style', plugin_dir_path(__FILE__) . '/css/switch.css');
        wp_register_style('cpt_style', plugin_dir_path(__FILE__) . '/css/style.css');
        wp_enqueue_style('cpt_switch_style');
        wp_enqueue_style('cpt_style');

        global $pagenow, $cpt_settings, $ct_settings;
        ?><div class="wrap rt-nginx-wrapper">
            <h2 class="rt_option_title"><?php _e('Custom Post Type and Taxonomy', 'nginx-helper'); ?></h2>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                         
                        <?php
                        /* Show Tabs */
                        if (( 'options-general.php' == $pagenow || 'settings.php' == $pagenow ) && isset($_GET['tab'])) {
                            cpt_admin_page_tabs($_GET['tab']);
                        } else {
                            cpt_admin_page_tabs('cpt');
                        }

                        /* Fetch Page Content */
                        $current = isset($_GET['tab']) ? $_GET['tab'] : 'cpt';
                        if (( 'options-general.php' == $pagenow || 'settings.php' == $pagenow ) && isset($_GET['page'])) {
                            switch ($current) {
                                case 'cpt' :
                                   
                                   
                                    $cpt_settings->add_field();
                                    $cpt_settings->add_cpt_section();
                                   
                                    break;
                                case 'ct' :
                                   
                                      $ct_settings->add_field();
                                      $ct_settings->add_ct_section();
                                  
                                    break;
                            }
                        }
                        ?>
            
                    </div> <!-- End of #post-body-content -->
                    <div id="postbox-container-1" class="postbox-container"><?php //default_admin_sidebar();         ?>
                    </div> <!-- End of #postbox-container-1 -->
                </div> <!-- End of #post-body -->
            </div> <!-- End of #poststuff -->
        </div> <!-- End of .wrap .rt-nginx-wrapper -->
        <?php
    }

    /**
     * Create tab with links
     * 
     * @param type $current current tab
     */
    function cpt_admin_page_tabs($current = 'cpt') {
        global $cpt_helper_tabs;
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($cpt_helper_tabs as $tab => $name) {
            $class = ( $tab == $current ) ? ' nav-tab-active' : '';
            echo '<a class="nav-tab' . $class . '" href="?page=cpt-generator&tab=' . $name['menu_slug'] . '">' . $name['menu_title'] . '</a>';
        }
        echo '</h2>';
    }

}
    
