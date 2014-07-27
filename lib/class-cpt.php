<?php

/**
 * Class for custom post type support
 */
class CptSettings {

    private $options, $dir, $editval;

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
        // add_action('admin_initParesh Radadiya', array($this, 'add_cpt_caps'));
    }

    /**
     * Register all custom post type from available options
     */
    function register_cpt() {
        if ($this->options) {
            foreach ($this->options as $value) {

                $labels = array(
                    'name' => __($value['cpt_labels_name'], 'cpt-generator'),
                    'singular_name' => __($value['cpt_labels_singular_name'], 'cpt-generator'),
                    'add_new' => __('Add New', 'cpt'),
                    'add_new_item' => __('Add New ' . $value['cpt_labels_singular_name'], 'cpt-generator'),
                    'edit_item' => __('Edit ' . $value['cpt_labels_singular_name'], 'cpt-generator'),
                    'new_item' => __('New ' . $value['cpt_labels_singular_name'], 'cpt-generator'),
                    'view_item' => __('View ' . $value['cpt_labels_singular_name'], 'cpt-generator'),
                    'search_items' => __('Search ' . $value['cpt_labels_name'], 'cpt-generator'),
                    'not_found' => __('No ' . $value['cpt_labels_name'] . ' found', 'cpt-generator'),
                    'not_found_in_trash' => __('No ' . $value['cpt_labels_name'] . ' found in Trash', 'cpt-generator'),
                );
                $attachment_url = null;
                if (!empty($value['cpt_menu_icon'])) {
                    $attachment_url = wp_get_attachment_image_src($value['cpt_menu_icon'], "cpt_menu_icon")[0];
                    //$attachment_url = $attachment_datail[0];
                }
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
                    'menu_icon' => $attachment_url,
                    'query_var' => $value['cpt_query_var']
                        )
                );
                flush_rewrite_rules();
            }
        }
    }

    /**
     * section for view all post and add new
     */
    function add_cpt_section() {

        $this->options = get_option('cpt_option');
        $this->add_cpt_field();

        if (isset($_GET["editmode"]) && !isset($_GET["settings-updated"])) {
            ?>
            <a class="add-new-h2" href="options-general.php?page=cpt-generator&tab=cpt"><?php _e('All Post', 'cpt-generator') ?></a><hr/>
            <div class="postbox">
                <h3 class="hndle">
                    <span><?php _e('Generate Post Type', 'cpt-generator'); ?></span>
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
            <a class="add-new-h2" href="options-general.php?page=cpt-generator&tab=cpt&editmode=add"><?php _e('Add New', 'cpt-generator') ?></a><hr/>
            <table class="wp-list-table widefat fixed pages">
                <thead>
                <th class="manage-column"><?php _e('Post Name', 'cpt-generator') ?></th>
                <th class="manage-column"><?php _e('Public', 'cpt-generator') ?></th>
                <th class="manage-column"><?php _e('Label', 'cpt-generator') ?></th>
                <th class="manage-column"><?php _e('Description', 'cpt-generator') ?></th>
                <th class="manage-column"><?php _e('Icon', 'cpt-generator') ?></th>
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
                                    <img src="<?php echo wp_get_attachment_image_src($value['cpt_menu_icon'], "cpt_menu_icon")[0]; ?>" />
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
            <th class="manage-column"><?php _e('Post Name', 'cpt-generator') ?></th>
            <th class="manage-column"><?php _e('Public', 'cpt-generator') ?></th>
            <th class="manage-column"><?php _e('Label', 'cpt-generator') ?></th>
            <th class="manage-column"><?php _e('Description', 'cpt-generator') ?></th>
            <th class="manage-column"><?php _e('Icon', 'cpt-generator') ?></th>
            </tfoot>
            </table>
            <div class="tablenav bottom">		
                <div class="tablenav-pages one-page"><span class="displaying-num"><?php printf(_n('%d item', '%d items', $index - 1, 'cpt-generator'), $index - 1); ?></span>		
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
        global $cpt_helper;
        add_settings_section(
                'cpt_setting_section', // ID
                __('General Settings', 'cpt-generator'), // Title
                array($this, 'general_section_info'), // Callback
                'cpt-generator' // Page
        );

        add_settings_field(
                'cpt_post_type', __('Post Type', 'cpt-generator'), array($cpt_helper, 'display_textbox_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_post_type","editval"=>$this->editval)
        );

        add_settings_field(
                'cpt_labels_name', __('Label Name', 'cpt-generator'), array($cpt_helper, 'display_textbox_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_labels_name","editval"=>$this->editval)
        );

        add_settings_field(
                'cpt_labels_singular_name', __('Singular Name', 'cpt-generator'), array($cpt_helper, 'display_textbox_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_labels_singular_name","editval"=>$this->editval)
        );

        add_settings_field(
                'cpt_description', __('Description', 'cpt-generator'), array($this, 'cpt_description_callback'), 'cpt-generator', 'cpt_setting_section'
        );

        add_settings_field(
                'cpt_public', __('Public', 'cpt-generator'), array($cpt_helper, 'display_switch_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_public","editval"=>$this->editval)
        );

        add_settings_field(
                'cpt_exclude_from_search', __('Exclude From Search', 'cpt-generator'), array($cpt_helper, 'display_switch_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_exclude_from_search","editval"=>$this->editval)
        );

        add_settings_field(
                'cpt_publicly_queryable', __('Publically Queryable', 'cpt-generator'), array($cpt_helper, 'display_switch_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_publicly_queryable","editval"=>$this->editval)
        );

        add_settings_field(
                'cpt_show_ui', __('Show UI', 'cpt-generator'), array($cpt_helper, 'display_switch_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_show_ui","editval"=>$this->editval)
        );

        add_settings_field(
                'cpt_show_in_nav_menus', __('Show In Nav Menu', 'cpt-generator'), array($cpt_helper, 'display_switch_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_show_in_nav_menus","editval"=>$this->editval)
        );

        add_settings_field(
                'cpt_show_in_menu', __('Show In Menu', 'cpt-generator'), array($cpt_helper, 'display_switch_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_show_in_menu","editval"=>$this->editval)
        );

        add_settings_field(
                'cpt_show_in_admin_bar', __('Show In Admin Bar', 'cpt-generator'), array($cpt_helper, 'display_switch_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_show_in_admin_bar","editval"=>$this->editval)
        );

        add_settings_field(
                'cpt_menu_position', __('Menu Position', 'cpt-generator'), array($this, 'cpt_menu_position_callback'), 'cpt-generator', 'cpt_setting_section'
        );

        add_settings_field(
                'cpt_hierarchical', __('Hierarchical', 'cpt-generator'), array($cpt_helper, 'display_switch_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_hierarchical","editval"=>$this->editval)
        );

        $taxonomies = array(
            array("field_value" => "category", "field_label" => "Category", "field_checked" => ""),
            array("field_value" => "post_tag", "field_label" => "Tag", "field_checked" => ""),
        );

        add_settings_field(
                'cpt_taxonomies', __('Built in Taxonomies', 'cpt-generator'), array($cpt_helper, 'display_checkbox_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_taxonomies", "field_values" => $taxonomies,"editval"=>$this->editval)
        );

        add_settings_field(
                'cpt_has_archive', __('Has Archive', 'cpt-generator'), array($cpt_helper, 'display_switch_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_has_archive","editval"=>$this->editval)
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
                'cpt_supports', __('Supports Type', 'cpt-generator'), array($cpt_helper, 'display_checkbox_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_supports", "field_values" => $supports,"editval"=>$this->editval)
        );


        add_settings_field(
                'cpt_menu_icon', __('Menu Icon', 'cpt-generator'), array($this, 'cpt_menu_icon_callback'), 'cpt-generator', 'cpt_setting_section'
        );

        add_settings_field(
                'cpt_query_var', __('Query Var', 'cpt-generator'), array($cpt_helper, 'display_switch_option'), 'cpt-generator', 'cpt_setting_section', array("field_name" => "cpt_query_var","editval"=>$this->editval)
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
            $cpt_option[$_POST["cpt_post_type"]]["cpt_post_type"] = esc_attr($_POST["cpt_post_type"]);
            $cpt_option[$_POST["cpt_post_type"]]["cpt_labels_name"] = esc_attr(!empty($_POST["cpt_labels_name"]) ? $_POST["cpt_labels_name"] : $_POST["cpt_post_type"]);
            $cpt_option[$_POST["cpt_post_type"]]["cpt_labels_singular_name"] = esc_attr(!empty($_POST["cpt_labels_singular_name"]) ? $_POST["cpt_labels_singular_name"] : $_POST["cpt_post_type"]);
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
            $cpt_option[$_POST["cpt_post_type"]]["cpt_menu_icon"] = !empty($_POST["cpt_menu_icon"]) ? $_POST["cpt_menu_icon"] : "";
            $cpt_option[$_POST["cpt_post_type"]]["cpt_query_var"] = isset($_POST["cpt_query_var"]) ? true : false;
            return $cpt_option;
        }
    }

    /**
     * Print the General Section info
     */
    function general_section_info() {
        _e('Enter your general cpt settings below', 'cpt-generator');
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
        <input id="cpt_menu_icon" type="hidden" size="36" name="cpt_menu_icon" value="<?php echo isset($this->editval) ? $this->editval['cpt_menu_icon'] : "" ?>" /> 
        <input id="choose_cpt_icon" class="button" type="button" value="Choose Image" />
        <br/>
        <img id="cpt_menu_icon_thumbnail" width="16" height="16" src="<?php echo isset($this->editval) ? wp_get_attachment_image_src($this->editval['cpt_menu_icon'], "cpt_menu_icon")[0] : "" ?>" alt="icon not found"/>
        <?php
    }

}
