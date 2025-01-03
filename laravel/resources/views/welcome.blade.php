<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }

        #welcome-message {
            font-size: 2rem;
            color: #333;
        }
    </style>
</head>
<body>
    <div id="welcome-message">Scan your NFC card...</div>

    <script>
let eventSourceContinuous;

document.addEventListener('DOMContentLoaded', () => {
    if (eventSourceContinuous) {
        eventSourceContinuous.close();
    }

    eventSourceContinuous = new EventSource('/nfc/recupererDernierClient');

    eventSourceContinuous.onmessage = (event) => {
        const data = JSON.parse(event.data);

        if (data.name && data.surname) {
            document.getElementById('welcome-message').innerText = `Bienvenue ${data.name} ${data.surname}`;
        }
    };

    eventSourceContinuous.onerror = () => {
        console.error('Erreur SSE Continuous : tentative de reconnexion...');
        setTimeout(() => {
            eventSourceContinuous = new EventSource('/sse-continuous');
        }, 5000);
    };

    window.addEventListener('beforeunload', () => {
        if (eventSourceContinuous) {
            eventSourceContinuous.close();
        }
    });
});

</script>

</body>
</html>
