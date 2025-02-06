FROM openjdk:17-slim

# Installer les paquets nécessaires
RUN apt-get update && apt-get install -y \
    pcscd \
    pcsc-tools \
    libpcsclite1 \
    libccid \
    libacsccid1 \
    usbutils \
    && rm -rf /var/lib/apt/lists/*

# Copier le programme NFCReader
WORKDIR /app
COPY NFCReader.java .

# Compiler le programme
RUN javac NFCReader.java

# Démarrer le service pcscd et exécuter le programme
CMD pcscd --foreground & java NFCReader
