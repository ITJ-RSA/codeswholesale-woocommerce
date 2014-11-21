<?php
/**
 * Plugin Name: CodesWholesale for WooCommerce
 * Plugin URI: http://docs.codeshowlesale.com
 * Depends: WooCommerce
 * Description: Integration with CodesWholesale API.
 * Version: 1.1
 * Author: DevTeam devteam@codeswholesale.com
 * Author URI: http://docs.codeswholesale.com
 * License: GPL2
 */

defined('ABSPATH') or die("No script kiddies please!");

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    die('no WooCommerce plugin found');
}

final class CodesWholesaleOrderFullFilledStatus
{
    const FILLED = 1;
    const TO_FILL = 0;
}

final class CodesWholesaleConst
{
    const ORDER_ITEM_LINKS_PROP_NAME = "_codeswholesale_links";
    const PRODUCT_CODESWHOLESALE_ID_PROP_NAME = "_codeswholesale_product_id";
    const ORDER_FULL_FILLED_PARAM_NAME = "_codeswholesale_filled";
    const AUTOMATICALLY_COMPLETE_ORDER_OPTION_NAME = "_codeswholesale_auto_complete";
    const NOTIFY_LOW_BALANCE_VALUE_OPTION_NAME = "_codeswholesale_notify_balance_value";
    const SETTINGS_CODESWHOLESALE_PARAMS_NAME = "codeswholesale_params";

    const OPTIONS_NAME = "cw_options";

    static public function format_money($money)
    {
        return "€" . number_format($money, 2, '.', '');
    }

}

final class CodesWholesaleAutoCompleteOrder
{
    const COMPLETE = 1;
    const NOT_COMPLETE = 0;
}


final class CodesWholesale
{
    /**
     *
     * @var CodesWholesale
     */
    protected static $_instance = null;

    /**
     * CodesWholesale API client
     *
     * @var CodesWholesale\Client
     */
    private $codes_wholesale_client;

    /**
     * Plugin version
     *
     * @var string
     */
    private $version = "1.2";

    /**
     * @var array
     */
    private $plugin_options;

    /**
     *
     */
    public function __construct()
    {
        // Auto-load classes on demand
        if (function_exists("__autoload")) {
            spl_autoload_register("__autoload");
        }

        spl_autoload_register(array($this, 'autoload'));

        $this->define_constants();

        $this->includes();

        $this->configure_cw_client();

    }

    /**
     * Auto-load WC classes on demand to reduce memory consumption.
     *
     * @param mixed $class
     * @return void
     */
    public function autoload($class)
    {
        $path = null;
        $class = strtolower($class);
        $file = 'class-' . str_replace('_', '-', $class) . '.php';

        if (strpos($class, 'cw_admin') === 0) {
            $path = $this->plugin_path() . '/includes/admin/';
        }

        if ($path && is_readable($path . $file)) {
            include_once($path . $file);
            return;
        }

        // Fallback
        if (strpos($class, 'cw_') === 0) {
            $path = $this->plugin_path() . '/includes/';
        }

        if ($path && is_readable($path . $file)) {
            include_once($path . $file);
            return;
        }
    }

    /**
     *
     */
    private function includes()
    {
        include_once('includes/cw-core-functions.php');
        include_once('vendor/autoload.php');
        include_once('includes/class-cw-install.php');
        include_once('includes/class-cw-checkout.php');
        include_once('includes/class-cw-sendkeys.php');
        include_once('includes/class-cw-calc.php');

        include_once('includes/abstracts/class-cw-cron-job.php');
        include_once('includes/cron/class-cw-cron-update-stock.php');
        include_once('includes/cron/class-cw-cron-check-filled-orders.php');

        // require endpoint
        include_once('includes/class-cw-receive-stock-price-update.php');

        if (is_admin()) {

            include_once('includes/admin/class-cw-admin.php');

        }

    }


    /**
     * Define WC Constants
     */
    private function define_constants()
    {
        define('CW_PLUGIN_FILE', __FILE__);
        define('CW_VERSION', $this->version);
    }

    /**
     * Main CodesWholesale Instance
     *
     * Ensures only one instance of CodesWholesale is loaded or can be loaded.
     *
     * @since 1.0
     * @static
     * @see CW()
     * @return CodesWholesale
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Get the plugin path.
     *
     * @return string
     */
    public function plugin_path()
    {
        return untrailingslashit(plugin_dir_path(__FILE__));
    }

    /**
     * Get the template path.
     *
     * @return string
     */
    public function template_path()
    {
        return apply_filters('CW_TEMPLATE_PATH', 'codeswholesale-woocommerce/');
    }

    /**
     * Get the plugin url.
     *
     * @return string
     */
    public function plugin_url()
    {
        return untrailingslashit(plugins_url('/', __FILE__));
    }

    /**
     *
     */
    private function configure_cw_client()
    {
        $options = get_option(CodesWholesaleConst::OPTIONS_NAME);

        if ($options) {

            $clientBuilder = new \CodesWholesale\ClientBuilder(array(
                'cw.endpoint_uri' => $options['environment'] == 0 ? \CodesWholesale\CodesWholesale::SANDBOX_ENDPOINT : \CodesWholesale\CodesWholesale::LIVE_ENDPOINT,
                'cw.client_id' => $options['api_client_id'],
                'cw.client_secret' => $options['api_client_secret'],
                'cw.token_storage' => new \fkooman\OAuth\Client\SessionStorage()
            ));

            $this->codes_wholesale_client = $clientBuilder->build();
        }
    }

    /**
     * @return \CodesWholesale\Client
     */
    public function get_codes_wholesale_client()
    {
        return $this->codes_wholesale_client;
    }

    /**
     *
     */
    public function refresh_codes_wholesale_client()
    {
        $_SESSION["php-oauth-client"] = array();
        $this->configure_cw_client();
    }

    /**
     * Return options for CW plugin
     */
    public function get_options()
    {
        if (count($this->plugin_options) == 0) {
            $this->plugin_options = get_option(CodesWholesaleConst::OPTIONS_NAME);
        }

        return $this->plugin_options;
    }

}

/**
 * Returns the main instance of CW to prevent the need to use globals.
 *
 * @since  1.0
 * @return CodesWholesale
 */
function CW()
{
    return CodesWholesale::instance();
}

CW();


