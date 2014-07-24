<?php

/**
 * Class for custom taxonomy plugin support
 */
class CtSettings {

    public $options, $dir, $editval;

    public function __construct() {
        $this->options = get_option('ct_option');
        $this->dir = plugins_url('', __FILE__);
        $this->editval;
// add_action('admin_menu', array($this, 'add_ct_plugin_page')); //Add menu inside setting for Generator
        add_action('admin_init', array($this, 'ct_page_init')); // Set setting page for CT Generator

        /*
         * delete or edit ct
         */
        if (isset($_GET["tab"]) && $_GET["tab"] == "ct") {
            if (isset($_GET["editmode"]) && $_GET["editmode"] == "delete") {
                unset($this->options[$_GET["ct_name"]]);
                update_option("ct_option", $this->options);
                header("location: options-general.php?page=cpt-generator&tab=ct");
            } elseif (isset($_GET["editmode"]) && $_GET["editmode"] == "edit") {
                $this->editval = $this->options[$_GET["ct_name"]];
            }
        }

        add_action('init', array($this, 'register_ct')); //Register all CT added using this plugin
    }

    /**
     * Register all custom taxonomy from available options
     */
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
                    'show_in_nav_menus' => $value['ct_show_in_nav_menus'],
                    'show_tagcloud' => $value['ct_show_tagcloud'],
                    'show_admin_column' => $value['ct_show_admin_column'],
                    'update_count_callback' => '_update_post_term_count',
                    'query_var' => $value['ct_query_var'],
                    'rewrite' => array('slug' => $value['ct_name']),
                );

                register_taxonomy($value['ct_name'], $value['post_types'], $args);
            }
        }
    }

    /**
     * Add options page
     */
    public function add_ct_plugin_page() {
        add_options_page('CT Generator', 'CT Generator', 'manage_options', 'cpt-generator', array($this, 'create_ct_page'));
    }

    /**
     * Options page callback
     */
    public function add_ct_section() {

        $this->options = get_option('ct_option');
        $this->add_ct_field();
        if (isset($_GET["editmode"]) && !isset($_GET["settings-updated"])) {
            ?>
            <a class="add-new-h2" href="options-general.php?page=cpt-generator&tab=ct">All Taxonomy</a><hr/>
            <div class="postbox">
                <h3 class="hndle">
                    <span><?php _e('Generate Taxonomy'); ?></span>
                </h3>
                <form method="post" action="options.php">
                    <div class="inside">
                        <?php
                        wp_nonce_field('save_options_action', 'save_options_nonce_field');
                        settings_fields('ct_option_group');
                        do_settings_sections('cpt-generator');
                        submit_button();
                        ?>
                    </div>
                </form>
            </div>
        <?php } else {
            ?>
            <a class="add-new-h2" href="options-general.php?page=cpt-generator&tab=ct&editmode=add">Add New</a><hr/>
            <table class="wp-list-table widefat fixed pages">
                <thead>
                <th class="manage-column">Name</th>

                <th class="manage-column">Label</th>
            </thead>
            <tbody>
                <?php
                if ($this->options) {
                    foreach ($this->options as $value) {
                        ?>
                        <tr>
                            <td class="post-title page-title column-title">
                                <strong><a><?php echo $value['ct_name']; ?></a></strong>
                                <div class="row-actions">
                                    <span class="edit"><a href="options-general.php?page=cpt-generator&tab=ct&editmode=edit&ct_name=<?php echo $value['ct_name']; ?>" title="Edit this item">Edit</a> | </span>

                                    <span class="trash"><a class="submitdelete" href="options-general.php?page=cpt-generator&tab=ct&editmode=delete&ct_name=<?php echo $value['ct_name']; ?>" title="Move this item to the Trash" href="">Trash</a> | </span>

                                </div>
                            </td>
                            <td><?php echo $value['ct_singular_name']; ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr class="no-items"><td class="colspanchange" colspan="2">No custom taxonomy found created using this plugin.</td></tr>
                    <?php
                }
                ?>
            </tbody>
            </table>
            <?php
        }
    }

    /**
     * Register and add settings ,sections and fields
     */
    public function ct_page_init() {
        register_setting(
                'ct_option_group', // Option group
                'ct_option', // Option name
                array($this, 'sanitize_ct_options')
        );
    }

    public function add_ct_field() {
        add_settings_section(
                'ct_setting_section', // ID
                'General Settings', // Title
                array($this, 'general_section_info'), // Callback
                'cpt-generator' // Page
        );

        add_settings_field(
                'ct_name', 'Label Name', array($this, 'name_callback'), 'cpt-generator', 'ct_setting_section'
        );

        add_settings_field(
                'ct_singular_name', 'Singular Name', array($this, 'singular_name_callback'), 'cpt-generator', 'ct_setting_section'
        );

        add_settings_field(
                'ct_hierarchical', 'Hierarchical', array($this, 'hierarchical_callback'), 'cpt-generator', 'ct_setting_section'
        );

        add_settings_field(
                'ct_show_ui', 'Show UI', array($this, 'show_ui_callback'), 'cpt-generator', 'ct_setting_section'
        );

        add_settings_field(
                'ct_show_in_nav_menus', 'Show In Nav Menu', array($this, 'ct_show_in_nav_menus_callback'), 'cpt-generator', 'ct_setting_section'
        );

        add_settings_field(
                'ct_show_tagcloud', 'Show Tag Cloud', array($this, 'ct_show_tagcloud_callback'), 'cpt-generator', 'ct_setting_section'
        );
        add_settings_field(
                'ct_show_admin_column', 'Show Admin Column', array($this, 'show_admin_column_callback'), 'cpt-generator', 'ct_setting_section'
        );

        add_settings_field(
                'ct_query_var', 'Query Var', array($this, 'ct_query_var_callback'), 'cpt-generator', 'ct_setting_section'
        );

        add_settings_field(
                'post_types', 'Post Type', array($this, 'post_types_callback'), 'cpt-generator', 'ct_setting_section'
        );
    }

    /**
     * Sanitize each option setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize_ct_options($input) {
        if (!empty($_POST) && check_admin_referer('save_options_action', 'save_options_nonce_field')) {
            $ct_option = get_option('ct_option'); // Get the current options from the db
            $ct_option[$_POST["ct_name"]]["ct_name"] = sanitize_text_field($_POST["ct_name"]);
            $ct_option[$_POST["ct_name"]]["ct_singular_name"] = sanitize_text_field(!empty($_POST["ct_singular_name"]) ? $_POST["ct_singular_name"] : $_POST["ct_name"]);
            $ct_option[$_POST["ct_name"]]["ct_hierarchical"] = isset($_POST["ct_hierarchical"]) ? true : false;
            $ct_option[$_POST["ct_name"]]["ct_show_ui"] = isset($_POST["ct_show_ui"]) ? true : false;
            $ct_option[$_POST["ct_name"]]["ct_show_in_nav_menus"] = isset($_POST["ct_show_in_nav_menus"]) ? true : false;
            $ct_option[$_POST["ct_name"]]["ct_show_tagcloud"] = isset($_POST["ct_show_tagcloud"]) ? true : false;
            $ct_option[$_POST["ct_name"]]["ct_show_admin_column"] = isset($_POST["ct_show_admin_column"]) ? true : false;
            $ct_option[$_POST["ct_name"]]["ct_query_var"] = isset($_POST["ct_query_var"]) ? true : false;
            $ct_option[$_POST["ct_name"]]["post_types"] = isset($_POST["post_types"]) ? $_POST["post_types"] : array('');

            return $ct_option;
        }
    }

    /**
     * Print the General Section info
     */
    public function general_section_info() {
        print 'Enter your general ct settings below:';
    }

    /**
     * Label name option callback
     */
    public function name_callback() {
        ?>
        <input type="text" name="ct_name" required="" value="<?php echo isset($this->editval) ? $this->editval['ct_name'] : "" ?>" />
        <?php
    }

    /**
     * Taxonomy singular name option callback
     */
    public function singular_name_callback() {
        ?>
        <input type="text" name="ct_singular_name" value="<?php echo isset($this->editval) ? $this->editval['ct_singular_name'] : "" ?>"/>
        <?php
    }

    /**
     * Is Hierarchical option callback
     */
    public function hierarchical_callback() {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" name="ct_hierarchical" id="ct_hierarchical" class="onoffswitch-checkbox" value="true" <?php isset($this->editval) ? checked($this->editval['ct_hierarchical'], true) : ""; ?>>
            <label class="onoffswitch-label" for="ct_hierarchical">
                <span class="onoffswitch-inner">
                    <span class="onoffswitch-active"><span class="onoffswitch-switch">YES</span></span>
                    <span class="onoffswitch-inactive"><span class="onoffswitch-switch">NO</span></span>
                </span>
            </label>
        </div>

        <?php
    }

    /**
     * Show UI option callback
     */
    public function show_ui_callback() {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" name="ct_show_ui" id="ct_show_ui" class="onoffswitch-checkbox" value="true" <?php echo isset($this->editval) ? checked($this->editval['ct_show_ui'], true) : "checked"; ?>>
            <label class="onoffswitch-label" for="ct_show_ui">
                <span class="onoffswitch-inner">
                    <span class="onoffswitch-active"><span class="onoffswitch-switch">YES</span></span>
                    <span class="onoffswitch-inactive"><span class="onoffswitch-switch">NO</span></span>
                </span>
            </label>
        </div>

        <?php
    }

    /**
     * Show UI option callback
     */
    public function ct_show_in_nav_menus_callback() {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" name="ct_show_in_nav_menus" id="ct_show_in_nav_menus" class="onoffswitch-checkbox" value="true" <?php echo isset($this->editval) ? checked($this->editval['ct_show_in_nav_menus'], true) : "checked"; ?>>
            <label class="onoffswitch-label" for="ct_show_in_nav_menus">
                <span class="onoffswitch-inner">
                    <span class="onoffswitch-active"><span class="onoffswitch-switch">YES</span></span>
                    <span class="onoffswitch-inactive"><span class="onoffswitch-switch">NO</span></span>
                </span>
            </label>
        </div>

        <?php
    }

    /**
     * Show UI option callback
     */
    public function ct_show_tagcloud_callback() {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" name="ct_show_tagcloud" id="ct_show_tagcloud" class="onoffswitch-checkbox" value="true" <?php echo isset($this->editval) ? checked($this->editval['ct_show_tagcloud'], true) : "checked"; ?>>
            <label class="onoffswitch-label" for="ct_show_tagcloud">
                <span class="onoffswitch-inner">
                    <span class="onoffswitch-active"><span class="onoffswitch-switch">YES</span></span>
                    <span class="onoffswitch-inactive"><span class="onoffswitch-switch">NO</span></span>
                </span>
            </label>
        </div>

        <?php
    }

    /**
     * Show in admin column option callback
     */
    public function show_admin_column_callback() {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" name="ct_show_admin_column" id="ct_show_admin_column" class="onoffswitch-checkbox" value="true" <?php echo isset($this->editval) ? checked($this->editval['ct_show_admin_column'], true) : "checked"; ?>>
            <label class="onoffswitch-label" for="ct_show_admin_column">
                <span class="onoffswitch-inner">
                    <span class="onoffswitch-active"><span class="onoffswitch-switch">YES</span></span>
                    <span class="onoffswitch-inactive"><span class="onoffswitch-switch">NO</span></span>
                </span>
            </label>
        </div>

        <?php
    }

    /**
     * Show in admin column option callback
     */
    public function ct_query_var_callback() {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" name="ct_query_var" id="ct_query_var" class="onoffswitch-checkbox" value="true" <?php echo isset($this->editval) ? checked($this->editval['ct_query_var'], true) : "checked"; ?>>
            <label class="onoffswitch-label" for="ct_query_var">
                <span class="onoffswitch-inner">
                    <span class="onoffswitch-active"><span class="onoffswitch-switch">YES</span></span>
                    <span class="onoffswitch-inactive"><span class="onoffswitch-switch">NO</span></span>
                </span>
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
