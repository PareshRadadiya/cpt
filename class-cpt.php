<?php

/**
 * Class for custom post type support
 */
class CptSettings {

    private $options, $dir, $editval;

    public function __construct() {
        $this->options = get_option('cpt_option');
        $this->dir = plugins_url('', __FILE__);
        $this->editval;
        add_action('admin_menu', array($this, 'add_cpt_plugin_page')); //Add menu inside setting for Generator
        add_action('admin_init', array($this, 'cpt_page_init')); // Set setting page for CPT Generator

        /*
         * delete or edit cpt 
         */
        if (isset($_GET["page"]) && $_GET["page"] == "cpt-generator") {
            if (isset($_GET["editmode"]) && $_GET["editmode"] == "delete") {
                unset($this->options[$_GET["cpt_post_type"]]);
                update_option("cpt_option", $this->options);
            } elseif (isset($_GET["editmode"]) && $_GET["editmode"] == "edit") {
                $this->editval = $this->options[$_GET["cpt_post_type"]];
            }
        }

        add_action('init', array($this, 'register_cpt')); //Register all CPT added using this plugin
    }

    /**
     * Register all custom post type from available options
     */
    function register_cpt() {
        if ($this->options) {
            foreach ($this->options as $value) {
                register_post_type($value['cpt_post_type'], array(
                    'labels' => array(
                        'name' => $value['labels_name'],
                        'singular_name' => $value['labels_singular_name']
                    ),
                    'public' => $value['public'],
                    'has_archive' => $value['has_archive'],
                    'rewrite' => array('slug' => $value['cpt_post_type']),
                    'supports' => $value['supports']
                        )
                );
            }
        }
    }

    /**
     * Add options page
     */
    public function add_cpt_plugin_page() {
        add_options_page('CPT Generator', 'CPT Generator', 'manage_options', 'cpt-generator', array($this, 'create_cpt_page'));
    }

    /**
     * Options page callback
     */
    public function create_cpt_page() {
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
            <?php foreach ($this->options as $value) { ?>
                    <tr>
                        <td class="post-title page-title column-title">
                            <strong><a><?php echo $value['cpt_post_type']; ?></a></strong>
                            <div class="row-actions">
                                <span class="edit"><a href="<?php echo $_SERVER['REQUEST_URI']; ?>&editmode=edit&cpt_post_type=<?php echo $value['cpt_post_type']; ?>" title="Edit this item">Edit</a> | </span>

                                <span class="trash"><a class="submitdelete" href="<?php echo $_SERVER['REQUEST_URI']; ?>&editmode=delete&cpt_post_type=<?php echo $value['cpt_post_type']; ?>" title="Move this item to the Trash" href="">Trash</a> | </span>

                            </div>
                        </td>
                        <td><?php echo $value['public'] ? "True" : "False"; ?></td>
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
    public function cpt_page_init() {
        register_setting(
                'cpt_option_group', // Option group
                'cpt_option', // Option name
                array($this, 'sanitize_cpt_options')
        );

        add_settings_section(
                'cpt_setting_section', // ID
                'General Settings', // Title
                array($this, 'general_section_info'), // Callback
                'cpt-generator' // Page
        );

        add_settings_field(
                'cpt_post_type',
                'Post Type', 
                array($this, 'post_type_callback'),
                'cpt-generator',
                'cpt_setting_section'          
        );

        add_settings_field(
                'labels_name',
                'Label Name',
                array($this, 'labels_name_callback'),
                'cpt-generator',
                'cpt_setting_section'         
        );

        add_settings_field(
                'labels_singular_name',
                'Singular Name',
                array($this, 'labels_singular_name_callback'),
                'cpt-generator',
                'cpt_setting_section'          
        );

        add_settings_field(
                'public',
                'Public', 
                array($this, 'public_callback'),
                'cpt-generator',
                'cpt_setting_section'          
        );

        add_settings_field(
                'has_archive',
                'Has Archive',
                array($this, 'has_archive_callback'),
                'cpt-generator',
                'cpt_setting_section'           
        );

        add_settings_field(
                'supports',
                'Supports Type', 
                array($this, 'support_callback'),
                'cpt-generator',
                'cpt_setting_section'           
        );
    }

    /**
     * Sanitize each option setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize_cpt_options($input) {
        $cpt_option = get_option('cpt_option'); // Get the current options from the db
        
        $cpt_option[$_POST["cpt_post_type"]]["cpt_post_type"] = $_POST["cpt_post_type"];
        $cpt_option[$_POST["cpt_post_type"]]["labels_name"] = $_POST["labels_name"];
        $cpt_option[$_POST["cpt_post_type"]]["labels_singular_name"] = isset($_POST["labels_singular_name"]) ? $_POST["labels_singular_name"] : $_POST["cpt_post_type"];
        $cpt_option[$_POST["cpt_post_type"]]["public"] = isset($_POST["public"]) ? true : false;
        $cpt_option[$_POST["cpt_post_type"]]["has_archive"] = isset($_POST["has_archive"]) ? true : false;
        $cpt_option[$_POST["cpt_post_type"]]["supports"] = isset($_POST["supports"]) ? $_POST["supports"] : array('');
        return $cpt_option;
    }

    /**
     * Print the General Section info
     */
    public function general_section_info() {
        print 'Enter your general cpt settings below:';
    }

    /**
     * Post type name option callback
     */
    public function post_type_callback() {
        ?>
        <input type="text" name="cpt_post_type" required="" value="<?php echo isset($this->editval) ? $this->editval['cpt_post_type'] : "" ?>" />
        <?php
    }

    /**
     * Post label name option callback
     */
    public function labels_name_callback() {
        ?>
        <input type="text" name="labels_name" required="" value="<?php echo isset($this->editval) ? $this->editval['labels_name'] : "" ?>"/>
        <?php
    }

    /**
     * Post label sigular name option callback
     */
    public function labels_singular_name_callback() {
        ?>
        <input type="text" name="labels_singular_name"  value="<?php echo isset($this->editval) ? $this->editval['labels_singular_name'] : "" ?>"/>
        <?php
    }

    /**
     * Public visibility option callback
     */
    public function public_callback() {
        ?>

        <div class="onoffswitch">
            <input type="checkbox" name="public" id="public" class="onoffswitch-checkbox" value="true" <?php echo isset($this->editval) ? checked($this->editval['public'], true) : "checked"; ?> >
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
            <input type="checkbox" name="has_archive" id="has_archive" class="onoffswitch-checkbox" value="true" <?php isset($this->editval) ? checked($this->editval['has_archive'], true) : ""; ?>>
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
        <input type="checkbox"  name="supports[]"  value="title"  <?php isset($this->editval) ? checked(in_array("title", $this->editval["supports"]), true) : ""; ?>/> Title<br/>
        <input type="checkbox"  name="supports[]"  value="editor"  <?php isset($this->editval) ? checked(in_array("editor", $this->editval["supports"]), true) : ""; ?>/> Editor<br/>
        <input type="checkbox"  name="supports[]"  value="author"  <?php isset($this->editval) ? checked(in_array("author", $this->editval["supports"]), true) : ""; ?>/> Author<br/>
        <input type="checkbox"  name="supports[]"  value="thumbnail"  <?php isset($this->editval) ? checked(in_array("thumbnail", $this->editval["supports"]), true) : ""; ?>/> Thumbnail <br/>
        <input type="checkbox"  name="supports[]"  value="excerpt"  <?php isset($this->editval) ? checked(in_array("excerpt", $this->editval["supports"]), true) : ""; ?>/> Excerpt<br/>
        <input type="checkbox"  name="supports[]"  value="trackbacks"  <?php isset($this->editval) ? checked(in_array("trackbacks", $this->editval["supports"]), true) : ""; ?>/> Trackbacks<br/>
        <input type="checkbox"  name="supports[]"  value="custom-fields"  <?php isset($this->editval) ? checked(in_array("custom-fields", $this->editval["supports"]), true) : ""; ?>/> Custom Field<br/>
        <input type="checkbox"  name="supports[]"  value="comments"  <?php isset($this->editval) ? checked(in_array("comments", $this->editval["supports"]), true) : ""; ?>/> Comments<br/>
        <input type="checkbox"  name="supports[]"  value="revisions"  <?php isset($this->editval) ? checked(in_array("revisions", $this->editval["supports"]), true) : ""; ?>/> Revisions<br/>
        <input type="checkbox"  name="supports[]"  value="page-attributes"  <?php isset($this->editval) ? checked(in_array("page-attributes", $this->editval["supports"]), true) : ""; ?>/> Page Attributes<br/>
        <input type="checkbox"  name="supports[]"  value="post-formats"  <?php isset($this->editval) ? checked(in_array("post-formats", $this->editval["supports"]), true) : ""; ?>/> Post Formats<br/>
        <?php
    }

}
