<?php

namespace EasyContentManager\Admin;

use EasyContentManager\Classes\Capabilities;
use EasyContentManager\Utils\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Menu {



	public static function init(): void {
		$self = new self();
		add_action( 'admin_menu', array( $self, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $self, 'add_admin_menu_css' ) );
	}

	public function admin_menu() {
		$icon_url = $this->get_toplevel_menu_icon_url();
		$page_title = $this->get_toplevel_menu_title();
		add_menu_page( $page_title, $page_title, Capabilities::VIEW_APP, EASY_CONTENT_MANAGER_PLUGIN_SLUG, [ $this, 'load_main_template' ], $icon_url, 2 );
		foreach ( Helper::get_admin_menu_list() as $item_key => $item ) {
			add_submenu_page( $item['parent_slug'], $item['title'], $item['title'], $item['capability'], $item_key, [ $this, 'load_main_template' ] );
		}
	}

	public function get_toplevel_menu_icon_url() {
        // phpcs:disable
        if (isset($_GET['page']) && 'easy-content-manager' === $_GET['page']) {
            $icon_url = 'data:image/svg+xml;base64, ' . base64_encode(file_get_contents(EASY_CONTENT_MANAGER_ASSETS_DIR_PATH . 'images/logo.svg'));
            return apply_filters('easy_content_manager/admin/toplevel_active_menu_icon', $icon_url);
        }
        $icon_url = 'data:image/svg+xml;base64, ' . base64_encode(file_get_contents(EASY_CONTENT_MANAGER_ASSETS_DIR_PATH . 'images/logo.svg'));
        return apply_filters('easy_content_manager/admin/toplevel_inactive_menu_icon', $icon_url);
    }

    public function get_toplevel_menu_title()
    {
        return apply_filters('easy_content_manager/admin/toplevel_menu_title', __('Content Manager', 'easy-content-manager'));
    }

    public function get_logo_url()
    {
        return apply_filters('easy_content_manager/admin/logo_url',  EASY_CONTENT_MANAGER_ASSETS_URI . 'images/logo.svg');
    }

    public function load_main_template()
    {
        echo '<div id="contentManagerWrap" class="contentManagerWrap"></div>';
    }
    public function add_admin_menu_css()
    {
        $css = '
			#adminmenu li.toplevel_page_easy-content-manager a.toplevel_page_easy-content-manager > .wp-menu-image {
				display: flex;
				justify-content: center;
				align-items: center;
			}
			#adminmenu li.toplevel_page_easy-content-manager a.toplevel_page_easy-content-manager > .wp-menu-image img {
				max-width: 20px;
				height: auto;
				padding: 0 !important;
			}
			#adminmenu li.toplevel_page_easy-content-manager ul li a, #adminmenu li.toplevel_page_easy-content-manager .wp-submenu > li > a {
				padding: 7px 12px;
			}

			#adminmenu li.toplevel_page_easy-content-manager ul.wp-submenu li {
				clear: both;
			}
			#adminmenu li.toplevel_page_easy-content-manager ul.wp-submenu li.wp-first-item a[href^="admin.php?page=easy-content-manager"]:after,
			#adminmenu li.toplevel_page_easy-content-manager ul.wp-submenu li.wp-first-item a[href*="admin.php?page=easy-content-manager"]:after {
				border-bottom: 1px solid hsla(0,0%,100%,.2);
				display: block;
				float: left;
				margin: 15px -15px 7px;
				content: "";
				width: calc(100% + 26px);
			}
		';
        wp_add_inline_style( 'admin-menu', $css );
    }
}
