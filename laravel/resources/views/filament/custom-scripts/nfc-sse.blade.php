<x-filament::button id="nfc-button" class="inline-flex items-center justify-center w-full">
    <span id="nfc-loading" wire:loading class="flex" style="display: none;">
        <x-filament::loading-indicator class="h-5 w-5" />
    </span>
    <span id="nfc-text" class="flex w-full">Scanner la carte NFC</span>
</x-filament::button>

<script>
let pollingActive = false;

function pollNfcData() {
    const interval = setInterval(() => {
        if (!pollingActive) {
            clearInterval(interval);
            return;
        }

        fetch('/nfc/dernier-id')
            .then((response) => {
                if (response.status === 204) {
                    return null;
                }
                return response.json();
            })
            .then((data) => {
                if (data && data.id_nfc) {
                    pollingActive = false;
                    clearInterval(interval);

                    // Arrêter l'effet de chargement
                    toggleLoading(false);

                    // Remplir le champ avec l'ID NFC
                    const inputField = document.getElementById('data.id_nfc');
                    if (inputField) {
                        inputField.value = data.id_nfc;
                        inputField.dispatchEvent(new Event('input'));
                    } else {
                        console.error("Champ 'data.id_nfc' introuvable.");
                    }
                }
            })
            .catch((error) => {
                console.error('Erreur lors du polling NFC:', error);
            });
    }, 2000);
}

function toggleLoading(isLoading) {
    const loadingIndicator = document.getElementById('nfc-loading');
    const textElement = document.getElementById('nfc-text');

    if (isLoading) {
        loadingIndicator.style.display = 'flex';
        textElement.style.display = 'none';
    } else {
        loadingIndicator.style.display = 'none';
        textElement.style.display = 'flex';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('nfc-button');
    button.addEventListener('click', () => {
        if (!pollingActive) {
            pollingActive = true;

            // Activer l'effet de chargement
            toggleLoading(true);

            // Démarrer le polling
            pollNfcData();
        }
    });
});
</script>
