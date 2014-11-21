<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('CW_Receive_Stock_Price_Update')) :

    /**
     *
     */
    class CW_Receive_Stock_Price_Update
    {
        /**
         *
         */
        public function __construct()
        {
            add_action('admin_post_update_price_and_stock', array($this, 'update_price_and_stock'));
            add_action('admin_post_nopriv_update_price_and_stock', array($this, 'update_price_and_stock'));
        }

        /**
         *
         */
        public function update_price_and_stock()
        {
            $product = CW()->get_codes_wholesale_client()->receiveProductAfterStockAndPriceUpdate();
            $cw_product_id = '6313677f-5219-47e4-a067-7401f55c5a3a'; // $product->getProductId();

            $args = array(
                'post_type' => 'product',
                'meta_key' => CodesWholesaleConst::PRODUCT_CODESWHOLESALE_ID_PROP_NAME,
                'meta_value' => $cw_product_id
            );

            $posts = get_posts($args);

            if ($posts) {

                $product = CodesWholesale\Resource\Product::get($cw_product_id);
                $sizeOfStock = $product->getStockQuantity();
                $price = $product->getLowestPrice();

                foreach ($posts as $post) {

                    $product = WC()->product_factory->get_product($post->ID, array());
                    $product->set_stock($sizeOfStock);

                    $thePrice = apply_filters('cw_calculate_price', $price);

                    update_post_meta( $post->ID, '_price', round($thePrice, 2));

                    if ($sizeOfStock == 0) {
                        $product->set_stock_status('outofstock');
                    } else {
                        $product->set_stock_status('instock');
                    }

                }
            }
        }
    }

endif;

new CW_Receive_Stock_Price_Update();


