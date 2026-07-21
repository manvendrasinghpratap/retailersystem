class SessionTimeout {

    constructor(options = {}) {

        this.enabled = options.enabled ?? true;

        if (!this.enabled) {
            return;
        }

        this.idleMinutes = parseInt(options.idleMinutes || 15);

        this.warningSeconds = parseInt(options.warningSeconds || 60);

        this.keepAliveUrl = options.keepAliveUrl;

        this.logoutUrl = options.logoutUrl;

        this.csrfToken = options.csrfToken;

        this.activityEvents = [
            'mousemove',
            'mousedown',
            'keydown',
            'touchstart',
            'scroll',
            'click'
        ];

        this.idleTimer = null;

        this.countdownTimer = null;

        this.popupOpen = false;

        this.remainingSeconds = this.warningSeconds;

        this.logoutSent = false;

        this.tabId = this.generateTabId();

        this.channel = null;

        this.channelName = 'retailer-session';

        this.storageKey = 'retailer-session-event';

        this.lastActivityKey = 'retailer-last-activity';

        this.initialize();

    }

    initialize() {

        this.initializeCommunication();

        this.bindActivityEvents();

        this.bindVisibilityEvents();

        this.saveActivity();

        this.resetIdleTimer();

    }

    generateTabId() {

        return Date.now() + "_" + Math.random().toString(36).substr(2,8);

    }
