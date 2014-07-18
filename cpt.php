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
class MySettingsPage {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
    }

    /**
     * Add options page
     */
    public function add_plugin_page() {
        // This page will be under "Settings"
        add_options_page(
                'CPT Generator', 'CPT Generator', 'manage_options', 'my-setting-admin', array($this, 'create_admin_page')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page() {
        // Set class property
        $this->options = get_option('my_option_name');
        var_dump($this->options);
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>My Settings</h2>           
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields('my_option_group');
                do_settings_sections('my-setting-admin');
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
                'my_option_group', // Option group
                'my_option_name', // Option name
                array($this, 'sanitize')
        );

        add_settings_section(
                'setting_section_id', // ID
                'My Custom Settings', // Title
                array($this, 'print_section_info'), // Callback
                'my-setting-admin' // Page
        );

        add_settings_field(
                'id_number', // ID
                'ID Number', // Title 
                array($this, 'id_number_callback'), // Callback
                'my-setting-admin', // Page
                'setting_section_id' // Section           
        );

        add_settings_field(
                'title', 'Title', array($this, 'title_callback'), 'my-setting-admin', 'setting_section_id'
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input) {

//        $new_input = array();
//        if( isset( $input['id_number'] ) )
//            $new_input['id_number[]'] = absint( $input['id_number[]'] );
//
//        if( isset( $input['title[]'] ) )
//            $new_input['title[]'] = sanitize_text_field( $input['title[]'] );
//
//        return $new_input;

        $venues = get_option('my_option_name'); // Get the current options from the db (Edit 
        // this and return the full modified version)
        $editmode = $_POST['editmode'];  // add, edit or delete     
       $x = count($venues);
       $x++;
       
        if ($editmode == 'add') {

            // Do Add Logic
            $venues[$x]["id_number"]=$_POST["id_number"];
            $venues[$x]["title"]=$_POST["title"];
           
            return $venues;
        } elseif ($editmode == 'edit') {

            // Do Edit Logic

            return $venues;
        } elseif ($editmode == 'delete') {

            // Do Delete Logic

            return $venues;
        }

        return $input; // only triggered if none of the above are called
    }

    /**
     * Print the Section text
     */
    public function print_section_info() {
        print 'Enter your settings below:';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function id_number_callback() {
      //  $x = count($this->options);
        printf(
                '<input type="text" id="id_number" name="id_number" value="%s" />', isset($this->options['id_number']) ? esc_attr($this->options['id_number']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function title_callback() {
       // $x = count($this->options);
        printf(
                '<input type="text" id="title" name="title" value="%s" />', isset($this->options['title']) ? esc_attr($this->options['title']) : ''
        );
    }

}

if (is_admin())
    $my_settings_page = new MySettingsPage();
