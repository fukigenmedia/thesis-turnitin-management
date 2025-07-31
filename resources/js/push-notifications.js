/**
 * Push Notification Manager
 * Handles browser push notifications for the Turnitin Management System
 */
class PushNotificationManager {
    constructor() {
        this.isSupported =
            "serviceWorker" in navigator && "PushManager" in window;
        this.isSubscribed = false;
        this.swRegistration = null;
        this.permission = Notification.permission;

        this.init();
    }

    /**
     * Initialize push notification manager
     */
    async init() {
        if (!this.isSupported) {
            console.warn(
                "Push notifications are not supported in this browser"
            );
            return;
        }

        try {
            // Register service worker
            this.swRegistration = await navigator.serviceWorker.register(
                "/sw.js"
            );
            console.log("Service Worker registered:", this.swRegistration);

            // Check if already subscribed
            const subscription =
                await this.swRegistration.pushManager.getSubscription();
            this.isSubscribed = !(subscription === null);

            // Listen for Livewire events
            this.setupLivewireListeners();

            // Setup periodic check for notifications
            this.setupPeriodicCheck();
        } catch (error) {
            console.error("Error initializing push notifications:", error);
        }
    }

    /**
     * Request permission for notifications
     */
    async requestPermission() {
        if (this.permission === "granted") {
            return true;
        }

        if (this.permission === "denied") {
            console.warn("Notification permission denied");
            return false;
        }

        try {
            const permission = await Notification.requestPermission();
            this.permission = permission;

            if (permission === "granted") {
                console.log("Notification permission granted");
                await this.subscribe();
                return true;
            } else {
                console.warn("Notification permission denied");
                return false;
            }
        } catch (error) {
            console.error("Error requesting notification permission:", error);
            return false;
        }
    }

    /**
     * Subscribe to push notifications
     */
    async subscribe() {
        if (!this.swRegistration) {
            console.error("Service Worker not registered");
            return false;
        }

        try {
            const subscription =
                await this.swRegistration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: this.urlBase64ToUint8Array(
                        this.getVapidPublicKey()
                    ),
                });

            console.log("Push subscription successful:", subscription);
            this.isSubscribed = true;

            // Send subscription to server (optional)
            await this.sendSubscriptionToServer(subscription);

            return true;
        } catch (error) {
            console.error("Error subscribing to push notifications:", error);
            return false;
        }
    }

    /**
     * Unsubscribe from push notifications
     */
    async unsubscribe() {
        if (!this.swRegistration) {
            return false;
        }

        try {
            const subscription =
                await this.swRegistration.pushManager.getSubscription();
            if (subscription) {
                await subscription.unsubscribe();
                this.isSubscribed = false;
                console.log("Push subscription cancelled");
                return true;
            }
        } catch (error) {
            console.error(
                "Error unsubscribing from push notifications:",
                error
            );
        }

        return false;
    }

    /**
     * Show local notification
     */
    showNotification(title, options = {}) {
        if (this.permission !== "granted") {
            console.warn("Cannot show notification: permission not granted");
            return;
        }

        const defaultOptions = {
            icon: "/images/notification-icon.png",
            badge: "/images/badge-icon.png",
            vibrate: [200, 100, 200],
            requireInteraction: true,
            actions: [
                { action: "view", title: "Lihat" },
                { action: "dismiss", title: "Tutup" },
            ],
        };

        const finalOptions = { ...defaultOptions, ...options };

        if (this.swRegistration) {
            // Use service worker to show notification
            this.swRegistration.showNotification(title, finalOptions);
        } else {
            // Fallback to regular notification
            new Notification(title, finalOptions);
        }
    }

    /**
     * Setup Livewire event listeners
     */
    setupLivewireListeners() {
        // Listen for notification events from Livewire
        document.addEventListener("livewire:load", () => {
            Livewire.on("show-push-notification", (data) => {
                this.handleNotificationEvent(data);
            });

            Livewire.on("notification-created", (data) => {
                this.handleNotificationEvent(data);
            });
        });

        // Listen for custom events
        window.addEventListener("turnitin-notification", (event) => {
            this.handleNotificationEvent(event.detail);
        });
    }

    /**
     * Handle notification event
     */
    handleNotificationEvent(data) {
        if (this.permission === "granted") {
            const { title, message, icon, url, actions } = data;

            this.showNotification(title, {
                body: message,
                icon: icon || "/images/notification-icon.png",
                data: { url },
                actions: actions || [
                    { action: "view", title: "Lihat" },
                    { action: "dismiss", title: "Tutup" },
                ],
            });
        }
    }

    /**
     * Setup periodic check for new notifications
     */
    setupPeriodicCheck() {
        // Check for new notifications every 2 minutes
        setInterval(async () => {
            if (document.hidden && this.permission === "granted") {
                await this.checkForNewNotifications();
            }
        }, 120000); // 2 minutes
    }

    /**
     * Check for new notifications (when tab is hidden)
     */
    async checkForNewNotifications() {
        try {
            const response = await fetch("/api/notifications/unread-count", {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    )?.content,
                },
            });

            if (response.ok) {
                const data = await response.json();
                if (data.hasNew) {
                    this.showNotification("Notifikasi Baru", {
                        body: `Anda memiliki ${data.count} notifikasi baru`,
                        data: { url: "/notifications" },
                    });
                }
            }
        } catch (error) {
            console.error("Error checking for new notifications:", error);
        }
    }

    /**
     * Send subscription to server
     */
    async sendSubscriptionToServer(subscription) {
        try {
            const response = await fetch("/api/push-subscription", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    )?.content,
                },
                body: JSON.stringify({
                    subscription: subscription,
                }),
            });

            if (response.ok) {
                console.log("Subscription sent to server successfully");
            }
        } catch (error) {
            console.error("Error sending subscription to server:", error);
        }
    }

    /**
     * Convert VAPID key
     */
    urlBase64ToUint8Array(base64String) {
        const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, "+")
            .replace(/_/g, "/");

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    /**
     * Get VAPID public key (you'll need to generate this)
     */
    getVapidPublicKey() {
        // This is a placeholder - you'll need to generate actual VAPID keys
        return "BEl62iUYgUivxIkv69yViEuiBIa40HI6YUsgVOrsKIRJdKeBVBENYdMOwJFCbqv2YZB2l3A6Zm5T9W2sF0i5YvQ";
    }

    /**
     * Get current subscription status
     */
    getStatus() {
        return {
            isSupported: this.isSupported,
            isSubscribed: this.isSubscribed,
            permission: this.permission,
        };
    }
}

// Export for global access
window.PushNotificationManager = PushNotificationManager;

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
    if (!window.pushNotificationManager) {
        window.pushNotificationManager = new PushNotificationManager();
    }
});

// Auto-request permission on first user interaction
document.addEventListener(
    "click",
    async () => {
        if (
            window.pushNotificationManager &&
            window.pushNotificationManager.permission === "default"
        ) {
            await window.pushNotificationManager.requestPermission();
        }
    },
    { once: true }
);
