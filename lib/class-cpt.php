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
        // add_action('admin_menu', array($this, 'add_cpt_plugin_tab')); //Add menu inside setting for Generator
        add_action('admin_init', array($this, 'cpt_page_init')); // Set setting page for CPT Generator

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
        add_action('admin_init', array($this, 'add_cpt_caps'));
    }

    function add_cpt_caps() {
        // gets the administrator role
        $admins = get_role('administrator');
        if ($this->options) {
            foreach ($this->options as $value) {
                $slug = $value['cpt_post_type'];
                $admins->add_cap("edit_{$slug}");
                $admins->add_cap("edit_{$slug}s");
                $admins->add_cap("edit_other_{$slug}s");
                $admins->add_cap("publish_{$slug}s");
                $admins->add_cap("read_{$slug}");
                $admins->add_cap("read_private_{$slug}s");
                $admins->add_cap("delete_{$slug}");
            }
        }
    }

    /**
     * Register all custom post type from available options
     */
    function register_cpt() {
        if ($this->options) {
            foreach ($this->options as $value) {
                $slug = $value['cpt_post_type'];
                register_post_type($value['cpt_post_type'], array(
                    'labels' => array(
                        'name' => $value['cpt_labels_name'],
                        'singular_name' => $value['cpt_labels_singular_name']
                    ),
                    'public' => $value['cpt_public'],
                    'description' => $value['cpt_description'],
                    'exclude_from_search' => $value['cpt_exclude_from_search'],
                    'publicly_queryable' => $value['cpt_publicly_queryable'],
                    'show_ui' => $value['cpt_show_ui'],
                    'show_in_nav_menus' => $value['cpt_show_in_nav_menus'],
                    'show_in_menu' => $value['cpt_show_in_menu'],
                    'show_in_admin_bar' => $value['cpt_show_in_admin_bar'],
                    'menu_position' => intval($value['cpt_menu_position']),
                    'capability_type' => $value['cpt_post_type'],
                    'capabilities' => array(
                        "publish_posts" => "publish_{$slug}s",
                        "edit_posts" => "edit_{$slug}s",
                        "edit_published_posts" => "edit_published_{$slug}s",
                        "delete_published_posts" => "delete_published_{$slug}s",
                        "edit_others_posts" => "edit_others_{$slug}s",
                        "delete_posts" => "delete_{$slug}s",
                        "delete_others_posts" => "delete_others_{$slug}s",
                        "read_private_posts" => "read_private_{$slug}s",
                        "edit_post" => "edit_{$slug}",
                        "delete_post" => "delete_{$slug}",
                        "read_post" => "read_{$slug}",
                    ),
                    'map_meta_cap' => true,
                    'hierarchical' => $value['cpt_hierarchical'],
                    'taxonomies' => $value['cpt_taxonomies'],
                    'has_archive' => $value['cpt_has_archive'],
                    'rewrite' => array('slug' => $value['cpt_post_type']),
                    'supports' => $value['cpt_supports']
                        )
                );
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
     * Options page callback
     */
    function add_cpt_section() {

        $this->options = get_option('cpt_option');
        $this->add_cpt_field()
        ?>
        <a class="button button-primary" href="options-general.php?page=cpt-generator&tab=cpt">Add New</a><hr/>
        <div class="postbox">
            <h3 class="hndle">
                <span><?php _e('Generate Post Type'); ?></span>
            </h3>
            <form method="post" action="options.php"  class="clearfix">
                <div class="inside">
                    <?php
                    //wp_nonce_field('cpt_save_options', 'save_options');
                    // This prints out all hidden setting fields

                    settings_fields('cpt_option_group');
                    do_settings_sections('cpt-generator');
                    submit_button();
                    ?>
                </div>
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
                                <span class="edit"><a href="options-general.php?page=cpt-generator&tab=cpt&editmode=edit&cpt_post_type=<?php echo $value['cpt_post_type']; ?>" title="Edit this item">Edit</a> | </span>

                                <span class="trash"><a class="submitdelete" href="options-general.php?page=cpt-generator&tab=cpt&editmode=delete&cpt_post_type=<?php echo $value['cpt_post_type']; ?>" title="Move this item to the Trash" href="">Trash</a> | </span>

                            </div>
                        </td>
                        <td><?php echo $value['cpt_public'] ? "True" : "False"; ?></td>
                        <td><?php echo $value['cpt_labels_name']; ?></td>
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
    function cpt_page_init() {
        register_setting(
                'cpt_option_group', // Option group
                'cpt_option', // Option name
                array($this, 'sanitize_cpt_options')
        );
    }

    public function add_cpt_field() {
        add_settings_section(
                'cpt_setting_section', // ID
                'General Settings', // Title
                array($this, 'general_section_info'), // Callback
                'cpt-generator' // Page
        );

        add_settings_field(
                'cpt_post_type', 'Post Type', array($this, 'post_type_callback'), 'cpt-generator', 'cpt_setting_section'
        );

        add_settings_field(
                'cpt_labels_name', 'Label Name', array($this, 'cpt_labels_name_callback'), 'cpt-generator', 'cpt_setting_section'
        );

        add_settings_field(
                'cpt_labels_singular_name', 'Singular Name', array($this, 'cpt_labels_singular_name_callback'), 'cpt-generator', 'cpt_setting_section'
        );

        add_settings_field(
                'cpt_description', 'Description', array($this, 'cpt_description_callback'), 'cpt-generator', 'cpt_setting_section'
        );


        add_settings_field(
                'cpt_public', 'Public', array($this, 'cpt_public_callback'), 'cpt-generator', 'cpt_setting_section'
        );

        add_settings_field(
                'cpt_exclude_from_search', 'Exclude From Search', array($this, 'cpt_exclude_from_search_callback'), 'cpt-generator', 'cpt_setting_section'
        );

        add_settings_field(
                'cpt_publicly_queryable', 'Publically Queryable', array($this, 'cpt_publicly_queryable_callback'), 'cpt-generator', 'cpt_setting_section'
        );

        add_settings_field(
                'cpt_show_ui', 'Show UI', array($this, 'cpt_show_ui_callback'), 'cpt-generator', 'cpt_setting_section'
        );

        add_settings_field(
                'cpt_show_in_nav_menus', 'Show In Nav Menu', array($this, 'cpt_show_in_nav_menus_callback'), 'cpt-generator', 'cpt_setting_section'
        );

        add_settings_field(
                'cpt_show_in_menu', 'Show In Menu', array($this, 'cpt_show_in_menu_callback'), 'cpt-generator', 'cpt_setting_section'
        );

        add_settings_field(
                'cpt_show_in_admin_bar', 'Show In Admin Bar', array($this, 'cpt_show_in_admin_bar_callback'), 'cpt-generator', 'cpt_setting_section'
        );

        add_settings_field(
                'cpt_menu_position', 'Menu Position', array($this, 'cpt_menu_position_callback'), 'cpt-generator', 'cpt_setting_section'
        );

//        add_settings_field(
//                'cpt_capability_type', 'Capability Type', array($this, 'cpt_capability_type_callback'), 'cpt-generator', 'cpt_setting_section'
//        );
//
//            add_settings_field(
//                    'cpt_capabilities', 'Capability Type', array($this, 'cpt_capabilities_callback'), 'cpt-generator', 'cpt_setting_section'
//            );
        add_settings_field(
                'cpt_hierarchical', 'Hierarchical', array($this, 'cpt_hierarchical_callback'), 'cpt-generator', 'cpt_setting_section'
        );

        add_settings_field(
                'cpt_taxonomies', 'Built in Taxonomies', array($this, 'cpt_taxonomies_callback'), 'cpt-generator', 'cpt_setting_section'
        );

        add_settings_field(
                'cpt_has_archive', 'Has Archive', array($this, 'cpt_has_archive_callback'), 'cpt-generator', 'cpt_setting_section'
        );


        add_settings_field(
                'cpt_supports', 'Supports Type', array($this, 'support_callback'), 'cpt-generator', 'cpt_setting_section'
        );
    }

    /**
     * Sanitize each option setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    function sanitize_cpt_options($input) {
        $cpt_option = get_option('cpt_option'); // Get the current options from the db
        $cpt_option[$_POST["cpt_post_type"]]["cpt_post_type"] = $_POST["cpt_post_type"];
        $cpt_option[$_POST["cpt_post_type"]]["cpt_labels_name"] = $_POST["cpt_labels_name"];
        $cpt_option[$_POST["cpt_post_type"]]["cpt_labels_singular_name"] = isset($_POST["cpt_labels_singular_name"]) ? $_POST["cpt_labels_singular_name"] : $_POST["cpt_post_type"];
        $cpt_option[$_POST["cpt_post_type"]]["cpt_public"] = isset($_POST["cpt_public"]) ? true : false;
        $cpt_option[$_POST["cpt_post_type"]]["cpt_description"] = isset($_POST["cpt_description"]) ? $_POST["cpt_description"] : "";
        $cpt_option[$_POST["cpt_post_type"]]["cpt_exclude_from_search"] = isset($_POST["cpt_exclude_from_search"]) ? true : false;
        $cpt_option[$_POST["cpt_post_type"]]["cpt_publicly_queryable"] = isset($_POST["cpt_publicly_queryable"]) ? true : false;
        $cpt_option[$_POST["cpt_post_type"]]["cpt_show_ui"] = isset($_POST["cpt_show_ui"]) ? true : false;
        $cpt_option[$_POST["cpt_post_type"]]["cpt_show_in_nav_menus"] = isset($_POST["cpt_show_in_nav_menus"]) ? true : false;
        $cpt_option[$_POST["cpt_post_type"]]["cpt_show_in_menu"] = isset($_POST["cpt_show_in_menu"]) ? true : false;
        $cpt_option[$_POST["cpt_post_type"]]["cpt_show_in_admin_bar"] = isset($_POST["cpt_show_in_admin_bar"]) ? true : false;
        $cpt_option[$_POST["cpt_post_type"]]["cpt_menu_position"] = isset($_POST["cpt_menu_position"]) ? $_POST["cpt_menu_position"] : "";
        $cpt_option[$_POST["cpt_post_type"]]["cpt_has_archive"] = isset($_POST["cpt_has_archive"]) ? true : false;
        $cpt_option[$_POST["cpt_post_type"]]["cpt_taxonomies"] = isset($_POST["cpt_taxonomies"]) ? $_POST["cpt_taxonomies"] : array('');
        $cpt_option[$_POST["cpt_post_type"]]["cpt_hierarchical"] = isset($_POST["cpt_hierarchical"]) ? true : false;
        $cpt_option[$_POST["cpt_post_type"]]["cpt_supports"] = isset($_POST["cpt_supports"]) ? $_POST["cpt_supports"] : array('');
        return $cpt_option;
    }

    /**
     * Print the General Section info
     */
    function general_section_info() {
        print 'Enter your general cpt settings below:';
    }

    /**
     * Post type name option callback
     */
    function post_type_callback() {
        ?>
        <input type="text" name="cpt_post_type" required="" value="<?php echo isset($this->editval) ? $this->editval['cpt_post_type'] : "" ?>" />
        <?php
    }

    /**
     * Post label name option callback
     */
    function cpt_labels_name_callback() {
        ?>
        <input type="text" name="cpt_labels_name" required="" value="<?php echo isset($this->editval) ? $this->editval['cpt_labels_name'] : "" ?>"/>
        <?php
    }

    /**
     * Post label sigular name option callback
     */
    function cpt_labels_singular_name_callback() {
        ?>
        <input type="text" name="cpt_labels_singular_name"  value="<?php echo isset($this->editval) ? $this->editval['cpt_labels_singular_name'] : "" ?>"/>
        <?php
    }

    /**
     * Post label sigular name option callback
     */
    function cpt_description_callback() {
        ?>
        <textarea name="cpt_description"><?php echo isset($this->editval) ? $this->editval['cpt_description'] : "" ?></textarea>
        <?php
    }

    /**
     * Public visibility option callback
     */
    function cpt_public_callback() {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" name="cpt_public" id="cpt_public" class="onoffswitch-checkbox" value="true" <?php echo isset($this->editval) ? checked($this->editval['cpt_public'], true) : "checked"; ?> >
            <label class="onoffswitch-label" for="cpt_public">
                <span class="onoffswitch-inner">
                    <span class="onoffswitch-active"><span class="onoffswitch-switch">YES</span></span>
                    <span class="onoffswitch-inactive"><span class="onoffswitch-switch">NO</span></span>
                </span>
            </label>
        </div>
        <?php
    }

    /**
     * Public visibility option callback
     */
    function cpt_exclude_from_search_callback() {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" name="cpt_exclude_from_search" id="cpt_exclude_from_search" class="onoffswitch-checkbox" value="true" <?php echo isset($this->editval) ? checked($this->editval['cpt_exclude_from_search'], true) : ""; ?> >
            <label class="onoffswitch-label" for="cpt_exclude_from_search">
               <span class="onoffswitch-inner">
                    <span class="onoffswitch-active"><span class="onoffswitch-switch">YES</span></span>
                    <span class="onoffswitch-inactive"><span class="onoffswitch-switch">NO</span></span>
                </span>
            </label>
        </div>
        <?php
    }

    /**
     * Public visibility option callback
     */
    function cpt_publicly_queryable_callback() {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" name="cpt_publicly_queryable" id="cpt_publicly_queryable" class="onoffswitch-checkbox" value="true" <?php echo isset($this->editval) ? checked($this->editval['cpt_publicly_queryable'], true) : "checked"; ?> >
            <label class="onoffswitch-label" for="cpt_publicly_queryable">
                <span class="onoffswitch-inner">
                    <span class="onoffswitch-active"><span class="onoffswitch-switch">YES</span></span>
                    <span class="onoffswitch-inactive"><span class="onoffswitch-switch">NO</span></span>
                </span>
            </label>
        </div>
        <?php
    }

    /**
     * Public visibility option callback
     */
    function cpt_show_ui_callback() {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" name="cpt_show_ui" id="cpt_show_ui" class="onoffswitch-checkbox" value="true" <?php echo isset($this->editval) ? checked($this->editval['cpt_show_ui'], true) : "checked"; ?> >
            <label class="onoffswitch-label" for="cpt_show_ui">
                <span class="onoffswitch-inner">
                    <span class="onoffswitch-active"><span class="onoffswitch-switch">YES</span></span>
                    <span class="onoffswitch-inactive"><span class="onoffswitch-switch">NO</span></span>
                </span>
            </label>
        </div>
        <?php
    }

    /**
     * Public visibility option callback
     */
    function cpt_show_in_nav_menus_callback() {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" name="cpt_show_in_nav_menus" id="cpt_show_in_nav_menus" class="onoffswitch-checkbox" value="true" <?php echo isset($this->editval) ? checked($this->editval['cpt_show_in_nav_menus'], true) : "checked"; ?> >
            <label class="onoffswitch-label" for="cpt_show_in_nav_menus">
                <span class="onoffswitch-inner">
                    <span class="onoffswitch-active"><span class="onoffswitch-switch">YES</span></span>
                    <span class="onoffswitch-inactive"><span class="onoffswitch-switch">NO</span></span>
                </span>
            </label>
        </div>
        <?php
    }

    /**
     * Public visibility option callback
     */
    function cpt_show_in_menu_callback() {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" name="cpt_show_in_menu" id="cpt_show_in_menu" class="onoffswitch-checkbox" value="true" <?php echo isset($this->editval) ? checked($this->editval['cpt_show_in_menu'], true) : "checked"; ?> >
            <label class="onoffswitch-label" for="cpt_show_in_menu">
                <span class="onoffswitch-inner">
                    <span class="onoffswitch-active"><span class="onoffswitch-switch">YES</span></span>
                    <span class="onoffswitch-inactive"><span class="onoffswitch-switch">NO</span></span>
                </span>
            </label>
        </div>
        <?php
    }

    /**
     * Public visibility option callback
     */
    function cpt_show_in_admin_bar_callback() {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" name="cpt_show_in_admin_bar" id="cpt_show_in_admin_bar" class="onoffswitch-checkbox" value="true" <?php echo isset($this->editval) ? checked($this->editval['cpt_show_in_admin_bar'], true) : "checked"; ?> >
            <label class="onoffswitch-label" for="cpt_show_in_admin_bar">
                <span class="onoffswitch-inner">
                    <span class="onoffswitch-active"><span class="onoffswitch-switch">YES</span></span>
                    <span class="onoffswitch-inactive"><span class="onoffswitch-switch">NO</span></span>
                </span>
            </label>
        </div>
        <?php
    }

    /**
     * Public visibility option callback
     */
    function cpt_menu_position_callback() {
        ?>
        <input type="number" name="cpt_menu_position" value="<?php echo isset($this->editval) ? $this->editval['cpt_menu_position'] : "25" ?>" />
        <?php
    }

    /**
     * Public visibility option callback
     */
    function cpt_capability_type_callback() {
        ?>
        <input type="checkbox"  name="cpt_capability_type[]"  value="post"  <?php isset($this->editval) ? checked(in_array("post", $this->editval["cpt_capability_type"]), true) : ""; ?>/> Post<br/>
        <input type="checkbox"  name="cpt_capability_type[]"  value="page"  <?php isset($this->editval) ? checked(in_array("page", $this->editval["cpt_capability_type"]), true) : ""; ?>/> Page<br/>
        <?php
    }

    /**
     * Public visibility option callback
     */
    function cpt_capabilities_callback() {
        ?>
        <input type="checkbox"  name="cpt_capabilities[]"  value="edit_post"  <?php isset($this->editval) ? checked(in_array("edit_post", $this->editval["cpt_capabilities"]), true) : ""; ?>/> Edit Post<br/>
        <input type="checkbox"  name="cpt_capabilities[]"  value="read_post"  <?php isset($this->editval) ? checked(in_array("read_post", $this->editval["cpt_capabilities"]), true) : ""; ?>/> Read Post<br/>
        <input type="checkbox"  name="cpt_capabilities[]"  value="delete_post"  <?php isset($this->editval) ? checked(in_array("delete_post", $this->editval["cpt_capabilities"]), true) : ""; ?>/> Delete Post<br/>
        <input type="checkbox"  name="cpt_capabilities[]"  value="edit_posts"  <?php isset($this->editval) ? checked(in_array("edit_posts", $this->editval["cpt_capabilities"]), true) : ""; ?>/> Edit Posts<br/>
        <input type="checkbox"  name="cpt_capabilities[]"  value="edit_others_posts"  <?php isset($this->editval) ? checked(in_array("edit_others_posts", $this->editval["cpt_capabilities"]), true) : ""; ?>/> Edit Others Posts<br/>
        <input type="checkbox"  name="cpt_capabilities[]"  value="publish_posts"  <?php isset($this->editval) ? checked(in_array("publish_posts", $this->editval["cpt_capabilities"]), true) : ""; ?>/> Publish Posts<br/>
        <input type="checkbox"  name="cpt_capabilities[]"  value="read_private_posts"  <?php isset($this->editval) ? checked(in_array("read_private_posts", $this->editval["cpt_capabilities"]), true) : ""; ?>/> Read Private Posts<br/>
        <input type="checkbox"  name="cpt_capabilities[]"  value="delete_posts"  <?php isset($this->editval) ? checked(in_array("delete_posts", $this->editval["cpt_capabilities"]), true) : ""; ?>/> Delete Posts<br/>
        <input type="checkbox"  name="cpt_capabilities[]"  value="delete_private_posts"  <?php isset($this->editval) ? checked(in_array("delete_private_posts", $this->editval["cpt_capabilities"]), true) : ""; ?>/> Delete Private Posts<br/>
        <input type="checkbox"  name="cpt_capabilities[]"  value="delete_published_posts"  <?php isset($this->editval) ? checked(in_array("delete_published_posts", $this->editval["cpt_capabilities"]), true) : ""; ?>/> Delete Published Posts<br/>
        <input type="checkbox"  name="cpt_capabilities[]"  value="delete_others_posts"  <?php isset($this->editval) ? checked(in_array("delete_others_posts", $this->editval["cpt_capabilities"]), true) : ""; ?>/> Delete Others Posts<br/>
        <input type="checkbox"  name="cpt_capabilities[]"  value="edit_private_posts"  <?php isset($this->editval) ? checked(in_array("edit_private_posts", $this->editval["cpt_capabilities"]), true) : ""; ?>/> Edit Private Posts<br/>
        <input type="checkbox"  name="cpt_capabilities[]"  value="edit_published_posts"  <?php isset($this->editval) ? checked(in_array("edit_published_posts", $this->editval["cpt_capabilities"]), true) : ""; ?>/> Edit Published Posts<br/>
        <?php
    }

    /**
     * Achive option callback
     */
    function cpt_hierarchical_callback() {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" name="cpt_hierarchical" id="cpt_hierarchical" class="onoffswitch-checkbox" value="true" <?php isset($this->editval) ? checked($this->editval['cpt_hierarchical'], true) : ""; ?>>
            <label class="onoffswitch-label" for="cpt_hierarchical">
                 <span class="onoffswitch-inner">
                    <span class="onoffswitch-active"><span class="onoffswitch-switch">YES</span></span>
                    <span class="onoffswitch-inactive"><span class="onoffswitch-switch">NO</span></span>
                </span>
            </label>
        </div>

        <?php
    }

    /**
     * Public visibility option callback
     */
    function cpt_taxonomies_callback() {
        ?>
        <input type="checkbox"  name="cpt_taxonomies[]"  value="category"  <?php isset($this->editval) ? checked(in_array("category", $this->editval["cpt_taxonomies"]), true) : ""; ?>/> Category<br/>
        <input type="checkbox"  name="cpt_taxonomies[]"  value="post_tag"  <?php isset($this->editval) ? checked(in_array("post_tag", $this->editval["cpt_taxonomies"]), true) : ""; ?>/> Tag<br/>
        <?php
    }

    /**
     * Achive option callback
     */
    function cpt_has_archive_callback() {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" name="cpt_has_archive" id="cpt_has_archive" class="onoffswitch-checkbox" value="true" <?php isset($this->editval) ? checked($this->editval['cpt_has_archive'], true) : ""; ?>>
            <label class="onoffswitch-label" for="cpt_has_archive">
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
    function support_callback() {
        ?>
        <input type="checkbox"  name="cpt_supports[]"  value="title"  <?php isset($this->editval) ? checked(in_array("title", $this->editval["cpt_supports"]), true) : ""; ?>/> Title<br/>
        <input type="checkbox"  name="cpt_supports[]"  value="editor"  <?php isset($this->editval) ? checked(in_array("editor", $this->editval["cpt_supports"]), true) : ""; ?>/> Editor<br/>
        <input type="checkbox"  name="cpt_supports[]"  value="author"  <?php isset($this->editval) ? checked(in_array("author", $this->editval["cpt_supports"]), true) : ""; ?>/> Author<br/>
        <input type="checkbox"  name="cpt_supports[]"  value="thumbnail"  <?php isset($this->editval) ? checked(in_array("thumbnail", $this->editval["cpt_supports"]), true) : ""; ?>/> Thumbnail <br/>
        <input type="checkbox"  name="cpt_supports[]"  value="excerpt"  <?php isset($this->editval) ? checked(in_array("excerpt", $this->editval["cpt_supports"]), true) : ""; ?>/> Excerpt<br/>
        <input type="checkbox"  name="cpt_supports[]"  value="trackbacks"  <?php isset($this->editval) ? checked(in_array("trackbacks", $this->editval["cpt_supports"]), true) : ""; ?>/> Trackbacks<br/>
        <input type="checkbox"  name="cpt_supports[]"  value="custom-fields"  <?php isset($this->editval) ? checked(in_array("custom-fields", $this->editval["cpt_supports"]), true) : ""; ?>/> Custom Field<br/>
        <input type="checkbox"  name="cpt_supports[]"  value="comments"  <?php isset($this->editval) ? checked(in_array("comments", $this->editval["cpt_supports"]), true) : ""; ?>/> Comments<br/>
        <input type="checkbox"  name="cpt_supports[]"  value="revisions"  <?php isset($this->editval) ? checked(in_array("revisions", $this->editval["cpt_supports"]), true) : ""; ?>/> Revisions<br/>
        <input type="checkbox"  name="cpt_supports[]"  value="page-attributes"  <?php isset($this->editval) ? checked(in_array("page-attributes", $this->editval["cpt_supports"]), true) : ""; ?>/> Page Attributes<br/>
        <input type="checkbox"  name="cpt_supports[]"  value="post-formats"  <?php isset($this->editval) ? checked(in_array("post-formats", $this->editval["cpt_supports"]), true) : ""; ?>/> Post Formats<br/>
        <?php
    }

}
