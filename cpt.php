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
    private $options, $dir;

    /**
     * Start up
     */
    public function __construct() {
        $this->options = get_option('cpt_option');
        $this->dir = plugins_url('', __FILE__);

        add_action('admin_menu', array($this, 'add_plugin_page')); //Add menu inside setting for Generator
        add_action('admin_init', array($this, 'page_init')); // Set setting page for CPT Generator
        add_action('init', array($this, 'register_cpt'));//Register all CPT added using this plugin
    }

    function register_cpt() {
        if ($this->options) {
            foreach ($this->options as $value) {
                register_post_type($value['post_type'], array(
                    'labels' => array(
                        'name' => $value['labels_name'],
                        'singular_name' => $value['labels_singular_name']
                    ),
                    'public' => $value['public'],
                    'has_archive' => $value['has_archive'],
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
        wp_register_style('cpt_generator_style', $this->dir . '/css/switch.css');
        wp_enqueue_style('cpt_generator_style');
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
 <?php if ($this->options) { ?>
<table class="wp-list-table widefat fixed pages">
    <thead>
    <th class="manage-column">Post Name</th>
    <th class="manage-column">Public</th>
    <th class="manage-column">Label</th>
    </thead>
    <tbody>
       <?php  foreach ($this->options as $value) { ?>
        <tr>
            <td class="post-title page-title column-title">
                <strong><a><?php echo $value['post_type']; ?></a></strong>
                <div class="row-actions">
                    <span class="edit"><a href="" title="Edit this item">Edit</a> | </span>
                    <span class="inline hide-if-no-js"><a href="#" class="editinline" title="Edit this item inline">Quick&nbsp;Edit</a> | </span>
                    <span class="trash"><a class="submitdelete" title="Move this item to the Trash" href="">Trash</a> | </span>
                    <span class="view"><a href="" title="View “Hello world!”" rel="permalink">View</a></span>
                </div>
            </td>
            <td><?php echo $value['public']; ?></td>
            <td><?php echo $value['labels_name']; ?></td>
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
                'labels_name', // ID
                'Label Name', // Title 
                array($this, 'labels_name_callback'), // Callback
                'cpt-generator', // Page
                'cpt_setting_section' // Section           
        );

        add_settings_field(
                'labels_singular_name', // ID
                'Singular Name', // Title 
                array($this, 'labels_singular_name_callback'), // Callback
                'cpt-generator', // Page
                'cpt_setting_section' // Section           
        );

        add_settings_field(
                'public', // ID
                'Public', // Title 
                array($this, 'public_callback'), // Callback
                'cpt-generator', // Page
                'cpt_setting_section' // Section           
        );

        add_settings_field(
                'has_archive', // ID
                'Has Archive', // Title 
                array($this, 'has_archive_callback'), // Callback
                'cpt-generator', // Page
                'cpt_setting_section' // Section           
        );

        add_settings_field(
                'supports', // ID
                'Supports Type', // Title 
                array($this, 'support_callback'), // Callback
                'cpt-generator', // Page
                'cpt_setting_section' // Section           
        );
    }

    /**
     * Sanitize each option setting field as needed
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
            $cpt_option[$cpt_amount]["labels_name"] = isset($_POST["labels_name"]) ? $_POST["labels_name"] : "";
            $cpt_option[$cpt_amount]["labels_singular_name"] = isset($_POST["labels_singular_name"]) ? $_POST["labels_singular_name"] : "";
            $cpt_option[$cpt_amount]["public"] = isset($_POST["public"]) ? true : false;
            $cpt_option[$cpt_amount]["has_archive"] = isset($_POST["has_archive"]) ? true : false;
            $cpt_option[$cpt_amount]["supports"] = isset($_POST["supports"]) ? $_POST["supports"] : array('');

//            var_dump($cpt_option);
//            die();

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
     * Print the General Section info
     */
    public function print_section_info() {
        print 'Enter your general cpt settings below:';
    }

    /**
     * Post type name option callback
     */
    public function post_type_callback() {
        ?>
        <input type="text" name="post_type" required="" />
        <?php
    }

    /**
     * Post label name option callback
     */
    public function labels_name_callback() {
        ?>
        <input type="text" name="labels_name" />
        <?php
    }

    /**
     * Post label sigular name option callback
     */
    public function labels_singular_name_callback() {
        ?>
        <input type="text" name="labels_singular_name" />
        <?php
    }

    /**
     * Public visibility option callback
     */
    public function public_callback() {
        ?>

        <div class="onoffswitch">
            <input type="checkbox" name="public" id="public" class="onoffswitch-checkbox" value="true" checked="" >
            <label class="onoffswitch-label" for="public">
                <span class="onoffswitch-inner"></span>
                <span class="onoffswitch-switch"></span>
            </label>
        </div>

        <?php
    }

    /**
     * Achive option callback
     */
    public function has_archive_callback() {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" name="has_archive" id="has_archive" class="onoffswitch-checkbox" value="true" >
            <label class="onoffswitch-label" for="has_archive">
                <span class="onoffswitch-inner"></span>
                <span class="onoffswitch-switch"></span>
            </label>
        </div>

        <?php
    }

    /**
     * Supports option callbacks
     */
    public function support_callback() {
        ?>
        <input type="checkbox"  name="supports[]"  value="title"/> Title<br/>
        <input type="checkbox"  name="supports[]"  value="editor"/> Editor<br/>
        <input type="checkbox"  name="supports[]"  value="author"/> Author<br/>
        <input type="checkbox"  name="supports[]"  value="thumbnail"/> Thumbnail <br/>
        <input type="checkbox"  name="supports[]"  value="excerpt"/> Excerpt<br/>
        <input type="checkbox"  name="supports[]"  value="trackbacks"/> Trackbacks<br/>
        <input type="checkbox"  name="supports[]"  value="custom-fields"/> Custom Field<br/>
        <input type="checkbox"  name="supports[]"  value="comments"/> Comments<br/>
        <input type="checkbox"  name="supports[]"  value="revisions"/> Revisions<br/>
        <input type="checkbox"  name="supports[]"  value="page-attributes"/> Page Attributes<br/>
        <input type="checkbox"  name="supports[]"  value="post-formats"/> Post Formats<br/>
        <?php
    }

}

if (is_admin())
    $cpt_settings_page = new CptSettingsPage();
