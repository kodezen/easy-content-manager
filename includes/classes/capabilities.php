<?php
/**
 * ECM capability registry, role grants and the renamed-post-type cap bridge.
 *
 * Two independent layers, deliberately kept apart:
 *
 *   1. STRUCTURE / CONFIG access — building post types, taxonomies, field
 *      groups, settings and tools. Owned by this plugin, expressed as the
 *      `ecm_*` capabilities below and granted per role.
 *   2. CONTENT access — managing the *entries* of a generated post type. NOT
 *      owned by this plugin: it defers entirely to the post type's own
 *      capabilities, so `edit_posts` / `edit_storeengine_products` / whatever
 *      the CPT declares remains the single source of truth.
 *
 * Conflating the two is what forces a plugin into `manage_options` everywhere.
 *
 * Capabilities are stored on WP_Role objects via add_cap()/remove_cap() rather
 * than in an option blob, so third-party role editors stay in sync and a
 * per-user grant ($user->add_cap( 'ecm_manage_settings' )) works with no extra
 * code — current_user_can() resolves role caps and user caps identically.
 *
 * @package EasyContentManager
 */

namespace EasyContentManager\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Capabilities {

	/** See the Content Manager admin area at all (menu + Dashboard). */
	const VIEW_APP = 'ecm_view_app';

	/** Create / edit / delete custom post types. */
	const MANAGE_POST_TYPES = 'ecm_manage_post_types';

	/** Create / edit / delete taxonomies. */
	const MANAGE_TAXONOMIES = 'ecm_manage_taxonomies';

	/** Create / edit / delete field groups and their fields. */
	const MANAGE_FIELD_GROUPS = 'ecm_manage_field_groups';

	/** Plugin settings, add-ons, licence, white-label. */
	const MANAGE_SETTINGS = 'ecm_manage_settings';

	/** Import / export (can overwrite structure — deliberately separate). */
	const MANAGE_TOOLS = 'ecm_manage_tools';

	/** Bumped when {@see defaults()} changes, to re-run the grant on upgrade. */
	const CAPS_VERSION = '1';

	const VERSION_OPTION = 'easy_content_manager_caps_version';

	/** Hash of the renamed-CPT cap surface, so the bridge only runs on change. */
	const PT_CAPS_HASH_OPTION = 'easy_content_manager_pt_caps_hash';

	/**
	 * Every ECM-owned capability, in display order.
	 *
	 * @return array<string,array{label:string,description:string}>
	 */
	public static function all(): array {
		return apply_filters(
			'easy_content_manager/capabilities',
			[
				self::VIEW_APP            => [
					'label'       => __( 'View Content Manager', 'easy-content-manager' ),
					'description' => __( 'Open the Content Manager admin area and its dashboard.', 'easy-content-manager' ),
				],
				self::MANAGE_POST_TYPES   => [
					'label'       => __( 'Manage post types', 'easy-content-manager' ),
					'description' => __( 'Create, edit, duplicate and delete custom post types.', 'easy-content-manager' ),
				],
				self::MANAGE_TAXONOMIES   => [
					'label'       => __( 'Manage taxonomies', 'easy-content-manager' ),
					'description' => __( 'Create, edit, duplicate and delete taxonomies.', 'easy-content-manager' ),
				],
				self::MANAGE_FIELD_GROUPS => [
					'label'       => __( 'Manage field groups', 'easy-content-manager' ),
					'description' => __( 'Create, edit, duplicate and delete field groups and custom fields.', 'easy-content-manager' ),
				],
				self::MANAGE_SETTINGS     => [
					'label'       => __( 'Manage settings & add-ons', 'easy-content-manager' ),
					'description' => __( 'Change plugin settings, toggle add-ons and manage the licence.', 'easy-content-manager' ),
				],
				self::MANAGE_TOOLS        => [
					'label'       => __( 'Manage tools', 'easy-content-manager' ),
					'description' => __( 'Import and export structure. Importing can overwrite existing post types and fields.', 'easy-content-manager' ),
				],
			]
		);
	}

	/**
	 * @return string[] Capability slugs only.
	 */
	public static function slugs(): array {
		return array_keys( self::all() );
	}

	public static function is_ecm_cap( string $cap ): bool {
		return in_array( $cap, self::slugs(), true );
	}

	/**
	 * Role => capabilities granted on install / upgrade.
	 *
	 * Editors get VIEW_APP only: enough to reach the Content Manager and manage
	 * the entries of any post type they already have rights to, but not to
	 * change the site's structure. Everything else is administrator-only until
	 * an administrator says otherwise in Settings → Permissions.
	 *
	 * @return array<string,string[]>
	 */
	public static function defaults(): array {
		return apply_filters(
			'easy_content_manager/capabilities/defaults',
			[
				'administrator' => self::slugs(),
				'editor'        => [ self::VIEW_APP ],
			]
		);
	}

	// ── install / upgrade ──────────────────────────────────────────────────────

	/**
	 * Grant the default capabilities. Idempotent and additive: it never removes
	 * a capability an administrator granted by hand, so re-running on upgrade is
	 * safe.
	 */
	public static function install(): void {
		if ( ! function_exists( 'wp_roles' ) ) {
			return;
		}

		foreach ( self::defaults() as $role_name => $caps ) {
			$role = get_role( $role_name );
			if ( ! $role ) {
				continue;
			}

			foreach ( $caps as $cap ) {
				if ( ! $role->has_cap( $cap ) ) {
					$role->add_cap( $cap );
				}
			}
		}

		update_option( self::VERSION_OPTION, self::CAPS_VERSION );
	}

	/**
	 * Run {@see install()} once per capability-schema version.
	 *
	 * Activation-only grants strand every existing install, so this also runs on
	 * a normal page load after an upgrade.
	 */
	public static function maybe_install(): void {
		if ( get_option( self::VERSION_OPTION ) === self::CAPS_VERSION ) {
			return;
		}

		self::install();
	}

	/**
	 * Strip every ECM capability from every role. Intended for uninstall — not
	 * deactivation, which must not destroy an administrator's configuration.
	 */
	public static function remove_all(): void {
		if ( ! function_exists( 'wp_roles' ) ) {
			return;
		}

		foreach ( wp_roles()->role_objects as $role ) {
			foreach ( self::slugs() as $cap ) {
				$role->remove_cap( $cap );
			}
		}

		delete_option( self::VERSION_OPTION );
	}

	// ── runtime checks ─────────────────────────────────────────────────────────

	/**
	 * Whether the `manage_options` fallback in {@see user_can()} still applies.
	 *
	 * The fallback exists for exactly one situation: the window between a plugin
	 * upgrade and {@see maybe_install()} granting the new capabilities, where an
	 * administrator would otherwise be locked out of their own site.
	 *
	 * Once the grant has run it must stop, because an unconditional fallback
	 * silently defeats the whole permission matrix — every role holding
	 * `manage_options` (a shop-manager-style custom role, say) keeps full access
	 * no matter what an administrator revokes in Settings → Permissions. A screen
	 * that reports a revocation it does not enforce is worse than no screen.
	 */
	public static function fallback_active(): bool {
		return get_option( self::VERSION_OPTION ) !== self::CAPS_VERSION;
	}

	/**
	 * Whether this user is an owner of the site, for whom access is never revocable.
	 *
	 * Narrowly the `administrator` role (the one row {@see roles_matrix()} reports
	 * as `locked`) and multisite super admins — NOT anyone holding
	 * `manage_options`, which is what made the matrix unenforceable.
	 */
	private static function is_site_owner( ?int $user_id = null ): bool {
		if ( is_multisite() && is_super_admin( $user_id ) ) {
			return true;
		}

		$user = null === $user_id ? wp_get_current_user() : get_userdata( $user_id );

		return $user && in_array( 'administrator', (array) $user->roles, true );
	}

	/**
	 * Capability check with a bounded owner fallback.
	 *
	 * Use this for gates in PHP; the raw slug is still what goes into
	 * add_menu_page() so WordPress can filter menus itself.
	 */
	private static function user_has_cap( string $cap, ?int $user_id ): bool {
		return null === $user_id ? current_user_can( $cap ) : user_can( $user_id, $cap );
	}

	public static function user_can( string $cap, ?int $user_id = null ): bool {
		if ( self::user_has_cap( $cap, $user_id ) ) {
			return true;
		}

		// Administrators and super admins keep access unconditionally: the matrix
		// already reports their row as locked, so honouring it here is the truth.
		if ( self::is_site_owner( $user_id ) ) {
			return true;
		}

		// Everyone else only gets the legacy `manage_options` bypass while the
		// capability grant has not run yet on this site.
		if ( ! self::fallback_active() ) {
			return false;
		}

		return self::user_has_cap( 'manage_options', $user_id );
	}

	/**
	 * The current user's ECM capabilities, for the admin app's localized data.
	 *
	 * @return array<string,bool>
	 */
	public static function current_user_caps(): array {
		$caps = [];
		foreach ( self::slugs() as $cap ) {
			$caps[ $cap ] = self::user_can( $cap );
		}

		return $caps;
	}

	// ── role matrix (Settings → Permissions) ───────────────────────────────────

	/**
	 * Every editable role with the ECM capabilities it currently holds.
	 *
	 * Administrator is reported with `locked => true`: the UI must not let an
	 * administrator revoke their own access and lock the site's owner out of the
	 * permission screen itself.
	 *
	 * @return array<int,array{key:string,name:string,locked:bool,capabilities:array<string,bool>}>
	 */
	public static function roles_matrix(): array {
		$out = [];

		foreach ( wp_roles()->get_names() as $key => $name ) {
			$role = get_role( $key );
			if ( ! $role ) {
				continue;
			}

			$caps = [];
			foreach ( self::slugs() as $cap ) {
				$caps[ $cap ] = 'administrator' === $key ? true : (bool) $role->has_cap( $cap );
			}

			$out[] = [
				'key'          => $key,
				'name'         => $name,
				'locked'       => 'administrator' === $key,
				'capabilities' => $caps,
			];
		}

		return $out;
	}

	/**
	 * Grant or revoke a single capability on a single role.
	 *
	 * Both arguments are validated against the registry, so an arbitrary
	 * capability string coming off the wire can never be written to a role.
	 *
	 * @return true|\WP_Error
	 */
	public static function set_role_cap( string $role_name, string $cap, bool $granted ) {
		if ( ! self::is_ecm_cap( $cap ) ) {
			return new \WP_Error(
				'ecm_invalid_capability',
				__( 'Unknown capability.', 'easy-content-manager' ),
				[ 'status' => 400 ]
			);
		}

		if ( 'administrator' === $role_name ) {
			return new \WP_Error(
				'ecm_locked_role',
				__( 'Administrator permissions cannot be changed.', 'easy-content-manager' ),
				[ 'status' => 400 ]
			);
		}

		$role = get_role( $role_name );
		if ( ! $role ) {
			return new \WP_Error(
				'ecm_invalid_role',
				__( 'Unknown role.', 'easy-content-manager' ),
				[ 'status' => 404 ]
			);
		}

		if ( $granted ) {
			$role->add_cap( $cap );
		} else {
			$role->remove_cap( $cap );
		}

		/**
		 * Fires after an ECM capability is granted to or revoked from a role.
		 *
		 * @param string $role_name Role slug.
		 * @param string $cap       Capability slug.
		 * @param bool   $granted   Whether it was granted or revoked.
		 */
		do_action( 'easy_content_manager/capabilities/role_changed', $role_name, $cap, $granted );

		return true;
	}

	// ── renamed post-type capability bridge ────────────────────────────────────

	/**
	 * The primitive capabilities WordPress derives for a post type, mapped to
	 * the core `post` capability each one mirrors.
	 *
	 * Meta capabilities (edit_post / read_post / delete_post) are deliberately
	 * absent: map_meta_cap() derives them per object, they are never stored on a
	 * role.
	 *
	 * @param string      $singular Singular capability base, e.g. "book".
	 * @param string|null $plural   Plural base. Defaults to $singular . 's', matching
	 *                              WordPress's own get_post_type_capabilities().
	 *
	 * @return array<string,string> generated capability => core equivalent.
	 */
	public static function post_type_cap_map( string $singular, ?string $plural = null ): array {
		$plural = $plural ? $plural : $singular . 's';

		return [
			'edit_' . $plural              => 'edit_posts',
			'edit_others_' . $plural       => 'edit_others_posts',
			'edit_private_' . $plural      => 'edit_private_posts',
			'edit_published_' . $plural    => 'edit_published_posts',
			'publish_' . $plural           => 'publish_posts',
			'read_private_' . $plural      => 'read_private_posts',
			'delete_' . $plural            => 'delete_posts',
			'delete_private_' . $plural    => 'delete_private_posts',
			'delete_published_' . $plural  => 'delete_published_posts',
			'delete_others_' . $plural     => 'delete_others_posts',
		];
	}

	/**
	 * Mirror renamed post-type capabilities onto every role.
	 *
	 * Turning on "rename capabilities" for a post type makes WordPress require
	 * caps like `edit_books` that no role holds — not even administrator, whose
	 * capability list is fixed. Without this the post type becomes unusable for
	 * everyone, which is why the toggle looked broken.
	 *
	 * The rule is a mirror: a role gets `edit_books` exactly when it already has
	 * `edit_posts`. So a renamed post type behaves like core posts out of the
	 * box (administrators and editors manage everything, authors their own,
	 * contributors drafts) while remaining independently revocable afterwards.
	 *
	 * Converges in both directions so revoking `edit_posts` from a role, or
	 * turning the rename back off, cleans up after itself.
	 *
	 * @param array<int,array{singular:string,plural:string}> $renamed Renamed cap bases.
	 */
	public static function sync_post_type_caps( array $renamed ): void {
		if ( ! function_exists( 'wp_roles' ) ) {
			return;
		}

		$map = [];
		foreach ( $renamed as $bases ) {
			$singular = (string) ( $bases['singular'] ?? '' );
			if ( '' === $singular ) {
				continue;
			}

			$map += self::post_type_cap_map( $singular, (string) ( $bases['plural'] ?? '' ) ?: null );
		}

		$hash = md5( wp_json_encode( $map ) );
		if ( get_option( self::PT_CAPS_HASH_OPTION ) === $hash ) {
			return;
		}

		// Every capability this bridge has ever owned, so caps belonging to a
		// post type that was deleted or un-renamed get cleaned up too.
		$previously_managed = (array) get_option( self::PT_CAPS_HASH_OPTION . '_caps', [] );
		$managed            = array_unique( array_merge( array_keys( $map ), $previously_managed ) );

		foreach ( wp_roles()->role_objects as $role ) {
			foreach ( $managed as $cap ) {
				$core = $map[ $cap ] ?? null;

				if ( null !== $core && $role->has_cap( $core ) ) {
					if ( ! $role->has_cap( $cap ) ) {
						$role->add_cap( $cap );
					}
					continue;
				}

				if ( $role->has_cap( $cap ) ) {
					$role->remove_cap( $cap );
				}
			}
		}

		update_option( self::PT_CAPS_HASH_OPTION, $hash );
		update_option( self::PT_CAPS_HASH_OPTION . '_caps', array_keys( $map ) );
	}
}
