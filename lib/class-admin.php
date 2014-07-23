<?php
require_once(plugin_dir_path(__FILE__) . 'class-cpt.php');
require_once(plugin_dir_path(__FILE__) . 'class-ct.php');

class admin {

    private $cpt_settings, $ct_settings, $cpt_helper_tabs;

    function __construct() {
        $this->cpt_settings = new CptSettings();
        $this->ct_settings = new CtSettings();

        $this->cpt_helper_tabs = array(
            'cpt' => array(
                'menu_title' => __('Post Type'),
                'menu_slug' => 'cpt'
            ),
            'ct' => array(
                'menu_title' => __('Taxonomy'),
                'menu_slug' => 'ct'
        ));

        add_action('admin_menu', array($this, 'add_cpt_plugin_page')); //Add menu inside setting for Generator
    }

    /**
     * Add options page
     */
    function add_cpt_plugin_page() {
        add_options_page('CPT Generator', 'CPT Generator', 'manage_options', 'cpt-generator', array($this, 'cpt_create_admin_page'));
    }

    function cpt_create_admin_page() {
        wp_register_style('cpt_switch_style', plugins_url('cpt') . '/css/switch.css');
        wp_register_style('cpt_style', plugins_url('cpt') . '/css/style.css');
        wp_enqueue_style('cpt_switch_style');
        wp_enqueue_style('cpt_style');

        global $pagenow;
        ?><div class="wrap rt-cpt-wrapper">
            <h2 class="rt_option_title"><?php _e('Custom Post Type and Taxonomy', 'cpt-helper'); ?></h2>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <?php
                        /* Show Tabs */
                        if (( 'options-general.php' == $pagenow || 'settings.php' == $pagenow ) && isset($_GET['tab'])) {
                            $this->cpt_admin_page_tabs($_GET['tab']);
                        } else {
                            $this->cpt_admin_page_tabs('cpt');
                        }
                        /* Fetch Page Content */
                        $current = isset($_GET['tab']) ? $_GET['tab'] : 'cpt';
                        if (( 'options-general.php' == $pagenow || 'settings.php' == $pagenow ) && isset($_GET['page'])) {
                            switch ($current) {
                                case 'cpt' :
                                    $this->cpt_settings->add_cpt_section();
                                    break;
                                case 'ct' :
                                    $this->ct_settings->add_ct_section();
                                    break;
                            }
                        }
                        ?>

                    </div> <!-- End of #post-body-content -->
                    <div id="postbox-container-1" class="postbox-container"><?php //default_admin_sidebar();                    ?>
                    </div> <!-- End of #postbox-container-1 -->
                </div> <!-- End of #post-body -->
            </div> <!-- End of #poststuff -->
        </div> <!-- End of .wrap .rt-cpt-wrapper -->
        <?php
    }

    /**
     * Create tab with links
     * 
     * @param type $current current tab
     */
    function cpt_admin_page_tabs($current = 'cpt') {
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($this->cpt_helper_tabs as $tab => $name) {
            $class = ( $tab == $current ) ? ' nav-tab-active' : '';
            echo '<a class="nav-tab' . $class . '" href="?page=cpt-generator&tab=' . $name['menu_slug'] . '">' . $name['menu_title'] . '</a>';
        }
        echo '</h2>';
    }

}
