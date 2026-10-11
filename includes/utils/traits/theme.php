<?php

namespace EasyContentManager\Utils\Traits;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

trait Theme {
    public static function is_fse_theme() {
        if ( function_exists( 'wp_is_block_theme' ) ) {
            return (bool) \wp_is_block_theme();
        }
        if ( function_exists( 'gutenberg_is_fse_theme' ) ) {
            return (bool) \gutenberg_is_fse_theme();
        }

        return false;
    }
}