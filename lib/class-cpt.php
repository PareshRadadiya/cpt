<?php

/**
 * Class for custom post type support
 */
class CptSettings {

    private $options, $dir, $editval, $helper;

    function __construct() {
        $this->options = get_option('cpt_option');
        $this->dir = plugins_url('', __FILE__);
        $this->editval;

        add_action('admin_init', array($this, 'cpt_register_setting')); // Set setting page for CPT Generator
        /*
         * delete or edit cpt 
         */
        if ((isset($_GET["tab"]) && $_GET["tab"] == "cpt") || (!isset($_GET["tab"]) && isset($_GET["page"]) && $_GET["page"] == "cpt-generator")) {
            if (isset($_GET["editmode"]) && $_GET["editmode"] == "delete") {
                unset($this->options[$_GET["cpt_post_type"]]);
                update_option("cpt_option", $this->options);
                header("location: options-general.php?page=cpt-generator&tab=cpt");
            } elseif (isset($_GET["editmode"]) && $_GET["editmode"] == "edit") {
                $this->editval = $this->options[$_GET["cpt_post_type"]];
            }
        }

        add_action('init', array($this, 'register_cpt')); //Register all CPT added using this plugin
        // add_action('admin_init', array($this, 'add_cpt_caps'));
    }

    /**
     * Register all custom post type from available options
     */
    function register_cpt() {
        if ($this->options) {
            foreach ($this->options as $value) {

                $labels = array(
                    'name' => __($value['cpt_labels_name'], 'cpt'),
                    'singular_name' => __($value['cpt_labels_singular_name'], 'cpt'),
                    'add_new' => __('Add New', 'cpt'),
                    'add_new_item' => __('Add New ' . $value['cpt_labels_singular_name'], 'cpt'),
                    'edit_item' => __('Edit ' . $value['cpt_labels_singular_name'], 'cpt'),
                    'new_item' => __('New ' . $value['cpt_labels_singular_name'], 'cpt'),
                    'view_item' => __('View ' . $value['cpt_labels_singular_name'], 'cpt'),
                    'search_items' => __('Search ' . $value['cpt_labels_name'], 'cpt'),
                    'not_found' => __('No ' . $value['cpt_labels_name'] . ' found', 'cpt'),
                    'not_found_in_trash' => __('No ' . $value['cpt_labels_name'] . ' found in Trash', 'cpt'),
                );

                register_post_type($value['cpt_post_type'], array(
                    'labels' => $labels,
                    'public' => $value['cpt_public'],
                    'description' => $value['cpt_description'],
                    'exclude_from_search' => $value['cpt_exclude_from_search'],
                    'publicly_queryable' => $value['cpt_publicly_queryable'],
                    'show_ui' => $value['cpt_show_ui'],
                    'show_in_nav_menus' => $value['cpt_show_in_nav_menus'],
                    'show_in_menu' => $value['cpt_show_in_menu'],
                    'show_in_admin_bar' => $value['cpt_show_in_admin_bar'],
                    'menu_position' => intval($value['cpt_menu_position']),
                    'map_meta_cap' => true,
                    'hierarchical' => $value['cpt_hierarchical'],
                    'taxonomies' => $value['cpt_taxonomies'],
                    'has_archive' => $value['cpt_has_archive'],
                    'rewrite' => array('slug' => $value['cpt_post_type']),
                    'supports' => $value['cpt_supports'],
                    'menu_icon' => $value['cpt_menu_icon'],
                    'query_var' => $value['cpt_query_var']
                        )
                );
                flush_rewrite_rules();
            }
        }
    }

    /**
     * Add options page
     */
    function add_cpt_plugin_page() {
        add_options_page('CPT Generator', 'CPT Generator', 'manage_options', 'cpt-generator', array($this, 'create_cpt_page'));
    }

    /**
     * section for view all post and add new
     */
    function add_cpt_section() {

        $this->options = get_option('cpt_option');
        $this->add_cpt_field();

        if (isset($_GET["editmode"]) && !isset($_GET["settings-updated"])) {
            ?>
            <a class="add-new-h2" href="options-general.php?page=cpt-generator&tab=cpt">All Post</a><hr/>
            <div class="postbox">
                <h3 class="hndle">
                    <span><?php _e('Generate Post Type'); ?></span>
                </h3>
                <form method="post" action="options.php"  class="clearfix">
                    <div class="inside">
                        <?php
                        wp_nonce_field('save_options_action', 'save_options_nonce_field');
                        settings_fields('cpt_option_group');
                        do_settings_sections('cpt-generator');
                        submit_button();
                        ?>
                    </div>
                </form>
            </div>
        <?php } else {
            ?>
            <a class="add-new-h2" href="options-general.php?page=cpt-generator&tab=cpt&editmode=add">Add New</a><hr/>
            <table class="wp-list-table widefat fixed pages">
                <thead>
                <th class="manage-column">Post Name</th>
                <th class="manage-column">Public</th>
                <th class="manage-column">Label</th>
                <th class="manage-column">Description</th>
                <th class="manage-column">Icon</th>
            </thead>
            <tbody>
                <?php
                $index = 1;
                if ($this->options) {

                    foreach ($this->options as $value) {
                        ?>
                        <tr class="<?php echo ($index % 2) ? "alternate" : "" ?>">

                            <td class="post-title page-title column-title">
                                <strong><a href="options-general.php?page=cpt-generator&tab=cpt&editmode=edit&cpt_post_type=<?php echo $value['cpt_post_type']; ?>"><?php echo $value['cpt_post_type']; ?></a></strong>
                                <div class="row-actions">
                                    <span class="edit"><a href="options-general.php?page=cpt-generator&tab=cpt&editmode=edit&cpt_post_type=<?php echo $value['cpt_post_type']; ?>" title="Edit this item">Edit</a> | </span>

                                    <span class="trash"><a class="submitdelete" href="options-general.php?page=cpt-generator&tab=cpt&editmode=delete&cpt_post_type=<?php echo $value['cpt_post_type']; ?>" title="Move this item to the Trash" href="">Trash</a> | </span>

                                </div>
                            </td>
                            <td><?php echo $value['cpt_public'] ? "True" : "False"; ?></td>
                            <td><?php echo $value['cpt_labels_name']; ?></td>
                            <td><?php echo $value['cpt_description']; ?></td>
                            <td>
                                <?php if (!empty($value['cpt_menu_icon'])) { ?>
                                    <img src="<?php echo $value['cpt_menu_icon']; ?>" width="16" height="16" />
                                <?php } else { ?>
                                    <div class="dashicons-before dashicons-admin-post"></div>
                    <?php } ?>
                            </td>
                        </tr>
                        <?php
                        $index++;
                    }
                } else {
                    ?>
                    <tr class="no-items"><td class="colspanchange" colspan="5">No custom posts found created using this plugin.</td></tr>
                    <?php
                }
                ?>
            </tbody>
            <tfoot>
            <th class="manage-column">Post Name</th>
            <th class="manage-column">Public</th>
            <th class="manage-column">Label</th>
            <th class="manage-column">Description</th>
            <th class="manage-column">Icon</th>
            </tfoot>
            </table>
            <div class="tablenav bottom">		
                <div class="tablenav-pages one-page"><span class="displaying-num"><?php echo $index - 1; ?> items</span>		
                    <br class="clear">
                </div>
            </div>
            <?php
        }
    }

    /**
     * Register and add settings ,sections and fields
     */
    function cpt_register_setting() {
        register_setting(
                'cpt_option_group', // Option group
                'cpt_option', // Option name
                array($this, 'sanitize_cpt_options')
        );
    }

    /**
     *  Add a new field to a cpt setting section of a settings page
     */
    public function add_cpt_field() {
        add_settings_section(
                'cpt_setting_section', // ID
                'General Settings', // Title
                array($this, 'general_section_info'), // Callback
                'cpt-generator' // Page
        );

        add_settings_field(
                'cpt_post_type', 'Post Type', array($this, 'display_textbox_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_post_type")
        );

        add_settings_field(
                'cpt_labels_name', 'Label Name', array($this,'display_textbox_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_labels_name")
        );

        add_settings_field(
                'cpt_labels_singular_name', 'Singular Name', array($this, 'display_textbox_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_labels_singular_name")
        );

        add_settings_field(
                'cpt_description', 'Description', array($this, 'cpt_description_callback'), 'cpt-generator', 'cpt_setting_section'
        );

        add_settings_field(
                'cpt_public', 'Public', array($this, 'display_switch_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_public")
        );

        add_settings_field(
                'cpt_exclude_from_search', 'Exclude From Search', array($this, 'display_switch_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_exclude_from_search")
        );

        add_settings_field(
                'cpt_publicly_queryable', 'Publically Queryable', array($this, 'display_switch_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_publicly_queryable")
        );

        add_settings_field(
                'cpt_show_ui', 'Show UI', array($this, 'display_switch_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_show_ui")
        );

        add_settings_field(
                'cpt_show_in_nav_menus', 'Show In Nav Menu', array($this, 'display_switch_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_show_in_nav_menus")
        );

        add_settings_field(
                'cpt_show_in_menu', 'Show In Menu', array($this, 'display_switch_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_show_in_menu")
        );

        add_settings_field(
                'cpt_show_in_admin_bar', 'Show In Admin Bar', array($this, 'display_switch_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_show_in_admin_bar")
        );

        add_settings_field(
                'cpt_menu_position', 'Menu Position', array($this, 'cpt_menu_position_callback'), 'cpt-generator', 'cpt_setting_section'
        );

        add_settings_field(
                'cpt_hierarchical', 'Hierarchical', array($this, 'display_switch_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_hierarchical")
        );

        $taxonomies = array(
            array("field_value" => "category", "field_label" => "Category", "field_checked" => ""),
            array("field_value" => "post_tag", "field_label" => "Tag", "field_checked" => ""),
        );

        add_settings_field(
                'cpt_taxonomies', 'Built in Taxonomies', array($this, 'display_checkbox_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_taxonomies", "field_values" => $taxonomies)
        );

        add_settings_field(
                'cpt_has_archive', 'Has Archive', array($this, 'display_switch_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_has_archive")
        );

        $supports = array(
            array("field_value" => "title", "field_label" => "Title", "field_checked" => "checked"),
            array("field_value" => "editor", "field_label" => "Editor", "field_checked" => "checked"),
            array("field_value" => "author", "field_label" => "Author", "field_checked" => "checked"),
            array("field_value" => "thumbnail", "field_label" => "Thumbnail", "field_checked" => ""),
            array("field_value" => "excerpt", "field_label" => "Excerpt", "field_checked" => ""),
            array("field_value" => "trackbacks", "field_label" => "Trackbacks", "field_checked" => ""),
            array("field_value" => "custom-fields", "field_label" => "Custom Fields", "field_checked" => ""),
            array("field_value" => "comments", "field_label" => "Comments", "field_checked" => ""),
            array("field_value" => "revisions", "field_label" => "Revisions", "field_checked" => ""),
            array("field_value" => "page-attributes", "field_label" => "Page Attributes", "field_checked" => ""),
            array("field_value" => "post-formats", "field_label" => "Post Formats", "field_checked" => ""),
        );

        add_settings_field(
                'cpt_supports', 'Supports Type', array($this, 'display_checkbox_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_supports", "field_values" => $supports)
        );


        add_settings_field(
                'cpt_menu_icon', 'Menu Icon', array($this, 'cpt_menu_icon_callback'), 'cpt-generator', 'cpt_setting_section'
        );

        add_settings_field(
                'cpt_query_var', 'Query Var', array($this, 'display_switch_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_query_var")
        );
    }

    /**
     * Sanitize each option setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    function sanitize_cpt_options($input) {
        if (!empty($_POST) && check_admin_referer('save_options_action', 'save_options_nonce_field')) {
            $cpt_option = get_option('cpt_option'); // Get the current options from the db
            $cpt_option[$_POST["cpt_post_type"]]["cpt_post_type"] = sanitize_text_field($_POST["cpt_post_type"]);
            $cpt_option[$_POST["cpt_post_type"]]["cpt_labels_name"] = sanitize_text_field(!empty($_POST["cpt_labels_name"]) ? $_POST["cpt_labels_name"] : $_POST["cpt_post_type"]);
            $cpt_option[$_POST["cpt_post_type"]]["cpt_labels_singular_name"] = sanitize_text_field(!empty($_POST["cpt_labels_singular_name"]) ? $_POST["cpt_labels_singular_name"] : $_POST["cpt_post_type"]);
            $cpt_option[$_POST["cpt_post_type"]]["cpt_public"] = isset($_POST["cpt_public"]) ? true : false;
            $cpt_option[$_POST["cpt_post_type"]]["cpt_description"] = esc_textarea($_POST["cpt_description"]);
            $cpt_option[$_POST["cpt_post_type"]]["cpt_exclude_from_search"] = isset($_POST["cpt_exclude_from_search"]) ? true : false;
            $cpt_option[$_POST["cpt_post_type"]]["cpt_publicly_queryable"] = isset($_POST["cpt_publicly_queryable"]) ? true : false;
            $cpt_option[$_POST["cpt_post_type"]]["cpt_show_ui"] = isset($_POST["cpt_show_ui"]) ? true : false;
            $cpt_option[$_POST["cpt_post_type"]]["cpt_show_in_nav_menus"] = isset($_POST["cpt_show_in_nav_menus"]) ? true : false;
            $cpt_option[$_POST["cpt_post_type"]]["cpt_show_in_menu"] = isset($_POST["cpt_show_in_menu"]) ? true : false;
            $cpt_option[$_POST["cpt_post_type"]]["cpt_show_in_admin_bar"] = isset($_POST["cpt_show_in_admin_bar"]) ? true : false;
            $cpt_option[$_POST["cpt_post_type"]]["cpt_menu_position"] = $_POST["cpt_menu_position"];
            $cpt_option[$_POST["cpt_post_type"]]["cpt_has_archive"] = isset($_POST["cpt_has_archive"]) ? true : false;
            $cpt_option[$_POST["cpt_post_type"]]["cpt_taxonomies"] = isset($_POST["cpt_taxonomies"]) ? $_POST["cpt_taxonomies"] : array('');
            $cpt_option[$_POST["cpt_post_type"]]["cpt_hierarchical"] = isset($_POST["cpt_hierarchical"]) ? true : false;
            $cpt_option[$_POST["cpt_post_type"]]["cpt_supports"] = isset($_POST["cpt_supports"]) ? $_POST["cpt_supports"] : array('');
            $cpt_option[$_POST["cpt_post_type"]]["cpt_menu_icon"] = !empty($_POST["cpt_menu_icon"]) ? $_POST["cpt_menu_icon"] : null;
            $cpt_option[$_POST["cpt_post_type"]]["cpt_query_var"] = isset($_POST["cpt_query_var"]) ? true : false;
            return $cpt_option;
        }
    }

    /**
     * Print the General Section info
     */
    function general_section_info() {
        print 'Enter your general cpt settings below:';
    }

    /**
     * Public visibility option callback
     */
    function cpt_menu_position_callback() {
        ?>
        <input type="number" name="cpt_menu_position" value="<?php echo isset($this->editval) ? $this->editval['cpt_menu_position'] : "25" ?>" />
        <?php
    }

    function cpt_description_callback() {
        ?>
        <textarea name="cpt_description"><?php echo isset($this->editval) ? $this->editval['cpt_description'] : "" ?></textarea>
        <?php
    }

    /**
     * Supports option callbacks
     */
    function cpt_menu_icon_callback() {
        ?>
        <input id="cpt_menu_icon" type="url" size="36" name="cpt_menu_icon" value="<?php echo isset($this->editval) ? $this->editval['cpt_menu_icon'] : "" ?>" /> 
        <input id="choose_cpt_icon" class="button" type="button" value="Choose Image" />
        <br/>
        <img id="cpt_menu_icon_thumbnail" src="<?php echo isset($this->editval) ? $this->editval['cpt_menu_icon'] : "" ?>" alt="icon not found"/>
        <?php
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
