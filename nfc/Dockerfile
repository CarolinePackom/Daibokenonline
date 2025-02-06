FROM openjdk:17-slim

# Installer les paquets nécessaires
RUN apt-get update && apt-get install -y \
    pcscd \
    pcsc-tools \
    libpcsclite1 \
    libccid \
    usbutils \
    && rm -rf /var/lib/apt/lists/*

# Ajouter les drivers pour le lecteur NFC
COPY ifd-ccid.bundle /usr/lib/pcsc/drivers/

# Copier le programme NFCReader
WORKDIR /app
COPY NFCReader.java .

# Compiler le programme
RUN javac NFCReader.java

# Démarrer le service pcscd et exécuter le programme
CMD pcscd --foreground & java NFCReader
