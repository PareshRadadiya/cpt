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

// create cpt plugin settings menu
add_action('admin_menu', 'cpt_create_menu');

function cpt_create_menu() {

	//create new top-level  CPT Setting menu
	add_menu_page('CPT Settings', 'CPT Settings', 'administrator', __FILE__, 'cpt_settings_page',plugins_url('/images/cpt-icon.png', __FILE__));

	//call register settings function
	add_action( 'admin_init', 'register_cpt_settings' );
}


function register_cpt_settings() {
	//register our settings 
	register_setting( 'cpt-settings-group', 'post_type' );
}

function cpt_settings_page() {
?>
<div class="wrap">
<h2>CPT Settings</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'cpt-settings-group' ); ?>
    <?php do_settings_sections( 'cpt-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Post Type</th>
        <td><input type="text" name="post_type" value="<?php echo get_option('post_type'); ?>" /></td>
        </tr>
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php } ?>