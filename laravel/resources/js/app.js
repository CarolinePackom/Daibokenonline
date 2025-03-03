import './bootstrap';

import { registerSW } from 'virtual:pwa-register';

// Enregistrement du Service Worker
registerSW({
    onNeedRefresh() {
        console.log('Nouvelle version disponible. Veuillez actualiser.');
    },
    onOfflineReady() {
        console.log('L’application est prête à fonctionner hors ligne.');
    },
});

// Ajout dynamique des balises <meta> et <link> pour la PWA
function addPwaMetaTags() {
    const head = document.head;

    const metaTags = [
        { name: 'mobile-web-app-capable', content: 'yes' },
        { name: 'apple-mobile-web-app-capable', content: 'yes' },
        { name: 'application-name', content: 'MonApplication' },
        { name: 'apple-mobile-web-app-title', content: 'MonApplication' },
        { name: 'theme-color', content: '#000000' },
        { name: 'msapplication-navbutton-color', content: '#000000' },
        { name: 'apple-mobile-web-app-status-bar-style', content: 'black-translucent' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1, shrink-to-fit=no' },
    ];

    metaTags.forEach(({ name, content }) => {
        let meta = document.createElement('meta');
        meta.name = name;
        meta.content = content;
        head.appendChild(meta);
    });

    // Ajout du lien vers le manifest.json
    let manifest = document.createElement('link');
    manifest.rel = 'manifest';
    manifest.href = '/manifest.webmanifest';
    head.appendChild(manifest);
}

// Exécuter la fonction une fois que le DOM est chargé
document.addEventListener('DOMContentLoaded', addPwaMetaTags);
