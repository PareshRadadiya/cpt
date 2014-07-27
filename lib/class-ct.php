<?php

/**
 * Class for custom taxonomy plugin support
 */
class CtSettings {

    private $options, $dir, $editval;

    public function __construct() {
        $this->options = get_option('ct_option');
        $this->dir = plugins_url('', __FILE__);
        $this->editval;

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
                    'name' => _x($value['ct_label_name'], 'taxonomy general name', 'cpt-generator'),
                    'singular_name' => _x($value['ct_singular_name'], 'taxonomy singular name'),
                    'search_items' => __('Search ' . $value['ct_label_name'], 'cpt-generator'),
                    'popular_items' => __('Popular ' . $value['ct_label_name'], 'cpt-generator'),
                    'all_items' => __('All ' . $value['ct_label_name'], 'cpt-generator'),
                    'parent_item' => __('Parent ' . $value['ct_singular_name'], 'cpt-generator'),
                    'parent_item_colon' => __('Parent ' . $value['ct_singular_name'], 'cpt-generator' . ':'),
                    'edit_item' => __('Edit ' . $value['ct_singular_name'], 'cpt-generator'),
                    'update_item' => __('Update ' . $value['ct_singular_name'], 'cpt-generator'),
                    'add_new_item' => __('Add New ' . $value['ct_singular_name'], 'cpt-generator'),
                    'new_item_name' => __('New ' . $value['ct_singular_name'], 'cpt-generator' . ' Name'),
                    'separate_items_with_commas' => __('Seperate ' . $value['ct_label_name'], 'cpt-generator' . ' with commas'),
                    'add_or_remove_items' => __('Add or remove ' . $value['ct_label_name'], 'cpt-generator'),
                    'choose_from_most_used' => __('Choose from the most used ' . $value['ct_label_name'], 'cpt-generator'),
                    'menu_name' => __('All ' . $value['ct_label_name'], 'cpt-generator')
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
     *  section for view all post and add new
     */
    public function add_ct_section() {

        $this->options = get_option('ct_option');
        $this->add_ct_field();
        if (isset($_GET["editmode"]) && !isset($_GET["settings-updated"])) {
            ?>
            <a class="add-new-h2" href="options-general.php?page=cpt-generator&tab=ct"><?php _e('All Taxonomy', 'cpt-generator') ?></a><hr/>
            <div class="postbox">
                <h3 class="hndle">
                    <span><?php _e('Generate Taxonomy', 'cpt-generator'); ?></span>
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
            <a class="add-new-h2" href="options-general.php?page=cpt-generator&tab=ct&editmode=add"><?php _e('Add New', 'cpt-generator') ?></a><hr/>
            <table class="wp-list-table widefat fixed pages">
                <thead>
                <th class="manage-column"><?php _e('Name', 'cpt-generator') ?></th>
                <th class="manage-column"><?php _e('Label', 'cpt-generator') ?></th>
                <th class="manage-column"><?php _e('Show UI', 'cpt-generator') ?></th>
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
            <th class="manage-column"><?php _e('Name', 'cpt-generator') ?></th>
            <th class="manage-column"><?php _e('Label', 'cpt-generator') ?></th>
            <th class="manage-column"><?php _e('Show UI', 'cpt-generator') ?></th>
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
        global $cpt_helper;
        add_settings_section(
                'ct_setting_section', // ID
                __('General Settings', 'cpt-generator'), // Title
                array($this, 'general_section_info'), // Callback
                'cpt-generator' // Page
        );

        add_settings_field(
                'ct_name', __('Taxonomy Name', 'cpt-generator'), array($cpt_helper, 'display_textbox_option'), 'cpt-generator', 'ct_setting_section', array("field_name" => "ct_name","editval"=>$this->editval)
        );

        add_settings_field(
                'ct_label_name', __('Label Name', 'cpt-generator'), array($cpt_helper, 'display_textbox_option'), 'cpt-generator', 'ct_setting_section', array("field_name" => "ct_label_name","editval"=>$this->editval)
        );

        add_settings_field(
                'ct_singular_name', __('Singular Name', 'cpt-generator'), array($cpt_helper, 'display_textbox_option'), 'cpt-generator', 'ct_setting_section', array("field_name" => "ct_singular_name","editval"=>$this->editval)
        );

        add_settings_field(
                'ct_hierarchical', __('Hierarchical', 'cpt-generator'), array($cpt_helper, 'display_switch_option'), 'cpt-generator', 'ct_setting_section', array("field_name" => "ct_hierarchical","editval"=>$this->editval)
        );

        add_settings_field(
                'ct_show_ui', __('Show UI', 'cpt-generator'), array($cpt_helper, 'display_switch_option'), 'cpt-generator', 'ct_setting_section', array("field_name" => "ct_show_ui","editval"=>$this->editval)
        );

        add_settings_field(
                'ct_show_in_nav_menus', __('Show In Nav Menu', 'cpt-generator'), array($cpt_helper, 'display_switch_option'), 'cpt-generator', 'ct_setting_section', array("field_name" => "ct_show_in_nav_menus","editval"=>$this->editval)
        );

        add_settings_field(
                'ct_show_tagcloud', __('Show Tag Cloud', 'cpt-generator'), array($cpt_helper, 'display_switch_option'), 'cpt-generator', 'ct_setting_section', array("field_name" => "ct_show_tagcloud","editval"=>$this->editval)
        );
        add_settings_field(
                'ct_show_admin_column', __('Show Admin Column', 'cpt-generator'), array($cpt_helper, 'display_switch_option'), 'cpt-generator', 'ct_setting_section', array("field_name" => "ct_show_admin_column","editval"=>$this->editval)
        );

        add_settings_field(
                'ct_query_var', __('Query Var', 'cpt-generator'), array($cpt_helper, 'display_switch_option'), 'cpt-generator', 'ct_setting_section', array("field_name" => "ct_query_var","editval"=>$this->editval)
        );


        $post_type_support = array();
        $post_types = get_post_types();
        foreach ($post_types as $post_type) {
            array_push($post_type_support, array("field_value" => "$post_type", "field_label" => "$post_type", "field_checked" => ""));
        }
        add_settings_field(
                'post_types', __('Post Type', 'cpt-generator'), array($cpt_helper, 'display_checkbox_option'), 'cpt-generator', 'ct_setting_section', array("field_name" => "post_types", "field_values" => $post_type_support,"editval"=>$this->editval)
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
        _e('Enter your general ct settings below');
    }

}
