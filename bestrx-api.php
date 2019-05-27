<?php
/**
 * BestRx API
 *
 * Plugin Name: BestRx API
 * Description: Allows user to submit perscription refill requests via BestRx
 * Version:     1.1
 * Author:      Adam Gleiss
 * Author URI:  https://github.com/adamgleiss/bestrx-api.git
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

if (!defined('ABSPATH')) {
    die('Invalid request.');
}

if (!class_exists('BestRx_API')) {

    require_once (__DIR__ . '/includes/bestrx-admin-loader.php');

    class BestRx_API
    {

        CONST DELIVERY_OPTIONS = ['DELIVERY', 'PICKUP', 'MAIL'];
        CONST BESTRX_URL       = "https://webservice.bcsbestrx.com/BCSWebService/v2/WebRefillService/SendRefillRequest";

        /**
         * Static property to hold our singleton instance
         *
         */
        static $instance = false;

        /**
         * If an instance exists, this returns it.  If not, it creates one and
         * retuns it.
         *
         * @return BestRx_API
         */
        public static function get_instance()
        {
            if (!self::$instance) {
                self::$instance = new self;
            }

            return self::$instance;
        }

        private function __construct()
        {
            add_shortcode('bestrx_api_form', [$this, 'bestrx_api_form']);
            add_action('wp_enqueue_scripts', [$this, 'setup_css']);

            $plugin = plugin_basename( __FILE__ );
            $adminLoader = new BestRxAdminLoader($plugin);
        }

        public function setup_css()
        {
            wp_register_style('bestrx', plugins_url('public/css/style.css', __FILE__));
            wp_enqueue_style('bestrx');
        }

        public function bestrx_api_form()
        {
            $templateParameters = ['currentPageUrl' => $_SERVER['REQUEST_URI']];

            if (!isset($_POST['bestrx-submitted'])) {
                return $this->display_page('bestrx-form', $templateParameters);
            }

            $nonce = $_REQUEST['_wpnonce'];
            if (!wp_verify_nonce($nonce, 'bestrx-nonce')) {
                exit; //Stop processing, could be CSRF
            }

            $formData = $this->get_form_data();
            $templateParameters = array_merge($templateParameters, $formData);

            if (count($formData['errors']) > 0) {
                return $this->display_page('bestrx-form', $templateParameters);
            }

            $status = $this->submit_perscription($formData);

            $error  = $status['error'] ?? false;
            if ($error) {
                $templateParameters['generalError'] = $error;
                return $this->display_page('bestrx-form', $templateParameters);
            }

            $templateParameters['message'] = $status['message'];

            return $this->display_page('bestrx-thankyou', $templateParameters);
        }

        private function display_page($pageName, $templateParameters)
        {
            ob_start();
            include __DIR__ . "/includes/$pageName.php";

            return ob_get_clean();
        }

        private function get_form_data()
        {
            $formData = [
                'name'      => sanitize_text_field($_POST['bestrx-lastname']),
                'rxNumber'  => sanitize_text_field($_POST['bestrx-rxnumber']),
                'dob'       => sanitize_text_field($_POST['bestrx-dob']),
                'dobObject' => null,
                'deliver'   => sanitize_text_field($_POST['bestrx-delivery'])
            ];

            $errors = [];

            if (empty($formData['name'])) {
                $errors['name'] = 'A last name is required.';
            }

            if (empty($formData['rxNumber']) || !is_numeric($formData['rxNumber'])) {
                $errors['rxNumber'] = 'A valid RX number is required.';
            }

            $dob = $this->get_date_from_user_input($formData);
            if ($dob) {
                $formData['dobObject'] = $dob;
            } else {
                $errors['dob'] = 'Date of birth is required and must be in mm/dd/YYYY format.';
            }

            if (!in_array($formData['deliver'], self::DELIVERY_OPTIONS)) {
                $errors['deliver'] = 'A valid delivery options is required.';
            }

            $formData['errors'] = $errors;

            return $formData;
        }

        private function get_date_from_user_input($input)
        {
            try {
                $dob = new DateTime($input['dob']);

                return $dob;
            } catch (Exception $ex) {
                return false;
            }
        }

        private function submit_perscription($templateParameters)
        {
            $status = [
                'message' => false,
                'error'   => false
            ];

            $currentTime = new DateTime();
            $jsonParams  = [
                "APIKey"            => get_option('bestrx_api_key'),
                "PharmacyNumber"    => get_option('bestrx_pharmacy_number'),
                "LastName"          => $templateParameters['name'],
                "DOB"               => $templateParameters['dobObject']->format('c'),
                "RxInRefillRequest" => [
                    ["RxNo" => $templateParameters['rxNumber']]
                ],
                "RequestDateTime"   => $currentTime->format('c'),
                "DeliveryOption"    => $templateParameters['deliver'],
                "RequestType"       => "WEB"
            ];

            $args = [
                'method'   => 'POST',
                'timeout'  => 45,
                'blocking' => true,
                'body'     => json_encode($jsonParams),
                'headers'  => ['Content-Type' => 'application/json; charset=utf-8']
            ];

            $response = wp_remote_post(
                get_option('bestrx_api_url'),
                $args
            );

            if (is_wp_error($response)) {
                $status['error'] = $response->get_error_message();
            } else {
                $responseData = json_decode($response['body'] ?? false, true);

                if (empty($responseData)) {
                    $status['error'] = 'Something went wrong with the request (could not read API data).';
                    return $status;
                }

                if ($responseData['ErrorDetail'] ?? false) {
                    $status['error'] = $responseData['ErrorDetail'];
                    return $status;
                }


                $status['message'] = $responseData['RxInRefillResponse'][0]['StatusDesc'] ?? 'Perscription submitted successfully.';
            }

            return $status;
        }

    }
}

// Instantiate our class so the constructor setup will run.
$BestRx_API = BestRx_API::get_instance();