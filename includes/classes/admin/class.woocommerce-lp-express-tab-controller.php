<?php

/**
 * Class that controls tabs in various locations and extensions
 * @package    woocommerce-lp-express
 * @subpackage woocommerce-lp-express/includes/classes/admin
 */

/**
 * Don't allow to call this file directly
 **/
if(!defined('ABSPATH')) {
    die;
}

if(!class_exists('Woocommerce_Lp_Express_Tab_Controller')) {
    class Woocommerce_Lp_Express_Tab_Controller
    {
        /**
         * Gets all current menu tab items
         * @return mixed
         */
        private static function get_current_menu()
        {
            return get_option('lpx_base_menu');
        }

        /**
         * Gets menu tabs for current page
         * @return array
         */
        public static function get_tabs()
        {
            if (self::get_current_menu() !== null) {
                $tabObject = self::get_current_menu();
                return $tabObject;
            }
            return [];
        }

        /**
         * Adds menu tab items
         * @param $tabObject
         */
        public function add_tabs($tabObject)
        {
            $currentTabObject = self::get_current_menu();
            $tabsArray = array();

            //Adds every object name to new array
            foreach ($currentTabObject as $currentTabObjectItem) {
                array_push($tabsArray, $currentTabObjectItem->getName());
            }

            //Checks if current object is already added
            if (!in_array($tabObject->getName(), $tabsArray)) {
                array_push($currentTabObject, $tabObject);
            }

            if ($currentTabObject == null) {
                $currentTabObject = array();
                array_push($currentTabObject, $tabObject);
            }

            update_option("lpx_base_menu", $currentTabObject);
        }

        /**
         * Removes tab by location
         * @param $id
         */
        public function remove_tabs($id)
        {
            $currentTabObject = self::get_current_menu();
            foreach ($currentTabObject as $key => $tabObject) {
                if ($tabObject->getId() == $id)
                    unset($currentTabObject[$key]);
            }
            update_option("lpx_base_menu", $currentTabObject);
        }
    }
}