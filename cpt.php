<?php
/*
  Plugin Name: Custom Post Type Generator
  Plugin URI:
  Description: Custom Post Type Generator lets you create Custom Post Types and custom Taxonomies in a user friendly way.
  Author: rtCamp
  Version: 0.0.1
  Author URI:
 */


$cptg = new Cptg();

class Cptg {

    // vars
    var $dir;

    function __construct() {

        // vars
        $this->dir = plugins_url('', __FILE__);


        // actions
        add_action('init', array($this, 'init'));  //hook for init cpt for custom post type generator
        add_action('init', array($this, 'cptm_create_custom_post_types')); // hook for register cpt added by user using ihis plugin
        add_action('admin_menu', array($this, 'cptm_admin_menu')); // hook for add menu option for Generate CPT
        add_action('admin_enqueue_scripts', array($this, 'cptm_styles')); // hook for enququing stylesheet for UI of Generate CPT
        add_action('add_meta_boxes', array($this, 'cptm_create_meta_boxes')); // hook to add metabox for options
        add_action('save_post', array($this, 'cptm_save_post')); //hook to save post_meta for all option on Publish or Update of CPT
        add_action('manage_posts_custom_column', array($this, 'cptm_custom_columns'), 10, 2);
        add_action('manage_posts_custom_column', array($this, 'cptm_tax_custom_columns'), 10, 2);
        add_action('admin_footer', array($this, 'cptm_admin_footer'));
        add_action('wp_prepare_attachment_for_js', array($this, 'wp_prepare_attachment_for_js'), 10, 3);

        // filters
        add_filter('manage_cptm_posts_columns', array($this, 'cptm_change_columns'));
        add_filter('manage_edit-cptm_sortable_columns', array($this, 'cptm_sortable_columns'));
        add_filter('manage_cptm_tax_posts_columns', array($this, 'cptm_tax_change_columns'));
        add_filter('manage_edit-cptm_tax_sortable_columns', array($this, 'cptm_tax_sortable_columns'));
        add_filter('post_updated_messages', array($this, 'cptm_post_updated_messages'));
        add_filter('wp_insert_post_data', array($this, 'default_title'), 10, 2);

        // set textdomain
        load_plugin_textdomain('cptm', false, basename(dirname(__FILE__)) . '/lang');
    }

    /*
     * Register cpt for plugin of type cptm
     */

    public function init() {

        // Create cptm post type
        $labels = array(
            'name' => __('Custom Post Type Generator', 'cptm'),
            'singular_name' => __('Custom Post Type', 'cptm'),
            'add_new' => __('Add New', 'cptm'),
            'add_new_item' => __('Add New Custom Post Type', 'cptm'),
            'edit_item' => __('Edit Custom Post Type', 'cptm'),
            'new_item' => __('New Custom Post Type', 'cptm'),
            'view_item' => __('View Custom Post Type', 'cptm'),
            'search_items' => __('Search Custom Post Types', 'cptm'),
            'not_found' => __('No Custom Post Types found', 'cptm'),
            'not_found_in_trash' => __('No Custom Post Types found in Trash', 'cptm'),
        );

        register_post_type('cptm', array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            '_builtin' => false,
            'capability_type' => 'page',
            'hierarchical' => false,
            'rewrite' => false,
            'query_var' => "cptm",
            'supports' => array(
                'title'
            ),
            'show_in_menu' => false,
        ));

        // Create cptm_tax post type
        $labels = array(
            'name' => __('Custom Taxonomies Generator', 'cptm'),
            'singular_name' => __('Custom Taxonomy', 'cptm'),
            'add_new' => __('Add New', 'cptm'),
            'add_new_item' => __('Add New Custom Taxonomy', 'cptm'),
            'edit_item' => __('Edit Custom Taxonomy', 'cptm'),
            'new_item' => __('New Custom Taxonomy', 'cptm'),
            'view_item' => __('View Custom Taxonomy', 'cptm'),
            'search_items' => __('Search Custom Taxonomies', 'cptm'),
            'not_found' => __('No Custom Taxonomies found', 'cptm'),
            'not_found_in_trash' => __('No Custom Taxonomies found in Trash', 'cptm'),
        );

        register_post_type('cptm_tax', array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            '_builtin' => false,
            'capability_type' => 'page',
            'hierarchical' => false,
            'rewrite' => false,
            'query_var' => "cptm_tax",
            'supports' => array(
                'title'
            ),
            'show_in_menu' => false,
        ));

        // Add image size for the Custom Post Type icon
        if (function_exists('add_image_size')) {
            add_image_size('cptm_icon', 16, 16, true);
        }
    }

    /**
     * Add admin menu for Generate CPT
     */
    public function cptm_admin_menu() {

        // add cptm page to options menu
        add_utility_page(__("CPT Maker", 'cptm'), __("Generate CPT", 'cptm'), 'manage_options', 'edit.php?post_type=cptm', '', $this->dir . '/img/cpt-icon-red.png');
        add_submenu_page('edit.php?post_type=cptm', __("Taxonomies", 'cptm'), __("Taxonomies", 'cptm'), 'manage_options', 'edit.php?post_type=cptm_tax');
    }

    /**
     * enqueuing styles for cptm plugin page
     * @param type $hook
     */
    public function cptm_styles($hook) {

        // register overview style
        if ($hook == 'edit.php' && isset($_GET['post_type']) && ( $_GET['post_type'] == 'cptm' || $_GET['post_type'] == 'cptm_tax' )) {
            wp_register_style('cptm_admin_styles', $this->dir . '/css/overview.css');
            wp_enqueue_style('cptm_admin_styles');

            wp_register_script('cptm_admin_js', $this->dir . '/js/overview.js', 'jquery', '0.0.1', true);
            wp_enqueue_script('cptm_admin_js');

            wp_enqueue_script(array('jquery', 'thickbox'));
            wp_enqueue_style(array('thickbox'));
        }

        // register add / edit style
        if (( $hook == 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'cptm' ) || ( $hook == 'post.php' && isset($_GET['post']) && get_post_type($_GET['post']) == 'cptm' ) || ( $hook == 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'cptm_tax' ) || ( $hook == 'post.php' && isset($_GET['post']) && get_post_type($_GET['post']) == 'cptm_tax' )) {
            wp_register_style('cptm_add_edit_styles', $this->dir . '/css/add-edit.css');
            wp_enqueue_style('cptm_add_edit_styles');

            wp_register_script('cptm_admin__add_edit_js', $this->dir . '/js/add-edit.js', 'jquery', '0.0.1', true);
            wp_enqueue_script('cptm_admin__add_edit_js');

            wp_enqueue_media();
        }
    }

    /**
     * Register all CPT of from post_meta of type cptm at init
     */
    public function cptm_create_custom_post_types() {

        // vars
        $cptms = array();
        $cptm_taxs = array();

        // query custom post types
        $get_cptm = array(
            'numberposts' => -1,
            'post_type' => 'cptm',
            'post_status' => 'publish',
            'suppress_filters' => false,
        );
        $cptm_post_types = get_posts($get_cptm);

        // create array of post meta
        if ($cptm_post_types) {
            foreach ($cptm_post_types as $cptm) {
                $cptm_meta = get_post_meta($cptm->ID, '', true);

                // text
                $cptm_name = ( array_key_exists('cptm_name', $cptm_meta) && $cptm_meta['cptm_name'][0] ? esc_html($cptm_meta['cptm_name'][0]) : 'no_name' );
                $cptm_label = ( array_key_exists('cptm_label', $cptm_meta) && $cptm_meta['cptm_label'][0] ? esc_html($cptm_meta['cptm_label'][0]) : $cptm_name );
                $cptm_singular_name = ( array_key_exists('cptm_singular_name', $cptm_meta) && $cptm_meta['cptm_singular_name'][0] ? esc_html($cptm_meta['cptm_singular_name'][0]) : $cptm_label );
                $cptm_description = ( array_key_exists('cptm_description', $cptm_meta) && $cptm_meta['cptm_description'][0] ? $cptm_meta['cptm_description'][0] : '' );
                $cptm_icon = ( array_key_exists('cptm_icon', $cptm_meta) && $cptm_meta['cptm_icon'][0] ? $cptm_meta['cptm_icon'][0] : false );
                $cptm_custom_rewrite_slug = ( array_key_exists('cptm_custom_rewrite_slug', $cptm_meta) && $cptm_meta['cptm_custom_rewrite_slug'][0] ? esc_html($cptm_meta['cptm_custom_rewrite_slug'][0]) : $cptm_name );
                $cptm_menu_position = ( array_key_exists('cptm_menu_position', $cptm_meta) && $cptm_meta['cptm_menu_position'][0] ? (int) $cptm_meta['cptm_menu_position'][0] : null );

                // dropdown
                $cptm_public = ( array_key_exists('cptm_public', $cptm_meta) && $cptm_meta['cptm_public'][0] == '1' ? true : false );
                $cptm_show_ui = ( array_key_exists('cptm_show_ui', $cptm_meta) && $cptm_meta['cptm_show_ui'][0] == '1' ? true : false );
                $cptm_has_archive = ( array_key_exists('cptm_has_archive', $cptm_meta) && $cptm_meta['cptm_has_archive'][0] == '1' ? true : false );
                $cptm_exclude_from_search = ( array_key_exists('cptm_exclude_from_search', $cptm_meta) && $cptm_meta['cptm_exclude_from_search'][0] == '1' ? true : false );
                $cptm_capability_type = ( array_key_exists('cptm_capability_type', $cptm_meta) && $cptm_meta['cptm_capability_type'][0] ? $cptm_meta['cptm_capability_type'][0] : 'post' );
                $cptm_hierarchical = ( array_key_exists('cptm_hierarchical', $cptm_meta) && $cptm_meta['cptm_hierarchical'][0] == '1' ? true : false );
                $cptm_rewrite = ( array_key_exists('cptm_rewrite', $cptm_meta) && $cptm_meta['cptm_rewrite'][0] == '1' ? true : false );
                $cptm_withfront = ( array_key_exists('cptm_withfront', $cptm_meta) && $cptm_meta['cptm_withfront'][0] == '1' ? true : false );
                $cptm_feeds = ( array_key_exists('cptm_feeds', $cptm_meta) && $cptm_meta['cptm_feeds'][0] == '1' ? true : false );
                $cptm_pages = ( array_key_exists('cptm_pages', $cptm_meta) && $cptm_meta['cptm_pages'][0] == '1' ? true : false );
                $cptm_query_var = ( array_key_exists('cptm_query_var', $cptm_meta) && $cptm_meta['cptm_query_var'][0] == '1' ? true : false );
                $cptm_show_in_menu = ( array_key_exists('cptm_show_in_menu', $cptm_meta) && $cptm_meta['cptm_show_in_menu'][0] == '1' ? true : false );

                // checkbox
                $cptm_supports = ( array_key_exists('cptm_supports', $cptm_meta) && $cptm_meta['cptm_supports'][0] ? $cptm_meta['cptm_supports'][0] : 'a:2:{i:0;s:5:"title";i:1;s:6:"editor";}' );
                $cptm_builtin_taxonomies = ( array_key_exists('cptm_builtin_taxonomies', $cptm_meta) && $cptm_meta['cptm_builtin_taxonomies'][0] ? $cptm_meta['cptm_builtin_taxonomies'][0] : 'a:0:{}' );

                $cptm_rewrite_options = array();
                if ($cptm_rewrite) {
                    $cptm_rewrite_options['slug'] = _x($cptm_custom_rewrite_slug, 'URL Slug', 'cptm');
                }
                if ($cptm_withfront) {
                    $cptm_rewrite_options['with_front'] = $cptm_withfront;
                }
                if ($cptm_feeds) {
                    $cptm_rewrite_options['feeds'] = $cptm_feeds;
                }
                if ($cptm_pages) {
                    $cptm_rewrite_options['pages'] = $cptm_pages;
                }

                $cptms[] = array(
                    'cptm_id' => $cptm->ID,
                    'cptm_name' => $cptm_name,
                    'cptm_label' => $cptm_label,
                    'cptm_singular_name' => $cptm_singular_name,
                    'cptm_description' => $cptm_description,
                    'cptm_icon' => $cptm_icon,
                    'cptm_custom_rewrite_slug' => $cptm_custom_rewrite_slug,
                    'cptm_menu_position' => $cptm_menu_position,
                    'cptm_public' => (bool) $cptm_public,
                    'cptm_show_ui' => (bool) $cptm_show_ui,
                    'cptm_has_archive' => (bool) $cptm_has_archive,
                    'cptm_exclude_from_search' => (bool) $cptm_exclude_from_search,
                    'cptm_capability_type' => $cptm_capability_type,
                    'cptm_hierarchical' => (bool) $cptm_hierarchical,
                    'cptm_rewrite' => $cptm_rewrite_options,
                    'cptm_query_var' => (bool) $cptm_query_var,
                    'cptm_show_in_menu' => (bool) $cptm_show_in_menu,
                    'cptm_supports' => unserialize($cptm_supports),
                    'cptm_builtin_taxonomies' => unserialize($cptm_builtin_taxonomies),
                );

                // register custom post types
                if (is_array($cptms)) {
                    foreach ($cptms as $cptm_post_type) {

                        $labels = array(
                            'name' => __($cptm_post_type['cptm_label'], 'cptm'),
                            'singular_name' => __($cptm_post_type['cptm_singular_name'], 'cptm'),
                            'add_new' => __('Add New', 'cptm'),
                            'add_new_item' => __('Add New ' . $cptm_post_type['cptm_singular_name'], 'cptm'),
                            'edit_item' => __('Edit ' . $cptm_post_type['cptm_singular_name'], 'cptm'),
                            'new_item' => __('New ' . $cptm_post_type['cptm_singular_name'], 'cptm'),
                            'view_item' => __('View ' . $cptm_post_type['cptm_singular_name'], 'cptm'),
                            'search_items' => __('Search ' . $cptm_post_type['cptm_label'], 'cptm'),
                            'not_found' => __('No ' . $cptm_post_type['cptm_label'] . ' found', 'cptm'),
                            'not_found_in_trash' => __('No ' . $cptm_post_type['cptm_label'] . ' found in Trash', 'cptm'),
                        );

                        $args = array(
                            'labels' => $labels,
                            'description' => $cptm_post_type['cptm_description'],
                            'menu_icon' => $cptm_post_type['cptm_icon'],
                            'rewrite' => $cptm_post_type['cptm_rewrite'],
                            'menu_position' => $cptm_post_type['cptm_menu_position'],
                            'public' => $cptm_post_type['cptm_public'],
                            'show_ui' => $cptm_post_type['cptm_show_ui'],
                            'has_archive' => $cptm_post_type['cptm_has_archive'],
                            'exclude_from_search' => $cptm_post_type['cptm_exclude_from_search'],
                            'capability_type' => $cptm_post_type['cptm_capability_type'],
                            'hierarchical' => $cptm_post_type['cptm_hierarchical'],
                            'show_in_menu' => $cptm_post_type['cptm_show_in_menu'],
                            'query_var' => $cptm_post_type['cptm_query_var'],
                            'publicly_queryable' => true,
                            '_builtin' => false,
                            'supports' => $cptm_post_type['cptm_supports'],
                            'taxonomies' => $cptm_post_type['cptm_builtin_taxonomies']
                        );

                        if ($cptm_post_type['cptm_name'] != 'no_name')
                            register_post_type($cptm_post_type['cptm_name'], $args);
                    }
                }
            }
        }

        // query custom taxonomies
        $get_cptm_tax = array(
            'numberposts' => -1,
            'post_type' => 'cptm_tax',
            'post_status' => 'publish',
            'suppress_filters' => false,
        );
        $cptm_taxonomies = get_posts($get_cptm_tax);

        // create array of post meta
        if ($cptm_taxonomies) {
            foreach ($cptm_taxonomies as $cptm_tax) {
                $cptm_meta = get_post_meta($cptm_tax->ID, '', true);

                // text
                $cptm_tax_name = ( array_key_exists('cptm_tax_name', $cptm_meta) && $cptm_meta['cptm_tax_name'][0] ? esc_html($cptm_meta['cptm_tax_name'][0]) : 'no_name' );
                $cptm_tax_label = ( array_key_exists('cptm_tax_label', $cptm_meta) && $cptm_meta['cptm_tax_label'][0] ? esc_html($cptm_meta['cptm_tax_label'][0]) : $cptm_tax_name );
                $cptm_tax_singular_name = ( array_key_exists('cptm_tax_singular_name', $cptm_meta) && $cptm_meta['cptm_tax_singular_name'][0] ? esc_html($cptm_meta['cptm_tax_singular_name'][0]) : $cptm_tax_label );
                $cptm_tax_custom_rewrite_slug = ( array_key_exists('cptm_tax_custom_rewrite_slug', $cptm_meta) && $cptm_meta['cptm_tax_custom_rewrite_slug'][0] ? esc_html($cptm_meta['cptm_tax_custom_rewrite_slug'][0]) : $cptm_tax_name );

                // dropdown
                $cptm_tax_show_ui = ( array_key_exists('cptm_tax_show_ui', $cptm_meta) && $cptm_meta['cptm_tax_show_ui'][0] == '1' ? true : false );
                $cptm_tax_hierarchical = ( array_key_exists('cptm_tax_hierarchical', $cptm_meta) && $cptm_meta['cptm_tax_hierarchical'][0] == '1' ? true : false );
                $cptm_tax_rewrite = ( array_key_exists('cptm_tax_rewrite', $cptm_meta) && $cptm_meta['cptm_tax_rewrite'][0] == '1' ? array('slug' => _x($cptm_tax_custom_rewrite_slug, 'URL Slug', 'cptm')) : false );
                $cptm_tax_query_var = ( array_key_exists('cptm_tax_query_var', $cptm_meta) && $cptm_meta['cptm_tax_query_var'][0] == '1' ? true : false );

                // checkbox
                $cptm_tax_post_types = ( array_key_exists('cptm_tax_post_types', $cptm_meta) && $cptm_meta['cptm_tax_post_types'][0] ? $cptm_meta['cptm_tax_post_types'][0] : 'a:0:{}' );

                $cptm_taxs[] = array(
                    'cptm_tax_id' => $cptm_tax->ID,
                    'cptm_tax_name' => $cptm_tax_name,
                    'cptm_tax_label' => $cptm_tax_label,
                    'cptm_tax_singular_name' => $cptm_tax_singular_name,
                    'cptm_tax_custom_rewrite_slug' => $cptm_tax_custom_rewrite_slug,
                    'cptm_tax_show_ui' => (bool) $cptm_tax_show_ui,
                    'cptm_tax_hierarchical' => (bool) $cptm_tax_hierarchical,
                    'cptm_tax_rewrite' => $cptm_tax_rewrite,
                    'cptm_tax_query_var' => (bool) $cptm_tax_query_var,
                    'cptm_tax_builtin_taxonomies' => unserialize($cptm_tax_post_types),
                );

                // register custom post types
                if (is_array($cptm_taxs)) {
                    foreach ($cptm_taxs as $cptm_taxonomy) {

                        $labels = array(
                            'name' => _x($cptm_taxonomy['cptm_tax_label'], 'taxonomy general name', 'cptm'),
                            'singular_name' => _x($cptm_taxonomy['cptm_tax_singular_name'], 'taxonomy singular name'),
                            'search_items' => __('Search ' . $cptm_taxonomy['cptm_tax_label'], 'cptm'),
                            'popular_items' => __('Popular ' . $cptm_taxonomy['cptm_tax_label'], 'cptm'),
                            'all_items' => __('All ' . $cptm_taxonomy['cptm_tax_label'], 'cptm'),
                            'parent_item' => __('Parent ' . $cptm_taxonomy['cptm_tax_singular_name'], 'cptm'),
                            'parent_item_colon' => __('Parent ' . $cptm_taxonomy['cptm_tax_singular_name'], 'cptm' . ':'),
                            'edit_item' => __('Edit ' . $cptm_taxonomy['cptm_tax_singular_name'], 'cptm'),
                            'update_item' => __('Update ' . $cptm_taxonomy['cptm_tax_singular_name'], 'cptm'),
                            'add_new_item' => __('Add New ' . $cptm_taxonomy['cptm_tax_singular_name'], 'cptm'),
                            'new_item_name' => __('New ' . $cptm_taxonomy['cptm_tax_singular_name'], 'cptm' . ' Name'),
                            'separate_items_with_commas' => __('Seperate ' . $cptm_taxonomy['cptm_tax_label'], 'cptm' . ' with commas'),
                            'add_or_remove_items' => __('Add or remove ' . $cptm_taxonomy['cptm_tax_label'], 'cptm'),
                            'choose_from_most_used' => __('Choose from the most used ' . $cptm_taxonomy['cptm_tax_label'], 'cptm'),
                            'menu_name' => __('All ' . $cptm_taxonomy['cptm_tax_label'], 'cptm')
                        );

                        $args = array(
                            'label' => $cptm_taxonomy['cptm_tax_label'],
                            'labels' => $labels,
                            'rewrite' => $cptm_taxonomy['cptm_tax_rewrite'],
                            'show_ui' => $cptm_taxonomy['cptm_tax_show_ui'],
                            'hierarchical' => $cptm_taxonomy['cptm_tax_hierarchical'],
                            'query_var' => $cptm_taxonomy['cptm_tax_query_var'],
                        );

                        if ($cptm_taxonomy['cptm_tax_name'] != 'no_name')
                            register_taxonomy($cptm_taxonomy['cptm_tax_name'], $cptm_taxonomy['cptm_tax_builtin_taxonomies'], $args);
                    }
                }
            }
        }
    }

    /**
     * Add metabox for options of CPT and taxonomy
     */
    public function cptm_create_meta_boxes() {

        // add options meta box
        add_meta_box(
                'cptm_options', __('Options', 'cptm'), array($this, 'cptm_meta_box'), 'cptm', 'advanced', 'high'
        );
        add_meta_box(
                'cptm_tax_options', __('Options', 'cptm'), array($this, 'cptm_tax_meta_box'), 'cptm_tax', 'advanced', 'high'
        );
    }

// # function cptm_create_meta_boxes()

    /**
     * Options metabox callback for CPT generator
     * @global type $pagenow
     * @param type $post
     */
    public function cptm_meta_box($post) {

        // get post meta values
        $values = get_post_custom($post->ID);

        // text fields
        $cptm_name = isset($values['cptm_name']) ? esc_attr($values['cptm_name'][0]) : '';
        $cptm_label = isset($values['cptm_label']) ? esc_attr($values['cptm_label'][0]) : '';
        $cptm_singular_name = isset($values['cptm_singular_name']) ? esc_attr($values['cptm_singular_name'][0]) : '';
        $cptm_description = isset($values['cptm_description']) ? esc_attr($values['cptm_description'][0]) : '';
        $cptm_icon = isset($values['cptm_icon']) ? esc_attr($values['cptm_icon'][0]) : '';
        $cptm_custom_rewrite_slug = isset($values['cptm_custom_rewrite_slug']) ? esc_attr($values['cptm_custom_rewrite_slug'][0]) : '';
        $cptm_menu_position = isset($values['cptm_menu_position']) ? esc_attr($values['cptm_menu_position'][0]) : '';

        // select fields
        $cptm_public = isset($values['cptm_public']) ? esc_attr($values['cptm_public'][0]) : '';
        $cptm_show_ui = isset($values['cptm_show_ui']) ? esc_attr($values['cptm_show_ui'][0]) : '';
        $cptm_has_archive = isset($values['cptm_has_archive']) ? esc_attr($values['cptm_has_archive'][0]) : '';
        $cptm_exclude_from_search = isset($values['cptm_exclude_from_search']) ? esc_attr($values['cptm_exclude_from_search'][0]) : '';
        $cptm_capability_type = isset($values['cptm_capability_type']) ? esc_attr($values['cptm_capability_type'][0]) : '';
        $cptm_hierarchical = isset($values['cptm_hierarchical']) ? esc_attr($values['cptm_hierarchical'][0]) : '';
        $cptm_rewrite = isset($values['cptm_rewrite']) ? esc_attr($values['cptm_rewrite'][0]) : '';
        $cptm_withfront = isset($values['cptm_withfront']) ? esc_attr($values['cptm_withfront'][0]) : '';
        $cptm_feeds = isset($values['cptm_feeds']) ? esc_attr($values['cptm_feeds'][0]) : '';
        $cptm_pages = isset($values['cptm_pages']) ? esc_attr($values['cptm_pages'][0]) : '';
        $cptm_query_var = isset($values['cptm_query_var']) ? esc_attr($values['cptm_query_var'][0]) : '';
        $cptm_show_in_menu = isset($values['cptm_show_in_menu']) ? esc_attr($values['cptm_show_in_menu'][0]) : '';

        // checkbox fields
        $cptm_supports = isset($values['cptm_supports']) ? unserialize($values['cptm_supports'][0]) : array();
        $cptm_supports_title = ( isset($values['cptm_supports']) && in_array('title', $cptm_supports) ? 'title' : '' );
        $cptm_supports_editor = ( isset($values['cptm_supports']) && in_array('editor', $cptm_supports) ? 'editor' : '' );
        $cptm_supports_excerpt = ( isset($values['cptm_supports']) && in_array('excerpt', $cptm_supports) ? 'excerpt' : '' );
        $cptm_supports_trackbacks = ( isset($values['cptm_supports']) && in_array('trackbacks', $cptm_supports) ? 'trackbacks' : '' );
        $cptm_supports_custom_fields = ( isset($values['cptm_supports']) && in_array('custom-fields', $cptm_supports) ? 'custom-fields' : '' );
        $cptm_supports_comments = ( isset($values['cptm_supports']) && in_array('comments', $cptm_supports) ? 'comments' : '' );
        $cptm_supports_revisions = ( isset($values['cptm_supports']) && in_array('revisions', $cptm_supports) ? 'revisions' : '' );
        $cptm_supports_featured_image = ( isset($values['cptm_supports']) && in_array('thumbnail', $cptm_supports) ? 'thumbnail' : '' );
        $cptm_supports_author = ( isset($values['cptm_supports']) && in_array('author', $cptm_supports) ? 'author' : '' );
        $cptm_supports_page_attributes = ( isset($values['cptm_supports']) && in_array('page-attributes', $cptm_supports) ? 'page-attributes' : '' );
        $cptm_supports_post_formats = ( isset($values['cptm_supports']) && in_array('post-formats', $cptm_supports) ? 'post-formats' : '' );

        $cptm_builtin_taxonomies = isset($values['cptm_builtin_taxonomies']) ? unserialize($values['cptm_builtin_taxonomies'][0]) : array();
        $cptm_builtin_taxonomies_categories = ( isset($values['cptm_builtin_taxonomies']) && in_array('category', $cptm_builtin_taxonomies) ? 'category' : '' );
        $cptm_builtin_taxonomies_tags = ( isset($values['cptm_builtin_taxonomies']) && in_array('post_tag', $cptm_builtin_taxonomies) ? 'post_tag' : '' );

        // nonce
        wp_nonce_field('cptm_meta_box_nonce_action', 'cptm_meta_box_nonce_field');

        // set defaults if new Custom Post Type is being created
        global $pagenow;

        $cptm_supports_title = $pagenow === 'post-new.php' ? 'title' : $cptm_supports_title;
        $cptm_supports_editor = $pagenow === 'post-new.php' ? 'editor' : $cptm_supports_editor;
        $cptm_supports_excerpt = $pagenow === 'post-new.php' ? 'excerpt' : $cptm_supports_excerpt;
        ?>
        <table class="cptm">
            <tr>
                <td class="label">
                    <label for="cptm_name"><span class="required">*</span> <?php _e('Post Type Name', 'cptm'); ?></label>

                </td>
                <td>
                    <input type="text" required="" name="cptm_name" id="cptm_name" class="widefat" tabindex="1" value="<?php echo $cptm_name; ?>" />
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_label"><?php _e('Label', 'cptm'); ?></label>

                </td>
                <td>
                    <input type="text" name="cptm_label" id="cptm_label" class="widefat" tabindex="2" value="<?php echo $cptm_label; ?>" />
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_singular_name"><?php _e('Singular Name', 'cptm'); ?></label>

                </td>
                <td>
                    <input type="text" name="cptm_singular_name" id="cptm_singular_name" class="widefat" tabindex="3" value="<?php echo $cptm_singular_name; ?>" />
                </td>
            </tr>
            <tr>
                <td class="label top">
                    <label for="cptm_description"><?php _e('Description', 'cptm'); ?></label>

                </td>
                <td>
                    <textarea name="cptm_description" id="cptm_description" class="widefat" tabindex="4" rows="4"><?php echo $cptm_description; ?></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="section">
                    <h3><?php _e('Visibility', 'cptm'); ?></h3>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_public"><?php _e('Public', 'cptm'); ?></label>
                </td>
                <td>
                    <select name="cptm_public" id="cptm_public" tabindex="5">
                        <option value="1" <?php selected($cptm_public, '1'); ?>><?php _e('True', 'cptm'); ?> (<?php _e('default', 'cptm'); ?>)</option>
                        <option value="0" <?php selected($cptm_public, '0'); ?>><?php _e('False', 'cptm'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="section">
                    <h3><?php _e('Rewrite Options', 'cptm'); ?></h3>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_rewrite"><?php _e('Rewrite', 'cptm'); ?></label>

                </td>
                <td>
                    <select name="cptm_rewrite" id="cptm_rewrite" tabindex="6">
                        <option value="1" <?php selected($cptm_rewrite, '1'); ?>><?php _e('True', 'cptm'); ?> (<?php _e('default', 'cptm'); ?>)</option>
                        <option value="0" <?php selected($cptm_rewrite, '0'); ?>><?php _e('False', 'cptm'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_withfront"><?php _e('With Front', 'cptm'); ?></label>

                </td>
                <td>
                    <select name="cptm_withfront" id="cptm_withfront" tabindex="7">
                        <option value="1" <?php selected($cptm_withfront, '1'); ?>><?php _e('True', 'cptm'); ?> (<?php _e('default', 'cptm'); ?>)</option>
                        <option value="0" <?php selected($cptm_withfront, '0'); ?>><?php _e('False', 'cptm'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_custom_rewrite_slug"><?php _e('Custom Rewrite Slug', 'cptm'); ?></label>

                </td>
                <td>
                    <input type="text" name="cptm_custom_rewrite_slug" id="cptm_custom_rewrite_slug" class="widefat" tabindex="8" value="<?php echo $cptm_custom_rewrite_slug; ?>" />
                </td>
            </tr>
            <tr>
                <td colspan="2" class="section">
                    <h3><?php _e('Front-end Options', 'cptm'); ?></h3>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_feeds"><?php _e('Feeds', 'cptm'); ?></label>

                </td>
                <td>
                    <select name="cptm_feeds" id="cptm_feeds" tabindex="9">
                        <option value="0" <?php selected($cptm_feeds, '0'); ?>><?php _e('False', 'cptm'); ?> (<?php _e('default', 'cptm'); ?>)</option>
                        <option value="1" <?php selected($cptm_feeds, '1'); ?>><?php _e('True', 'cptm'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_pages"><?php _e('Pages', 'cptm'); ?></label>

                </td>
                <td>
                    <select name="cptm_pages" id="cptm_pages" tabindex="10">
                        <option value="1" <?php selected($cptm_pages, '1'); ?>><?php _e('True', 'cptm'); ?> (<?php _e('default', 'cptm'); ?>)</option>
                        <option value="0" <?php selected($cptm_pages, '0'); ?>><?php _e('False', 'cptm'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_exclude_from_search"><?php _e('Exclude From Search', 'cptm'); ?></label>

                </td>
                <td>
                    <select name="cptm_exclude_from_search" id="cptm_exclude_from_search" tabindex="11">
                        <option value="0" <?php selected($cptm_exclude_from_search, '0'); ?>><?php _e('False', 'cptm'); ?> (<?php _e('default', 'cptm'); ?>)</option>
                        <option value="1" <?php selected($cptm_exclude_from_search, '1'); ?>><?php _e('True', 'cptm'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_has_archive"><?php _e('Has Archive', 'cptm'); ?></label>


                </td>
                <td>
                    <select name="cptm_has_archive" id="cptm_has_archive" tabindex="12">
                        <option value="0" <?php selected($cptm_has_archive, '0'); ?>><?php _e('False', 'cptm'); ?> (<?php _e('default', 'cptm'); ?>)</option>
                        <option value="1" <?php selected($cptm_has_archive, '1'); ?>><?php _e('True', 'cptm'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="section">
                    <h3><?php _e('Admin Menu Options', 'cptm'); ?></h3>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_show_ui"><?php _e('Show UI', 'cptm'); ?></label>

                </td>
                <td>
                    <select name="cptm_show_ui" id="cptm_show_ui" tabindex="13">
                        <option value="1" <?php selected($cptm_show_ui, '1'); ?>><?php _e('True', 'cptm'); ?> (<?php _e('default', 'cptm'); ?>)</option>
                        <option value="0" <?php selected($cptm_show_ui, '0'); ?>><?php _e('False', 'cptm'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_menu_position"><?php _e('Menu Position', 'cptm'); ?></label>

                </td>
                <td>
                    <input type="number" name="cptm_menu_position" id="cptm_menu_position" class="widefat" tabindex="14" value="<?php echo $cptm_menu_position; ?>" />
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_show_in_menu"><?php _e('Show in Menu', 'cptm'); ?></label>

                </td>
                <td>
                    <select name="cptm_show_in_menu" id="cptm_show_in_menu" tabindex="15">
                        <option value="1" <?php selected($cptm_show_in_menu, '1'); ?>><?php _e('True', 'cptm'); ?> (<?php _e('default', 'cptm'); ?>)</option>
                        <option value="0" <?php selected($cptm_show_in_menu, '0'); ?>><?php _e('False', 'cptm'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_menu_position"><?php _e('Icon', 'cptm'); ?></label>
                </td>
                <td>
                    <div class="cptm-icon">
                        <div class="current-cptm-icon"><?php if ($cptm_icon) { ?><img src="<?php echo $cptm_icon; ?>" /><?php } ?></div>
                        <a href="/" class="remove-cptm-icon button-secondary"<?php if (!$cptm_icon) { ?> style="display: none;"<?php } ?>>Remove icon</a>
                        <a  href="/"class="media-uploader-button button-primary" data-post-id="<?php echo $post->ID; ?>"><?php if (!$cptm_icon) { ?><?php _e('Add icon', 'cptm'); ?><?php } else { ?><?php _e('Edit icon', 'cptm'); ?><?php } ?></a>
                    </div>
                    <input type="hidden" name="cptm_icon" id="cptm_icon" class="widefat" value="<?php echo $cptm_icon; ?>" />
                </td>
            </tr>
            <tr>
                <td colspan="2" class="section">
                    <h3><?php _e('Wordpress Integration', 'cptm'); ?></h3>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_capability_type"><?php _e('Capability Type', 'cptm'); ?></label>

                </td>
                <td>
                    <select name="cptm_capability_type" id="cptm_capability_type" tabindex="16">
                        <option value="post" <?php selected($cptm_capability_type, 'post'); ?>><?php _e('Post', 'cptm'); ?> (<?php _e('default', 'cptm'); ?>)</option>
                        <option value="page" <?php selected($cptm_capability_type, 'page'); ?>><?php _e('Page', 'cptm'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_hierarchical"><?php _e('Hierarchical', 'cptm'); ?></label>

                </td>
                <td>
                    <select name="cptm_hierarchical" id="cptm_hierarchical" tabindex="17">
                        <option value="0" <?php selected($cptm_hierarchical, '0'); ?>><?php _e('False', 'cptm'); ?> (<?php _e('default', 'cptm'); ?>)</option>
                        <option value="1" <?php selected($cptm_hierarchical, '1'); ?>><?php _e('True', 'cptm'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_query_var"><?php _e('Query Var', 'cptm'); ?></label>

                </td>
                <td>
                    <select name="cptm_query_var" id="cptm_query_var" tabindex="18">
                        <option value="1" <?php selected($cptm_query_var, '1'); ?>><?php _e('True', 'cptm'); ?> (<?php _e('default', 'cptm'); ?>)</option>
                        <option value="0" <?php selected($cptm_query_var, '0'); ?>><?php _e('False', 'cptm'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label top">
                    <label for="cptm_supports"><?php _e('Supports', 'cptm'); ?></label>

                </td>
                <td>
                    <input type="checkbox" tabindex="19" name="cptm_supports[]" id="cptm_supports_title" value="title" <?php checked($cptm_supports_title, 'title'); ?> /> <label for="cptm_supports_title"><?php _e('Title', 'cptm'); ?> <span class="default">(<?php _e('default', 'cptm'); ?>)</span></label><br />
                    <input type="checkbox" tabindex="20" name="cptm_supports[]" id="cptm_supports_editor" value="editor" <?php checked($cptm_supports_editor, 'editor'); ?> /> <label for="cptm_supports_editor"><?php _e('Editor', 'cptm'); ?> <span class="default">(<?php _e('default', 'cptm'); ?>)</span></label><br />
                    <input type="checkbox" tabindex="21" name="cptm_supports[]" id="cptm_supports_excerpt" value="excerpt" <?php checked($cptm_supports_excerpt, 'excerpt'); ?> /> <label for="cptm_supports_excerpt"><?php _e('Excerpt', 'cptm'); ?> <span class="default">(<?php _e('default', 'cptm'); ?>)</span></label><br />
                    <input type="checkbox" tabindex="22" name="cptm_supports[]" id="cptm_supports_trackbacks" value="trackbacks" <?php checked($cptm_supports_trackbacks, 'trackbacks'); ?> /> <label for="cptm_supports_trackbacks"><?php _e('Trackbacks', 'cptm'); ?></label><br />
                    <input type="checkbox" tabindex="23" name="cptm_supports[]" id="cptm_supports_custom_fields" value="custom-fields" <?php checked($cptm_supports_custom_fields, 'custom-fields'); ?> /> <label for="cptm_supports_custom_fields"><?php _e('Custom Fields', 'cptm'); ?></label><br />
                    <input type="checkbox" tabindex="24" name="cptm_supports[]" id="cptm_supports_comments" value="comments" <?php checked($cptm_supports_comments, 'comments'); ?> /> <label for="cptm_supports_comments"><?php _e('Comments', 'cptm'); ?></label><br />
                    <input type="checkbox" tabindex="25" name="cptm_supports[]" id="cptm_supports_revisions" value="revisions" <?php checked($cptm_supports_revisions, 'revisions'); ?> /> <label for="cptm_supports_revisions"><?php _e('Revisions', 'cptm'); ?></label><br />
                    <input type="checkbox" tabindex="26" name="cptm_supports[]" id="cptm_supports_featured_image" value="thumbnail" <?php checked($cptm_supports_featured_image, 'thumbnail'); ?> /> <label for="cptm_supports_featured_image"><?php _e('Featured Image', 'cptm'); ?></label><br />
                    <input type="checkbox" tabindex="27" name="cptm_supports[]" id="cptm_supports_author" value="author" <?php checked($cptm_supports_author, 'author'); ?> /> <label for="cptm_supports_author"><?php _e('Author', 'cptm'); ?></label><br />
                    <input type="checkbox" tabindex="28" name="cptm_supports[]" id="cptm_supports_page_attributes" value="page-attributes" <?php checked($cptm_supports_page_attributes, 'page-attributes'); ?> /> <label for="cptm_supports_page_attributes"><?php _e('Page Attributes', 'cptm'); ?></label><br />
                    <input type="checkbox" tabindex="29" name="cptm_supports[]" id="cptm_supports_post_formats" value="post-formats" <?php checked($cptm_supports_post_formats, 'post-formats'); ?> /> <label for="cptm_supports_post_formats"><?php _e('Post Formats', 'cptm'); ?></label><br />
                </td>
            </tr>
            <tr>
                <td class="label top">
                    <label for="cptm_builtin_taxonomies"><?php _e('Built-in Taxonomies', 'cptm'); ?></label>
                    <p><?php _e('', 'cptm'); ?></p>
                </td>
                <td>
                    <input type="checkbox" tabindex="30" name="cptm_builtin_taxonomies[]" id="cptm_builtin_taxonomies_categories" value="category" <?php checked($cptm_builtin_taxonomies_categories, 'category'); ?> /> <label for="cptm_builtin_taxonomies_categories"><?php _e('Categories', 'cptm'); ?></label><br />
                    <input type="checkbox" tabindex="31" name="cptm_builtin_taxonomies[]" id="cptm_builtin_taxonomies_tags" value="post_tag" <?php checked($cptm_builtin_taxonomies_tags, 'post_tag'); ?> /> <label for="cptm_builtin_taxonomies_tags"><?php _e('Tags', 'cptm'); ?></label><br />
                </td>
            </tr>
        </table>

        <?php
    }

    /**
     * Options metabox callback for taxonomy generator
     * @param type $post
     */
    public function cptm_tax_meta_box($post) {

        // get post meta values
        $values = get_post_custom($post->ID);

        // text fields
        $cptm_tax_name = isset($values['cptm_tax_name']) ? esc_attr($values['cptm_tax_name'][0]) : '';
        $cptm_tax_label = isset($values['cptm_tax_label']) ? esc_attr($values['cptm_tax_label'][0]) : '';
        $cptm_tax_singular_name = isset($values['cptm_tax_singular_name']) ? esc_attr($values['cptm_tax_singular_name'][0]) : '';
        $cptm_tax_custom_rewrite_slug = isset($values['cptm_tax_custom_rewrite_slug']) ? esc_attr($values['cptm_tax_custom_rewrite_slug'][0]) : '';

        // select fields
        $cptm_tax_show_ui = isset($values['cptm_tax_show_ui']) ? esc_attr($values['cptm_tax_show_ui'][0]) : '';
        $cptm_tax_hierarchical = isset($values['cptm_tax_hierarchical']) ? esc_attr($values['cptm_tax_hierarchical'][0]) : '';
        $cptm_tax_rewrite = isset($values['cptm_tax_rewrite']) ? esc_attr($values['cptm_tax_rewrite'][0]) : '';
        $cptm_tax_query_var = isset($values['cptm_tax_query_var']) ? esc_attr($values['cptm_tax_query_var'][0]) : '';

        // checkbox fields
        $cptm_tax_supports = isset($values['cptm_tax_supports']) ? unserialize($values['cptm_tax_supports'][0]) : array();
        $cptm_tax_supports_title = ( isset($values['cptm_tax_supports']) && in_array('title', $cptm_supports) ? 'title' : '' );
        $cptm_tax_supports_editor = ( isset($values['cptm_tax_supports']) && in_array('editor', $cptm_supports) ? 'editor' : '' );
        $cptm_tax_supports_excerpt = ( isset($values['cptm_tax_supports']) && in_array('excerpt', $cptm_supports) ? 'excerpt' : '' );
        $cptm_tax_supports_trackbacks = ( isset($values['cptm_tax_supports']) && in_array('trackbacks', $cptm_supports) ? 'trackbacks' : '' );
        $cptm_tax_supports_custom_fields = ( isset($values['cptm_tax_supports']) && in_array('custom-fields', $cptm_supports) ? 'custom-fields' : '' );
        $cptm_tax_supports_comments = ( isset($values['cptm_tax_supports']) && in_array('comments', $cptm_supports) ? 'comments' : '' );
        $cptm_tax_supports_revisions = ( isset($values['cptm_tax_supports']) && in_array('revisions', $cptm_supports) ? 'revisions' : '' );
        $cptm_tax_supports_featured_image = ( isset($values['cptm_tax_supports']) && in_array('thumbnail', $cptm_supports) ? 'thumbnail' : '' );
        $cptm_tax_supports_author = ( isset($values['cptm_tax_supports']) && in_array('author', $cptm_supports) ? 'author' : '' );
        $cptm_tax_supports_page_attributes = ( isset($values['cptm_tax_supports']) && in_array('page-attributes', $cptm_supports) ? 'page-attributes' : '' );
        $cptm_tax_supports_post_formats = ( isset($values['cptm_tax_supports']) && in_array('post-formats', $cptm_supports) ? 'post-formats' : '' );

        $cptm_tax_post_types = isset($values['cptm_tax_post_types']) ? unserialize($values['cptm_tax_post_types'][0]) : array();
        $cptm_tax_post_types_post = ( isset($values['cptm_tax_post_types']) && in_array('post', $cptm_tax_post_types) ? 'post' : '' );
        $cptm_tax_post_types_page = ( isset($values['cptm_tax_post_types']) && in_array('page', $cptm_tax_post_types) ? 'page' : '' );

        // nonce
        wp_nonce_field('cptm_meta_box_nonce_action', 'cptm_meta_box_nonce_field');
        ?>
        <table class="cptm">
            <tr>
                <td class="label">
                    <label for="cptm_tax_name"><span class="required">*</span> <?php _e('Taxonomy Name', 'cptm'); ?></label>

                </td>
                <td>
                    <input type="text" required="" name="cptm_tax_name" id="cptm_tax_name" class="widefat" tabindex="1" value="<?php echo $cptm_tax_name; ?>" />
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_tax_label"><?php _e('Label', 'cptm'); ?></label>

                </td>
                <td>
                    <input type="text" name="cptm_tax_label" id="cptm_tax_label" class="widefat" tabindex="2" value="<?php echo $cptm_tax_label; ?>" />
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_tax_singular_name"><?php _e('Singular Name', 'cptm'); ?></label>

                </td>
                <td>
                    <input type="text" name="cptm_tax_singular_name" id="cptm_tax_singular_name" class="widefat" tabindex="3" value="<?php echo $cptm_tax_singular_name; ?>" />
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_tax_show_ui"><?php _e('Show UI', 'cptm'); ?></label>

                </td>
                <td>
                    <select name="cptm_tax_show_ui" id="cptm_tax_show_ui" tabindex="4">
                        <option value="1" <?php selected($cptm_tax_show_ui, '1'); ?>><?php _e('True', 'cptm'); ?> (<?php _e('default', 'cptm'); ?>)</option>
                        <option value="0" <?php selected($cptm_tax_show_ui, '0'); ?>><?php _e('False', 'cptm'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_tax_hierarchical"><?php _e('Hierarchical', 'cptm'); ?></label>

                </td>
                <td>
                    <select name="cptm_tax_hierarchical" id="cptm_tax_hierarchical" tabindex="5">
                        <option value="0" <?php selected($cptm_tax_hierarchical, '0'); ?>><?php _e('False', 'cptm'); ?> (<?php _e('default', 'cptm'); ?>)</option>
                        <option value="1" <?php selected($cptm_tax_hierarchical, '1'); ?>><?php _e('True', 'cptm'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_tax_rewrite"><?php _e('Rewrite', 'cptm'); ?></label>

                </td>
                <td>
                    <select name="cptm_tax_rewrite" id="cptm_tax_rewrite" tabindex="6">
                        <option value="1" <?php selected($cptm_tax_rewrite, '1'); ?>><?php _e('True', 'cptm'); ?> (<?php _e('default', 'cptm'); ?>)</option>
                        <option value="0" <?php selected($cptm_tax_rewrite, '0'); ?>><?php _e('False', 'cptm'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_tax_custom_rewrite_slug"><?php _e('Custom Rewrite Slug', 'cptm'); ?></label>

                </td>
                <td>
                    <input type="text" name="cptm_tax_custom_rewrite_slug" id="cptm_tax_custom_rewrite_slug" class="widefat" tabindex="7" value="<?php echo $cptm_tax_custom_rewrite_slug; ?>" />
                </td>
            </tr>
            <tr>
                <td class="label">
                    <label for="cptm_tax_query_var"><?php _e('Query Var', 'cptm'); ?></label>

                </td>
                <td>
                    <select name="cptm_tax_query_var" id="cptm_tax_query_var" tabindex="8">
                        <option value="1" <?php selected($cptm_tax_query_var, '1'); ?>><?php _e('True', 'cptm'); ?> (<?php _e('default', 'cptm'); ?>)</option>
                        <option value="0" <?php selected($cptm_tax_query_var, '0'); ?>><?php _e('False', 'cptm'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label top">
                    <label for="cptm_tax_post_types"><?php _e('Post Types', 'cptm'); ?></label>
                    <p><?php _e('', 'cptm'); ?></p>
                </td>
                <td>
                    <input type="checkbox" tabindex="9" name="cptm_tax_post_types[]" id="cptm_tax_post_types_post" value="post" <?php checked($cptm_tax_post_types_post, 'post'); ?> /> <label for="cptm_tax_post_types_post"><?php _e('Posts', 'cptm'); ?></label><br />
                    <input type="checkbox" tabindex="10" name="cptm_tax_post_types[]" id="cptm_tax_post_types_page" value="page" <?php checked($cptm_tax_post_types_page, 'page'); ?> /> <label for="cptm_tax_post_types_page"><?php _e('Pages', 'cptm'); ?></label><br />
                    <?php
                    $post_types = get_post_types(array('public' => true, '_builtin' => false));
                    $i = 10;
                    foreach ($post_types as $post_type) {
                        $checked = in_array($post_type, $cptm_tax_post_types) ? 'checked="checked"' : '';
                        ?>
                        <input type="checkbox" tabindex="<?php echo $i; ?>" name="cptm_tax_post_types[]" id="cptm_tax_post_types_<?php echo $post_type; ?>" value="<?php echo $post_type; ?>" <?php echo $checked; ?> /> <label for="cptm_tax_post_types_<?php echo $post_type; ?>"><?php echo ucfirst($post_type); ?></label><br />
                        <?php
                        $i++;
                    }
                    ?>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save all options of CPT and custom taxonomy inside post_meta
     * @param type $post_id
     * @return type
     */
    public function cptm_save_post($post_id) {

        // verify if this is an auto save routine.
        // If it is our form has not been submitted, so we dont want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        // if our nonce isn't there, or we can't verify it, bail
        if (!isset($_POST['cptm_meta_box_nonce_field']) || !wp_verify_nonce($_POST['cptm_meta_box_nonce_field'], 'cptm_meta_box_nonce_action'))
            return;

        // update custom post type meta values
        if (isset($_POST['cptm_name']))
            update_post_meta($post_id, 'cptm_name', sanitize_text_field(str_replace(' ', '', $_POST['cptm_name'])));

        if (isset($_POST['cptm_label']))
            update_post_meta($post_id, 'cptm_label', sanitize_text_field($_POST['cptm_label']));

        if (isset($_POST['cptm_singular_name']))
            update_post_meta($post_id, 'cptm_singular_name', sanitize_text_field($_POST['cptm_singular_name']));

        if (isset($_POST['cptm_description']))
            update_post_meta($post_id, 'cptm_description', esc_textarea($_POST['cptm_description']));

        if (isset($_POST['cptm_icon']))
            update_post_meta($post_id, 'cptm_icon', esc_textarea($_POST['cptm_icon']));

        if (isset($_POST['cptm_public']))
            update_post_meta($post_id, 'cptm_public', esc_attr($_POST['cptm_public']));

        if (isset($_POST['cptm_show_ui']))
            update_post_meta($post_id, 'cptm_show_ui', esc_attr($_POST['cptm_show_ui']));

        if (isset($_POST['cptm_has_archive']))
            update_post_meta($post_id, 'cptm_has_archive', esc_attr($_POST['cptm_has_archive']));

        if (isset($_POST['cptm_exclude_from_search']))
            update_post_meta($post_id, 'cptm_exclude_from_search', esc_attr($_POST['cptm_exclude_from_search']));

        if (isset($_POST['cptm_capability_type']))
            update_post_meta($post_id, 'cptm_capability_type', esc_attr($_POST['cptm_capability_type']));

        if (isset($_POST['cptm_hierarchical']))
            update_post_meta($post_id, 'cptm_hierarchical', esc_attr($_POST['cptm_hierarchical']));

        if (isset($_POST['cptm_rewrite']))
            update_post_meta($post_id, 'cptm_rewrite', esc_attr($_POST['cptm_rewrite']));

        if (isset($_POST['cptm_withfront']))
            update_post_meta($post_id, 'cptm_withfront', esc_attr($_POST['cptm_withfront']));

        if (isset($_POST['cptm_feeds']))
            update_post_meta($post_id, 'cptm_feeds', esc_attr($_POST['cptm_feeds']));

        if (isset($_POST['cptm_pages']))
            update_post_meta($post_id, 'cptm_pages', esc_attr($_POST['cptm_pages']));

        if (isset($_POST['cptm_custom_rewrite_slug']))
            update_post_meta($post_id, 'cptm_custom_rewrite_slug', sanitize_text_field($_POST['cptm_custom_rewrite_slug']));

        if (isset($_POST['cptm_query_var']))
            update_post_meta($post_id, 'cptm_query_var', esc_attr($_POST['cptm_query_var']));

        if (isset($_POST['cptm_menu_position']))
            update_post_meta($post_id, 'cptm_menu_position', sanitize_text_field($_POST['cptm_menu_position']));

        if (isset($_POST['cptm_show_in_menu']))
            update_post_meta($post_id, 'cptm_show_in_menu', esc_attr($_POST['cptm_show_in_menu']));

        $cptm_supports = isset($_POST['cptm_supports']) ? $_POST['cptm_supports'] : array();
        update_post_meta($post_id, 'cptm_supports', $cptm_supports);

        $cptm_builtin_taxonomies = isset($_POST['cptm_builtin_taxonomies']) ? $_POST['cptm_builtin_taxonomies'] : array();
        update_post_meta($post_id, 'cptm_builtin_taxonomies', $cptm_builtin_taxonomies);

        // update taxonomy meta values
        if (isset($_POST['cptm_tax_name']))
            update_post_meta($post_id, 'cptm_tax_name', sanitize_text_field(str_replace(' ', '', $_POST['cptm_tax_name'])));

        if (isset($_POST['cptm_tax_label']))
            update_post_meta($post_id, 'cptm_tax_label', sanitize_text_field($_POST['cptm_tax_label']));

        if (isset($_POST['cptm_tax_singular_name']))
            update_post_meta($post_id, 'cptm_tax_singular_name', sanitize_text_field($_POST['cptm_tax_singular_name']));

        if (isset($_POST['cptm_tax_show_ui']))
            update_post_meta($post_id, 'cptm_tax_show_ui', esc_attr($_POST['cptm_tax_show_ui']));

        if (isset($_POST['cptm_tax_hierarchical']))
            update_post_meta($post_id, 'cptm_tax_hierarchical', esc_attr($_POST['cptm_tax_hierarchical']));

        if (isset($_POST['cptm_tax_rewrite']))
            update_post_meta($post_id, 'cptm_tax_rewrite', esc_attr($_POST['cptm_tax_rewrite']));

        if (isset($_POST['cptm_tax_custom_rewrite_slug']))
            update_post_meta($post_id, 'cptm_tax_custom_rewrite_slug', sanitize_text_field($_POST['cptm_tax_custom_rewrite_slug']));

        if (isset($_POST['cptm_tax_query_var']))
            update_post_meta($post_id, 'cptm_tax_query_var', esc_attr($_POST['cptm_tax_query_var']));

        $cptm_tax_post_types = isset($_POST['cptm_tax_post_types']) ? $_POST['cptm_tax_post_types'] : array();
        update_post_meta($post_id, 'cptm_tax_post_types', $cptm_tax_post_types);
    }

// # function save_post()

    function cptm_change_columns($cols) {

        $cols = array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Post Type', 'cptm'),
            'custom_post_type_name' => __('Custom Post Type Name', 'cptm'),
            'label' => __('Label', 'cptm'),
            'description' => __('Description', 'cptm'),
        );
        return $cols;
    }

// # function cptm_change_columns()

    function cptm_sortable_columns() {

        return array(
            'title' => 'title'
        );
    }

// # function cptm_sortable_columns()

    function cptm_custom_columns($column, $post_id) {

        switch ($column) {
            case "custom_post_type_name":
                echo get_post_meta($post_id, 'cptm_name', true);
                break;
            case "label":
                echo get_post_meta($post_id, 'cptm_label', true);
                break;
            case "description":
                echo get_post_meta($post_id, 'cptm_description', true);
                break;
        }
    }

// # function cptm_custom_columns()

    function cptm_tax_change_columns($cols) {

        $cols = array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Taxonomy', 'cptm'),
            'custom_post_type_name' => __('Custom Taxonomy Name', 'cptm'),
            'label' => __('Label', 'cptm')
        );
        return $cols;
    }

// # function cptm_tax_change_columns()

    function cptm_tax_sortable_columns() {

        return array(
            'title' => 'title'
        );
    }

// # function cptm_tax_sortable_columns()

    function cptm_tax_custom_columns($column, $post_id) {

        switch ($column) {
            case "custom_post_type_name":
                echo get_post_meta($post_id, 'cptm_tax_name', true);
                break;
            case "label":
                echo get_post_meta($post_id, 'cptm_tax_label', true);
                break;
        }
    }

// # function cptm_tax_custom_columns()

    function cptm_admin_footer() {

        global $post_type;
        ?>
        <?php
        if ('cptm' == $post_type) {

            // Get all public Custom Post Types
            $post_types = get_post_types(array('public' => true, '_builtin' => false), 'objects');
            // Get all Custom Post Types created by Custom Post Type Maker
            $cptm_posts = get_posts(array('post_type' => 'cptm'));
            // Remove all Custom Post Types created by the Custom Post Type Maker plugin
            foreach ($cptm_posts as $cptm_post) {
                $values = get_post_custom($cptm_post->ID);
                unset($post_types[$values['cptm_name'][0]]);
            }

            if (count($post_types) != 0) {
                ?>
                <div id="cptm-cpt-overview" class="hidden">
                    <div id="icon-edit" class="icon32 icon32-posts-cptm"><br></div>
                    <h2><?php _e('Other registered Custom Post Types', 'cptm'); ?></h2>
                    <p><?php _e('The Custom Post Types below are registered in WordPress but were not created by the Custom Post Type Maker plugin.', 'cptm'); ?></p>
                    <table class="wp-list-table widefat fixed posts" cellspacing="0">
                        <thead>
                            <tr>
                                <th scope="col" id="cb" class="manage-column column-cb check-column">
                                </th>
                                <th scope="col" id="title" class="manage-column column-title">
                                    <span><?php _e('Post Type', 'cptm'); ?></span><span class="sorting-indicator"></span>
                                </th>
                                <th scope="col" id="custom_post_type_name" class="manage-column column-custom_post_type_name">
                                    <span><?php _e('Custom Post Type Name', 'cptm'); ?></span><span class="sorting-indicator"></span>
                                </th>
                                <th scope="col" id="label" class="manage-column column-label">
                                    <span><?php _e('Label', 'cptm'); ?></span><span class="sorting-indicator"></span>
                                </th>
                                <th scope="col" id="description" class="manage-column column-description">
                                    <span><?php _e('Description', 'cptm'); ?></span><span class="sorting-indicator"></span>
                                </th>
                            </tr>
                        </thead>

                        <tfoot>
                            <tr>
                                <th scope="col" class="manage-column column-cb check-column">
                                </th>
                                <th scope="col" class="manage-column column-title">
                                    <span><?php _e('Post Type', 'cptm'); ?></span><span class="sorting-indicator"></span>
                                </th>
                                <th scope="col" class="manage-column column-custom_post_type_name">
                                    <span><?php _e('Custom Post Type Name', 'cptm'); ?></span><span class="sorting-indicator"></span>
                                </th>
                                <th scope="col" class="manage-column column-label">
                                    <span><?php _e('Label', 'cptm'); ?></span><span class="sorting-indicator"></span>
                                </th>
                                <th scope="col" class="manage-column column-description">
                                    <span><?php _e('Description', 'cptm'); ?></span><span class="sorting-indicator"></span>
                                </th>
                            </tr>
                        </tfoot>

                        <tbody id="the-list">
                            <?php
                            // Create list of all other registered Custom Post Types
                            foreach ($post_types as $post_type) {
                                ?>
                                <tr valign="top">
                                    <th scope="row" class="check-column">
                                    </th>
                                    <td class="post-title page-title column-title">
                                        <strong><?php echo $post_type->labels->name; ?></strong>
                                    </td>
                                    <td class="custom_post_type_name column-custom_post_type_name"><?php echo $post_type->name; ?></td>
                                    <td class="label column-label"><?php echo $post_type->labels->name; ?></td>
                                    <td class="description column-description"><?php echo $post_type->description; ?></td>
                                </tr>
                                <?php
                            }

                            if (count($post_types) == 0) {
                                ?>
                                <tr class="no-items"><td class="colspanchange" colspan="5"><?php _e('No Custom Post Types found', 'cptm'); ?>.</td></tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>

                    <div class="tablenav bottom">
                        <div class="tablenav-pages one-page">
                            <span class="displaying-num">
                                <?php
                                $count = count($post_types);
                                printf(_n('%d item', '%d items', $count), $count);
                                ?>
                            </span>
                            <br class="clear">
                        </div>
                    </div>

                </div>
                <?php
            }
        }
        if ('cptm_tax' == $post_type) {

            // Get all public custom Taxonomies
            $taxonomies = get_taxonomies(array('public' => true, '_builtin' => false), 'objects');
            // Get all custom Taxonomies created by Custom Post Type Maker
            $cptm_tax_posts = get_posts(array('post_type' => 'cptm_tax'));
            // Remove all custom Taxonomies created by the Custom Post Type Maker plugin
            foreach ($cptm_tax_posts as $cptm_tax_post) {
                $values = get_post_custom($cptm_tax_post->ID);
                unset($taxonomies[$values['cptm_tax_name'][0]]);
            }

            if (count($taxonomies) != 0) {
                ?>
                <div id="cptm-cpt-overview" class="hidden">
                    <div id="icon-edit" class="icon32 icon32-posts-cptm"><br></div>
                    <h2><?php _e('Other registered custom Taxonomies', 'cptm'); ?></h2>
                    <p><?php _e('The custom Taxonomies below are registered in WordPress but were not created by the Custom Post Type Maker plugin.', 'cptm'); ?></p>
                    <table class="wp-list-table widefat fixed posts" cellspacing="0">
                        <thead>
                            <tr>
                                <th scope="col" id="cb" class="manage-column column-cb check-column">
                                </th>
                                <th scope="col" id="title" class="manage-column column-title">
                                    <span><?php _e('Taxonomy', 'cptm'); ?></span><span class="sorting-indicator"></span>
                                </th>
                                <th scope="col" id="custom_post_type_name" class="manage-column column-custom_taxonomy_name">
                                    <span><?php _e('Custom Taxonomy Name', 'cptm'); ?></span><span class="sorting-indicator"></span>
                                </th>
                                <th scope="col" id="label" class="manage-column column-label">
                                    <span><?php _e('Label', 'cptm'); ?></span><span class="sorting-indicator"></span>
                                </th>
                            </tr>
                        </thead>

                        <tfoot>
                            <tr>
                                <th scope="col" class="manage-column column-cb check-column">
                                </th>
                                <th scope="col" class="manage-column column-title">
                                    <span><?php _e('Taxonomy', 'cptm'); ?></span><span class="sorting-indicator"></span>
                                </th>
                                <th scope="col" class="manage-column column-custom_post_type_name">
                                    <span><?php _e('Custom Taxonomy Name', 'cptm'); ?></span><span class="sorting-indicator"></span>
                                </th>
                                <th scope="col" class="manage-column column-label">
                                    <span><?php _e('Label', 'cptm'); ?></span><span class="sorting-indicator"></span>
                                </th>
                            </tr>
                        </tfoot>

                        <tbody id="the-list">
                            <?php
                            // Create list of all other registered Custom Post Types
                            foreach ($taxonomies as $taxonomy) {
                                ?>
                                <tr valign="top">
                                    <th scope="row" class="check-column">
                                    </th>
                                    <td class="post-title page-title column-title">
                                        <strong><?php echo $taxonomy->labels->name; ?></strong>
                                    </td>
                                    <td class="custom_post_type_name column-custom_post_type_name"><?php echo $taxonomy->name; ?></td>
                                    <td class="label column-label"><?php echo $taxonomy->labels->name; ?></td>
                                </tr>
                                <?php
                            }

                            if (count($taxonomies) == 0) {
                                ?>
                                <tr class="no-items"><td class="colspanchange" colspan="4"><?php _e('No custom Taxonomies found', 'cptm'); ?>.</td></tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>

                    <div class="tablenav bottom">
                        <div class="tablenav-pages one-page">
                            <span class="displaying-num">
                                <?php
                                $count = count($taxonomies);
                                printf(_n('%d item', '%d items', $count), $count);
                                ?>
                            </span>
                            <br class="clear">
                        </div>
                    </div>

                </div>
                <?php
            }
        }
    }

// # function cptm_admin_footer()

    function cptm_post_updated_messages($messages) {

        global $post, $post_ID;

        $messages['cptm'] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => __('Custom Post Type updated.', 'cptm'),
            2 => __('Custom Post Type updated.', 'cptm'),
            3 => __('Custom Post Type deleted.', 'cptm'),
            4 => __('Custom Post Type updated.', 'cptm'),
            /* translators: %s: date and time of the revision */
            5 => isset($_GET['revision']) ? sprintf(__('Custom Post Type restored to revision from %s', 'cptm'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
            6 => __('Custom Post Type published.', 'cptm'),
            7 => __('Custom Post Type saved.', 'cptm'),
            8 => __('Custom Post Type submitted.', 'cptm'),
            9 => __('Custom Post Type scheduled for.', 'cptm'),
            10 => __('Custom Post Type draft updated.', 'cptm'),
        );

        return $messages;
    }

// # function cptm_post_updated_messages()

    function wp_prepare_attachment_for_js($response, $attachment, $meta) {
        // only for image
        if ($response['type'] != 'image') {
            return $response;
        }


        $attachment_url = $response['url'];
        $base_url = str_replace(wp_basename($attachment_url), '', $attachment_url);

        if (is_array($meta['sizes'])) {
            foreach ($meta['sizes'] as $k => $v) {
                if (!isset($response['sizes'][$k])) {
                    $response['sizes'][$k] = array(
                        'height' => $v['height'],
                        'width' => $v['width'],
                        'url' => $base_url . $v['file'],
                        'orientation' => $v['height'] > $v['width'] ? 'portrait' : 'landscape',
                    );
                }
            }
        }

        return $response;
    }

    function default_title($data, $postarr) {
        if ($data['post_type'] == 'cptm') {
            if (empty($data['post_title'])) {
                $data['post_title'] = $_POST['cptm_name'];
            }
        }
        return $data;
    }

// # function wp_prepare_attachment_for_js()
}
