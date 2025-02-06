# Utilisation d'une image légère pour Java
FROM openjdk:17-slim

# Installer les dépendances nécessaires pour les lecteurs NFC
RUN apt-get update && \
    apt-get install -y pcscd pcsc-tools libpcsclite1 usbutils && \
    rm -rf /var/lib/apt/lists/*

# Copier le fichier compilé .class
WORKDIR /app
COPY NFCReader.class /app

# Commande pour démarrer directement le fichier .class
CMD ["sh", "-c", "pcscd --foreground & sleep 2 && java NFCReader"]
