<?php
/**
 * Shortcode handler for Live Campaign Dashboard.
 *
 * Registers [live_campaign_dashboard] and renders the premium
 * analytics dashboard HTML with dynamic unique IDs for multi-instance support.
 *
 * @package LiveCampaignDashboard
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class LCD_Shortcode
 */
class LCD_Shortcode {

    /**
     * Instance counter for generating unique IDs.
     *
     * @var int
     */
    private static $instance_count = 0;

    /**
     * Constructor — register the shortcode.
     */
    public function __construct() {
        add_shortcode( 'live_campaign_dashboard', array( $this, 'render' ) );
    }

    /**
     * Render the shortcode output.
     *
     * @param array|string $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function render( $atts ) {
        // Signal the main plugin class to enqueue assets.
        Live_Campaign_Dashboard::get_instance()->set_shortcode_present();

        self::$instance_count++;
        $uid = 'lcd-' . self::$instance_count . '-' . wp_rand( 1000, 9999 );

        $settings = get_option( 'lcd_settings', array() );

        $atts = shortcode_atts( array(
            'theme'           => isset( $settings['theme'] ) ? $settings['theme'] : 'light',
            'revenue'         => isset( $settings['revenue'] ) ? $settings['revenue'] : '2.4Cr',
            'roas'            => isset( $settings['roas'] ) ? $settings['roas'] : '4.8x',
            'ctr'             => isset( $settings['ctr'] ) ? $settings['ctr'] : '18%',
            'views'           => isset( $settings['views'] ) ? $settings['views'] : '200M+',
            'conversion'      => isset( $settings['conversion'] ) ? $settings['conversion'] : '12.6%',
            'floating'        => 'true',
            'animation_speed' => isset( $settings['animation_speed'] ) ? $settings['animation_speed'] : 'medium',
        ), $atts, 'live_campaign_dashboard' );

        $theme           = sanitize_key( $atts['theme'] );
        $revenue         = sanitize_text_field( $atts['revenue'] );
        $roas            = sanitize_text_field( $atts['roas'] );
        $ctr             = sanitize_text_field( $atts['ctr'] );
        $views_val       = sanitize_text_field( $atts['views'] );
        $conversion      = sanitize_text_field( $atts['conversion'] );
        $floating        = ( 'true' === $atts['floating'] || '1' === $atts['floating'] ) ? true : false;
        $animation_speed = sanitize_key( $atts['animation_speed'] );

        $revenue_growth = isset( $settings['revenue_growth'] ) ? sanitize_text_field( $settings['revenue_growth'] ) : '28';
        $roas_growth    = isset( $settings['roas_growth'] ) ? sanitize_text_field( $settings['roas_growth'] ) : '1.2x';
        $ctr_growth     = isset( $settings['ctr_growth'] ) ? sanitize_text_field( $settings['ctr_growth'] ) : '6';
        $primary_color  = isset( $settings['primary_color'] ) ? sanitize_hex_color( $settings['primary_color'] ) : '#2563eb';
        $accent_color   = isset( $settings['accent_color'] ) ? sanitize_hex_color( $settings['accent_color'] ) : '#10b981';

        $floating_class  = $floating ? ' lcd-floating-enabled' : '';
        $speed_class     = ' lcd-speed-' . $animation_speed;

        ob_start();
        ?>
        <div class="lcd-wrapper lcd-theme-<?php echo esc_attr( $theme ); ?><?php echo esc_attr( $floating_class . $speed_class ); ?>"
             id="<?php echo esc_attr( $uid ); ?>"
             data-lcd-instance="<?php echo esc_attr( $uid ); ?>"
             data-theme="<?php echo esc_attr( $theme ); ?>"
             data-speed="<?php echo esc_attr( $animation_speed ); ?>"
             style="--lcd-primary: <?php echo esc_attr( $primary_color ); ?>; --lcd-accent: <?php echo esc_attr( $accent_color ); ?>;"
             role="region"
             aria-label="<?php esc_attr_e( 'Campaign Performance Dashboard', 'live-campaign-dashboard' ); ?>">

            <!-- Floating Card: Top-Right -->
            <?php if ( $floating ) : ?>
            <div class="lcd-floating-card lcd-float-top-right" aria-hidden="true">
                <div class="lcd-floating-card-inner">
                    <span class="lcd-floating-icon lcd-floating-icon--chart">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </span>
                    <div class="lcd-floating-text">
                        <span class="lcd-floating-value lcd-accent-text">+347%</span>
                        <span class="lcd-floating-label">ROAS this month</span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Main Dashboard Panel -->
            <div class="lcd-dashboard-panel">
                <!-- Header -->
                <div class="lcd-panel-header">
                    <h2 class="lcd-panel-title"><?php esc_html_e( 'Campaign Performance', 'live-campaign-dashboard' ); ?></h2>
                    <div class="lcd-live-badge">
                        <span class="lcd-pulse-dot"></span>
                        <span><?php esc_html_e( 'Live', 'live-campaign-dashboard' ); ?></span>
                    </div>
                </div>

                <!-- Metric Cards Grid -->
                <div class="lcd-metrics-grid">
                    <!-- Revenue Card -->
                    <div class="lcd-metric-card" data-metric="revenue" data-value="<?php echo esc_attr( $revenue ); ?>">
                        <div class="lcd-metric-value-wrap">
                            <span class="lcd-metric-prefix">₹</span>
                            <span class="lcd-metric-value lcd-counter" data-target="<?php echo esc_attr( $revenue ); ?>"><?php echo esc_html( $revenue ); ?></span>
                        </div>
                        <span class="lcd-metric-label"><?php esc_html_e( 'Revenue Generated', 'live-campaign-dashboard' ); ?></span>
                        <span class="lcd-metric-growth lcd-accent-text">↑ <?php echo esc_html( $revenue_growth ); ?>% MoM</span>
                    </div>

                    <!-- ROAS Card -->
                    <div class="lcd-metric-card" data-metric="roas" data-value="<?php echo esc_attr( $roas ); ?>">
                        <div class="lcd-metric-value-wrap">
                            <span class="lcd-metric-value lcd-counter" data-target="<?php echo esc_attr( $roas ); ?>"><?php echo esc_html( $roas ); ?></span>
                        </div>
                        <span class="lcd-metric-label"><?php esc_html_e( 'Avg. ROAS', 'live-campaign-dashboard' ); ?></span>
                        <span class="lcd-metric-growth lcd-accent-text">↑ <?php echo esc_html( $roas_growth ); ?></span>
                    </div>

                    <!-- CTR Card -->
                    <div class="lcd-metric-card" data-metric="ctr" data-value="<?php echo esc_attr( $ctr ); ?>">
                        <div class="lcd-metric-value-wrap">
                            <span class="lcd-metric-value lcd-counter" data-target="<?php echo esc_attr( $ctr ); ?>"><?php echo esc_html( $ctr ); ?></span>
                        </div>
                        <span class="lcd-metric-label"><?php esc_html_e( 'CTR Rate', 'live-campaign-dashboard' ); ?></span>
                        <span class="lcd-metric-growth lcd-accent-text">↑ <?php echo esc_html( $ctr_growth ); ?>%</span>
                    </div>
                </div>

                <!-- Chart Section -->
                <div class="lcd-chart-section">
                    <h3 class="lcd-chart-title"><?php esc_html_e( 'Ad spend vs Revenue — last 9 weeks', 'live-campaign-dashboard' ); ?></h3>
                    <div class="lcd-chart-container">
                        <div class="lcd-bar-group" data-week="1">
                            <div class="lcd-bar lcd-bar-spend" style="--bar-height: 35%;" aria-label="<?php esc_attr_e( 'Week 1 ad spend', 'live-campaign-dashboard' ); ?>"></div>
                            <div class="lcd-bar lcd-bar-revenue" style="--bar-height: 28%;" aria-label="<?php esc_attr_e( 'Week 1 revenue', 'live-campaign-dashboard' ); ?>"></div>
                        </div>
                        <div class="lcd-bar-group" data-week="2">
                            <div class="lcd-bar lcd-bar-spend" style="--bar-height: 30%;" aria-label="<?php esc_attr_e( 'Week 2 ad spend', 'live-campaign-dashboard' ); ?>"></div>
                            <div class="lcd-bar lcd-bar-revenue" style="--bar-height: 32%;" aria-label="<?php esc_attr_e( 'Week 2 revenue', 'live-campaign-dashboard' ); ?>"></div>
                        </div>
                        <div class="lcd-bar-group" data-week="3">
                            <div class="lcd-bar lcd-bar-spend" style="--bar-height: 40%;" aria-label="<?php esc_attr_e( 'Week 3 ad spend', 'live-campaign-dashboard' ); ?>"></div>
                            <div class="lcd-bar lcd-bar-revenue" style="--bar-height: 38%;" aria-label="<?php esc_attr_e( 'Week 3 revenue', 'live-campaign-dashboard' ); ?>"></div>
                        </div>
                        <div class="lcd-bar-group" data-week="4">
                            <div class="lcd-bar lcd-bar-spend" style="--bar-height: 42%;" aria-label="<?php esc_attr_e( 'Week 4 ad spend', 'live-campaign-dashboard' ); ?>"></div>
                            <div class="lcd-bar lcd-bar-revenue" style="--bar-height: 45%;" aria-label="<?php esc_attr_e( 'Week 4 revenue', 'live-campaign-dashboard' ); ?>"></div>
                        </div>
                        <div class="lcd-bar-group" data-week="5">
                            <div class="lcd-bar lcd-bar-spend" style="--bar-height: 55%;" aria-label="<?php esc_attr_e( 'Week 5 ad spend', 'live-campaign-dashboard' ); ?>"></div>
                            <div class="lcd-bar lcd-bar-revenue" style="--bar-height: 65%;" aria-label="<?php esc_attr_e( 'Week 5 revenue', 'live-campaign-dashboard' ); ?>"></div>
                        </div>
                        <div class="lcd-bar-group" data-week="6">
                            <div class="lcd-bar lcd-bar-spend" style="--bar-height: 60%;" aria-label="<?php esc_attr_e( 'Week 6 ad spend', 'live-campaign-dashboard' ); ?>"></div>
                            <div class="lcd-bar lcd-bar-revenue" style="--bar-height: 72%;" aria-label="<?php esc_attr_e( 'Week 6 revenue', 'live-campaign-dashboard' ); ?>"></div>
                        </div>
                        <div class="lcd-bar-group" data-week="7">
                            <div class="lcd-bar lcd-bar-spend" style="--bar-height: 65%;" aria-label="<?php esc_attr_e( 'Week 7 ad spend', 'live-campaign-dashboard' ); ?>"></div>
                            <div class="lcd-bar lcd-bar-revenue" style="--bar-height: 80%;" aria-label="<?php esc_attr_e( 'Week 7 revenue', 'live-campaign-dashboard' ); ?>"></div>
                        </div>
                        <div class="lcd-bar-group" data-week="8">
                            <div class="lcd-bar lcd-bar-spend" style="--bar-height: 58%;" aria-label="<?php esc_attr_e( 'Week 8 ad spend', 'live-campaign-dashboard' ); ?>"></div>
                            <div class="lcd-bar lcd-bar-revenue" style="--bar-height: 85%;" aria-label="<?php esc_attr_e( 'Week 8 revenue', 'live-campaign-dashboard' ); ?>"></div>
                        </div>
                        <div class="lcd-bar-group" data-week="9">
                            <div class="lcd-bar lcd-bar-spend" style="--bar-height: 62%;" aria-label="<?php esc_attr_e( 'Week 9 ad spend', 'live-campaign-dashboard' ); ?>"></div>
                            <div class="lcd-bar lcd-bar-revenue" style="--bar-height: 92%;" aria-label="<?php esc_attr_e( 'Week 9 revenue', 'live-campaign-dashboard' ); ?>"></div>
                        </div>
                    </div>
                </div>

                <!-- Progress Bars Section -->
                <div class="lcd-progress-section">
                    <div class="lcd-progress-row">
                        <div class="lcd-progress-info">
                            <span class="lcd-progress-label"><?php esc_html_e( 'Meta Ads', 'live-campaign-dashboard' ); ?></span>
                            <span class="lcd-progress-pct">95%</span>
                        </div>
                        <div class="lcd-progress-track">
                            <div class="lcd-progress-fill" data-width="95" style="--fill-width: 95%;"></div>
                        </div>
                    </div>
                    <div class="lcd-progress-row">
                        <div class="lcd-progress-info">
                            <span class="lcd-progress-label"><?php esc_html_e( 'Quick Commerce Ads', 'live-campaign-dashboard' ); ?></span>
                            <span class="lcd-progress-pct">90%</span>
                        </div>
                        <div class="lcd-progress-track">
                            <div class="lcd-progress-fill" data-width="90" style="--fill-width: 90%;"></div>
                        </div>
                    </div>
                    <div class="lcd-progress-row">
                        <div class="lcd-progress-info">
                            <span class="lcd-progress-label"><?php esc_html_e( 'Content Marketing', 'live-campaign-dashboard' ); ?></span>
                            <span class="lcd-progress-pct">80%</span>
                        </div>
                        <div class="lcd-progress-track">
                            <div class="lcd-progress-fill lcd-progress-fill--accent" data-width="80" style="--fill-width: 80%;"></div>
                        </div>
                    </div>
                    <div class="lcd-progress-row">
                        <div class="lcd-progress-info">
                            <span class="lcd-progress-label"><?php esc_html_e( 'Google Ads', 'live-campaign-dashboard' ); ?></span>
                            <span class="lcd-progress-pct">50%</span>
                        </div>
                        <div class="lcd-progress-track">
                            <div class="lcd-progress-fill" data-width="50" style="--fill-width: 50%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Floating Card: Bottom-Left -->
            <?php if ( $floating ) : ?>
            <div class="lcd-floating-card lcd-float-bottom-left" aria-hidden="true">
                <div class="lcd-floating-card-inner">
                    <span class="lcd-floating-icon lcd-floating-icon--target">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                    </span>
                    <div class="lcd-floating-text">
                        <span class="lcd-floating-value lcd-counter" data-target="<?php echo esc_attr( $views_val ); ?>"><?php echo esc_html( $views_val ); ?></span>
                        <span class="lcd-floating-label"><?php esc_html_e( 'Views Generated', 'live-campaign-dashboard' ); ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Floating Notification Popup -->
            <?php if ( $floating ) : ?>
            <div class="lcd-notification-popup" aria-hidden="true">
                <span class="lcd-notif-dot"></span>
                <span class="lcd-notif-text"><?php esc_html_e( 'New conversion recorded!', 'live-campaign-dashboard' ); ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
