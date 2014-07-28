<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of cpt-helper
 *
 * @author paresh
 */
class Helper {

//put your code here
    function display_switch_option($args) {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" name="<?php _e($args["field_name"]) ?>" id="<?php _e($args["field_name"]) ?>" class="onoffswitch-checkbox" value="true" <?php echo isset($args["editval"]) ? checked($args["editval"][$args["field_name"]], true) : "checked"; ?>>
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
        <input type="text" name="<?php _e($args["field_name"]) ?>" value="<?php echo isset($args["editval"]) ? $args["editval"][$args["field_name"]] : "" ?>"/>
        <?php
    }

    function display_checkbox_option($args) {
        foreach ($args["field_values"] as $value) {
            ?><input type="checkbox"  name="<?php _e($args["field_name"]) ?>[]"  value="<?php _e($value["field_value"]) ?>"  <?php echo isset($args["editval"]) ? checked(in_array($value["field_value"], $args["editval"][$args["field_name"]]), true) : $value["field_checked"]; ?>/> <?php _e($value["field_label"]) ?><br/>
            <?php
        }
    }

}
