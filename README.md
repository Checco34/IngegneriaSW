# IngegneriaSW
Repo per progetto di IngegneriaSW "A cena con estranei"

Guida all'Installazione:
Seguire questi passaggi per configurare e avviare l'ambiente di sviluppo in locale.

1. Prerequisiti
Assicurarsi di avere installato sul tuo sistema:
    Git (per clonare il repository)
    Docker e Docker Compose (consigliamo di installare Docker Desktop, che li include entrambi)

2. Clonare il Repository
Apri un terminale e clona il repository del progetto nella cartella desiderata:
    git clone https://github.com/Checco34/IngegneriaSW
    
3. Configurare le Variabili d'Ambiente
L'applicazione utilizza un file .env per gestire le configurazioni sensibili (credenziali del database, chiavi segrete). Questo file non è incluso nel repository per motivi di sicurezza.

Crea un nuovo file chiamato .env nella cartella principale del progetto.
Copia e incolla il seguente contenuto nel file .env, personalizzando i valori se necessario:

    # Configurazione del Database
    DB_NAME=CenaConEstranei_db
    DB_USER=user
    DB_PASS=123@Password@123
    MYSQL_ROOT_PASSWORD=123@Password@123

    # Chiave Segreta per JWT
    JWT_SECRET_KEY=una_frase_segreta_molto_molto_lunga_e_difficile

4. Avviare l'Applicazione
Una volta configurato il file .env, puoi avviare l'intera applicazione con un singolo comando.
Dal terminale, nella cartella principale del progetto, eseguire:
    docker-compose up -d --build
up: Avvia i container definiti nel file docker-compose.yml.
-d: Esegue i container in modalità "detached" (in background).
--build: Forza la costruzione dell'immagine personalizzata per il servizio web, assicurando che tutte le dipendenze del Dockerfile siano installate.