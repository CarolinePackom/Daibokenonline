<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Bienvenue</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.clouds.min.js"></script>
    <style>
        * {
            margin: 0;
        }

        body {
            overflow: hidden;
            font-family: Nunito, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #1D3557;
            color: white;
        }

        #background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            rotate: 180deg;
        }

        .container {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            position: relative;
            text-align: center;
            z-index: 10;
        }

        .logo {
            margin-top: 3%;
            height: 50vh;
            width: 50vh;
        }

        #welcome-message {
            position: relative;
            color: white;
            margin-bottom: 9%;
            width: fit-content;
            height: 60px;
        }

        #welcome-message h1,
        #prenom {
            position: absolute;
            font-size: 70px;
            font-weight: 800;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            transition: transform 1s ease-in-out, opacity 0.5s ease-in-out;
            white-space: nowrap;
        }

        #welcome-message h1 {
            text-shadow: 0 0 10px black;
        }

        #prenom {
            background: linear-gradient(35deg, #cfa62f, #c89e2d, #cfa62f, #ffe14d, #cfa62f, #c89e2d);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }
    </style>
</head>
<body>
    <div id="background"></div>

    <div class="container">
        <img src="images/logo.png" alt="Logo de l'entreprise" class="logo">
        <div id="welcome-message">
            <h1>Bienvenue</h1>
            <h2 id="prenom"></h2>
        </div>
    </div>

    <script>
        VANTA.CLOUDS({
            el: "#background",
            mouseControls: false,
            touchControls: false,
            gyroControls: false,
            minHeight: 200.00,
            minWidth: 200.00,
            speed: 0.50,
            zoom: 0.01,
            backgroundColor: 0xffffff,
            skyColor: 0x0078af,
            cloudColor: 0x156A91,
            cloudShadowColor: 0x00476d,
            sunColor: 0xffed57,
            sunGlareColor: 0xecb731,
            sunlightColor: 0xfbee58
        })

        let eventSourceContinuous;

        document.addEventListener('DOMContentLoaded', () => {
            if (eventSourceContinuous) {
                eventSourceContinuous.close();
            }
            eventSourceContinuous = new EventSource('/nfc/dernier-client');

            eventSourceContinuous.onmessage = (event) => {
                const data = JSON.parse(event.data);

                if (data.prenom) {
                    const userNameElement = document.getElementById('prenom');
                    const welcomeMessageElement = document.querySelector('#welcome-message h1');

                    userNameElement.innerText = data.prenom;

                   const nameWidth = userNameElement.offsetWidth;
                        const welcomeWidth = welcomeMessageElement.offsetWidth;
                        const spaceBetween = 50;

                        const totalWidth = welcomeWidth + nameWidth + spaceBetween;
                        const moveDistance = totalWidth / 2;

                        const correctionRight = 35;

                        welcomeMessageElement.style.transform = `translateX(calc(-50% - ${moveDistance / 2 - correctionRight}px))`;
                        userNameElement.style.transform = `translateX(calc(-50% + ${moveDistance / 2 + correctionRight}px))`;

                        setTimeout(() => {
                            userNameElement.style.opacity = "1";

                            setTimeout(() => {
                                userNameElement.style.opacity = "0";
                                welcomeMessageElement.style.transform = "translateX(-50%)";

                                setTimeout(() => {
                                    userNameElement.innerText = "";
                                }, 500);
                            }, 5000);
                        }, 700);
                }
            };

            eventSourceContinuous.onerror = () => {
                console.error('Erreur SSE Continuous : tentative de reconnexion...');
                setTimeout(() => {
                    eventSourceContinuous = new EventSource('/nfc/dernier-client');
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
