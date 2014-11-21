<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('CW_Admin_Menus')) :
    /**
     * CW_Admin_Menus Class
     */
    class CW_Admin_Menus
    {
        /**
         * @var array
         */
        private $admin_options = array(

            'environment' =>

                array(
                    'label' => 'Environment',
                    'renderer' => 'render_environment'
                ),

            'api_client_id' =>

                array(
                    'label' => 'API Client ID',
                    'description' => 'Get client id from CodesWholesale platform under "WEB API" tab',
                    'renderer' => 'render_options_text'
                ),

            'api_client_secret' =>

                array(
                    'label' => 'API Client secret',
                    'description' => 'Get client id from CodesWholesale platform under "WEB API" tab',
                    'renderer' => 'render_options_text'
                ),

            'orders_auto_complete' =>

                array(
                    'label' => 'Complete orders',
                    'description' => 'Automatically complete order when payment is received',
                    'renderer' => 'render_orders_checkbox'
                ),

            'balance_value_notify' =>

                array(
                    'label' => 'Balance value',
                    'description' => 'If your balance will reach under this value you will receive an email with warning',
                    'renderer' => 'render_options_text'
                ),

            'spread_type' =>

                array(
                    'label' => 'Spread type',
                    'description' => 'Select your spread type',
                    'renderer' => 'render_spread_type'
                ),


            'spread_value' =>

                array(
                    'label' => 'Spread value',
                    'description' => 'Spread for each product. Percent if chosen as "Percent", flat value if chosen "Flat". Spread will be calculated based on price from CodesWholesale and added to product price.',
                    'renderer' => 'render_options_text'
                ),

        );

        /**
         * Hook in tabs.
         */
        public function __construct()
        {
            /**
             * For admin only
             */
            if (is_admin()) {
                // General plugin setup
                add_action('admin_menu', array($this, 'add_admin_menu'));
                add_action('admin_init', array($this, 'admin_construct'));
            }
        }

        /**
         * Add menu items
         */
        public function add_admin_menu()
        {
            add_menu_page('Codes Wholesale', 'Codes Wholesale', 'manage_options', 'codeswholesale', array($this, 'set_up_admin_page'), 'dashicons-admin-codeswholesale', 30);
            // add_submenu_page( 'codeswholesale', 'Check orders', 'Check orders', 'manage_options', 'cw-check-orders', array($this, 'check_orders'));
        }

        /**
         *
         */
        public function admin_construct()
        {
            register_setting('cw-settings-group', 'cw_options');
            add_settings_section('cw-settings-section', 'General settings', array($this, 'section_one_callback'), 'cw_options_page_slug');

            $options = $this->get_options();

            foreach ($this->admin_options as $option_key => $option) {

                add_settings_field($option_key, $option['label'], array($this, $option['renderer']), 'cw_options_page_slug', 'cw-settings-section',
                    array(
                        'name' => $option_key,
                        'options' => $options,
                    ));

            }
        }

        /**
         *
         */
        public function section_one_callback()
        {
            // section one description
        }

        /*
         * Render a text field
         *
         * @access public
         * @param array $args
         * @return void
         */
        public function render_options_text($args = array())
        {
            printf(
                '<input type="text" id="%s" name="cw_options[%s]" value="%s" /><p class="description">%s</p>',
                $args['name'],
                $args['name'],
                $args['options'][$args['name']],
                $this->admin_options[$args['name']]['description']
            );
        }

        /**
         * @param array $args
         */
        public function render_orders_checkbox($args = array())
        {
            printf(
                '<input type="checkbox" id="%s" name="cw_options[%s]" value="1" %s /><p class="description">%s</p>',
                $args['name'],
                $args['name'],
                $args['options'][$args['name']] == 1 ? "checked" : "",
                $this->admin_options[$args['name']]['description']
            );
        }

        /*
         * Render a text field
         *
         * @access public
         * @param array $args
         * @return void
         */
        public function render_environment($args = array())
        {
            ?>
            <label title="Sandbox">
                <input type="radio" name="cw_options[<?php echo $args['name'] ?>]" value="0"
                       class="cw_env_type" <?php if ($args['options'][$args['name']] == 0) { ?> checked <?php } ?>>
                <span>Sandbox</span>
            </label> <br/> <br />
            <label title="Live" style="padding-top:10px;">
                <input type="radio" name="cw_options[<?php echo $args['name'] ?>]" value="1"
                       class="cw_env_type" <?php if ($args['options'][$args['name']] == 1) { ?> checked <?php } ?>>
                <span>Live</span>
            </label>
            <?php
        }

        /*
         * Render spread type
         *
         * @access public
         * @param array $args
         * @return void
         */
        public function render_spread_type($args = array())
        {
            ?>
            <label title="Flat">
                <input type="radio" name="cw_options[<?php echo $args['name'] ?>]" value="flat"
                       class="cw_env_type" <?php if ($args['options'][$args['name']] == "flat") { ?> checked <?php } ?>>
                <span>Flat</span>
            </label> <br/> <br />
            <label title="Percent" style="padding-top:10px;">
                <input type="radio" name="cw_options[<?php echo $args['name'] ?>]" value="percent"
                       class="cw_env_type" <?php if ($args['options'][$args['name']] == "percent") { ?> checked <?php } ?>>
                <span>Percent</span>
            </label>
        <?php
        }

        /**
         * Set up admin form menu
         *
         *
         *
         */
        public function set_up_admin_page()
        {
            $account = null;
            $error = null;

            try {
                CW()->refresh_codes_wholesale_client();
                $account = CW()->get_codes_wholesale_client()->getAccount();
            } catch (Exception $e) {
                $error = $e;
            }

            ?>
            <div class="wrap">
                <form action="options.php" method="POST">
                    <div id="poststuff">
                        <div id="post-body" class="metabox-holder columns-2">
                            <div id="post-body-content">
                                <h2>CodesWholesale Options</h2>
                                <?php settings_fields('cw-settings-group'); ?>
                                <?php do_settings_sections('cw_options_page_slug'); ?>
                                <?php submit_button(); ?>
                            </div>

                            <div id="postbox-container-1" class="postbox-container">

                                <div id="woocommerce_dashboard_status" class="postbox ">

                                    <div class="handlediv" title="Click to toggle"><br></div>
                                    <h3 class="hndle"><span>Integration status</span></h3>

                                    <div class="inside">
                                        <ul>

                                            <?php if ($error) : ?>

                                                <li class="updated">
                                                    <p><strong>Connection failed.</strong></p>
                                                </li>

                                                <li>
                                                    <b>Error:</b> <?php echo $error->getMessage(); ?>
                                                </li>

                                            <?php endif; ?>

                                            <?php if ($account) : ?>

                                                <li class="updated">
                                                    <p><strong>Successfully connected.</strong></p>
                                                </li>
                                                <li>
                                                    <?php echo $account->getFullName(); ?>
                                                </li>
                                                <li>
                                                    <?php echo $account->getEmail(); ?>
                                                </li>

                                                <li>
                                                    <b>Money to use:</b>
                                                    <?php echo "€" . number_format($account->getTotalToUse(), 2, '.', ''); ?>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <script type="text/javascript">

                var firstGo = 0;

                jQuery(".cw_env_type").change(function (val) {

                    var envType = jQuery(".cw_env_type:checked").val();

                    if (envType == 0) {

                        jQuery(".form-table tr:eq(1)").hide();
                        jQuery(".form-table tr:eq(1) input").val('<?php echo CW_Install::$default_client_id; ?>');
                        jQuery(".form-table tr:eq(2)").hide();
                        jQuery(".form-table tr:eq(2) input").val('<?php echo CW_Install::$default_client_secret; ?>');

                    } else {

                        jQuery(".form-table tr:eq(1)").show();
                        jQuery(".form-table tr:eq(2)").show();

                        if(firstGo > 1){
                            jQuery(".form-table tr:eq(1) input").val('');
                            jQuery(".form-table tr:eq(2) input").val('');
                        }

                    }

                    firstGo++;
                });

                jQuery(".cw_env_type").change();

            </script>
        <?php
        }

        /*
         * Get plugin options set by user
         *
         * @access public
         * @return array
         */
        public function get_options()
        {
            return CW()->instance()->get_options();
        }


        /**
         *
         */
        public function check_orders()
        {
            $term = get_term_by('slug', 'completed', 'shop_order_status');

            $customer_orders = get_posts(array(

                'post_type' => 'shop_order',

                'meta_key' => '_codeswholesale_filled',
                'meta_value' => 1,

                'tax_query' => array(
                    array(
                        'taxonomy' => 'shop_order_status',
                        'field' => 'term_id',
                        'terms' => $term->term_id)
                ),

                'numberposts' => 1

            ));

            foreach ($customer_orders as $k => $v) {
                $order = new WC_Order($customer_orders[$k]->ID);

                $a = new CW_SendKeys();
                $a->send_keys_for_order($customer_orders[$k]->ID);

                echo $order->order_id;
                echo 'Order by ' . $order->billing_first_name . ' ' . $order->billing_last_name . "<br />";
                echo $order->needs_payment() . "<br />";
            }
        }

    }

endif;

return new CW_Admin_Menus();