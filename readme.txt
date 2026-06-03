=== Live Campaign Dashboard ===
Contributors: antigravity
Tags: analytics, dashboard, campaign, live stats, SaaS
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A premium SaaS-style live analytics dashboard widget with animated counters, floating cards, interactive charts, and glassmorphism effects.

== Description ==

**Live Campaign Dashboard** adds a stunning, interactive analytics section to any WordPress page using a simple shortcode. Designed for agencies, marketers, and SaaS websites that want to showcase campaign performance with premium, animated visuals.

= Key Features =

* **Live Animated Counters** — Revenue, ROAS, CTR, Views, and Conversion Rate with smooth count-up animations that continue incrementing in real time
* **Floating Glassmorphism Cards** — Premium floating cards with parallax mouse-follow effects, soft blur, and subtle rotation animations
* **Interactive Chart Bars** — Animated bar graph showing ad spend vs revenue trends with hover effects
* **Progress Bars** — Smooth animated progress fills with shimmer effects
* **Notification Popups** — Cycling live notification badges that create a sense of real-time activity
* **Dark & Light Themes** — Full theme support with CSS custom properties
* **Fully Responsive** — Perfect on mobile, tablet, laptop, and large desktop screens
* **Admin Settings Panel** — Complete control over metrics, colors, animations, floating intensity, and card management
* **Shortcode Support** — Works with Gutenberg, Elementor, Classic Editor, and any page builder
* **Multi-Instance** — Multiple shortcodes on the same page with zero conflicts
* **Performance Optimized** — Assets load only when shortcode is present, pure Vanilla JS, transform-based animations

= Shortcode Usage =

Basic: `[live_campaign_dashboard]`

Advanced:
`[live_campaign_dashboard theme="dark" revenue="5Cr" roas="6.2x" ctr="22%" floating="true" animation_speed="fast"]`

= Requirements =

* WordPress 5.8 or higher
* PHP 7.4 or higher

== Installation ==

1. Upload the `live-campaign-dashboard` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu
3. Go to **Campaign Dashboard** in the admin sidebar to configure settings
4. Add `[live_campaign_dashboard]` to any page or post

== Frequently Asked Questions ==

= Can I use multiple dashboards on one page? =
Yes! Each shortcode instance gets a unique ID to prevent JS/CSS conflicts.

= Does it work with Elementor? =
Yes, it works with Elementor, Gutenberg, Classic Editor, and any page builder that supports shortcodes.

= Is jQuery required? =
No. The plugin uses pure Vanilla JavaScript with zero jQuery dependency.

= Can I customize the colors? =
Yes, the admin panel includes color pickers for primary and accent colors.

== Screenshots ==

1. Dashboard frontend with light theme
2. Dashboard frontend with dark theme
3. Admin settings panel
4. Mobile responsive view

== Changelog ==

= 1.0.0 =
* Initial release
* Live animated counter system
* Floating glassmorphism cards with parallax
* Interactive bar chart with growth animation
* Progress bars with shimmer effects
* Notification popup cycling
* Full admin settings panel
* Light and dark theme support
* Fully responsive design
* REST API endpoints
* Multi-instance shortcode support
