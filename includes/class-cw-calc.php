<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('CW_Price_Calc')) :

    class CW_Price_Calc
    {
        /**
         *
         */
        public function __construct()
        {
            add_filter('cw_calculate_price', array($this, 'calculate'));
        }

        /**
         * Check what type of spread to calculate
         */
        public function calculate($price)
        {
            $options = CW()->instance()->get_options();
            $action = "calculate_" . $options['spread_type'];
            return call_user_func_array(array($this, $action), array($price, $options['spread_value']));
        }

        /**
         * Calculate flat price
         */
        public function calculate_flat($price, $spread_value)
        {
            return $price + $spread_value;
        }

        /**
         * Calculate percent price
         */
        public function calculate_percent($price, $spread_value)
        {
            return $price + ($price * ($spread_value/100));
        }
    }

endif;

new CW_Price_Calc();