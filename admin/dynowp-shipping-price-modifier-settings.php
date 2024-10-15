<?php

if (!defined('ABSPATH')) {
    exit;
}

class DynoWP_Shipping_Price_Modifier_Settings
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'setup_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
    }

    public function enqueue_styles()
    {
        wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap');
        wp_enqueue_style('shipping-price-modifier-styles', plugin_dir_url(__FILE__) . '../css/dynowp-shipping-price-modifier-style.css');
    }

    public function add_settings_page()
    {
        add_submenu_page(
            'woocommerce',
            __('Shipping Price Modifier Settings', 'shipping-price-modifier'),
            __('Shipping Modifier', 'shipping-price-modifier'),
            'manage_options',
            'shipping-price-modifier_options',
            array($this, 'settings_page_content')
        );
    }

    public function settings_page_content()
    {
        ?>
        <div class="wrap shipping-price-settings">
            <h2 class="edit_title"><?php _e('Adjust the shipping cost as you prefer', 'shipping-price-modifier'); ?></h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('shipping_price_modifier_options_group');
                do_settings_sections('shipping-price-modifier');
                submit_button(__('Apply Shipping Cost Changes', 'shipping-price-modifier'));
                ?>
            </form>
        </div>
        <?php
    }

    public function setup_settings()
    {
        // Register the settings group and option name
        register_setting('shipping_price_modifier_options_group', 'dynowp_shipping_price_modifier_options');

        // Add a settings section
        add_settings_section(
            'shipping_price_modifier_main_section',
            __('Shipping Settings', 'shipping-price-modifier'),
            null,
            'shipping-price-modifier'
        );

        // Add settings fields
        $this->add_settings_field(
            'dynowp_shipping_price_modifier_enable',
            __('Enable Shipping Modification?', 'shipping-price-modifier'),
            'settings_field_checkbox',
            array('id' => 'dynowp_shipping_price_modifier_enable')
        );

        $this->add_settings_field(
            'dynowp_shipping_price_modifier_discount_type',
            __('Discount Type', 'shipping-price-modifier'),
            'settings_field_select',
            array(
                'id' => 'dynowp_shipping_price_modifier_discount_type',
                'options' => array(
                    'fixed' => __('Fixed Value', 'shipping-price-modifier'),
                    'percentage' => __('Percentage', 'shipping-price-modifier')
                )
            )
        );

        $this->add_settings_field(
            'dynowp_shipping_price_modifier_action',
            __('Action to Perform', 'shipping-price-modifier'),
            'settings_field_select',
            array(
                'id' => 'dynowp_shipping_price_modifier_action',
                'options' => array(
                    'increase' => __('Increase Shipping Cost', 'shipping-price-modifier'),
                    'decrease' => __('Apply Discount', 'shipping-price-modifier')
                )
            )
        );

        $this->add_settings_field(
            'dynowp_shipping_price_modifier_value',
            __('Enter Value', 'shipping-price-modifier'),
            'settings_field_input',
            array('id' => 'dynowp_shipping_price_modifier_value')
        );

        $this->add_settings_field(
            'dynowp_shipping_price_modifier_log',
            __('Enable Logging', 'shipping-price-modifier'),
            'settings_field_checkbox_log',
            array('id' => 'dynowp_shipping_price_modifier_log')
        );
    }

    private function add_settings_field($id, $title, $callback, $args)
    {
        add_settings_field(
            $id,
            $title,
            array($this, $callback),
            'shipping-price-modifier',
            'shipping_price_modifier_main_section',
            $args
        );
    }

    public function settings_field_checkbox($args)
    {
        $options = get_option('dynowp_shipping_price_modifier_options');
        $checked = isset($options[$args['id']]) ? checked(1, $options[$args['id']], false) : '';
        echo "<input type='checkbox' id='" . esc_attr($args['id']) . "' name='dynowp_shipping_price_modifier_options[" . esc_attr($args['id']) . "]' value='1' " . esc_html($checked) . "/>";
    }

    public function settings_field_select($args)
    {
        $options = get_option('dynowp_shipping_price_modifier_options');
        $value = isset($options[$args['id']]) ? $options[$args['id']] : '';
        echo "<select id='" . esc_attr($args['id']) . "' name='dynowp_shipping_price_modifier_options[" . esc_attr($args['id']) . "]'>";
        foreach ($args['options'] as $key => $label) {
            $selected = selected($value, $key, false);
            echo "<option value='" . esc_attr($key) . "' " . esc_html($selected) . ">" . esc_html($label) . "</option>";
        }
        echo "</select>";
    }

    public function settings_field_input($args)
    {
        $options = get_option('dynowp_shipping_price_modifier_options');
        $value = isset($options[$args['id']]) ? esc_attr($options[$args['id']]) : '';
        echo "<input type='text' id='" . esc_attr($args['id']) . "' name='dynowp_shipping_price_modifier_options[" . esc_attr($args['id']) . "]' value='" . $value . "' />";
    }

    public function settings_field_checkbox_log($args)
    {
        $options = get_option('dynowp_shipping_price_modifier_options');
        $checked = isset($options[$args['id']]) ? checked(1, $options[$args['id']], false) : '';
        echo "<input type='checkbox' id='" . esc_attr($args['id']) . "' name='dynowp_shipping_price_modifier_options[" . esc_attr($args['id']) . "]' value='1' " . esc_html($checked) . "/>";
        echo "<label for='" . esc_attr($args['id']) . "'>" . __('View logs in WooCommerce > Status > Logs', 'shipping-price-modifier') . "</label>";
    }
}