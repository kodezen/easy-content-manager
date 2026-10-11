<?php

namespace EasyContentManager\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use EasyContentManager\Utils\Helper;

class GlobalCss {


	public static function init() {
		$self = new self();
		add_action( 'wp_enqueue_scripts', array( $self, 'enqueue_global_css' ) );
		add_action( 'admin_enqueue_scripts', array( $self, 'enqueue_global_css' ) );
	}

	/**
	 * Attach the theme color variables as an inline style on a src-less handle
	 * (no extra HTTP request) instead of echoing a raw <style> block.
	 */
	public function enqueue_global_css() {
		$handle = 'easy-content-manager-global-vars';
		wp_register_style( $handle, false, array(), EASY_CONTENT_MANAGER_VERSION );
		wp_enqueue_style( $handle );
		wp_add_inline_style( $handle, $this->get_global_css() );
	}

	/**
	 * Restrict a stored color setting to CSS color syntax so it can't break out of
	 * the declaration (settings are admin-set, but keep the inline CSS injection-safe).
	 *
	 * @param mixed $color
	 * @return string
	 */
	private function sanitize_css_color( $color ) {
		return preg_replace( '/[^a-zA-Z0-9#%,.()\/\s-]/', '', (string) $color );
	}

	public function get_global_css() {
		global $ecm_settings;
		$primary_color = ( 'dark' === $ecm_settings->color_theme_mode )
			? $ecm_settings->dark_primary_color
			: $ecm_settings->primary_color;

		$secondary_color = ( 'dark' === $ecm_settings->color_theme_mode )
			? $ecm_settings->dark_secondary_color
			: $ecm_settings->secondary_color;

		$text_color = ( 'dark' === $ecm_settings->color_theme_mode )
			? $ecm_settings->dark_text_color
			: $ecm_settings->text_color;

		$body_color = ( 'dark' === $ecm_settings->color_theme_mode )
			? $ecm_settings->dark_body_color
			: $ecm_settings->body_color;

		$background_color = ( 'dark' === $ecm_settings->color_theme_mode )
			? $ecm_settings->dark_background_color
			: $ecm_settings->background_color;

		$border_color = ( 'dark' === $ecm_settings->color_theme_mode )
			? $ecm_settings->dark_border_color
			: $ecm_settings->border_color;

		$primary_color    = $this->sanitize_css_color( $primary_color );
		$secondary_color  = $this->sanitize_css_color( $secondary_color );
		$text_color       = $this->sanitize_css_color( $text_color );
		$body_color       = $this->sanitize_css_color( $body_color );
		$background_color  = $this->sanitize_css_color( $background_color );
		$border_color     = $this->sanitize_css_color( $border_color );

		return "
			:root {
				--ecm-primary: {$primary_color};
				--ecm-secondary: {$secondary_color};
				--ecm-font-color: {$text_color};
				--ecm-body-background: {$body_color};
				--ecm-background: {$background_color};
				--ecm-border-color: {$border_color};
				--ecm-gray: #BAC2CB;
				--ecm-gray-2: #fff;
				--ecm-text-muted: #738496;
				--ecm-warning: #F15B50;
				--ecm-shadow: 0px 0px 1px 0px rgba(20, 26, 36, 0.20), 0px 1px 2px 0px rgba(20, 26, 36, 0.10);
				--ecm-shadow-2: 0px 0.5px 2px 0px rgba(16, 24, 40, 0.15);
			}
		";
	}
}
