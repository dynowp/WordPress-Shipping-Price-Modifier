<?php
/*
Plugin Name: DynoWP Shipping Price Modifier
Plugin URI: https://dyogomacedo.com.br
Description: Um plugin para modificar o valor do frete no WooCommerce.
Version: 1.0.0
Author: Dyogo Macedo
Author URI: https://dyogomacedo.com.br
License: GPL2
Text Domain: shipping-price-modifier
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Inclui a classe principal e as configurações
require_once plugin_dir_path(__FILE__) . 'includes/class-dynowp-shipping-price-modifier.php';
require_once plugin_dir_path(__FILE__) . 'admin/dynowp-shipping-price-modifier-settings.php';

// Inicializa o plugin
function dynowp_shipping_price_modifier_init() {
    $dynowp_shipping_price_modifier = new DynoWP_Shipping_Price_Modifier();
    $dynowp_shipping_price_modifier->init();
}
add_action('plugins_loaded', 'dynowp_shipping_price_modifier_init');

// Carrega o text domain para tradução
function shipping_price_modifier_load_textdomain() {
    load_plugin_textdomain('shipping-price-modifier', false, basename(dirname(__FILE__)) . '/languages/');
}
add_action('init', 'shipping_price_modifier_load_textdomain');

// Função chamada na desativação do plugin para limpar configurações
function dynowp_shipping_price_modifier_deactivate() {
    delete_option('dynowp_shipping_price_modifier_options');
}
register_deactivation_hook(__FILE__, 'dynowp_shipping_price_modifier_deactivate');

// Adiciona o link de configuração na lista de plugins
function dynowp_shipping_price_modifier_add_settings_link($links) {
    $settings_link = '<a href="admin.php?page=shipping-price-modifier_options">' . __('Configurações', 'shipping-price-modifier') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'dynowp_shipping_price_modifier_add_settings_link');
