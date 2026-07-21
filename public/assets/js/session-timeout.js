class SessionTimeout {

    constructor(options = {}) {

        this.enabled = options.enabled ?? true;

        if (!this.enabled) {
            return;
        }

        this.idleMinutes = options.idleMinutes || 15;

        this.warningSeconds = options.warningSeconds || 60;

        this.keepAliveUrl = options.keepAliveUrl;

        this.logoutUrl = options.logoutUrl;

        this.csrfToken = options.csrfToken;

        this.idleTimer = null;

        this.countdownTimer = null;

        this.popupOpen = false;

        this.remainingSeconds = this.warningSeconds;

        this.activityEvents = [
            'mousemove',
            'mousedown',
            'keydown',
            'click',
            'scroll',
            'touchstart'
        ];

        this.initialize();

    }

    initialize() {

        this.bindActivityEvents();

        this.resetIdleTimer();

    }

    bindActivityEvents() {

        this.activityEvents.forEach(event => {

            window.addEventListener(event, () => {

                this.userActivity();

            }, true);

        });

    }

    userActivity() {

        if (this.popupOpen) {
            return;
        }

        this.resetIdleTimer();

    }

    resetIdleTimer() {

        clearTimeout(this.idleTimer);

        this.idleTimer = setTimeout(() => {

            this.showWarningPopup();

        }, (this.idleMinutes * 60 * 1000) - (this.warningSeconds * 1000));

    }

    showWarningPopup() {

        if (this.popupOpen) {
            return;
        }

        this.popupOpen = true;

        this.remainingSeconds = this.warningSeconds;

        Swal.fire({

            title: 'Session Expiring',

            html:
                `
                <p>You have been inactive.</p>

                <p>You will be logged out in</p>

                <h2 id="session-countdown">${this.remainingSeconds}</h2>

                <p>seconds.</p>
                `,

            icon: 'warning',

            allowOutsideClick: false,

            allowEscapeKey: false,

            showCancelButton: true,

            confirmButtonText: 'Stay Logged In',

            cancelButtonText: 'Logout Now',

            didOpen: () => {

                this.startCountdown();

            }

        }).then((result) => {

            if (result.isConfirmed) {

                this.keepAlive();

            } else {

                this.logout();

            }

        });

    }

    startCountdown() {

        this.countdownTimer = setInterval(() => {

            this.remainingSeconds--;

            const counter = document.getElementById('session-countdown');

            if (counter) {

                counter.innerHTML = this.remainingSeconds;

            }

            if (this.remainingSeconds <= 0) {

                clearInterval(this.countdownTimer);

                Swal.close();

                this.logout();

            }

        }, 1000);

    }

    keepAlive() {

        clearInterval(this.countdownTimer);

        fetch(this.keepAliveUrl, {

            method: 'POST',

            headers: {

                'Content-Type': 'application/json',

                'X-CSRF-TOKEN': this.csrfToken,

                'Accept': 'application/json'

            }

        })

        .then(response => response.json())

        .then(() => {

            this.popupOpen = false;

            this.resetIdleTimer();

        })

        .catch(() => {

            this.logout();

        });

    }

    logout() {

        clearInterval(this.countdownTimer);

        fetch(this.logoutUrl, {

            method: 'POST',

            headers: {

                'Content-Type': 'application/json',

                'X-CSRF-TOKEN': this.csrfToken,

                'Accept': 'application/json'

            }

        })

        .then(response => response.json())

        .then(data => {

            window.location.href = data.redirect;

        })

        .catch(() => {

            window.location.reload();

        });

    }

}