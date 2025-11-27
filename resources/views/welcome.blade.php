<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live GPS Tracking - Laravel Reverb</title>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

        #header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        #header h1 {
            font-size: 24px;
        }

        #status {
            margin-top: 10px;
            font-size: 14px;
            padding: 5px 15px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            display: inline-block;
        }

        #status.connected {
            background: rgba(76, 175, 80, 0.8);
        }

        #status.disconnected {
            background: rgba(244, 67, 54, 0.8);
        }

        #map {
            height: calc(100vh - 100px);
            width: 100%;
        }

        .leaflet-popup-content {
            font-size: 14px;
            min-width: 150px;
        }

        .user-info {
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }

        .location-info {
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <div id="header">
        <h1>🗺️ Live GPS Tracking Dashboard</h1>
        <div id="status" class="disconnected">⚫ Connecting...</div>
    </div>

    <div id="map"></div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Pusher JS -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <!-- Laravel Echo -->
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <script>
        // Initialize Map
        const map = L.map('map').setView([23.8103, 90.4125], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        // Store user markers and colors
        const userMarkers = {};
        const userColors = {};

        // Random color generator
        function getColorForUser(userId) {
            if (!userColors[userId]) {
                const colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', '#F7DC6F', '#BB8FCE'];
                userColors[userId] = colors[userId % colors.length];
            }
            return userColors[userId];
        }

        // Custom marker icon
        function createCustomIcon(color) {
            return L.divIcon({
                className: 'custom-marker',
                html: `<div style="
                    background-color: ${color};
                    width: 30px;
                    height: 30px;
                    border-radius: 50% 50% 50% 0;
                    border: 3px solid white;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
                    transform: rotate(-45deg);
                "></div>`,
                iconSize: [30, 30],
                iconAnchor: [15, 30]
            });
        }

        const statusDiv = document.getElementById('status');

        // Configure Echo
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ env('REVERB_APP_KEY') }}',
            wsHost: '{{ env('REVERB_HOST') }}',
            wsPort: {{ env('REVERB_PORT') }},
            wssPort: {{ env('REVERB_PORT') }},
            forceTLS: ('{{ env('REVERB_SCHEME') }}' === 'https'),
            enabledTransports: ['ws', 'wss'],
        });

        // Listen for location updates
        window.Echo.channel('locations')
            .listen('.location.updated', (event) => {
                const lat = parseFloat(event.location.latitude);
                const lng = parseFloat(event.location.longitude);
                const userId = event.userId;
                const color = getColorForUser(userId);

                // Update or create marker
                if (userMarkers[userId]) {
                    userMarkers[userId].setLatLng([lat, lng]);
                    userMarkers[userId].setPopupContent(`
        <div class="user-info">👤 User ${userId}</div>
        <div class="location-info">
            📍 ${lat.toFixed(6)}, ${lng.toFixed(6)}<br>
            🕐 ${new Date().toLocaleTimeString()}
        </div>
    `);
                    // Auto-open popup
                    userMarkers[userId].openPopup();
                } else {
                    const marker = L.marker([lat, lng], {
                            icon: createCustomIcon(color)
                        })
                        .addTo(map)
                        .bindPopup(`
            <div class="user-info">👤 User ${userId}</div>
            <div class="location-info">
                📍 ${lat.toFixed(6)}, ${lng.toFixed(6)}<br>
                🕐 ${new Date().toLocaleTimeString()}
            </div>
        `)
                        .openPopup(); // Auto-open on creation

                    userMarkers[userId] = marker;
                }


                // Fit map bounds to show all users
                const allLatLngs = Object.values(userMarkers).map(m => m.getLatLng());
                if (allLatLngs.length > 0) {
                    map.fitBounds(L.latLngBounds(allLatLngs), {
                        padding: [50, 50]
                    });
                }
            });

        // WebSocket connection status
        window.Echo.connector.pusher.connection.bind('connected', () => {
            statusDiv.textContent = '🟢 Connected to Websocket';
            statusDiv.className = 'connected';
        });
        window.Echo.connector.pusher.connection.bind('disconnected', () => {
            statusDiv.textContent = '🔴 Disconnected';
            statusDiv.className = 'disconnected';
        });
        window.Echo.connector.pusher.connection.bind('error', (err) => {
            console.error('Websocket Error:', err);
            statusDiv.textContent = '⚠️ Connection Error';
            statusDiv.className = 'disconnected';
        });
    </script>
</body>

</html>
