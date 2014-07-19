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
class CptSettingsPage {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct() {
// delete_option( 'cpt_option' );
        $this->options = get_option('cpt_option');
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        add_action('init', array($this, 'register_cpt'));
    }

    function register_cpt() {
        if ($this->options) {
            foreach ($this->options as $value) {
                register_post_type($value['post_type'], array(
                    'labels' => array(
                        'name' => $value['post_type'],
                        'singular_name' => $value['post_type']
                    ),
                    'public' => true,
                    'has_archive' => FALSE,
                    'rewrite' => array('slug' => $value['post_type']),
                    'supports' => $value['supports']
                        )
                );
            }
        }
    }

    /**
     * Add options page
     */
    public function add_plugin_page() {
// This page will be under "Settings"
        add_options_page(
                'CPT Generator', 'CPT Generator', 'manage_options', 'cpt-generator', array($this, 'create_admin_page')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page() {
// Set class property
        $this->options = get_option('cpt_option');
//  var_dump($this->options);
        ?>
        <div class="wrap">

            <h2>CPT Generator</h2>           
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields('cpt_option_group');
                do_settings_sections('cpt-generator');
                submit_button();
                ?>
                <input type="hidden" name="editmode" value="add" />

            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init() {
        register_setting(
                'cpt_option_group', // Option group
                'cpt_option', // Option name
                array($this, 'sanitize')
        );

        add_settings_section(
                'cpt_setting_section', // ID
                'General Settings', // Title
                array($this, 'print_section_info'), // Callback
                'cpt-generator' // Page
        );

        add_settings_field(
                'post_type', // ID
                'Post Type', // Title 
                array($this, 'post_type_callback'), // Callback
                'cpt-generator', // Page
                'cpt_setting_section' // Section           
        );

        add_settings_field(
                'support', // ID
                'Support Type', // Title 
                array($this, 'support_callback'), // Callback
                'cpt-generator', // Page
                'cpt_setting_section' // Section           
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input) {

        $cpt_option = get_option('cpt_option'); // Get the current options from the db (Edit 
// this and return the full modified version)
        $editmode = $_POST['editmode'];  // add, edit or delete     
        $cpt_amount = count($cpt_option);
        if (get_option('cpt_option')) {
            $cpt_amount++;
        }



        if ($editmode == 'add') {

// Do Add Logic
            $cpt_option[$cpt_amount]["post_type"] = $_POST["post_type"];
            $cpt_option[$cpt_amount]["supports"] = $_POST["supports"];
// $cpt_option[$cpt_amount]["title"] = $_POST["title"];

            return $cpt_option;
        } elseif ($editmode == 'edit') {

// Do Edit Logic

            return $cpt_option;
        } elseif ($editmode == 'delete') {

// Do Delete Logic

            return $cpt_option;
        }

        return $input; // only triggered if none of the above are called
    }

    /**
     * Print the Section text
     */
    public function print_section_info() {
        print 'Enter your general cpt settings below:';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function post_type_callback() {
        ?>
        <input type="text" id="post_type" name="post_type" />
        <?php
    }

    public function support_callback() {
//  $x = count($this->options);
        ?>
        <input type="checkbox"  name="supports[]"  value="title"/>
        <input type="checkbox"  name="supports[]"  value="editor"/>
        <input type="checkbox"  name="supports[]"  value="author"/>
        <input type="checkbox"  name="supports[]"  value="thumbnail"/>
        <input type="checkbox"  name="supports[]"  value="excerpt"/>
        <input type="checkbox"  name="supports[]"  value="trackbacks"/>
        <input type="checkbox"  name="supports[]"  value="custom-fields"/>
        <input type="checkbox"  name="supports[]"  value="comments"/>
        <input type="checkbox"  name="supports[]"  value="revisions"/>
        <input type="checkbox"  name="supports[]"  value="page-attributes"/>
        <input type="checkbox"  name="supports[]"  value="post-formats"/>
        <?php
    }

}

if (is_admin())
    $cpt_settings_page = new CptSettingsPage();
