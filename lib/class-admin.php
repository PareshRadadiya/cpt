<?php
require_once(plugin_dir_path(__FILE__) . 'class-cpt.php');
require_once(plugin_dir_path(__FILE__) . 'class-ct.php');

class Admin {

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

        add_action('admin_enqueue_scripts', array($this, 'cpt_admin_scripts'));
        add_action('admin_print_styles', array($this, 'cpt_admin_styles'));
    }

    /**
     * Add options page
     */
    function add_cpt_plugin_page() {
        add_options_page('CPT Generator', 'CPT Generator', 'manage_options', 'cpt-generator', array($this, 'cpt_create_admin_page'));
    }

    /**
     * Setting page add callback
     * @global type $pagenow
     */
    function cpt_create_admin_page() {
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
                        ?>

                        <?php
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
                    <div id="postbox-container-1" class="postbox-container"><?php $this->cpt_sidebar(); ?>
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

    /*
     * Enqueuing style and script
     */

    function cpt_admin_scripts() {
        global $pagenow;
        if ('options-general.php' == $pagenow || 'settings.php' == $pagenow) {
            wp_register_style('cpt_switch_style', plugins_url('cpt') . '/css/switch.css');
            wp_enqueue_style('cpt_switch_style');
            wp_enqueue_media();
            wp_register_script('cpt-uploader', plugins_url('cpt') . '/js/uploader.js', array('jquery'));
            wp_enqueue_script('cpt-uploader');
        }
    }

    function cpt_admin_styles() {
        global $pagenow;
        if ('options-general.php' == $pagenow || 'settings.php' == $pagenow) {
            wp_register_style('cpt_style', plugins_url('cpt') . '/css/style.css');
            wp_enqueue_style('cpt_style');
        }
    }

    /**
     * Sidebar panel
     */
    function cpt_sidebar() {
        ?>
        <div class = "postbox" id = "support">
            <h3 class = "hndle">
                <span><?php _e('Need Help?', 'cpt-helper');
        ?></span>
            </h3>
            <div class="inside">
                <p><?php printf(__('Please use our <a href="%s">free support forum</a>.', 'cpt-helper'), 'http://rtcamp.com/support/forum/wordpress-cpt/'); ?></p>
            </div>
        </div>

        <div class="postbox" id="social">
            <h3 class="hndle">
                <span><?php _e('Getting Social is Good', 'cpt-helper'); ?></span>
            </h3>
            <div style="text-align:center;" class="inside">
                <a class="cpt-helper-facebook" title="<?php _e('Become a fan on Facebook', 'cpt-helper'); ?>" target="_blank" href="http://www.facebook.com/rtCamp.solutions/"><i class="fa fa-facebook"></i></a>
                <a class="cpt-helper-twitter" title="<?php _e('Follow us on Twitter', 'cpt-helper'); ?>" target="_blank" href="https://twitter.com/rtcamp/"><i class="fa fa-twitter"></i></a>
                <a class="cpt-helper-gplus" title="<?php _e('Add to Circle', 'cpt-helper'); ?>" target="_blank" href="https://plus.google.com/110214156830549460974/posts"><i class="fa fa-google-plus"></i></a>
                <a class="cpt-helper-rss" title="<?php _e('Subscribe to our feeds', 'cpt-helper'); ?>" target="_blank" href="http://feeds.feedburner.com/rtcamp/"><i class="fa fa-rss"></i></a>
            </div>
        </div>

        <div class="postbox" id="useful-links">
            <h3 class="hndle">
                <span><?php _e('Useful Links', 'cpt-helper'); ?></span>
            </h3>
            <div class="inside">
                <ul role="list">
                    <li role="listitem">
                        <a href="https://rtcamp.com/wordpress-cpt/" title="<?php _e('WordPress-Nginx Solutions', 'cpt-helper'); ?>"><?php _e('WordPress-Nginx Solutions', 'cpt-helper'); ?></a>
                    </li>
                    <li role="listitem">
                        <a href="https://rtcamp.com/services/wordPress-themes-design-development/" title="<?php _e('WordPress Theme Devleopment', 'cpt-helper'); ?>"><?php _e('WordPress Theme Devleopment', 'cpt-helper'); ?></a>
                    </li>
                    <li role="listitem">
                        <a href="http://rtcamp.com/services/wordpress-plugins/" title="<?php _e('WordPress Plugin Development', 'cpt-helper'); ?>"><?php _e('WordPress Plugin Development', 'cpt-helper'); ?></a>
                    </li>
                    <li role="listitem">
                        <a href="http://rtcamp.com/services/custom-wordpress-solutions/" title="<?php _e('WordPress Consultancy', 'cpt-helper'); ?>"><?php _e('WordPress Consultancy', 'cpt-helper'); ?></a>
                    </li>
                    <li role="listitem">
                        <a href="https://rtcamp.com/easyengine/" title="<?php _e('easyengine (ee)', 'cpt-helper'); ?>"><?php _e('easyengine (ee)', 'cpt-helper'); ?></a>
                    </li>        
                </ul>
            </div>
        </div>

        <div class="postbox" id="latest_news">
            <div title="<?php _e('Click to toggle', 'cpt-helper'); ?>" class="handlediv"><br /></div>
            <h3 class="hndle"><span><?php _e('Latest News', 'cpt-helper'); ?></span></h3>
            <div class="inside"></div>
        </div>
        <?php
    }

}
