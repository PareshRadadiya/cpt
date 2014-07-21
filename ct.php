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
class CtSettingsPage {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options, $dir, $editval;

    /**
     * Start up
     */
    public function __construct() {
        $this->options = get_option('ct_option');
        $this->dir = plugins_url('', __FILE__);
        $this->editval;
        add_action('admin_menu', array($this, 'add_plugin_page')); //Add menu inside setting for Generator
        add_action('admin_init', array($this, 'page_init')); // Set setting page for CT Generator

        /*
         * delete cpt
         */
        if (isset($_GET["page"]) && $_GET["page"] == "ct-generator") {
            if (isset($_GET["editmode"]) && $_GET["editmode"] == "delete") {
                unset($this->options[$_GET["ct_name"]]);
                update_option("ct_option", $this->options);
            } elseif (isset($_GET["editmode"]) && $_GET["editmode"] == "edit") {
                $this->editval = $this->options[$_GET["ct_name"]];
            }
        }



        add_action('init', array($this, 'register_ct')); //Register all CT added using this plugin
    }

    function register_ct() {

        if ($this->options) {
            foreach ($this->options as $value) {
                $labels = array(
                    'name' => _x($value['ct_name'], 'taxonomy general name'),
                    'singular_name' => _x($value['ct_singular_name'], 'taxonomy singular name')
                );

                $args = array(
                    'hierarchical' => $value['ct_hierarchical'],
                    'labels' => $labels,
                    'show_ui' => $value['ct_show_ui'],
                    'show_admin_column' => $value['ct_show_admin_column'],
                    'update_count_callback' => '_update_post_term_count',
                    'query_var' => true,
                    'rewrite' => array('slug' => $value['ct_name']),
                );

                register_taxonomy($value['ct_name'], $value['post_types'], $args);
            }
        }
    }

    /**
     * Add options page
     */
    public function add_plugin_page() {
// This page will be under "Settings"
        add_options_page(
                'CT Generator', 'CT Generator', 'manage_options', 'ct-generator', array($this, 'create_admin_page')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page() {
// Set class property

        $this->options = get_option('ct_option');
        wp_register_style('ct_generator_style', $this->dir . '/css/switch.css');
        wp_enqueue_style('ct_generator_style');
        ?>
        <div class="wrap">

            <h2>CT Generator</h2>           
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields('ct_option_group');
                do_settings_sections('ct-generator');
                submit_button();
                ?>

            </form>
        </div>
        <?php if ($this->options) { ?>
            <table class="wp-list-table widefat fixed pages">
                <thead>
                <th class="manage-column">Name</th>

                <th class="manage-column">Label</th>
            </thead>
            <tbody>
                <?php foreach ($this->options as $value) { ?>
                    <tr>
                        <td class="post-title page-title column-title">
                            <strong><a><?php echo $value['ct_name']; ?></a></strong>
                            <div class="row-actions">
                                <span class="edit"><a href="<?php echo $_SERVER['REQUEST_URI']; ?>&editmode=edit&ct_name=<?php echo $value['ct_name']; ?>" title="Edit this item">Edit</a> | </span>

                                <span class="trash"><a class="submitdelete" href="<?php echo $_SERVER['REQUEST_URI']; ?>&editmode=delete&ct_name=<?php echo $value['ct_name']; ?>" title="Move this item to the Trash" href="">Trash</a> | </span>

                            </div>
                        </td>
                        <td><?php echo $value['ct_singular_name']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
            </table>
            <?php
        }
    }

    /**
     * Register and add settings ,sections and fields
     */
    public function page_init() {
        register_setting(
                'ct_option_group', // Option group
                'ct_option', // Option name
                array($this, 'sanitize')
        );

        add_settings_section(
                'ct_setting_section', // ID
                'General Settings', // Title
                array($this, 'print_section_info'), // Callback
                'ct-generator' // Page
        );

        add_settings_field(
                'ct_name', // ID
                'Label Name', // Title 
                array($this, 'ct_name_callback'), // Callback
                'ct-generator', // Page
                'ct_setting_section' // Section           
        );

        add_settings_field(
                'ct_singular_name', // ID
                'Singular Name', // Title 
                array($this, 'ct_singular_name_callback'), // Callback
                'ct-generator', // Page
                'ct_setting_section' // Section           
        );

        add_settings_field(
                'ct_hierarchical', // ID
                'Hierarchical', // Title 
                array($this, 'ct_hierarchical_callback'), // Callback
                'ct-generator', // Page
                'ct_setting_section' // Section           
        );
        
        add_settings_field(
                'ct_show_ui', // ID
                'Show UI', // Title 
                array($this, 'ct_show_ui_callback'), // Callback
                'ct-generator', // Page
                'ct_setting_section' // Section           
        );
        add_settings_field(
                'ct_show_admin_column', // ID
                'Show Admin Column', // Title 
                array($this, 'ct_show_admin_column_callback'), // Callback
                'ct-generator', // Page
                'ct_setting_section' // Section           
        );

        add_settings_field(
                'post_types', // ID
                'Post Type', // Title 
                array($this, 'post_types_callback'), // Callback
                'ct-generator', // Page
                'ct_setting_section' // Section           
        );
    }

    /**
     * Sanitize each option setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input) {
        $ct_option = get_option('ct_option'); // Get the current options from the db (Edit 
// Do Add Logic
        $ct_option[$_POST["ct_name"]]["ct_name"] = $_POST["ct_name"];
        $ct_option[$_POST["ct_name"]]["ct_singular_name"] = $_POST["ct_singular_name"];
        $ct_option[$_POST["ct_name"]]["ct_hierarchical"] = isset($_POST["ct_hierarchical"]) ? true : false;
        $ct_option[$_POST["ct_name"]]["ct_show_ui"] = isset($_POST["ct_show_ui"]) ? true : false;
        $ct_option[$_POST["ct_name"]]["ct_show_admin_column"] = isset($_POST["ct_show_admin_column"]) ? true : false;
        $ct_option[$_POST["ct_name"]]["post_types"] = isset($_POST["post_types"]) ? $_POST["post_types"] : array('');

 
        return $ct_option;
    }

    /**
     * Print the General Section info
     */
    public function print_section_info() {
        print 'Enter your general ct settings below:';
    }

    /**
     * Post type name option callback
     */
    public function ct_name_callback() {
        ?>
        <input type="text" name="ct_name" required="" value="<?php echo isset($this->editval) ? $this->editval['ct_name'] : "" ?>" />
        <?php
    }

    /**
     * Post label name option callback
     */
    public function ct_singular_name_callback() {
        ?>
        <input type="text" name="ct_singular_name" required="" value="<?php echo isset($this->editval) ? $this->editval['ct_singular_name'] : "" ?>"/>
        <?php
    }

    /**
     * Achive option callback
     */
    public function ct_hierarchical_callback() {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" name="ct_hierarchical" id="ct_hierarchical" class="onoffswitch-checkbox" value="true" <?php isset($this->editval) ? checked($this->editval['ct_hierarchical'], true) : ""; ?>>
            <label class="onoffswitch-label" for="ct_hierarchical">
                <span class="onoffswitch-inner"></span>
                <span class="onoffswitch-switch"></span>
            </label>
        </div>

        <?php
    }
    /**
     * Achive option callback
     */
    public function ct_show_ui_callback() {
        ?>
                <div class="onoffswitch">
                    <input type="checkbox" name="ct_show_ui" id="ct_show_ui" class="onoffswitch-checkbox" value="true" <?php echo isset($this->editval) ? checked($this->editval['ct_show_ui'], true) : "checked"; ?>>
                    <label class="onoffswitch-label" for="ct_show_ui">
                        <span class="onoffswitch-inner"></span>
                        <span class="onoffswitch-switch"></span>
                    </label>
                </div>

        <?php
    }
    /**
     * Achive option callback
     */
    public function ct_show_admin_column_callback() {
        ?>
                <div class="onoffswitch">
                    <input type="checkbox" name="ct_show_admin_column" id="ct_show_admin_column" class="onoffswitch-checkbox" value="true" <?php echo isset($this->editval) ? checked($this->editval['ct_show_admin_column'], true) : "checked"; ?>>
                    <label class="onoffswitch-label" for="ct_show_admin_column">
                        <span class="onoffswitch-inner"></span>
                        <span class="onoffswitch-switch"></span>
                    </label>
                </div>

        <?php
    }

    /**
     * Supports option callbacks
     */
    public function post_types_callback() {
        $post_types = get_post_types();
        foreach ($post_types as $post_type) {
            ?>
            <input type="checkbox"  name="post_types[]"  value="<?php echo $post_type; ?>" <?php isset($this->editval) ? checked(in_array($post_type, $this->editval["post_types"]), true) : ""; ?> /><?php echo $post_type; ?><br/>
            <?php
        }
    }

}

if (is_admin())
    $ct_settings_page = new CtSettingsPage();
