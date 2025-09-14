CREATE TABLE `utenti` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(255) NOT NULL,
  `cognome` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL
);

CREATE TABLE `cene` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_oste` INT NOT NULL,
  `titolo` VARCHAR(255) NOT NULL,
  `descrizione` TEXT NOT NULL,
  `dataOra` DATETIME NOT NULL,
  `localita` VARCHAR(255) NOT NULL,
  `numPostiDisponibili` INT NOT NULL,
  `menu` TEXT,
  `stato` ENUM('APERTA', 'COMPLETA', 'ANNULLATA') DEFAULT 'APERTA',
  FOREIGN KEY (`id_oste`) REFERENCES `utenti`(`id`) ON DELETE CASCADE
);

CREATE TABLE `richieste_partecipazione` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_cena` INT NOT NULL,
  `id_commensale` INT NOT NULL,
  `dataRichiesta` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `stato` ENUM('IN ATTESA', 'ACCETTATA', 'RIFIUTATA') DEFAULT 'IN ATTESA',
  FOREIGN KEY (`id_cena`) REFERENCES `cene`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_commensale`) REFERENCES `utenti`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_request` (`id_cena`, `id_commensale`)
);

CREATE TABLE `partecipazioni` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_richiesta` INT NOT NULL UNIQUE,
  `id_cena` INT NOT NULL,
  `id_commensale` INT NOT NULL,
  `dataConferma` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `statoPartecipante` ENUM('CONFERMATO', 'ANNULLATO_DA_UTENTE') DEFAULT 'CONFERMATO',
  FOREIGN KEY (`id_richiesta`) REFERENCES `richieste_partecipazione`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_cena`) REFERENCES `cene`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_commensale`) REFERENCES `utenti`(`id`) ON DELETE CASCADE
);

CREATE TABLE `recensioni` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_cena` INT NOT NULL,
  `id_valutatore` INT NOT NULL,
  `id_valutato` INT NOT NULL,
  `voto` INT NOT NULL CHECK (`voto` >= 1 AND `voto` <= 5),
  `commento` TEXT,
  `data` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_cena`) REFERENCES `cene`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_valutatore`) REFERENCES `utenti`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_valutato`) REFERENCES `utenti`(`id`) ON DELETE CASCADE
);