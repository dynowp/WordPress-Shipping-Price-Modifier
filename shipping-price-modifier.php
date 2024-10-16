<?php
/*
Plugin Name: Shipping Price Modifier by DynoWP
Plugin URI: https://dynowp.com.br
Description: A plugin to modify the shipping cost in WooCommerce.
Version: 1.0.1
Author: DynoWP
Author URI: https://dynowp.com.br
License: GPL2
Text Domain: shipping-price-modifier
Requires Plugins: woocommerce
Domain Path: /languages
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include necessary classes
require_once plugin_dir_path(__FILE__) . 'includes/class-dynowp-shipping-price-modifier.php';
require_once plugin_dir_path(__FILE__) . 'admin/dynowp-shipping-price-modifier-settings.php';

/**
 * Load plugin text domain for translations.
 */
function shipping_price_modifier_load_textdomain() {
    load_plugin_textdomain('shipping-price-modifier', false, basename(dirname(__FILE__)) . '/languages/');
}
add_action('init', 'shipping_price_modifier_load_textdomain');

/**
 * Clean up options on plugin deactivation.
 */
function dynowp_shipping_price_modifier_deactivate() {
    delete_option('dynowp_shipping_price_modifier_options');
}
register_deactivation_hook(__FILE__, 'dynowp_shipping_price_modifier_deactivate');

/**
 * Add a settings link to the plugins page.
 * 
 * @param array $links Array of existing plugin action links.
 * @return array Modified array with settings link.
 */
function dynowp_shipping_price_modifier_add_settings_link($links) {
    $settings_link = '<a href="admin.php?page=shipping-price-modifier_options">' . __('Settings', 'shipping-price-modifier') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'dynowp_shipping_price_modifier_add_settings_link');

/**
 * Initialize the plugin after all plugins are loaded.
 */
add_action('plugins_loaded', function() {
    // Initialize main shipping price modifier class
    $shipping_price_modifier = new DynoWP_Shipping_Price_Modifier();
    $shipping_price_modifier->init();
    
    // Initialize admin settings class
    new DynoWP_Shipping_Price_Modifier_Settings();
});
