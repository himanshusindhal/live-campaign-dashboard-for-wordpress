<?php
/**
 * REST API endpoints for Live Campaign Dashboard.
 *
 * Exposes read-only metric data so external tools or
 * front-end AJAX calls can fetch live values.
 *
 * @package LiveCampaignDashboard
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class LCD_Rest_API
 */
class LCD_Rest_API {

    /**
     * Register custom REST routes.
     */
    public static function register_routes() {
        register_rest_route( 'lcd/v1', '/metrics', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'get_metrics' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( 'lcd/v1', '/metrics', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'update_metrics' ),
            'permission_callback' => array( __CLASS__, 'check_admin_permission' ),
        ) );
    }

    /**
     * GET /lcd/v1/metrics — return current metric values.
     *
     * @return WP_REST_Response
     */
    public static function get_metrics() {
        $settings = get_option( 'lcd_settings', array() );

        $data = array(
            'revenue'        => isset( $settings['revenue'] ) ? $settings['revenue'] : '2.4Cr',
            'roas'           => isset( $settings['roas'] ) ? $settings['roas'] : '4.8x',
            'ctr'            => isset( $settings['ctr'] ) ? $settings['ctr'] : '18%',
            'views'          => isset( $settings['views'] ) ? $settings['views'] : '200M+',
            'conversion'     => isset( $settings['conversion'] ) ? $settings['conversion'] : '12.6%',
            'revenue_growth' => isset( $settings['revenue_growth'] ) ? $settings['revenue_growth'] : '28',
            'roas_growth'    => isset( $settings['roas_growth'] ) ? $settings['roas_growth'] : '1.2x',
            'ctr_growth'     => isset( $settings['ctr_growth'] ) ? $settings['ctr_growth'] : '6',
            'theme'          => isset( $settings['theme'] ) ? $settings['theme'] : 'light',
            'liveAnimation'  => isset( $settings['live_animation'] ) ? $settings['live_animation'] : '1',
            'animSpeed'      => isset( $settings['animation_speed'] ) ? $settings['animation_speed'] : 'medium',
        );

        return new WP_REST_Response( $data, 200 );
    }

    /**
     * POST /lcd/v1/metrics — update metric values (admin only).
     *
     * @param WP_REST_Request $request Incoming request.
     * @return WP_REST_Response
     */
    public static function update_metrics( $request ) {
        $settings = get_option( 'lcd_settings', array() );
        $params   = $request->get_json_params();

        $allowed_keys = array(
            'revenue', 'roas', 'ctr', 'views', 'conversion',
            'revenue_growth', 'roas_growth', 'ctr_growth',
            'live_animation', 'animation_speed', 'theme',
            'primary_color', 'accent_color', 'floating_intensity',
        );

        foreach ( $allowed_keys as $key ) {
            if ( isset( $params[ $key ] ) ) {
                $settings[ $key ] = sanitize_text_field( $params[ $key ] );
            }
        }

        update_option( 'lcd_settings', $settings );

        return new WP_REST_Response( array( 'success' => true, 'settings' => $settings ), 200 );
    }

    /**
     * Permission check — only administrators can POST.
     *
     * @return bool
     */
    public static function check_admin_permission() {
        return current_user_can( 'manage_options' );
    }
}
