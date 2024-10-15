<?php

if (!defined('ABSPATH')) {
    exit;
}

class DynoWP_Shipping_Price_Modifier {
    private $logger;
    private $options;

    public function __construct() {
        if (function_exists('wc_get_logger')) {
            $this->logger = wc_get_logger();
        }
        $this->options = get_option('dynowp_shipping_price_modifier_options');
    }

    public function init() {
        if ($this->is_shipping_modification_enabled()) {
            add_filter('woocommerce_shipping_packages', array($this, 'modify_shipping_packages'), 10, 1);
        } else {
            $this->log_info('Shipping modification is disabled.');
        }
    }

    public function modify_shipping_packages($packages) {
        $this->log_info('modify_shipping_packages hook triggered.');

        foreach ($packages as &$package) {
            foreach ($package['rates'] as &$rate) {
                if ('free_shipping' === $rate->method_id) {
                    $this->log_info('Skipping free_shipping method.');
                    continue;
                }

                $original_cost = $rate->get_cost();
                $this->log_info(sprintf('Original shipping cost: %s', $this->format_price_without_html($original_cost)));

                $discount_type = isset($this->options['dynowp_shipping_price_modifier_discount_type']) ? sanitize_text_field($this->options['dynowp_shipping_price_modifier_discount_type']) : 'fixed';
                $action = isset($this->options['dynowp_shipping_price_modifier_action']) ? sanitize_text_field($this->options['dynowp_shipping_price_modifier_action']) : 'increase';
                $adjustment_value = isset($this->options['dynowp_shipping_price_modifier_value']) ? floatval($this->options['dynowp_shipping_price_modifier_value']) : 0;

                if ($adjustment_value <= 0) {
                    $this->log_info('Invalid adjustment value: ' . $adjustment_value);
                    continue;
                }

                $calculated_value = $this->calculate_value($discount_type, $adjustment_value, $original_cost);
                $this->log_info(sprintf('Calculated adjustment value: %s', $this->format_price_without_html($calculated_value)));

                if ($action === 'increase') {
                    $new_cost = $original_cost + $calculated_value;
                    $action_label = 'Increased by';
                } else {
                    $new_cost = max(0, $original_cost - $calculated_value);
                    $action_label = 'Decreased by';
                }

                $rate->set_cost($new_cost);

                $this->log_info(sprintf('%s %s', $action_label, $this->format_price_without_html($calculated_value)));
                $this->log_info(sprintf('New shipping cost: %s', $this->format_price_without_html($new_cost)));
            }
        }

        return $packages;
    }

    private function calculate_value($discount_type, $value, $original_cost) {
        if ($discount_type === 'percentage') {
            return ($original_cost * $value) / 100;
        } elseif ($discount_type === 'fixed') {
            return $value;
        } else {
            $this->log_info('Unknown discount type: ' . $discount_type);
            return 0;
        }
    }

    private function is_shipping_modification_enabled() {
        $enabled = isset($this->options['dynowp_shipping_price_modifier_enable']) && '1' === $this->options['dynowp_shipping_price_modifier_enable'];
        $this->log_info('Shipping modification enabled: ' . ($enabled ? 'Yes' : 'No'));
        return $enabled;
    }

    private function is_logging_enabled() {
        return isset($this->options['dynowp_shipping_price_modifier_log']) && '1' === $this->options['dynowp_shipping_price_modifier_log'];
    }

    private function log_info($message) {
        if ($this->is_logging_enabled() && $this->logger) {
            $this->logger->info($message, array('source' => 'shipping-price-modifier'));
        }
    }

    private function format_price_without_html($price) {
        // Get WooCommerce price formatting settings
        $decimals = wc_get_price_decimals();
        $decimal_separator = wc_get_price_decimal_separator();
        $thousand_separator = wc_get_price_thousand_separator();
        $currency_symbol = get_woocommerce_currency_symbol();
        $currency_pos = get_option('woocommerce_currency_pos');

        // Format the number
        $formatted_price = number_format($price, $decimals, $decimal_separator, $thousand_separator);

        // Add currency symbol based on position
        switch ($currency_pos) {
            case 'left':
                return $currency_symbol . $formatted_price;
            case 'left_space':
                return $currency_symbol . ' ' . $formatted_price;
            case 'right':
                return $formatted_price . $currency_symbol;
            case 'right_space':
                return $formatted_price . ' ' . $currency_symbol;
            default:
                return $currency_symbol . $formatted_price;
        }
    }
}