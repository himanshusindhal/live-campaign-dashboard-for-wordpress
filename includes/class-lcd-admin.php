<?php
/**
 * Admin settings page for Live Campaign Dashboard.
 *
 * Provides a full-featured WordPress admin panel where site owners
 * can configure metrics, animations, colors, floating intensity,
 * card management, and preview the dashboard live.
 *
 * @package LiveCampaignDashboard
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class LCD_Admin
 *
 * Handles the admin menu, settings registration, page rendering,
 * and admin-only asset enqueuing.
 */
class LCD_Admin {

    /**
     * Constructor — register admin hooks.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Add the settings page under the WordPress Settings menu.
     */
    public function add_menu_page() {
        add_menu_page(
            __( 'Campaign Dashboard', 'live-campaign-dashboard' ),
            __( 'Campaign Dashboard', 'live-campaign-dashboard' ),
            'manage_options',
            'lcd-settings',
            array( $this, 'render_settings_page' ),
            'dashicons-chart-area',
            30
        );
    }

    /**
     * Register the lcd_settings option and sanitization callback.
     */
    public function register_settings() {
        register_setting( 'lcd_settings_group', 'lcd_settings', array(
            'type'              => 'array',
            'sanitize_callback' => array( $this, 'sanitize_settings' ),
        ) );
    }

    /**
     * Sanitize all settings before saving.
     *
     * @param array $input Raw form input.
     * @return array Sanitized settings.
     */
    public function sanitize_settings( $input ) {
        $sanitized = array();

        $sanitized['revenue']            = isset( $input['revenue'] ) ? sanitize_text_field( $input['revenue'] ) : '2.4Cr';
        $sanitized['roas']               = isset( $input['roas'] ) ? sanitize_text_field( $input['roas'] ) : '4.8x';
        $sanitized['ctr']                = isset( $input['ctr'] ) ? sanitize_text_field( $input['ctr'] ) : '18%';
        $sanitized['views']              = isset( $input['views'] ) ? sanitize_text_field( $input['views'] ) : '200M+';
        $sanitized['conversion']         = isset( $input['conversion'] ) ? sanitize_text_field( $input['conversion'] ) : '12.6%';
        $sanitized['revenue_growth']     = isset( $input['revenue_growth'] ) ? sanitize_text_field( $input['revenue_growth'] ) : '28';
        $sanitized['roas_growth']        = isset( $input['roas_growth'] ) ? sanitize_text_field( $input['roas_growth'] ) : '1.2x';
        $sanitized['ctr_growth']         = isset( $input['ctr_growth'] ) ? sanitize_text_field( $input['ctr_growth'] ) : '6';
        $sanitized['live_animation']     = ! empty( $input['live_animation'] ) ? '1' : '0';
        $sanitized['animation_speed']    = isset( $input['animation_speed'] ) && in_array( $input['animation_speed'], array( 'slow', 'medium', 'fast' ), true ) ? $input['animation_speed'] : 'medium';
        $sanitized['primary_color']      = isset( $input['primary_color'] ) ? sanitize_hex_color( $input['primary_color'] ) : '#2563eb';
        $sanitized['accent_color']       = isset( $input['accent_color'] ) ? sanitize_hex_color( $input['accent_color'] ) : '#10b981';
        $sanitized['floating_intensity'] = isset( $input['floating_intensity'] ) && in_array( $input['floating_intensity'], array( 'none', 'low', 'medium', 'high' ), true ) ? $input['floating_intensity'] : 'medium';
        $sanitized['theme']              = isset( $input['theme'] ) && in_array( $input['theme'], array( 'light', 'dark' ), true ) ? $input['theme'] : 'light';

        // Cards config — decode, sanitize, re-encode.
        if ( isset( $input['cards_config'] ) ) {
            $cards = json_decode( wp_unslash( $input['cards_config'] ), true );
            if ( is_array( $cards ) ) {
                $clean_cards = array();
                foreach ( $cards as $card ) {
                    $clean_cards[] = array(
                        'id'     => isset( $card['id'] ) ? sanitize_key( $card['id'] ) : '',
                        'label'  => isset( $card['label'] ) ? sanitize_text_field( $card['label'] ) : '',
                        'value'  => isset( $card['value'] ) ? sanitize_text_field( $card['value'] ) : '',
                        'prefix' => isset( $card['prefix'] ) ? sanitize_text_field( $card['prefix'] ) : '',
                        'suffix' => isset( $card['suffix'] ) ? sanitize_text_field( $card['suffix'] ) : '',
                        'growth' => isset( $card['growth'] ) ? sanitize_text_field( $card['growth'] ) : '',
                        'icon'   => isset( $card['icon'] ) ? sanitize_key( $card['icon'] ) : 'revenue',
                    );
                }
                $sanitized['cards_config'] = wp_json_encode( $clean_cards );
            } else {
                $sanitized['cards_config'] = get_option( 'lcd_settings' )['cards_config'] ?? '[]';
            }
        }

        return $sanitized;
    }

    /**
     * Enqueue admin-only assets on our settings page.
     *
     * @param string $hook_suffix The current admin page.
     */
    public function enqueue_admin_assets( $hook_suffix ) {
        if ( 'toplevel_page_lcd-settings' !== $hook_suffix ) {
            return;
        }

        wp_enqueue_style( 'wp-color-picker' );

        wp_enqueue_style(
            'lcd-admin',
            LCD_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            LCD_VERSION
        );

        // Load frontend CSS + JS for the live preview.
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

        wp_localize_script( 'lcd-dashboard', 'lcdSettings', array(
            'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
            'restUrl'       => esc_url_raw( rest_url( 'lcd/v1/' ) ),
            'nonce'         => wp_create_nonce( 'wp_rest' ),
            'liveAnimation' => '1',
            'animSpeed'     => 'medium',
            'primaryColor'  => '#2563eb',
            'accentColor'   => '#10b981',
            'floatingIntensity' => 'medium',
        ) );

        wp_enqueue_script(
            'lcd-admin',
            LCD_PLUGIN_URL . 'assets/js/admin.js',
            array( 'wp-color-picker', 'lcd-dashboard' ),
            LCD_VERSION,
            true
        );

        wp_localize_script( 'lcd-admin', 'lcdAdmin', array(
            'nonce'   => wp_create_nonce( 'lcd_admin_nonce' ),
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        ) );
    }

    /**
     * Render the admin settings page.
     */
    public function render_settings_page() {
        // Check permissions.
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $settings = get_option( 'lcd_settings', array() );

        $revenue            = isset( $settings['revenue'] ) ? $settings['revenue'] : '2.4Cr';
        $roas               = isset( $settings['roas'] ) ? $settings['roas'] : '4.8x';
        $ctr                = isset( $settings['ctr'] ) ? $settings['ctr'] : '18%';
        $views              = isset( $settings['views'] ) ? $settings['views'] : '200M+';
        $conversion         = isset( $settings['conversion'] ) ? $settings['conversion'] : '12.6%';
        $revenue_growth     = isset( $settings['revenue_growth'] ) ? $settings['revenue_growth'] : '28';
        $roas_growth        = isset( $settings['roas_growth'] ) ? $settings['roas_growth'] : '1.2x';
        $ctr_growth         = isset( $settings['ctr_growth'] ) ? $settings['ctr_growth'] : '6';
        $live_animation     = isset( $settings['live_animation'] ) ? $settings['live_animation'] : '1';
        $animation_speed    = isset( $settings['animation_speed'] ) ? $settings['animation_speed'] : 'medium';
        $primary_color      = isset( $settings['primary_color'] ) ? $settings['primary_color'] : '#2563eb';
        $accent_color       = isset( $settings['accent_color'] ) ? $settings['accent_color'] : '#10b981';
        $floating_intensity = isset( $settings['floating_intensity'] ) ? $settings['floating_intensity'] : 'medium';
        $theme              = isset( $settings['theme'] ) ? $settings['theme'] : 'light';
        $cards_config       = isset( $settings['cards_config'] ) ? $settings['cards_config'] : '[]';
        ?>
        <div class="wrap lcd-admin-wrap">
            <div class="lcd-admin-header">
                <div class="lcd-admin-header-inner">
                    <div class="lcd-admin-title-group">
                        <span class="lcd-admin-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                        </span>
                        <div>
                            <h1><?php esc_html_e( 'Live Campaign Dashboard', 'live-campaign-dashboard' ); ?></h1>
                            <p class="lcd-admin-subtitle"><?php esc_html_e( 'Configure your premium analytics dashboard widget', 'live-campaign-dashboard' ); ?></p>
                        </div>
                    </div>
                    <span class="lcd-admin-version">v<?php echo esc_html( LCD_VERSION ); ?></span>
                </div>
            </div>

            <div class="lcd-admin-body">
                <div class="lcd-admin-grid">
                    <!-- Settings Column -->
                    <div class="lcd-admin-settings-col">
                        <form method="post" action="options.php" id="lcd-settings-form">
                            <?php settings_fields( 'lcd_settings_group' ); ?>

                            <!-- Shortcode Copy -->
                            <div class="lcd-admin-card lcd-shortcode-card">
                                <h2><span class="dashicons dashicons-shortcode"></span> <?php esc_html_e( 'Shortcode', 'live-campaign-dashboard' ); ?></h2>
                                <div class="lcd-shortcode-box">
                                    <code id="lcd-shortcode-text">[live_campaign_dashboard]</code>
                                    <button type="button" class="lcd-copy-btn" id="lcd-copy-shortcode" title="<?php esc_attr_e( 'Copy shortcode', 'live-campaign-dashboard' ); ?>">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                        <span><?php esc_html_e( 'Copy', 'live-campaign-dashboard' ); ?></span>
                                    </button>
                                </div>
                                <p class="lcd-shortcode-hint">
                                    <?php esc_html_e( 'Advanced: [live_campaign_dashboard theme="dark" revenue="5Cr" roas="6.2x" floating="true"]', 'live-campaign-dashboard' ); ?>
                                </p>
                            </div>

                            <!-- Metrics -->
                            <div class="lcd-admin-card">
                                <h2><span class="dashicons dashicons-chart-bar"></span> <?php esc_html_e( 'Metric Values', 'live-campaign-dashboard' ); ?></h2>
                                <div class="lcd-admin-field-grid">
                                    <div class="lcd-field-group">
                                        <label for="lcd-revenue"><?php esc_html_e( 'Revenue', 'live-campaign-dashboard' ); ?></label>
                                        <input type="text" id="lcd-revenue" name="lcd_settings[revenue]" value="<?php echo esc_attr( $revenue ); ?>" placeholder="2.4Cr">
                                    </div>
                                    <div class="lcd-field-group">
                                        <label for="lcd-roas"><?php esc_html_e( 'ROAS', 'live-campaign-dashboard' ); ?></label>
                                        <input type="text" id="lcd-roas" name="lcd_settings[roas]" value="<?php echo esc_attr( $roas ); ?>" placeholder="4.8x">
                                    </div>
                                    <div class="lcd-field-group">
                                        <label for="lcd-ctr"><?php esc_html_e( 'CTR Rate', 'live-campaign-dashboard' ); ?></label>
                                        <input type="text" id="lcd-ctr" name="lcd_settings[ctr]" value="<?php echo esc_attr( $ctr ); ?>" placeholder="18%">
                                    </div>
                                    <div class="lcd-field-group">
                                        <label for="lcd-views"><?php esc_html_e( 'Views', 'live-campaign-dashboard' ); ?></label>
                                        <input type="text" id="lcd-views" name="lcd_settings[views]" value="<?php echo esc_attr( $views ); ?>" placeholder="200M+">
                                    </div>
                                    <div class="lcd-field-group">
                                        <label for="lcd-conversion"><?php esc_html_e( 'Conversion Rate', 'live-campaign-dashboard' ); ?></label>
                                        <input type="text" id="lcd-conversion" name="lcd_settings[conversion]" value="<?php echo esc_attr( $conversion ); ?>" placeholder="12.6%">
                                    </div>
                                </div>
                            </div>

                            <!-- Growth Indicators -->
                            <div class="lcd-admin-card">
                                <h2><span class="dashicons dashicons-arrow-up-alt"></span> <?php esc_html_e( 'Growth Indicators', 'live-campaign-dashboard' ); ?></h2>
                                <div class="lcd-admin-field-grid">
                                    <div class="lcd-field-group">
                                        <label for="lcd-revenue-growth"><?php esc_html_e( 'Revenue Growth (%)', 'live-campaign-dashboard' ); ?></label>
                                        <input type="text" id="lcd-revenue-growth" name="lcd_settings[revenue_growth]" value="<?php echo esc_attr( $revenue_growth ); ?>" placeholder="28">
                                    </div>
                                    <div class="lcd-field-group">
                                        <label for="lcd-roas-growth"><?php esc_html_e( 'ROAS Growth', 'live-campaign-dashboard' ); ?></label>
                                        <input type="text" id="lcd-roas-growth" name="lcd_settings[roas_growth]" value="<?php echo esc_attr( $roas_growth ); ?>" placeholder="1.2x">
                                    </div>
                                    <div class="lcd-field-group">
                                        <label for="lcd-ctr-growth"><?php esc_html_e( 'CTR Growth (%)', 'live-campaign-dashboard' ); ?></label>
                                        <input type="text" id="lcd-ctr-growth" name="lcd_settings[ctr_growth]" value="<?php echo esc_attr( $ctr_growth ); ?>" placeholder="6">
                                    </div>
                                </div>
                            </div>

                            <!-- Animation & Appearance -->
                            <div class="lcd-admin-card">
                                <h2><span class="dashicons dashicons-admin-appearance"></span> <?php esc_html_e( 'Appearance & Animation', 'live-campaign-dashboard' ); ?></h2>
                                <div class="lcd-admin-field-grid">
                                    <div class="lcd-field-group">
                                        <label for="lcd-theme"><?php esc_html_e( 'Theme', 'live-campaign-dashboard' ); ?></label>
                                        <select id="lcd-theme" name="lcd_settings[theme]">
                                            <option value="light" <?php selected( $theme, 'light' ); ?>><?php esc_html_e( 'Light', 'live-campaign-dashboard' ); ?></option>
                                            <option value="dark" <?php selected( $theme, 'dark' ); ?>><?php esc_html_e( 'Dark', 'live-campaign-dashboard' ); ?></option>
                                        </select>
                                    </div>
                                    <div class="lcd-field-group">
                                        <label for="lcd-live-animation">
                                            <input type="checkbox" id="lcd-live-animation" name="lcd_settings[live_animation]" value="1" <?php checked( $live_animation, '1' ); ?>>
                                            <?php esc_html_e( 'Enable Live Animation', 'live-campaign-dashboard' ); ?>
                                        </label>
                                    </div>
                                    <div class="lcd-field-group">
                                        <label for="lcd-anim-speed"><?php esc_html_e( 'Animation Speed', 'live-campaign-dashboard' ); ?></label>
                                        <select id="lcd-anim-speed" name="lcd_settings[animation_speed]">
                                            <option value="slow" <?php selected( $animation_speed, 'slow' ); ?>><?php esc_html_e( 'Slow', 'live-campaign-dashboard' ); ?></option>
                                            <option value="medium" <?php selected( $animation_speed, 'medium' ); ?>><?php esc_html_e( 'Medium', 'live-campaign-dashboard' ); ?></option>
                                            <option value="fast" <?php selected( $animation_speed, 'fast' ); ?>><?php esc_html_e( 'Fast', 'live-campaign-dashboard' ); ?></option>
                                        </select>
                                    </div>
                                    <div class="lcd-field-group">
                                        <label for="lcd-floating-intensity"><?php esc_html_e( 'Floating Intensity', 'live-campaign-dashboard' ); ?></label>
                                        <select id="lcd-floating-intensity" name="lcd_settings[floating_intensity]">
                                            <option value="none" <?php selected( $floating_intensity, 'none' ); ?>><?php esc_html_e( 'None', 'live-campaign-dashboard' ); ?></option>
                                            <option value="low" <?php selected( $floating_intensity, 'low' ); ?>><?php esc_html_e( 'Low', 'live-campaign-dashboard' ); ?></option>
                                            <option value="medium" <?php selected( $floating_intensity, 'medium' ); ?>><?php esc_html_e( 'Medium', 'live-campaign-dashboard' ); ?></option>
                                            <option value="high" <?php selected( $floating_intensity, 'high' ); ?>><?php esc_html_e( 'High', 'live-campaign-dashboard' ); ?></option>
                                        </select>
                                    </div>
                                    <div class="lcd-field-group">
                                        <label for="lcd-primary-color"><?php esc_html_e( 'Primary Color', 'live-campaign-dashboard' ); ?></label>
                                        <input type="text" id="lcd-primary-color" name="lcd_settings[primary_color]" value="<?php echo esc_attr( $primary_color ); ?>" class="lcd-color-picker" data-default-color="#2563eb">
                                    </div>
                                    <div class="lcd-field-group">
                                        <label for="lcd-accent-color"><?php esc_html_e( 'Accent Color', 'live-campaign-dashboard' ); ?></label>
                                        <input type="text" id="lcd-accent-color" name="lcd_settings[accent_color]" value="<?php echo esc_attr( $accent_color ); ?>" class="lcd-color-picker" data-default-color="#10b981">
                                    </div>
                                </div>
                            </div>

                            <!-- Cards Configuration (JSON) -->
                            <div class="lcd-admin-card">
                                <h2><span class="dashicons dashicons-screenoptions"></span> <?php esc_html_e( 'Cards Configuration', 'live-campaign-dashboard' ); ?></h2>
                                <p class="description"><?php esc_html_e( 'Manage metric cards displayed on the dashboard. Add, remove, or edit cards below.', 'live-campaign-dashboard' ); ?></p>
                                <div id="lcd-cards-manager">
                                    <!-- Dynamically managed by admin.js -->
                                </div>
                                <button type="button" class="button lcd-add-card-btn" id="lcd-add-card">
                                    <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Add Card', 'live-campaign-dashboard' ); ?>
                                </button>
                                <textarea name="lcd_settings[cards_config]" id="lcd-cards-config-field" class="lcd-hidden-field"><?php echo esc_textarea( $cards_config ); ?></textarea>
                            </div>

                            <?php submit_button( __( 'Save Settings', 'live-campaign-dashboard' ), 'primary lcd-save-btn', 'submit', true ); ?>
                        </form>
                    </div>

                    <!-- Preview Column -->
                    <div class="lcd-admin-preview-col">
                        <div class="lcd-admin-card lcd-preview-card">
                            <h2><span class="dashicons dashicons-visibility"></span> <?php esc_html_e( 'Live Preview', 'live-campaign-dashboard' ); ?></h2>
                            <div class="lcd-preview-container" id="lcd-admin-preview">
                                <?php echo do_shortcode( '[live_campaign_dashboard]' ); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
