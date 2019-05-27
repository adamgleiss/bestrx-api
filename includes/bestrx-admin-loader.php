<?php

if (!defined('ABSPATH')) {
    die('Invalid request.');
}

class BestRxAdminLoader
{
    private $pluginName;

    public function __construct($pluginName)
    {
        $this->pluginName = $pluginName;
        add_action('admin_init', [$this, 'setup_admin_section']);
    }

    public function setup_admin_section()
    {
        add_filter( "plugin_action_links_" . $this->pluginName, [$this, 'plugin_add_settings_link'] );

        register_setting('general', 'bestrx_api_key');
        register_setting('general', 'bestrx_pharmacy_number');
        register_setting('general', 'bestrx_api_url');

        add_settings_section(
            'bestrx_settings',
            'BestRx Settings',
            [$this, 'section_display_callback'],
            'general'
        );

        add_settings_field(
            'bestrx_api_url',
            'API Url',
            [$this, 'textbox_display_callback'],
            'general',
            'bestrx_settings',
            ['bestrx_api_url', 'large-text ltr']
        );

        add_settings_field(
            'bestrx_api_key',
            'API Key',
            [$this, 'textbox_display_callback'],
            'general',
            'bestrx_settings',
            ['bestrx_api_key']
        );

        add_settings_field(
            'bestrx_pharmacy_number',
            'Pharmacy Number',
            [$this, 'textbox_display_callback'],
            'general',
            'bestrx_settings',
            ['bestrx_pharmacy_number']
        );
    }

    public function textbox_display_callback($args)
    {
        $class = $args[1] ?? '';
        $optionId = $args[0];
        $optionValue = get_option($optionId);
        echo '<input class="'. $class . '" type="text" id="' . $optionId . '" name="' . $optionId . '" value="' . $optionValue . '" />';
    }

    public function section_display_callback()
    {
        echo '<p id="bestrx">Contact BestRx for this data. The URL must be full URL to the route to actually send the refil request. </p>';
    }

    public function plugin_add_settings_link( $links ) {
        $settingsLink = '<a href="options-general.php?#bestrx">Settings</a>';
        $links[] = $settingsLink;
        return $links;
    }
}