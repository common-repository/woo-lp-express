<?php

/**
 * Main Tab Class
 * @package    woocommerce-lp-express
 * @subpackage woocommerce-lp-express/includes/classes/admin
 */

/**
 * Don't allow to call this file directly
 **/
if(!defined('ABSPATH')) {
    die;
}

if(!class_exists('Woocommerce_Lp_Express_Tab')) {
    class Woocommerce_Lp_Express_Tab
    {
        /**
         * @var
         * Get set page
         */
        private $page;

        /**
         * @var
         * Get set location
         */
        private $location;

        /**
         * @var
         * Get set name
         */
        private $name;

        /**
         * @var
         * Get set content id
         */
        private $id;

        /**
         * Woocommerce_Lp_Express_Tab constructor.
         * @param $page
         * @param $location
         * @param $name
         * @param $id
         */
        public function __construct($page, $location, $name, $id)
        {
            $this->page = $page;
            $this->location = $location;
            $this->name = $name;
            $this->id = $id;
        }

        /**
         * @return mixed
         */
        public function getName()
        {
            return $this->name;
        }

        /**
         * @return mixed
         */
        public function getLocation()
        {
            return $this->location;
        }

        /**
         * @return mixed
         */
        public function getPage()
        {
            return $this->page;
        }

        /**
         * @return mixed
         */
        public function getID()
        {
            return $this->id;
        }
    }
}