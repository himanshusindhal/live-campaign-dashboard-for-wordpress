/**
 * Live Campaign Dashboard — Frontend JavaScript
 *
 * Handles all interactive behaviors:
 * - Smooth count-up animations
 * - Live counter increments
 * - Floating card parallax on mouse movement
 * - Chart bar growth animations
 * - Progress bar fill on scroll
 * - Notification popup cycling
 * - Intersection Observer for scroll triggers
 * - Mouse-follow tilt effects on metric cards
 *
 * Zero jQuery dependency — pure Vanilla JS.
 *
 * @package LiveCampaignDashboard
 */

(function () {
    'use strict';

    /* ============================================================
       Configuration
       ============================================================ */
    const config = {
        liveAnimation: typeof lcdSettings !== 'undefined' ? lcdSettings.liveAnimation === '1' : true,
        animSpeed: typeof lcdSettings !== 'undefined' ? lcdSettings.animSpeed : 'medium',
        primaryColor: typeof lcdSettings !== 'undefined' ? lcdSettings.primaryColor : '#2563eb',
        accentColor: typeof lcdSettings !== 'undefined' ? lcdSettings.accentColor : '#10b981',
        floatingIntensity: typeof lcdSettings !== 'undefined' ? lcdSettings.floatingIntensity : 'medium',
    };

    const speedMap = {
        slow: { counterDuration: 3000, incrementInterval: 6000, notifInterval: 12000 },
        medium: { counterDuration: 2000, incrementInterval: 4000, notifInterval: 8000 },
        fast: { counterDuration: 1200, incrementInterval: 2000, notifInterval: 5000 },
    };

    const timing = speedMap[config.animSpeed] || speedMap.medium;

    /* ============================================================
       Utility — Parse metric string to numeric components
       ============================================================ */
    function parseMetricValue(str) {
        if (!str || typeof str !== 'string') return { num: 0, suffix: '', prefix: '' };
        str = str.trim();

        let prefix = '';
        let suffix = '';
        let numStr = '';

        // Extract leading non-numeric chars (prefix like ₹)
        const prefixMatch = str.match(/^([^\d.+-]*)/);
        if (prefixMatch && prefixMatch[1]) {
            prefix = prefixMatch[1];
            str = str.slice(prefix.length);
        }

        // Extract numeric part
        const numMatch = str.match(/^([+-]?\d*\.?\d+)/);
        if (numMatch) {
            numStr = numMatch[1];
            suffix = str.slice(numStr.length);
        } else {
            // No numeric part found — treat whole thing as label
            return { num: 0, suffix: str, prefix: prefix };
        }

        return {
            num: parseFloat(numStr),
            suffix: suffix,
            prefix: prefix,
            decimals: numStr.includes('.') ? numStr.split('.')[1].length : 0,
        };
    }

    function formatMetricValue(parsed, value) {
        const formatted = parsed.decimals > 0 ? value.toFixed(parsed.decimals) : Math.round(value).toString();
        return parsed.prefix + formatted + parsed.suffix;
    }

    /* ============================================================
       Count-up Animation (requestAnimationFrame)
       ============================================================ */
    function animateCountUp(element, targetStr, duration) {
        const parsed = parseMetricValue(targetStr);
        if (parsed.num === 0 && parsed.suffix) {
            element.textContent = targetStr;
            return;
        }

        const startTime = performance.now();
        const startValue = 0;
        const endValue = parsed.num;

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Ease-out cubic
            const eased = 1 - Math.pow(1 - progress, 3);
            const currentValue = startValue + (endValue - startValue) * eased;

            element.textContent = formatMetricValue(parsed, currentValue);

            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }

        requestAnimationFrame(update);
    }

    /* ============================================================
       Live Increment — subtle value bumps
       ============================================================ */
    function startLiveIncrements(wrapper) {
        if (!config.liveAnimation) return;

        const counters = wrapper.querySelectorAll('.lcd-counter');

        setInterval(() => {
            counters.forEach((counter) => {
                const currentText = counter.textContent;
                const parsed = parseMetricValue(currentText);

                if (parsed.num === 0) return;

                // Tiny random increment (0.01 – 0.05 relative to value scale)
                const magnitude = parsed.num > 100 ? 0.01 : parsed.num > 10 ? 0.1 : 0.01;
                const increment = (Math.random() * magnitude * 5 + magnitude).toFixed(parsed.decimals > 0 ? parsed.decimals : 2);
                const newValue = parsed.num + parseFloat(increment);

                // Smooth micro-animation for the bump
                counter.style.transition = 'transform 0.3s ease';
                counter.style.transform = 'scale(1.04)';

                setTimeout(() => {
                    counter.textContent = formatMetricValue(parsed, newValue);
                    counter.style.transform = 'scale(1)';
                }, 150);
            });
        }, timing.incrementInterval);
    }

    /* ============================================================
       Chart Bar Animation
       ============================================================ */
    function animateChartBars(wrapper) {
        const bars = wrapper.querySelectorAll('.lcd-bar');
        bars.forEach((bar, index) => {
            setTimeout(() => {
                bar.classList.add('lcd-animated');
            }, index * 80);
        });
    }

    /* ============================================================
       Progress Bar Fill Animation
       ============================================================ */
    function animateProgressBars(wrapper) {
        const fills = wrapper.querySelectorAll('.lcd-progress-fill');
        fills.forEach((fill, index) => {
            setTimeout(() => {
                fill.classList.add('lcd-animated');
            }, index * 200 + 600);
        });
    }

    /* ============================================================
       Mouse Parallax for Floating Cards
       ============================================================ */
    function initParallax(wrapper) {
        const floatingCards = wrapper.querySelectorAll('.lcd-floating-card');
        if (floatingCards.length === 0) return;

        // Only on non-touch devices
        if ('ontouchstart' in window) return;

        wrapper.addEventListener('mousemove', (e) => {
            const rect = wrapper.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;

            floatingCards.forEach((card, i) => {
                const factor = i === 0 ? 12 : 8;
                const tx = x * factor;
                const ty = y * factor;
                card.style.setProperty('--parallax-x', `${tx}px`);
                card.style.setProperty('--parallax-y', `${ty}px`);
                // Apply as additional transform without breaking the float animation
                const inner = card.querySelector('.lcd-floating-card-inner');
                if (inner) {
                    inner.style.transform = `translate(${tx}px, ${ty}px)`;
                }
            });
        });

        wrapper.addEventListener('mouseleave', () => {
            floatingCards.forEach((card) => {
                const inner = card.querySelector('.lcd-floating-card-inner');
                if (inner) {
                    inner.style.transition = 'transform 0.5s ease';
                    inner.style.transform = 'translate(0, 0)';
                    setTimeout(() => {
                        inner.style.transition = '';
                    }, 500);
                }
            });
        });
    }

    /* ============================================================
       Mouse Tilt Effect on Metric Cards
       ============================================================ */
    function initCardTilt(wrapper) {
        const cards = wrapper.querySelectorAll('.lcd-metric-card');
        if ('ontouchstart' in window) return;

        cards.forEach((card) => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width;
                const y = (e.clientY - rect.top) / rect.height;
                const tiltX = (y - 0.5) * -6;
                const tiltY = (x - 0.5) * 6;
                card.style.transform = `perspective(600px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) translateY(-4px) scale(1.03)`;
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
            });
        });
    }

    /* ============================================================
       Notification Popup Cycling
       ============================================================ */
    function initNotificationPopups(wrapper) {
        if (!config.liveAnimation) return;

        const popup = wrapper.querySelector('.lcd-notification-popup');
        if (!popup) return;

        const messages = [
            'New conversion recorded!',
            'Campaign ROI increased +2.3%',
            'New lead captured from Google Ads',
            '₹12.4L revenue this hour',
            'CTR improved by 0.8%',
            'Meta Ads campaign optimized',
            'New subscriber from landing page',
            '1,247 views in last 30 min',
        ];

        let msgIndex = 0;

        function showNotification() {
            const textEl = popup.querySelector('.lcd-notif-text');
            if (textEl) {
                textEl.textContent = messages[msgIndex % messages.length];
            }
            popup.classList.add('lcd-notif-visible');

            setTimeout(() => {
                popup.classList.remove('lcd-notif-visible');
                msgIndex++;
            }, 3500);
        }

        // First notification after a delay
        setTimeout(showNotification, 3000);

        // Then cycle
        setInterval(showNotification, timing.notifInterval);
    }

    /* ============================================================
       Intersection Observer — Trigger Animations on Scroll
       ============================================================ */
    function initScrollObserver(wrapper) {
        if (!('IntersectionObserver' in window)) {
            // Fallback: animate immediately
            triggerAllAnimations(wrapper);
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        triggerAllAnimations(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.15 }
        );

        observer.observe(wrapper);
    }

    /* ============================================================
       Trigger All Animations
       ============================================================ */
    function triggerAllAnimations(wrapper) {
        // Count-up counters
        const counters = wrapper.querySelectorAll('.lcd-counter');
        counters.forEach((counter) => {
            const target = counter.getAttribute('data-target') || counter.textContent;
            animateCountUp(counter, target, timing.counterDuration);
        });

        // Chart bars
        setTimeout(() => animateChartBars(wrapper), 300);

        // Progress bars
        animateProgressBars(wrapper);

        // Start live increments
        setTimeout(() => startLiveIncrements(wrapper), timing.counterDuration + 500);

        // Fade-in elements
        wrapper.querySelectorAll('.lcd-fade-in').forEach((el, i) => {
            setTimeout(() => el.classList.add('lcd-visible'), i * 150);
        });

        // Notification popups
        initNotificationPopups(wrapper);
    }

    /* ============================================================
       Apply Custom Colors via CSS Custom Properties
       ============================================================ */
    function applyCustomColors(wrapper) {
        if (config.primaryColor) {
            wrapper.style.setProperty('--lcd-primary', config.primaryColor);

            // Compute lighter variant
            const hex = config.primaryColor.replace('#', '');
            const r = parseInt(hex.substr(0, 2), 16);
            const g = parseInt(hex.substr(2, 2), 16);
            const b = parseInt(hex.substr(4, 2), 16);
            const lighter = `rgb(${Math.min(r + 30, 255)}, ${Math.min(g + 30, 255)}, ${Math.min(b + 30, 255)})`;
            wrapper.style.setProperty('--lcd-primary-light', lighter);
            wrapper.style.setProperty('--lcd-primary-ultra-light', `rgba(${r}, ${g}, ${b}, 0.08)`);
        }

        if (config.accentColor) {
            wrapper.style.setProperty('--lcd-accent', config.accentColor);
            const hex = config.accentColor.replace('#', '');
            const r = parseInt(hex.substr(0, 2), 16);
            const g = parseInt(hex.substr(2, 2), 16);
            const b = parseInt(hex.substr(4, 2), 16);
            const lighter = `rgb(${Math.min(r + 30, 255)}, ${Math.min(g + 30, 255)}, ${Math.min(b + 30, 255)})`;
            wrapper.style.setProperty('--lcd-accent-light', lighter);
            wrapper.style.setProperty('--lcd-accent-ultra-light', `rgba(${r}, ${g}, ${b}, 0.08)`);
        }

        // Floating intensity data attribute
        if (config.floatingIntensity) {
            wrapper.setAttribute('data-float-intensity', config.floatingIntensity);
        }
    }

    /* ============================================================
       Initialize a Single Dashboard Instance
       ============================================================ */
    function initDashboardInstance(wrapper) {
        // Apply colors
        applyCustomColors(wrapper);

        // Init parallax on floating cards
        initParallax(wrapper);

        // Init tilt on metric cards
        initCardTilt(wrapper);

        // Set up scroll observer to trigger animations
        initScrollObserver(wrapper);
    }

    /* ============================================================
       DOM Ready — Initialize All Instances
       ============================================================ */
    function init() {
        const wrappers = document.querySelectorAll('.lcd-wrapper');
        wrappers.forEach((wrapper) => {
            initDashboardInstance(wrapper);
        });
    }

    // Boot
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose for admin preview re-init
    window.LCDInit = init;
    window.LCDInitInstance = initDashboardInstance;
})();
