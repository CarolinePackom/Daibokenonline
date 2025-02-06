import javax.smartcardio.*;
import java.io.*;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.List;

public class NFCReader {
    private static final byte[] GET_UID_COMMAND = new byte[]{(byte) 0xFF, (byte) 0xCA, 0x00, 0x00, 0x00};

    public static void main(String[] args) {
        // Vérifier que pcscd tourne bien avant de démarrer
        waitForPCSC();

        // Trouver le bon lecteur NFC
        CardTerminal terminal = findNFCTerminal();
        if (terminal == null) {
            System.err.println("Aucun lecteur NFC détecté !");
            return;
        }
        System.out.println("Lecteur NFC utilisé : " + terminal.getName());

        // Boucle de lecture des cartes NFC
        while (true) {
            try {
                terminal.waitForCardPresent(0);
                readCard(terminal);
                terminal.waitForCardAbsent(0);
            } catch (Exception e) {
                System.err.println("Erreur de lecture de la carte : " + e.getMessage());
            }
        }
    }

    private static void readCard(CardTerminal terminal) {
        try {
            Card card = terminal.connect("*");
            CardChannel channel = card.getBasicChannel();

            ResponseAPDU response = channel.transmit(new CommandAPDU(GET_UID_COMMAND));
            if (response.getSW1() == 0x90 && response.getSW2() == 0x00) {
                String uidHex = bytesToHex(response.getData());

                new Thread(() -> sendUIDToServer(uidHex)).start();
                System.out.println("Carte détectée : " + uidHex);
            } else {
                System.err.println("Erreur de lecture (SW1/SW2 incorrects)");
            }
            card.disconnect(false);
        } catch (CardException e) {
            System.err.println("Problème avec la carte NFC : " + e.getMessage());
        }
    }

    private static CardTerminal findNFCTerminal() {
        try {
            TerminalFactory factory = TerminalFactory.getDefault();
            List<CardTerminal> terminals = factory.terminals().list();
            if (!terminals.isEmpty()) return terminals.get(0);
        } catch (Exception e) {
            System.err.println("Impossible de récupérer les lecteurs NFC via javax.smartcardio !");
        }

        System.err.println("Essai de détection via `lsusb`...");
        if (isNFCReaderConnected()) {
            System.out.println("Lecteur détecté via lsusb, mais pas pris en charge par javax.smartcardio.");
        }
        return null;
    }

    private static boolean isNFCReaderConnected() {
        try {
            Process process = new ProcessBuilder("lsusb").start();
            BufferedReader reader = new BufferedReader(new InputStreamReader(process.getInputStream()));
            String line;
            while ((line = reader.readLine()) != null) {
                if (line.contains("072F:2200")) { // ID du lecteur ACS ACR122U
                    return true;
                }
            }
        } catch (IOException e) {
            System.err.println("Erreur lors de l'exécution de lsusb : " + e.getMessage());
        }
        return false;
    }

    private static void waitForPCSC() {
        for (int i = 0; i < 10; i++) {
            try {
                Process process = new ProcessBuilder("pgrep", "-x", "pcscd").start();
                if (process.waitFor() == 0) {
                    System.out.println("pcscd est actif !");
                    return;
                }
            } catch (Exception ignored) {}
            System.out.println("pcscd non détecté, nouvelle tentative...");
            try {
                Thread.sleep(2000);
            } catch (InterruptedException ignored) {}
        }
        System.err.println("pcscd n'a pas démarré après plusieurs tentatives.");
        System.exit(1);
    }

    private static String bytesToHex(byte[] bytes) {
        StringBuilder hexString = new StringBuilder();
        for (byte b : bytes) {
            hexString.append(String.format("%02X", b));
        }
        return hexString.toString();
    }

    private static void sendUIDToServer(String uid) {
        try {
            URL url = new URL("http://localhost/api/scan");
            HttpURLConnection connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("POST");
            connection.setRequestProperty("Content-Type", "application/json");
            connection.setDoOutput(true);

            String jsonInput = "{\"id_nfc\": \"" + uid + "\"}";
            try (OutputStream os = connection.getOutputStream()) {
                os.write(jsonInput.getBytes("utf-8"));
            }

            int responseCode = connection.getResponseCode();
            if (responseCode != 200) {
                System.err.println("Erreur du serveur : " + responseCode);
            }
        } catch (Exception e) {
            System.err.println("Erreur lors de l'envoi de l'UID : " + e.getMessage());
        }
    }
}
