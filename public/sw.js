// Service Worker for Push Notifications
const CACHE_NAME = "turnitin-notifications-v1";

// Install event
self.addEventListener("install", (event) => {
    console.log("Service Worker installing...");
    self.skipWaiting();
});

// Activate event
self.addEventListener("activate", (event) => {
    console.log("Service Worker activating...");
    event.waitUntil(clients.claim());
});

// Push event - handle incoming push notifications
self.addEventListener("push", (event) => {
    console.log("Push received:", event);

    if (!event.data) {
        return;
    }

    let data;
    try {
        data = event.data.json();
    } catch (e) {
        data = {
            title: "Notifikasi Baru",
            message: event.data.text() || "Anda memiliki notifikasi baru",
            icon: "/images/notification-icon.png",
        };
    }

    const options = {
        body: data.message,
        icon: data.icon || "/images/notification-icon.png",
        badge: "/images/badge-icon.png",
        data: {
            url: data.url || "/",
            actions: data.actions || [],
        },
        actions: data.actions || [
            {
                action: "view",
                title: "Lihat",
            },
            {
                action: "dismiss",
                title: "Tutup",
            },
        ],
        requireInteraction: true,
        vibrate: [200, 100, 200],
        tag: "turnitin-notification",
    };

    event.waitUntil(self.registration.showNotification(data.title, options));
});

// Notification click event
self.addEventListener("notificationclick", (event) => {
    console.log("Notification clicked:", event);

    event.notification.close();

    if (event.action === "dismiss") {
        return;
    }

    const url = event.notification.data?.url || "/";

    event.waitUntil(
        clients.matchAll({ type: "window" }).then((clientList) => {
            // Check if there's already a window/tab open with the target URL
            for (const client of clientList) {
                if (client.url === url && "focus" in client) {
                    return client.focus();
                }
            }

            // If no existing window/tab, open a new one
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});

// Background sync (optional - for offline functionality)
self.addEventListener("sync", (event) => {
    console.log("Background sync:", event.tag);

    if (event.tag === "notification-sync") {
        event.waitUntil(
            // Handle offline notifications sync
            console.log("Syncing notifications...")
        );
    }
});

// Message event - handle messages from main thread
self.addEventListener("message", (event) => {
    console.log("Service Worker received message:", event.data);

    if (event.data && event.data.type === "SHOW_NOTIFICATION") {
        const { title, message, icon, url, actions } = event.data.payload;

        const options = {
            body: message,
            icon: icon || "/images/notification-icon.png",
            badge: "/images/badge-icon.png",
            data: { url, actions },
            actions: actions || [
                { action: "view", title: "Lihat" },
                { action: "dismiss", title: "Tutup" },
            ],
            requireInteraction: true,
            vibrate: [200, 100, 200],
            tag: "turnitin-notification",
        };

        self.registration.showNotification(title, options);
    }
});
