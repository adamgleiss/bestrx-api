<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option('bestrx_api_key');
delete_option('bestrx_pharmacy_number');
delete_option('bestrx_api_url');