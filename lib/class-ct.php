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
        // $this->utility = new Utility();

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
                    'name' => _x($value['ct_label_name'], 'taxonomy general name', 'cpt'),
                    'singular_name' => _x($value['ct_singular_name'], 'taxonomy singular name'),
                    'search_items' => __('Search ' . $value['ct_label_name'], 'cpt'),
                    'popular_items' => __('Popular ' . $value['ct_label_name'], 'cpt'),
                    'all_items' => __('All ' . $value['ct_label_name'], 'cpt'),
                    'parent_item' => __('Parent ' . $value['ct_singular_name'], 'cpt'),
                    'parent_item_colon' => __('Parent ' . $value['ct_singular_name'], 'cpt' . ':'),
                    'edit_item' => __('Edit ' . $value['ct_singular_name'], 'cpt'),
                    'update_item' => __('Update ' . $value['ct_singular_name'], 'cpt'),
                    'add_new_item' => __('Add New ' . $value['ct_singular_name'], 'cpt'),
                    'new_item_name' => __('New ' . $value['ct_singular_name'], 'cpt' . ' Name'),
                    'separate_items_with_commas' => __('Seperate ' . $value['ct_label_name'], 'cpt' . ' with commas'),
                    'add_or_remove_items' => __('Add or remove ' . $value['ct_label_name'], 'cpt'),
                    'choose_from_most_used' => __('Choose from the most used ' . $value['ct_label_name'], 'cpt'),
                    'menu_name' => __('All ' . $value['ct_label_name'], 'cpt')
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
     *  section for view all post and add new
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
                <th class="manage-column">Show UI</th>

            </thead>
            <tbody>
                <?php
                $index = 1;
                if ($this->options) {

                    foreach ($this->options as $value) {
                        ?>
                        <tr class="<?php echo ($index % 2) ? "alternate" : "" ?>">
                            <td class="post-title page-title column-title">
                                <strong><a href="options-general.php?page=cpt-generator&tab=ct&editmode=edit&ct_name=<?php echo $value['ct_name']; ?>"><?php echo $value['ct_name']; ?></a></strong>
                                <div class="row-actions">
                                    <span class="edit"><a href="options-general.php?page=cpt-generator&tab=ct&editmode=edit&ct_name=<?php echo $value['ct_name']; ?>" title="Edit this item">Edit</a> | </span>

                                    <span class="trash"><a class="submitdelete" href="options-general.php?page=cpt-generator&tab=ct&editmode=delete&ct_name=<?php echo $value['ct_name']; ?>" title="Move this item to the Trash" href="">Trash</a> | </span>

                                </div>
                            </td>
                            <td><?php echo $value['ct_label_name']; ?></td>
                            <td><?php echo $value['ct_show_ui'] ? "True" : "False"; ?></td>
                        </tr>
                        <?php
                        $index++;
                    }
                } else {
                    ?>
                    <tr class="no-items"><td class="colspanchange" colspan="3">No custom taxonomy found created using this plugin.</td></tr>
                    <?php
                }
                ?>
            </tbody>
            <tfoot>
            <th class="manage-column">Name</th>
            <th class="manage-column">Label</th>
            <th class="manage-column">Show UI</th>
            </tfoot>
            </table>
            <div class="tablenav bottom">		
                <div class="tablenav-pages one-page"><span class="displaying-num"><?php printf( _n( '%d item', '%d items', $index - 1, 'cpt-generator' ), $index - 1 ); ?></span>		
                    <br class="clear">
                </div>
            </div>
            <?php
        }
    }

    /**
     * Register and add settings, sections and fields
     */
    public function ct_page_init() {
        register_setting(
                'ct_option_group', // Option group
                'ct_option', // Option name
                array($this, 'sanitize_ct_options')
        );
    }

    /**
     * Add a new field to a ct setting section of a settings page
     */
    public function add_ct_field() {
        add_settings_section(
                'ct_setting_section', // ID
                'General Settings', // Title
                array($this, 'general_section_info'), // Callback
                'cpt-generator' // Page
        );

        add_settings_field(
                'ct_name', 'Taxonomy Name', array($this, 'display_textbox_option'), 'cpt-generator', 'ct_setting_section', array("field_name" => "ct_name")
        );

        add_settings_field(
                'ct_label_name', 'Label Name', array($this, 'display_textbox_option'), 'cpt-generator', 'ct_setting_section', array("field_name" => "ct_label_name")
        );

        add_settings_field(
                'ct_singular_name', 'Singular Name', array($this, 'display_textbox_option'), 'cpt-generator', 'ct_setting_section', array("field_name" => "ct_singular_name")
        );

        add_settings_field(
                'ct_hierarchical', 'Hierarchical', array($this, 'display_switch_option'), 'cpt-generator', 'ct_setting_section', array("field_name" => "ct_hierarchical")
        );

        add_settings_field(
                'ct_show_ui', 'Show UI', array($this, 'display_switch_option'), 'cpt-generator', 'ct_setting_section', array("field_name" => "ct_show_ui")
        );

        add_settings_field(
                'ct_show_in_nav_menus', 'Show In Nav Menu', array($this, 'display_switch_option'), 'cpt-generator', 'ct_setting_section', array("field_name" => "ct_show_in_nav_menus")
        );

        add_settings_field(
                'ct_show_tagcloud', 'Show Tag Cloud', array($this, 'display_switch_option'), 'cpt-generator', 'ct_setting_section', array("field_name" => "ct_show_tagcloud")
        );
        add_settings_field(
                'ct_show_admin_column', 'Show Admin Column', array($this, 'display_switch_option'), 'cpt-generator', 'ct_setting_section', array("field_name" => "ct_show_admin_column")
        );

        add_settings_field(
                'ct_query_var', 'Query Var', array($this, 'display_switch_option'), 'cpt-generator', 'ct_setting_section', array("field_name" => "ct_query_var")
        );


        $post_type_support = array();
        $post_types = get_post_types();
        foreach ($post_types as $post_type) {
             array_push($post_type_support,array("field_value" => "$post_type", "field_label" => "$post_type", "field_checked" => ""));
        }
        add_settings_field(
                'post_types', 'Post Type', array($this, 'display_checkbox_option'), 'cpt-generator', 'ct_setting_section',array("field_name" => "post_types", "field_values" => $post_type_support)
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
            $ct_option[$_POST["ct_name"]]["ct_name"] = esc_attr($_POST["ct_name"]);
            $ct_option[$_POST["ct_name"]]["ct_label_name"] = esc_attr(!empty($_POST["ct_label_name"]) ? $_POST["ct_label_name"] : $_POST["ct_name"]);
            $ct_option[$_POST["ct_name"]]["ct_singular_name"] = esc_attr(!empty($_POST["ct_singular_name"]) ? $_POST["ct_singular_name"] : $_POST["ct_name"]);
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

    function display_switch_option($args) {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" name="<?php _e($args["field_name"]) ?>" id="<?php _e($args["field_name"]) ?>" class="onoffswitch-checkbox" value="true" <?php echo isset($this->editval) ? checked($this->editval[$args["field_name"]], true) : "checked"; ?>>
            <label class="onoffswitch-label" for="<?php _e($args["field_name"]) ?>">
                <span class="onoffswitch-inner">
                    <span class="onoffswitch-active"><span class="onoffswitch-switch">YES</span></span>
                    <span class="onoffswitch-inactive"><span class="onoffswitch-switch">NO</span></span>
                </span>
            </label>
        </div>
        <?php
    }

    function display_textbox_option($args) {
        ?>
        <input type="text" name="<?php _e($args["field_name"]) ?>" value="<?php echo isset($this->editval) ? $this->editval[$args["field_name"]] : "" ?>"/>
        <?php
    }

    function display_checkbox_option($args) {
        foreach ($args["field_values"] as $value) {
            ?><input type="checkbox"  name="<?php _e($args["field_name"]) ?>[]"  value="<?php _e($value["field_value"]) ?>"  <?php echo isset($this->editval) ? checked(in_array($value["field_value"], $this->editval[$args["field_name"]]), true) : $value["field_checked"]; ?>/> <?php _e($value["field_label"]) ?><br/>
            <?php
        }
    }

}
