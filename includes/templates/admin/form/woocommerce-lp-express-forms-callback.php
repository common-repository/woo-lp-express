<?php
/**
 * Form callbacks
 */

/**
 * Don't allow to call this file directly
 **/
if(!defined('ABSPATH')) {
    die;
}

/**
 * API Options callback
 * @param $args
 */
if(!function_exists('wclp_api_options_callback')) {
    function wclp_api_options_callback($args)
    {
        echo "<input type='text' id='" . $args["callback"] ."' name='wc_lp_express_api[" . $args["callback"] . "]' value='" .
            get_option('wc_lp_express_api')[$args["callback"]] . "'>";
        echo "<br/>" . $args['example'];
    }
}

/**
 * Sender info options callback
 * @param $args
 */
if(!function_exists('wclp_sender_options_callback')) {
    function wclp_sender_options_callback($args)
    {
        echo "<input type='text' id='" . $args["callback"] ."' name='wc_lp_express_sender[" . $args["callback"] . "]' value='" .
            get_option('wc_lp_express_sender')[$args["callback"]] . "'>";
        echo "<br/>" . $args['example'];
    }
}

/**
 * Controlls checkboxes for label and manifest generation options
 * @param $args
 */
if(!function_exists('wclp_sender_settings_checkbox_controll')) {
    function wclp_sender_settings_checkbox_controll($args)
    {
        $option = get_option('wc_lp_express_sender_settings')[$args["callback"]];
        $option = $option != null ? $option : 'manual';

        echo "<label style='padding-right:33px'>Rankinis:</label><input type='radio' " . checked('manual', $option, false) . " 
                name='wc_lp_express_sender_settings[" . $args["callback"] . "]' value='manual'><br />";

        echo "<label style='padding-right:10px'>Automatinis:</label><input type='radio' " . checked('auto', $option, false) . " 
                name='wc_lp_express_sender_settings[" . $args["callback"] . "]' value='auto'>";
    }
}

/**
 * Sender settings checkbox for label generation
 * @param $args
 */
if(!function_exists('wclp_sender_settings_label')) {
    function wclp_sender_settings_label($args)
    {
        wclp_sender_settings_checkbox_controll($args);
    }
}
/**
 * Sender settings checkbox for manifest generation
 * @param $args
 */
if(!function_exists('wclp_sender_settings_manifest')) {
    function wclp_sender_settings_manifest($args)
    {
        wclp_sender_settings_checkbox_controll($args);
    }
}

/**
 * Manifest cron job controll
 * @param $args
 */
if(!function_exists('wclp_sender_settings_manifest_cron')) {
    function wclp_sender_settings_manifest_cron($args)
    {
        $option = get_option('wc_lp_express_sender_settings')[$args["callback"]];
        echo "<select id='" . $args["callback"] ."' name='wc_lp_express_sender_settings[" . $args["callback"] . "]'>";
        foreach ($args['hours'] as $key => $value) {
            echo "<option value='" . $key . "' " . selected($option, $key, false) . ">" . $value . "</option>";
        }
        echo "</select> " . __("(Jeigu manifesto generavimas automatinis).", 'lp_express');
    }
}

/**
 * Fixed price for lp express terminal
 * @param $args
 */
if(!function_exists('wclp_sender_settings_fixed_terminal')) {
    function wclp_sender_settings_fixed_terminal($args)
    {
        echo "<input type='number' step='0.1' id='" . $args["callback"] ."' name='wc_lp_express_sender_settings[" . $args["callback"] . "]' 
    value='" . get_option('wc_lp_express_sender_settings')[$args["callback"]] . "'> ";
        echo "{" . $args['additional_info'] . ").";
    }
}

/**
 * Fixed price for lp express courier
 * @param $args
 */
if(!function_exists('wclp_sender_settings_fixed_courier')) {
    function wclp_sender_settings_fixed_courier($args)
    {
        echo "<input type='number' step='0.1' id='" . $args["callback"] ."' name='wc_lp_express_sender_settings[" . $args["callback"] . "]' 
    value='" . get_option('wc_lp_express_sender_settings')[$args["callback"]] . "'> ";
        echo "{" . $args['additional_info'] . ").";
    }
}

if(!function_exists('wclp_print_all')) {
    function wclp_print_all($available_destinations, $args) {
        echo "<div class='" . $args["callback"] . "_container'>";

        echo "<select class='wc_int_country' name='wc_lp_express_sender_settings[all_" . $args["callback"] . "]'>";
        echo '<option value="all" selected>Visi kiti</option>';
        foreach ($available_destinations as $destination) {
            echo "<option value='" . $destination->code . "'>" . $destination->namelt . "</option>";
        }
        echo "</select>";
        echo "<input type='number' step='0.1' id='" . $args["callback"] . "' name='wc_lp_express_sender_settings[all_" . $args["callback"] . "]' 
            value='" . get_option('wc_lp_express_sender_settings')['all_' . $args["callback"]] . "'> ";
        echo "<button type='button' id='wp_lp_fixed_country_append'>+</button>";

        echo "(" . $args['additional_info'] . ").";

        echo "</div>";
    }
}

if(!function_exists('wclp_print_any')) {
    function wclp_print_any($available_destinations, $all_key_exits, $args) {
        //Append existing fixed prices
        foreach ($available_destinations as $destination) {
            if (array_key_exists($destination->code . '_' . $args['callback'],get_option('wc_lp_express_sender_settings'))) {
                echo "<div class='" . $args["callback"] . "_container'>";
                echo "<select class='wc_int_country' name='wc_lp_express_sender_settings[" . $destination->code  . "_" . $args["callback"] . "]'>";

                //Append all option if all key does not exist
                echo !$all_key_exits && @$country_counter++ == 0 ? '<option value="all" selected>Visi kiti</option>' : '';

                //Selected country
                foreach ($available_destinations as $dest) {
                    echo "<option value='" . $dest->code . "' " . selected($dest->code,$destination->code) . ">" . $dest->namelt . "</option>";
                }

                echo "</select>";
                echo "<input type='number' step='0.1' id='" . $args["callback"] ."' name='wc_lp_express_sender_settings[" . $destination->code . "_" . $args["callback"] . "]'
            value='" . get_option('wc_lp_express_sender_settings')[$destination->code . '_' . $args["callback"]] . "'> ";
                echo !$all_key_exits && @$country_counter++ == 1 ? "<button type='button' id='wp_lp_fixed_country_append'>+</button>" :
                    "<button type='button' class='wp_lp_fixed_country_delete'>-</button>";

                echo !$all_key_exits && @$country_counter++ == 2 ? "(" . $args['additional_info'] . ")." : "";

                echo "</div>";
            }
        }
    }
}

/**
 * Fixed price for international lp express shipping
 * @param $args
 */
if(!function_exists('wclp_sender_settings_fixed_international')) {
    function wclp_sender_settings_fixed_international($args)
    {
        $all_key_exits          = array_key_exists('all_' . $args['callback'],get_option('wc_lp_express_sender_settings'));
        $available_destinations = Woocommerce_Lp_Express_Shipping_Methods_Controller::get_available_destinations();
        $any_axists             = false;

        foreach ($available_destinations as $destination) {
            if (get_option('wc_lp_express_sender_settings') != null && array_key_exists($destination->code . '_' . $args['callback'], get_option('wc_lp_express_sender_settings'))) {
                $any_axists = true;
            }
        }

        if ($all_key_exits && $any_axists) {
            wclp_print_all($available_destinations, $args);
            wclp_print_any($available_destinations,$all_key_exits,$args);
        } else if($any_axists) {
            wclp_print_any($available_destinations,$all_key_exits,$args);
        } else {
            wclp_print_all($available_destinations, $args);
        }
    }
}

/**
 * Fixed price for lp express post office
 * @param $args
 */
if(!function_exists('wclp_sender_settings_fixed_post_office')) {
    function wclp_sender_settings_fixed_post_office($args)
    {
        echo "<input type='number' step='0.1' id='" . $args["callback"] ."' name='wc_lp_express_sender_settings[" . $args["callback"] . "]' 
    value='" . get_option('wc_lp_express_sender_settings')[$args["callback"]] . "'> ";
        echo "{" . $args['additional_info'] . ").";
    }
}

/**
 * Lp express terminal type
 * @param $args
 */
if(!function_exists('wclp_sender_settings_terminal_shipping_type')) {
    function wclp_sender_settings_terminal_shipping_type($args)
    {
        //HC is default
        $option = get_option('wc_lp_express_sender_settings')[$args["callback"]];
        $option = $option != null ? $option : 'HC';

        echo "<select id='" . $args["callback"] ."' name='wc_lp_express_sender_settings[" . $args["callback"] . "]'>";
        foreach ($args['methods'] as $key => $value) {
            echo "<option value='" . $key . "' " . selected($option, $key, false) . ">" . $value . "</option>";
        }
        echo "</select> " . __("(Apie tipus galite perskaityti pagalbos skiltyje).", "lp_express");
    }
}