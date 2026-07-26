(function () {

    class SessionManager {

        constructor(config) {

            this.config = config;

            this.ACTIVITY_KEY = 'session_last_activity';
            this.LOGOUT_KEY = 'session_logout';
            this.LEADER_KEY = 'session_leader';

            this.lastActivity = Date.now();

            this.timeout = config.timeout * 60 * 1000;
            this.warning = config.warning;

            this.LEADER_TIMEOUT = 5000;

            this.tabId = Date.now() + '_' + Math.random().toString(36).substring(2);

            this.isLeader = false;
            this.isWarningShown = false;
            this.isLoggedOut = false;
            this.lastKeepAlive = Date.now();
            this.warningTimer = null;

            this.warningShownAt = null;
            this.init();
        }

        init() {

            console.log('Session Manager Started');

            this.registerEvents();

            this.electLeader();

            window.addEventListener('beforeunload', () => {

                if (!this.isLeader) {
                    return;
                }

                const leader = JSON.parse(localStorage.getItem(this.LEADER_KEY));

                if (leader && leader.id === this.tabId) {
                    localStorage.removeItem(this.LEADER_KEY);
                }

            });

            this.startMonitor();

        }

        /*
        |--------------------------------------------------------------------------
        | Leader Election
        |--------------------------------------------------------------------------
        */

        electLeader() {

            const leader = JSON.parse(localStorage.getItem(this.LEADER_KEY));

            if (!leader) {
                this.becomeLeader();
                return;
            }

            if ((Date.now() - leader.timestamp) > this.LEADER_TIMEOUT) {
                this.becomeLeader();
            }

        }

        becomeLeader() {

            this.isLeader = true;

            localStorage.setItem(this.LEADER_KEY, JSON.stringify({
                id: this.tabId,
                timestamp: Date.now()
            }));

            console.log("Leader Tab");

        }

        heartbeat() {

            if (!this.isLeader) {
                return;
            }

            localStorage.setItem(this.LEADER_KEY, JSON.stringify({
                id: this.tabId,
                timestamp: Date.now()
            }));

        }
        keepAlive(force = false) {

            if (!this.isLeader) {
                return;
            }

            let diff = Date.now() - this.lastKeepAlive;

            // Every 5 minutes
            if (!force && diff < 300000) {
                return;
            }

            this.lastKeepAlive = Date.now();

            fetch(this.config.keepAliveUrl, {

                method: 'POST',

                headers: {

                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,

                    'Accept': 'application/json',

                    'Content-Type': 'application/json'

                }

            })
                .then(response => response.json())
                .then(data => {

                    console.log('KeepAlive', data.time);

                })
                .catch(error => {

                    console.error(error);

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

            const self = this;

            const events = [
                'mousemove',
                'mousedown',
                'keydown',
                'scroll',
                'touchstart',
                'click'
            ];

            events.forEach(function (event) {

                document.addEventListener(event, function () {

                    self.updateActivity();

                }, true);

            });

        }

        updateActivity() {

            this.lastActivity = Date.now();

            localStorage.setItem(this.ACTIVITY_KEY, this.lastActivity);

            if (this.isWarningShown) {

                this.isWarningShown = false;

                if (this.warningTimer) {

                    clearInterval(this.warningTimer);

                    this.warningTimer = null;

                }

                Swal.close();

            }

        }

        checkActivity() {

            let activity = parseInt(localStorage.getItem(this.ACTIVITY_KEY) || 0);

            if (activity > this.lastActivity) {

                this.lastActivity = activity;

                if (this.isWarningShown) {

                    this.isWarningShown = false;

                    if (this.warningTimer) {

                        clearInterval(this.warningTimer);

                        this.warningTimer = null;

                    }

                    Swal.close();

                }

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Monitor
        |--------------------------------------------------------------------------
        */

        startMonitor() {

            const self = this;

            setInterval(function () {

                self.checkActivity();

                self.checkLeader();

                self.heartbeat();
                self.keepAlive();
                self.checkIdle();

            }, 1000);

        }

        checkIdle() {

            let diff = Date.now() - this.lastActivity;

            let remaining = Math.floor((this.timeout - diff) / 1000);

            console.clear();

            console.table({

                Leader: this.isLeader,

                Idle: Math.floor(diff / 1000),

                Remaining: remaining,

                LastKeepAlive: Math.floor((Date.now() - this.lastKeepAlive) / 1000) + " sec",

                Tab: this.tabId

            });

            // Warning before logout

            if (
                remaining <= this.warning &&
                remaining > 0 &&
                !this.isWarningShown &&
                !Swal.isVisible()
            ) {

                this.showWarning();

            }

            if (remaining <= 0 && !this.isLoggedOut) {

                this.logout();

            }

        }

        showWarning() {
            this.warningShownAt = this.lastActivity;
            // Already showing
            if (this.isWarningShown || this.isLoggedOut) {
                return;
            }

            this.isWarningShown = true;

            let self = this;

            Swal.fire({
                title: 'Session Expiring',
                html: `
            <div style="font-size:42px;font-weight:bold">
                <span id="session-countdown"></span>
            </div>
            <br>
            You have been inactive.<br>
            Click <b>Stay Logged In</b> to continue.
        `,
                icon: 'warning',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showCancelButton: true,
                confirmButtonText: 'Stay Logged In',
                cancelButtonText: 'Logout'
            }).then(function (result) {

                clearInterval(self.warningTimer);

                self.warningTimer = null;

                self.isWarningShown = false;

                // Stay Logged In
                if (result.isConfirmed) {

                    self.updateActivity();

                    self.keepAlive(true);

                    Swal.close();

                    return;
                }

                // Logout button
                self.logout();

            });

            // Update countdown every second
            this.warningTimer = setInterval(function () {

                let diff = Date.now() - self.lastActivity;

                let remaining = Math.max(
                    0,
                    Math.floor((self.timeout - diff) / 1000)
                );

                const el = document.getElementById('session-countdown');

                if (el) {
                    el.innerHTML = remaining;
                }

                // User became active from another tab
                if (self.lastActivity > self.warningShownAt) {

                    clearInterval(self.warningTimer);

                    self.warningTimer = null;

                    self.isWarningShown = false;

                    Swal.close();

                    return;

                }

                if (remaining <= 0) {

                    clearInterval(self.warningTimer);

                    self.warningTimer = null;

                    Swal.close();

                    self.logout();

                }

            }, 1000);

        }
        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */

        logout() {

            if (this.isLoggedOut) {
                return;
            }

            this.isLoggedOut = true;

            localStorage.setItem(this.LOGOUT_KEY, Date.now());

            window.location.href = this.config.logoutUrl;

        }

    }

    window.SessionManager = SessionManager;

})();