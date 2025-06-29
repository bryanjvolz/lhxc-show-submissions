<?php

if (!defined('ABSPATH')) exit;

class Show_Submissions_Constants {
    private static $instance = null;

    private function __construct() {}

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function get_time_zones() {
        return array(
            array(
                'zone' => 'America/Kentucky/Louisville',
                'name' => 'Louisville Time'
            ),
            array(
                'zone' => 'America/New_York',
                'name' => 'Eastern Time'
            ),
            array(
                'zone' => 'America/Chicago',
                'name' => 'Central Time'
            ),
            array(
                'zone' => 'America/Denver',
                'name' => 'Mountain Time'
            )
        );
    }
}