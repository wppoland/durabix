<?php
/**
 * Plugin Name:       Durabix - EU Legal Guarantee Notice for WooCommerce
 * Plugin URI:        https://plogins.com/durabix/
 * Description:       The EU harmonised legal guarantee notice and the GARAN label in the official design, plus a repairability class badge and a green claims phrase check for WooCommerce.
 * Version:           1.3.1
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Requires Plugins:  woocommerce
 * Author:            WPPoland.com
 * Author URI:        https://wppoland.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       durabix
 * Domain Path:       /languages
 * WC requires at least: 8.0
 * WC tested up to: 11.2
 *
 * @package Durabix
 */

declare(strict_types=1);

namespace Durabix;

defined('ABSPATH') || exit;

const VERSION     = '1.3.1';
const PLUGIN_FILE = __FILE__;

require_once __DIR__ . '/autoload.php';

// Declare HPOS and checkout block compatibility.
add_action('before_woocommerce_init', static function (): void {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
});

add_action('plugins_loaded', static function (): void {
    if (! class_exists('WooCommerce')) {
        add_action('admin_notices', static function (): void {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('Durabix - EU Legal Guarantee Notice for WooCommerce requires WooCommerce to be active.', 'durabix');
            echo '</p></div>';
        });
        return;
    }

    add_action('init', static function (): void {
        Plugin::instance()->boot();
    }, 0);
}, 10);
