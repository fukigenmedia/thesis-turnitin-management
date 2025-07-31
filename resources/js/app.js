// Import push notification functionality
import "./push-notifications.js";

// Register service worker for push notifications
if ("serviceWorker" in navigator && "PushManager" in window) {
    window.addEventListener("load", () => {
        navigator.serviceWorker
            .register("/sw.js")
            .then((registration) => {
                console.log("ServiceWorker registration successful");

                // Initialize push notification manager
                if (window.PushNotificationManager) {
                    window.pushNotificationManager =
                        new window.PushNotificationManager();
                }
            })
            .catch((error) => {
                console.log("ServiceWorker registration failed: ", error);
            });
    });
}
