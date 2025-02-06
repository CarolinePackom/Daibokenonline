# Utilisation d'une image légère pour Java
FROM openjdk:17-slim

# Installer les dépendances nécessaires pour les lecteurs NFC
RUN apt-get update && \
    apt-get install -y pcscd pcsc-tools libpcsclite1 usbutils tini && \
    rm -rf /var/lib/apt/lists/*

# Utiliser tini pour gérer correctement les processus
ENTRYPOINT ["/usr/bin/tini", "--"]

# Copier le fichier compilé .class
WORKDIR /app
COPY NFCReader.class /app

# Commande pour démarrer `pcscd` en arrière-plan et Java
CMD ["sh", "-c", "pcscd --foreground & sleep 2 && exec java NFCReader"]
