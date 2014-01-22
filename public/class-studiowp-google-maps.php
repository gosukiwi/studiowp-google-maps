<?php
/**
 * StudioWP Google Maps
 *
 * @package   StudioWP_Google_Maps
 * @author    Federico Ramírez <federico@studiowp.net>
 * @license   GPL-2.0+
 * @link      http://studiowp.net
 * @copyright 2014 Studio WP
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-plugin-name-admin.php`
 *
 * @package StudioWP_Google_Maps
 * @author  Your Name <email@example.com>
 */
class StudioWP_Google_Maps {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'studiowp-google-maps';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // Register the content filter
        add_filter( 'the_content', array( $this, 'filter_replace_tags' ) );

        // Register TinyMCE extra button
        if ( ( current_user_can('edit_posts') || current_user_can('edit_pages') ) && get_user_option('rich_editing') ) {
            add_filter( 'mce_external_plugins', array( $this, 'register_tinymce_javascript' ) );
            add_filter( 'mce_buttons', array( $this, 'register_buttons' ) );
        }

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
        // Create cache table for lat lng
        global $wpdb;
        $table_name = $wpdb->prefix . 'swp_maps_cache';

        $sql = "CREATE TABLE $table_name (
          address_hash varchar(32) NOT NULL,
          lat varchar(16) NOT NULL,
          lng varchar(16) NOT NULL,
          UNIQUE KEY address_hash (address_hash)
        );";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
        // Drop tables used by this plugin
        global $wpdb;
        $table_name = $wpdb->prefix . 'swp_maps_cache';
        $wpdb->query( "DROP TABLE IF EXISTS $table" );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-google-maps-api', 'http://maps.googleapis.com/maps/api/js?sensor=false', array(), self::VERSION, true );
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery', $this->plugin_slug . '-google-maps-api' ), self::VERSION, true );
	}

    /**
     * Replace google map tags with html markup which is later going to be
     * picked up by the plugin's javascript.
	 *
	 * @since    1.0.0
	 */
    public function filter_replace_tags( $content ) {
        // Load wpdb, as we'll work with the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'swp_maps_cache';

        // Now seek all google map tags
        preg_match_all( 
            '/\[google-map (([a-z]+=".+?"\s*?)+)\]/', 
            $content, 
            $matches, 
            PREG_SET_ORDER 
        );

        // For each tag...
        foreach( $matches as $match ) {
            // Save the full tag string
            $old_string = $match[0];

            // Now, using the key and value pairs in html forlat, extract them to an array
            $key_value_pairs = $match[1];
            preg_match_all( '/(([a-z]+)="(.*?)")/', $key_value_pairs, $matches, PREG_SET_ORDER );
            $settings = array();
            foreach( $matches as $match ) {
                $settings[$match[2]] = $match[3];
            }

            // Check for invalid address
            if( !isset( $settings['address'] ) ) {
                continue;
            }

            // Let's get the latlang from the address using Google's geolocation API only once
            $encoded_address = md5( $settings['address'] );
            $location = $wpdb->get_row( "SELECT * FROM $table_name WHERE address_hash = '$encoded_address'", ARRAY_A );
            if( is_null( $location ) ) {
                $geoloc = json_decode(file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($settings['address']).'&sensor=false'), true);
                $location = $geoloc['results'][0]['geometry']['location'];
                $wpdb->insert(
                    $table_name, 
                    array(
                        'address_hash'  => $encoded_address,
                        'lat'           => $location['lat'],
                        'lng'           => $location['lng'],
                    )
                );
            }

            // Add lat and lng to settings
            $settings['lat'] = $location['lat'];
            $settings['lng'] = $location['lng'];

            // Finally transform the tag to html to be picked up by javascript later on
            $content = str_replace( $old_string, '<div '. $this->array_to_html_key_values( $settings ) .' class="studiowp-google-map"></div>', $content );
        }

        // Finally, return the modified content
        return $content;
    }

    /**
     * Transforms a PHP array into an html key-value string
	 *
	 * @since    1.0.0
	 */
    private function array_to_html_key_values ( $array ) {
        $kvs = array();
        foreach( $array as $key => $value ) {
            $kvs[] = 'map-' . $key . '="' . str_replace('"', '', $value) . '"';
        }

        return implode( ' ', $kvs );
    }

    public function register_buttons( $buttons ) {
        // Add a separator and a 'add-map' button
        array_push( $buttons, 'add-map' );
        return $buttons;
    }

    public function register_tinymce_javascript( $plugin_array ) {
        $plugin_array['studiowpgooglemaps'] = plugins_url( 'assets/js/tinymce-plugin.js', __FILE__ );
        return $plugin_array;
    }

}
