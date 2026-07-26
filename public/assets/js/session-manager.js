(function () {

    class SessionManager {

        constructor(config) {

            // this.config = Object.assign({
            //     timeout: 15,          // In minutes
            //     warning: 60,          // In seconds (e.g. show warning at 60s remaining)
            //     keepAliveUrl: '/admin/keep-alive',
            //     logoutUrl: '/admin/logout',
            //     debug: false
            // }, config);

            this.config = config;
            this.ACTIVITY_KEY = 'session_last_activity';
            this.LOGOUT_KEY = 'session_logout';
            this.LEADER_KEY = 'session_leader';

            this.lastActivity = Date.now();
            this.timeout = this.config.timeout * 60 * 1000;
            this.warning = this.config.warning;

            this.LEADER_TIMEOUT = 5000;
            this.tabId = Date.now() + '_' + Math.random().toString(36).substring(2);

            this.isLeader = false;
            this.isWarningShown = false;
            this.isLoggedOut = false;
            this.lastKeepAlive = Date.now();
            this.warningTimer = null;
            this.warningShownAt = null;

            // Throttle activity updates (max once every 2 seconds)
            this.lastThrottledUpdate = 0;
            this.THROTTLE_INTERVAL = 2000;

            // Popup elements
            this.overlay = null;
            this.popup = null;

            this.injectStyles();
            this.createPopup();
            this.init();
        }

        init() {

            this.log('Session Manager Started');

            this.registerEvents();
            this.registerStorageListener();
            this.electLeader();

            window.addEventListener('beforeunload', () => {
                if (!this.isLeader) return;

                const leader = JSON.parse(localStorage.getItem(this.LEADER_KEY));
                if (leader && leader.id === this.tabId) {
                    localStorage.removeItem(this.LEADER_KEY);
                }
            });

            this.startMonitor();
        }

        log(...args) {
            if (this.config.debug) {
                console.log('[SessionManager]', ...args);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Inject Styles
        |--------------------------------------------------------------------------
        */

        injectStyles() {
            if (document.getElementById('session-mgr-styles')) return;

            const style = document.createElement('style');
            style.id = 'session-mgr-styles';
            style.textContent = `
                /* ── Overlay ── */
                .sm-overlay {
                    position: fixed;
                    inset: 0;
                    z-index: 99999;
                    background: rgba(15, 23, 42, 0.65);
                    backdrop-filter: blur(6px);
                    -webkit-backdrop-filter: blur(6px);
                    display: none;
                    align-items: center;
                    justify-content: center;
                    opacity: 0;
                    transition: opacity 0.35s ease;
                }
                .sm-overlay.sm-visible {
                    display: flex;
                    opacity: 1;
                }

                /* ── Popup Card ── */
                .sm-popup {
                    background: #ffffff;
                    border-radius: 20px;
                    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.28),
                                0 0 0 1px rgba(255, 255, 255, 0.08);
                    width: 420px;
                    max-width: 92vw;
                    padding: 40px 36px 32px;
                    text-align: center;
                    transform: scale(0.85) translateY(30px);
                    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1),
                                opacity 0.35s ease;
                    opacity: 0;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                }
                .sm-overlay.sm-visible .sm-popup {
                    transform: scale(1) translateY(0);
                    opacity: 1;
                }

                /* ── Circular Countdown ── */
                .sm-countdown-wrap {
                    position: relative;
                    width: 110px;
                    height: 110px;
                    margin: 0 auto 24px;
                }
                .sm-countdown-svg {
                    transform: rotate(-90deg);
                    width: 110px;
                    height: 110px;
                }
                .sm-countdown-track {
                    fill: none;
                    stroke: #e2e8f0;
                    stroke-width: 6;
                }
                .sm-countdown-bar {
                    fill: none;
                    stroke: url(#sm-gradient);
                    stroke-width: 6;
                    stroke-linecap: round;
                    stroke-dasharray: 283;
                    stroke-dashoffset: 0;
                    transition: stroke-dashoffset 1s linear, stroke 0.4s ease;
                }
                .sm-countdown-bar.sm-danger {
                    stroke: #ef4444;
                }
                .sm-countdown-value {
                    position: absolute;
                    inset: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 32px;
                    font-weight: 700;
                    color: #1e293b;
                    letter-spacing: -1px;
                }

                /* ── Icon pulse ── */
                .sm-icon-pulse {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 56px;
                    height: 56px;
                    border-radius: 50%;
                    background: linear-gradient(135deg, #fef3c7, #fde68a);
                    margin-bottom: 18px;
                    animation: sm-pulse 2s ease-in-out infinite;
                }
                @keyframes sm-pulse {
                    0%, 100% { box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.4); }
                    50% { box-shadow: 0 0 0 14px rgba(251, 191, 36, 0); }
                }
                .sm-icon-pulse svg {
                    width: 28px;
                    height: 28px;
                    color: #b45309;
                }

                /* ── Typography ── */
                .sm-title {
                    font-size: 22px;
                    font-weight: 700;
                    color: #1e293b;
                    margin: 0 0 8px;
                    line-height: 1.3;
                }
                .sm-subtitle {
                    font-size: 14px;
                    color: #64748b;
                    margin: 0 0 28px;
                    line-height: 1.6;
                }

                /* ── Buttons ── */
                .sm-btn-row {
                    display: flex;
                    gap: 12px;
                }
                .sm-btn {
                    flex: 1;
                    padding: 13px 20px;
                    border-radius: 12px;
                    font-size: 14px;
                    font-weight: 600;
                    border: none;
                    cursor: pointer;
                    transition: all 0.25s ease;
                    letter-spacing: 0.2px;
                    outline: none;
                }
                .sm-btn:active {
                    transform: scale(0.97);
                }
                .sm-btn-primary {
                    background: linear-gradient(135deg, #3b82f6, #2563eb);
                    color: #ffffff;
                    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
                }
                .sm-btn-primary:hover {
                    background: linear-gradient(135deg, #2563eb, #1d4ed8);
                    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45);
                }
                .sm-btn-danger {
                    background: #fef2f2;
                    color: #dc2626;
                    border: 1.5px solid #fecaca;
                }
                .sm-btn-danger:hover {
                    background: #fee2e2;
                    border-color: #fca5a5;
                }
            `;
            document.head.appendChild(style);
        }

        /*
        |--------------------------------------------------------------------------
        | Create Popup DOM
        |--------------------------------------------------------------------------
        */

        createPopup() {
            // Overlay
            this.overlay = document.createElement('div');
            this.overlay.className = 'sm-overlay';
            this.overlay.id = 'sm-idle-overlay';

            this.overlay.innerHTML = `
                <div class="sm-popup" id="sm-idle-popup">

                    <!-- Warning icon -->
                    <div class="sm-icon-pulse">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>

                    <!-- Title -->
                    <h2 class="sm-title">Session Expiring Soon</h2>
                    <p class="sm-subtitle">
                        You've been inactive for a while.<br>
                        Your session will expire in:
                    </p>

                    <!-- Circular countdown -->
                    <div class="sm-countdown-wrap">
                        <svg class="sm-countdown-svg" viewBox="0 0 100 100">
                            <defs>
                                <linearGradient id="sm-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#3b82f6"/>
                                    <stop offset="100%" stop-color="#8b5cf6"/>
                                </linearGradient>
                            </defs>
                            <circle class="sm-countdown-track" cx="50" cy="50" r="45"/>
                            <circle class="sm-countdown-bar" id="sm-countdown-bar" cx="50" cy="50" r="45"/>
                        </svg>
                        <div class="sm-countdown-value" id="sm-countdown-value">--</div>
                    </div>

                    <!-- Buttons -->
                    <div class="sm-btn-row">
                        <button class="sm-btn sm-btn-primary" id="sm-btn-stay">Stay Active</button>
                        <button class="sm-btn sm-btn-danger" id="sm-btn-logout">Logout</button>
                    </div>
                </div>
            `;

            document.body.appendChild(this.overlay);

            // Event listeners
            document.getElementById('sm-btn-stay').addEventListener('click', () => {
                this.dismissWarning();
                this.updateActivity();
                this.keepAlive(true);
            });

            document.getElementById('sm-btn-logout').addEventListener('click', () => {
                this.dismissWarning();
                this.logout();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Show / Hide Popup
        |--------------------------------------------------------------------------
        */

        showPopup() {
            if (!this.overlay) return;

            // Force reflow to restart CSS transition
            this.overlay.classList.remove('sm-visible');
            void this.overlay.offsetWidth;
            this.overlay.classList.add('sm-visible');
        }

        hidePopup() {
            if (!this.overlay) return;
            this.overlay.classList.remove('sm-visible');
        }

        /*
        |--------------------------------------------------------------------------
        | Storage Event Listener (Instant Multi-Tab Sync)
        |--------------------------------------------------------------------------
        */

        registerStorageListener() {
            window.addEventListener('storage', (e) => {
                // If another tab recorded activity, update local state & close popup
                if (e.key === this.ACTIVITY_KEY && e.newValue) {
                    this.checkActivity();
                }

                // If another tab initiated logout, instantly redirect this tab too
                if (e.key === this.LOGOUT_KEY && !this.isLoggedOut) {
                    this.isLoggedOut = true;
                    window.location.href = this.config.logoutUrl;
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Leader Election
        |--------------------------------------------------------------------------
        */

        electLeader() {
            const leader = JSON.parse(localStorage.getItem(this.LEADER_KEY));

            if (!leader || (Date.now() - leader.timestamp) > this.LEADER_TIMEOUT) {
                this.becomeLeader();
            }
        }

        becomeLeader() {
            this.isLeader = true;

            localStorage.setItem(this.LEADER_KEY, JSON.stringify({
                id: this.tabId,
                timestamp: Date.now()
            }));

            this.log("Leader Tab Established");
        }

        heartbeat() {
            if (!this.isLeader) return;

            localStorage.setItem(this.LEADER_KEY, JSON.stringify({
                id: this.tabId,
                timestamp: Date.now()
            }));
        }

        keepAlive(force = false) {
            if (!this.isLeader) return;

            let diff = Date.now() - this.lastKeepAlive;

            // Ping server every 5 minutes unless forced
            if (!force && diff < 300000) return;

            this.lastKeepAlive = Date.now();

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');

            fetch(this.config.keepAliveUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfMeta ? csrfMeta.content : '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    this.log('KeepAlive response:', data);
                })
                .catch(error => {
                    console.error('KeepAlive error:', error);
                });
        }

        checkLeader() {
            const leader = JSON.parse(localStorage.getItem(this.LEADER_KEY));

            if (!leader) {
                this.becomeLeader();
                return;
            }

            if (leader.id === this.tabId) {
                this.isLeader = true;
                return;
            }

            this.isLeader = false;

            if ((Date.now() - leader.timestamp) > this.LEADER_TIMEOUT) {
                this.becomeLeader();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | User Activity
        |--------------------------------------------------------------------------
        */

        registerEvents() {
            const events = [
                'mousemove',
                'mousedown',
                'keydown',
                'scroll',
                'touchstart',
                'click'
            ];

            events.forEach((event) => {
                document.addEventListener(event, () => {
                    this.updateActivityThrottled();
                }, true);
            });
        }

        updateActivityThrottled() {
            // When the warning popup is visible, ignore ALL activity events.
            // The user MUST click a popup button ("Stay Active" / "Logout") to proceed.
            if (this.isWarningShown) return;

            const now = Date.now();
            if (now - this.lastThrottledUpdate > this.THROTTLE_INTERVAL) {
                this.lastThrottledUpdate = now;
                this.updateActivity();
            }
        }

        updateActivity() {
            this.lastActivity = Date.now();
            localStorage.setItem(this.ACTIVITY_KEY, this.lastActivity);

            if (this.isWarningShown) {
                this.dismissWarning();
            }
        }

        checkActivity() {
            let activity = parseInt(localStorage.getItem(this.ACTIVITY_KEY) || 0);

            if (activity > this.lastActivity) {
                this.lastActivity = activity;

                if (this.isWarningShown) {
                    this.dismissWarning();
                }
            }
        }

        dismissWarning() {
            this.isWarningShown = false;

            if (this.warningTimer) {
                clearInterval(this.warningTimer);
                this.warningTimer = null;
            }

            // Hide custom popup
            this.hidePopup();

            // Also close SweetAlert if it's somehow open
            if (typeof Swal !== 'undefined' && Swal.isVisible()) {
                Swal.close();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Monitor
        |--------------------------------------------------------------------------
        */

        startMonitor() {
            setInterval(() => {
                this.checkActivity();
                this.checkLeader();
                this.heartbeat();
                this.keepAlive();
                this.checkIdle();
            }, 1000);
        }

        checkIdle() {
            let diff = Date.now() - this.lastActivity;
            let remaining = Math.floor((this.timeout - diff) / 1000);

            if (this.config.debug) {
                console.table({
                    Leader: this.isLeader,
                    IdleSeconds: Math.floor(diff / 1000),
                    RemainingSeconds: remaining,
                    LastKeepAlive: Math.floor((Date.now() - this.lastKeepAlive) / 1000) + " sec",
                    Tab: this.tabId
                });
            }

            // Show popup warning when remaining time enters the warning threshold
            if (
                remaining <= this.warning &&
                remaining > 0 &&
                !this.isWarningShown
            ) {
                this.showWarning();
            }

            // Trigger logout when time is up
            if (remaining <= 0 && !this.isLoggedOut) {
                this.logout();
            }
        }

        showWarning() {
            this.warningShownAt = this.lastActivity;

            if (this.isWarningShown || this.isLoggedOut) return;

            this.isWarningShown = true;

            const CIRCUMFERENCE = 2 * Math.PI * 45; // ~283
            const countdownBar = document.getElementById('sm-countdown-bar');
            const countdownValue = document.getElementById('sm-countdown-value');

            // Show the popup
            this.showPopup();

            // Update countdown every second
            this.warningTimer = setInterval(() => {

                let diff = Date.now() - this.lastActivity;
                let remaining = Math.max(0, Math.floor((this.timeout - diff) / 1000));

                // Update number
                if (countdownValue) {
                    countdownValue.textContent = remaining;
                }

                // Update circular progress
                if (countdownBar) {
                    let progress = remaining / this.warning;
                    let offset = CIRCUMFERENCE * (1 - progress);
                    countdownBar.style.strokeDashoffset = offset;

                    // Turn red when under 15 seconds
                    if (remaining <= 15) {
                        countdownBar.classList.add('sm-danger');
                    } else {
                        countdownBar.classList.remove('sm-danger');
                    }
                }

                // If user becomes active from another tab
                if (this.lastActivity > this.warningShownAt) {
                    this.dismissWarning();
                    return;
                }

                // Auto logout if timer hits 0 inside popup
                if (remaining <= 0) {
                    this.dismissWarning();
                    this.logout();
                }

            }, 1000);
        }

        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */

        logout() {

            console.log("Logout called");

            if (this.isLoggedOut) {
                return;
            }

            this.isLoggedOut = true;

            localStorage.setItem(this.LOGOUT_KEY, Date.now());

            // Submit the same logout form that the menu uses,
            // so Laravel handles the redirect to the main website page.
            const logoutForm = document.getElementById('logout-form');

            if (logoutForm) {
                logoutForm.submit();
                return;
            }

            // Fallback: if the form doesn't exist in the DOM, use fetch
            fetch(this.config.logoutUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
                .then(() => {
                    window.location.href = '/';
                })
                .catch(error => {
                    console.error(error);
                    window.location.href = '/';
                });

        }

    }

    window.SessionManager = SessionManager;

})();