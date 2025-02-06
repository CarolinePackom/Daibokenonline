# Base avec Java et outils NFC
FROM ubuntu:22.04

# Installation des dépendances NFC et Java
RUN apt-get update && apt-get install -y \
    openjdk-17-jdk \
    pcscd \
    pcsc-tools \
    libpcsclite1 \
    libpcsclite-dev \
    usbutils \
    && apt-get clean

# Définition du répertoire de travail
WORKDIR /app

# Copier le fichier source
COPY NFCDetector.java /app/

# Compilation du programme Java
RUN javac NFCDetector.java

# Exécuter `pcscd` puis lancer Java
CMD /usr/sbin/pcscd --foreground --debug & \
    sleep 2 && \
    java NFCDetector
