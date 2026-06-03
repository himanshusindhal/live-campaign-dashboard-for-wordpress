<?php
/**
 * Plugin Name:       Live Campaign Dashboard
 * Plugin URI:        https://antigravity.dev/plugins/live-campaign-dashboard
 * Description:       A premium SaaS-style live analytics dashboard widget with animated counters, floating cards, interactive charts, and glassmorphism effects. Use shortcode [live_campaign_dashboard] on any page.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            AntyGravity
 * Author URI:        https://antigravity.dev
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       live-campaign-dashboard
 * Domain Path:       /languages
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Plugin constants.
 */
define( 'LCD_VERSION', '1.0.0' );
define( 'LCD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LCD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LCD_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class — singleton pattern.
 *
 * Orchestrates admin settings, shortcode rendering, REST API,
 * and asset enqueuing for the Live Campaign Dashboard.
 */
final class Live_Campaign_Dashboard {

    /**
     * Singleton instance.
     *
     * @var Live_Campaign_Dashboard|null
     */
    private static $instance = null;

    /**
     * Flag: shortcode present on the current request.
     *
     * @var bool
     */
    private $shortcode_present = false;

    /**
     * Return the singleton instance.
     *
     * @return Live_Campaign_Dashboard
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor — wire everything up.
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Include required files.
     */
    private function load_dependencies() {
        require_once LCD_PLUGIN_DIR . 'includes/class-lcd-admin.php';
        require_once LCD_PLUGIN_DIR . 'includes/class-lcd-shortcode.php';
        require_once LCD_PLUGIN_DIR . 'includes/class-lcd-rest-api.php';
    }

    /**
     * Register WordPress hooks.
     */
    private function init_hooks() {
        // Activation / deactivation.
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        // Admin.
        if ( is_admin() ) {
            new LCD_Admin();
        }

        // Shortcode.
        new LCD_Shortcode();

        // REST API.
        add_action( 'rest_api_init', array( 'LCD_Rest_API', 'register_routes' ) );

        // Conditional asset loading.
        add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_assets' ) );

        // Add settings link on plugins page.
        add_filter( 'plugin_action_links_' . LCD_PLUGIN_BASENAME, array( $this, 'add_settings_link' ) );
    }

    /**
     * On activation — seed default options.
     */
    public function activate() {
        $defaults = array(
            'revenue'            => '2.4Cr',
            'roas'               => '4.8x',
            'ctr'                => '18%',
            'views'              => '200M+',
            'conversion'         => '12.6%',
            'revenue_growth'     => '28',
            'roas_growth'        => '1.2x',
            'ctr_growth'         => '6',
            'live_animation'     => '1',
            'animation_speed'    => 'medium',
            'primary_color'      => '#2563eb',
            'accent_color'       => '#10b981',
            'floating_intensity' => 'medium',
            'theme'              => 'light',
            'cards_config'       => wp_json_encode( array(
                array(
                    'id'     => 'revenue',
                    'label'  => 'Revenue Generated',
                    'value'  => '2.4Cr',
                    'prefix' => '₹',
                    'suffix' => '',
                    'growth' => '28% MoM',
                    'icon'   => 'revenue',
                ),
                array(
                    'id'     => 'roas',
                    'label'  => 'Avg. ROAS',
                    'value'  => '4.8x',
                    'prefix' => '',
                    'suffix' => '',
                    'growth' => '↑ 1.2x',
                    'icon'   => 'roas',
                ),
                array(
                    'id'     => 'ctr',
                    'label'  => 'CTR Rate',
                    'value'  => '18%',
                    'prefix' => '',
                    'suffix' => '',
                    'growth' => '↑ 6%',
                    'icon'   => 'ctr',
                ),
                array(
                    'id'     => 'views',
                    'label'  => 'Views Generated',
                    'value'  => '200M+',
                    'prefix' => '',
                    'suffix' => '',
                    'growth' => '↑ 42%',
                    'icon'   => 'views',
                ),
                array(
                    'id'     => 'conversion',
                    'label'  => 'Conversion Rate',
                    'value'  => '12.6%',
                    'prefix' => '',
                    'suffix' => '',
                    'growth' => '↑ 3.1%',
                    'icon'   => 'conversion',
                ),
            ) ),
        );

        if ( false === get_option( 'lcd_settings' ) ) {
            add_option( 'lcd_settings', $defaults );
        }
    }

    /**
     * On deactivation — lightweight cleanup (options preserved).
     */
    public function deactivate() {
        // Options intentionally preserved for reactivation.
    }

    /**
     * Conditionally enqueue front-end assets only when the
     * shortcode is detected in the current post content.
     */
    public function maybe_enqueue_assets() {
        global $post;

        $should_load = false;

        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'live_campaign_dashboard' ) ) {
            $should_load = true;
        }

        // Also check widget areas via a flag set by the shortcode class.
        if ( $this->shortcode_present ) {
            $should_load = true;
        }

        if ( $should_load ) {
            $this->enqueue_frontend_assets();
        }
    }

    /**
     * Set the shortcode-present flag (called by LCD_Shortcode).
     */
    public function set_shortcode_present() {
        $this->shortcode_present = true;

        // If assets haven't been enqueued yet, do it now.
        if ( ! wp_script_is( 'lcd-dashboard', 'enqueued' ) ) {
            $this->enqueue_frontend_assets();
        }
    }

    /**
     * Register and enqueue front-end CSS & JS.
     */
    private function enqueue_frontend_assets() {
        wp_enqueue_style(
            'lcd-dashboard',
            LCD_PLUGIN_URL . 'assets/css/dashboard.css',
            array(),
            LCD_VERSION
        );

        wp_enqueue_script(
            'lcd-dashboard',
            LCD_PLUGIN_URL . 'assets/js/dashboard.js',
            array(),
            LCD_VERSION,
            true
        );

        // Pass settings to JS.
        $settings = get_option( 'lcd_settings', array() );

        wp_localize_script( 'lcd-dashboard', 'lcdSettings', array(
            'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
            'restUrl'       => esc_url_raw( rest_url( 'lcd/v1/' ) ),
            'nonce'         => wp_create_nonce( 'wp_rest' ),
            'liveAnimation' => isset( $settings['live_animation'] ) ? $settings['live_animation'] : '1',
            'animSpeed'     => isset( $settings['animation_speed'] ) ? $settings['animation_speed'] : 'medium',
            'primaryColor'  => isset( $settings['primary_color'] ) ? $settings['primary_color'] : '#2563eb',
            'accentColor'   => isset( $settings['accent_color'] ) ? $settings['accent_color'] : '#10b981',
            'floatingIntensity' => isset( $settings['floating_intensity'] ) ? $settings['floating_intensity'] : 'medium',
        ) );
    }

    /**
     * Add a "Settings" link on the Plugins list table.
     *
     * @param array $links Existing action links.
     * @return array
     */
    public function add_settings_link( $links ) {
        $settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=lcd-settings' ) ) . '">'
                       . esc_html__( 'Settings', 'live-campaign-dashboard' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }
}

// Boot the plugin.
Live_Campaign_Dashboard::get_instance();
