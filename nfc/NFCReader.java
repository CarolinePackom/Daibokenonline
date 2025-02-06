import javax.smartcardio.*;
import java.net.HttpURLConnection;
import java.net.URL;
import java.io.OutputStream;
import java.nio.charset.StandardCharsets;
import java.util.List;

public class NFCReader {
    // Commande pour récupérer l'UID (pour de nombreux lecteurs)
    private static final byte[] GET_UID_COMMAND = new byte[]{(byte)0xFF, (byte)0xCA, (byte)0x00, (byte)0x00, (byte)0x00};

    public static void main(String[] args) {
        try {
            // Récupération du TerminalFactory et de la liste des lecteurs connectés
            TerminalFactory factory = TerminalFactory.getDefault();
            List<CardTerminal> terminals = factory.terminals().list();

            if (terminals.isEmpty()) {
                System.out.println("Aucun lecteur NFC détecté.");
                return;
            }

            CardTerminal terminal = terminals.get(0);
            System.out.println("Lecteur NFC utilisé : " + terminal.getName());

            // Boucle infinie pour surveiller la présence de carte
            while (true) {
                // Attente de la présence d'une carte (le paramètre 0 signifie attente infinie)
                terminal.waitForCardPresent(0);

                Card card = null;
                try {
                    // Connexion à la carte
                    card = terminal.connect("*");
                    CardChannel channel = card.getBasicChannel();

                    // Envoi de la commande pour obtenir l'UID
                    ResponseAPDU response = channel.transmit(new CommandAPDU(GET_UID_COMMAND));

                    // Vérification du statut (0x9000 signifie succès)
                    if (response.getSW() == 0x9000) {
                        String uidHex = bytesToHex(response.getData());
                        System.out.println("UID détecté : " + uidHex);

                        // Envoi asynchrone de l'UID vers le serveur Laravel
                        new Thread(() -> sendUIDToServer(uidHex)).start();
                    } else {
                        System.err.println("Erreur lors de la lecture, SW: "
                                + Integer.toHexString(response.getSW()));
                    }
                } catch (CardException e) {
                    System.err.println("Erreur de lecture de la carte : " + e.getMessage());
                } finally {
                    // Déconnexion de la carte si elle a été connectée
                    if (card != null) {
                        try {
                            card.disconnect(false);
                        } catch (CardException e) {
                            System.err.println("Erreur lors de la déconnexion de la carte : " + e.getMessage());
                        }
                    }
                    // Attente que la carte soit retirée avant de continuer
                    terminal.waitForCardAbsent(0);
                }
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    // Méthode utilitaire pour convertir un tableau de bytes en chaîne hexadécimale
    private static String bytesToHex(byte[] bytes) {
        StringBuilder hexString = new StringBuilder();
        for (byte b : bytes) {
            hexString.append(String.format("%02X", b));
        }
        return hexString.toString();
    }

    // Méthode pour envoyer l'UID vers votre API Laravel
    private static void sendUIDToServer(String uid) {
        HttpURLConnection connection = null;
        try {
            // Adaptez l'URL selon la configuration de votre serveur
            URL url = new URL("http://localhost/api/scan");
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("POST");
            connection.setRequestProperty("Content-Type", "application/json; utf-8");
            connection.setDoOutput(true);

            // Construction du JSON à envoyer
            String jsonInput = "{\"id_nfc\": \"" + uid + "\"}";

            // Envoi du JSON
            try (OutputStream os = connection.getOutputStream()) {
                byte[] input = jsonInput.getBytes(StandardCharsets.UTF_8);
                os.write(input, 0, input.length);
            }

            int responseCode = connection.getResponseCode();
            if (responseCode == HttpURLConnection.HTTP_OK
                    || responseCode == HttpURLConnection.HTTP_CREATED) {
                System.out.println("UID envoyé avec succès au serveur.");
            } else {
                System.err.println("Erreur du serveur lors de l'envoi de l'UID, code réponse: " + responseCode);
            }
        } catch (Exception e) {
            System.err.println("Erreur lors de l'envoi de l'UID : " + e.getMessage());
        } finally {
            if (connection != null) {
                connection.disconnect();
            }
        }
    }
}
