-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Apr 16, 2026 alle 16:57
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `5ainc_fsl`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `azienda`
--

CREATE TABLE `azienda` (
  `codice_azienda` int(11) NOT NULL,
  `ragione_sociale` varchar(100) NOT NULL,
  `partita_iva` varchar(20) NOT NULL,
  `sede_legale` varchar(100) NOT NULL,
  `sede_operativa` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `azienda`
--

INSERT INTO `azienda` (`codice_azienda`, `ragione_sociale`, `partita_iva`, `sede_legale`, `sede_operativa`) VALUES
(1, 'Tech Solutions SRL', '02134567890', 'Milano', 'Milano'),
(2, 'DataMind SPA', '03456789123', 'Roma', 'Roma'),
(3, 'InnovaLab SRL', '04567891234', 'Bologna', 'Bologna'),
(4, 'CyberNet SRL', '05678912345', 'Torino', 'Torino'),
(5, 'CloudWare SPA', '06789123456', 'Firenze', 'Firenze'),
(6, 'SmartSystems SRL', '07891234567', 'Ancona', 'Ancona'),
(7, 'FutureTech SRL', '08912345678', 'Perugia', 'Perugia'),
(8, 'DigitalWave SPA', '09123456789', 'Napoli', 'Napoli'),
(9, 'NextGen SRL', '01239876543', 'Padova', 'Padova'),
(10, 'AI Dynamics srl', '02345678912', 'Verona', 'Verona'),
(11, 'InfoLogic SRL', '03456789124', 'Rimini', 'Rimini'),
(12, 'BitFactory SRL', '04567891235', 'Pesaro', 'Pesaro'),
(13, 'SoftDesign SPA', '05678912346', 'Modena', 'Modena'),
(14, 'NetSystems SRL', '06789123457', 'Parma', 'Parma'),
(15, 'DataVision SRL', '07891234568', 'Ferrara', 'Ferrara'),
(16, 'LogicWare SRL', '08912345679', 'Bari', 'Bari'),
(17, 'CodeFactory SRL', '09123456780', 'Genova', 'Genova'),
(18, 'DigitalForge SRL', '01234598765', 'Trieste', 'Trieste'),
(19, 'QuantumSoft SRL', '02345987654', 'Pisa', 'Pisa'),
(20, 'SecureIT SRL', '03459876543', 'Cagliari', 'Cagliari');

-- --------------------------------------------------------

--
-- Struttura della tabella `candidatura`
--

CREATE TABLE `candidatura` (
  `codice_candidatura` int(11) NOT NULL,
  `data_candidatura` date NOT NULL,
  `lettera_motivazionale` text NOT NULL,
  `stato_candidatura` enum('inserita','in valutazione','accettata','rifiutata','ritirata') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `candidatura`
--

INSERT INTO `candidatura` (`codice_candidatura`, `data_candidatura`, `lettera_motivazionale`, `stato_candidatura`) VALUES
(1, '2026-02-01', 'Interesse nello sviluppo software', 'inserita'),
(2, '2026-02-02', 'Appassionato di AI', 'in valutazione'),
(3, '2026-02-03', 'Esperienza con Python', 'accettata'),
(4, '2026-02-04', 'Motivato a imparare', 'inserita'),
(5, '2026-02-05', 'Interesse cybersecurity', 'in valutazione'),
(6, '2026-02-06', 'Appassionato database', 'accettata'),
(7, '2026-02-07', 'Curiosità cloud', 'inserita'),
(8, '2026-02-08', 'Esperienza web', 'accettata'),
(9, '2026-02-09', 'Studente motivato', 'in valutazione'),
(10, '2026-02-10', 'Interesse DevOps', 'inserita'),
(11, '2026-02-11', 'Amante coding', 'accettata'),
(12, '2026-02-12', 'Interessato AI', 'in valutazione'),
(13, '2026-02-13', 'Appassionato reti', 'accettata'),
(14, '2026-02-14', 'Curioso backend', 'inserita'),
(15, '2026-02-15', 'Frontend lover', 'accettata'),
(16, '2026-02-16', 'Data analysis', 'in valutazione'),
(17, '2026-02-17', 'Security focus', 'inserita'),
(18, '2026-02-18', 'Game dev interest', 'accettata'),
(19, '2026-02-19', 'IoT enthusiast', 'in valutazione'),
(20, '2026-02-20', 'Software design', 'inserita');

-- --------------------------------------------------------

--
-- Struttura della tabella `disponibilita`
--

CREATE TABLE `disponibilita` (
  `codice_disponibilita` int(11) NOT NULL,
  `periodo_previsto` date NOT NULL,
  `numero_studenti` int(11) NOT NULL CHECK (`numero_studenti` > 0),
  `descrizione` varchar(255) NOT NULL,
  `competenze` varchar(255) NOT NULL,
  `indirizzo_consigliato` varchar(100) NOT NULL,
  `codice_azienda` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `disponibilita`
--

INSERT INTO `disponibilita` (`codice_disponibilita`, `periodo_previsto`, `numero_studenti`, `descrizione`, `competenze`, `indirizzo_consigliato`, `codice_azienda`) VALUES
(1, '2026-03-01', 2, 'Sviluppo web', 'HTML CSS JS', 'Informatica', 1),
(2, '2026-03-01', 3, 'Database management', 'SQL', 'Informatica', 2),
(3, '2026-03-05', 2, 'Cybersecurity base', 'Networking', 'Informatica', 3),
(4, '2026-03-10', 4, 'Cloud computing', 'Linux', 'Informatica', 4),
(5, '2026-03-12', 2, 'AI project', 'Python', 'Informatica', 5),
(6, '2026-03-15', 3, 'Data analysis', 'Python SQL', 'Informatica', 6),
(7, '2026-03-18', 2, 'Software testing', 'QA', 'Informatica', 7),
(8, '2026-03-20', 3, 'DevOps', 'Docker', 'Informatica', 8),
(9, '2026-03-22', 2, 'Web backend', 'PHP', 'Informatica', 9),
(10, '2026-03-25', 2, 'Frontend UI', 'React', 'Informatica', 10),
(11, '2026-03-28', 3, 'Database design', 'SQL', 'Informatica', 11),
(12, '2026-04-01', 2, 'Mobile dev', 'Java', 'Informatica', 12),
(13, '2026-04-03', 2, 'IoT project', 'C++', 'Informatica', 13),
(14, '2026-04-05', 3, 'Network config', 'Cisco', 'Informatica', 14),
(15, '2026-04-07', 2, 'AI basics', 'Python', 'Informatica', 15),
(16, '2026-04-10', 4, 'Big data intro', 'Hadoop', 'Informatica', 16),
(17, '2026-04-12', 2, 'Web security', 'OWASP', 'Informatica', 17),
(18, '2026-04-15', 3, 'System admin', 'Linux', 'Informatica', 18),
(19, '2026-04-18', 2, 'Game dev', 'Unity', 'Informatica', 19),
(20, '2026-04-20', 3, 'Software design', 'UML', 'Informatica', 20);

-- --------------------------------------------------------

--
-- Struttura della tabella `esperienza`
--

CREATE TABLE `esperienza` (
  `codice_esperienza` int(11) NOT NULL,
  `periodo_effettivo` varchar(50) NOT NULL,
  `numero_ore_previste` int(11) NOT NULL CHECK (`numero_ore_previste` >= 0),
  `numero_ore_svolte` int(11) NOT NULL CHECK (`numero_ore_svolte` >= 0),
  `numero_studenti` int(11) NOT NULL CHECK (`numero_studenti` > 0),
  `codice_docente` int(11) NOT NULL,
  `codice_disponibilita` int(11) NOT NULL,
  `codice_tutor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `esperienza`
--

INSERT INTO `esperienza` (`codice_esperienza`, `periodo_effettivo`, `numero_ore_previste`, `numero_ore_svolte`, `numero_studenti`, `codice_docente`, `codice_disponibilita`, `codice_tutor`) VALUES
(1, 'Mar-Apr 2026', 120, 100, 2, 1, 1, 1),
(2, 'Mar-Apr 2026', 120, 110, 3, 2, 2, 2),
(3, 'Mar-Apr 2026', 120, 90, 2, 3, 3, 3),
(4, 'Mar-Apr 2026', 120, 100, 4, 4, 4, 4),
(5, 'Mar-Apr 2026', 120, 95, 2, 5, 5, 5),
(6, 'Mar-Apr 2026', 120, 105, 3, 6, 6, 6),
(7, 'Mar-Apr 2026', 120, 100, 2, 7, 7, 7),
(8, 'Mar-Apr 2026', 120, 100, 3, 8, 8, 8),
(9, 'Mar-Apr 2026', 120, 100, 2, 9, 9, 9),
(10, 'Mar-Apr 2026', 120, 100, 2, 10, 10, 10),
(11, 'Mar-Apr 2026', 120, 100, 3, 11, 11, 11),
(12, 'Mar-Apr 2026', 120, 100, 2, 12, 12, 12),
(13, 'Mar-Apr 2026', 120, 100, 2, 13, 13, 13),
(14, 'Mar-Apr 2026', 120, 100, 3, 14, 14, 14),
(15, 'Mar-Apr 2026', 120, 100, 2, 15, 15, 15),
(16, 'Mar-Apr 2026', 120, 100, 4, 16, 16, 16),
(17, 'Mar-Apr 2026', 120, 100, 2, 17, 17, 17),
(18, 'Mar-Apr 2026', 120, 100, 3, 18, 18, 18),
(19, 'Mar-Apr 2026', 120, 100, 2, 19, 19, 19),
(20, 'Mar-Apr 2026', 120, 100, 3, 20, 20, 20);

-- --------------------------------------------------------

--
-- Struttura della tabella `studente`
--

CREATE TABLE `studente` (
  `codice_studente` int(11) NOT NULL,
  `nome` varchar(30) NOT NULL,
  `cognome` varchar(30) NOT NULL,
  `data_di_nascita` date NOT NULL,
  `luogo_di_nascita` varchar(50) NOT NULL,
  `indirizzo` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `classe` varchar(10) NOT NULL,
  `indirizzo_di_studi` varchar(50) NOT NULL,
  `codice_esperienza` int(11) DEFAULT NULL,
  `codice_candidatura` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `studente`
--

INSERT INTO `studente` (`codice_studente`, `nome`, `cognome`, `data_di_nascita`, `luogo_di_nascita`, `indirizzo`, `email`, `classe`, `indirizzo_di_studi`, `codice_esperienza`, `codice_candidatura`) VALUES
(1, 'Luca', 'Bianchi', '2007-05-12', 'Ancona', 'Via Roma 10', 'luca.bianchi@email.it', '4AI', 'Informatica', 1, 1),
(2, 'Marco', 'Rossi', '2007-03-02', 'Pesaro', 'Via Milano 5', 'marco.rossi@email.it', '4AI', 'Informatica', 2, 2),
(3, 'Giulia', 'Verdi', '2007-07-20', 'Fano', 'Via Dante 8', 'giulia.verdi@email.it', '4AI', 'Informatica', 3, 3),
(4, 'Andrea', 'Neri', '2007-01-11', 'Urbino', 'Via Leopardi 3', 'andrea.neri@email.it', '4AI', 'Informatica', 4, 4),
(5, 'Chiara', 'Galli', '2007-02-15', 'Pesaro', 'Via Carducci 9', 'chiara.galli@email.it', '4AI', 'Informatica', 5, 5),
(6, 'Davide', 'Conti', '2007-09-01', 'Fano', 'Via Mazzini 2', 'davide.conti@email.it', '4AI', 'Informatica', 6, 6),
(7, 'Elena', 'Greco', '2007-04-17', 'Ancona', 'Via Garibaldi 6', 'elena.greco@email.it', '4AI', 'Informatica', 7, 7),
(8, 'Matteo', 'Costa', '2007-12-03', 'Pesaro', 'Via Verdi 12', 'matteo.costa@email.it', '4AI', 'Informatica', 8, 8),
(9, 'Sara', 'Ferri', '2007-10-21', 'Fano', 'Via Manzoni 7', 'sara.ferri@email.it', '4AI', 'Informatica', 9, 9),
(10, 'Paolo', 'Riva', '2007-11-02', 'Ancona', 'Via Roma 15', 'paolo.riva@email.it', '4AI', 'Informatica', 10, 10),
(11, 'Anna', 'Testa', '2007-05-30', 'Pesaro', 'Via Venezia 4', 'anna.testa@email.it', '4AI', 'Informatica', 11, 11),
(12, 'Simone', 'Moretti', '2007-06-10', 'Fano', 'Via Firenze 11', 'simone.moretti@email.it', '4AI', 'Informatica', 12, 12),
(13, 'Valentina', 'Barbieri', '2007-02-27', 'Ancona', 'Via Torino 14', 'valentina.barbieri@email.it', '4AI', 'Informatica', 13, 13),
(14, 'Stefano', 'Fontana', '2007-08-18', 'Pesaro', 'Via Napoli 21', 'stefano.fontana@email.it', '4AI', 'Informatica', 14, 14),
(15, 'Martina', 'Villa', '2007-01-25', 'Fano', 'Via Bari 6', 'martina.villa@email.it', '4AI', 'Informatica', 15, 15),
(16, 'Francesco', 'Leone', '2007-04-05', 'Ancona', 'Via Genova 17', 'francesco.leone@email.it', '4AI', 'Informatica', 16, 16),
(17, 'Alessia', 'Serra', '2007-09-14', 'Pesaro', 'Via Palermo 19', 'alessia.serra@email.it', '4AI', 'Informatica', 17, 17),
(18, 'Roberto', 'Grassi', '2007-10-09', 'Fano', 'Via Bologna 13', 'roberto.grassi@email.it', '4AI', 'Informatica', 18, 18),
(19, 'Marta', 'Carli', '2007-11-11', 'Ancona', 'Via Pisa 20', 'marta.carli@email.it', '4AI', 'Informatica', 19, 19),
(20, 'Lorenzo', 'Ricci', '2007-03-22', 'Pesaro', 'Via Siena 18', 'lorenzo.ricci@email.it', '4AI', 'Informatica', 20, 20);

-- --------------------------------------------------------

--
-- Struttura della tabella `tutor_aziendale`
--

CREATE TABLE `tutor_aziendale` (
  `codice_tutor` int(11) NOT NULL,
  `nome` varchar(30) NOT NULL,
  `cognome` varchar(30) NOT NULL,
  `ruolo` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `tutor_aziendale`
--

INSERT INTO `tutor_aziendale` (`codice_tutor`, `nome`, `cognome`, `ruolo`, `email`) VALUES
(1, 'Marco', 'Rossi', 'Software Engineer', 'm.rossi@azienda.it'),
(2, 'Luca', 'Bianchi', 'Project Manager', 'l.bianchi@azienda.it'),
(3, 'Anna', 'Verdi', 'Data Analyst', 'a.verdi@azienda.it'),
(4, 'Paolo', 'Neri', 'System Administrator', 'p.neri@azienda.it'),
(5, 'Giulia', 'Galli', 'Cybersecurity Specialist', 'g.galli@azienda.it'),
(6, 'Andrea', 'Conti', 'Developer', 'a.conti@azienda.it'),
(7, 'Sara', 'De Luca', 'UX Designer', 's.deluca@azienda.it'),
(8, 'Davide', 'Greco', 'Backend Developer', 'd.greco@azienda.it'),
(9, 'Elena', 'Martini', 'Frontend Developer', 'e.martini@azienda.it'),
(10, 'Matteo', 'Costa', 'Cloud Engineer', 'm.costa@azienda.it'),
(11, 'Laura', 'Ferri', 'AI Specialist', 'l.ferri@azienda.it'),
(12, 'Giorgio', 'Riva', 'DBA', 'g.riva@azienda.it'),
(13, 'Chiara', 'Testa', 'Software Tester', 'c.testa@azienda.it'),
(14, 'Simone', 'Moretti', 'DevOps', 's.moretti@azienda.it'),
(15, 'Valentina', 'Barbieri', 'Analyst', 'v.barbieri@azienda.it'),
(16, 'Stefano', 'Fontana', 'Network Engineer', 's.fontana@azienda.it'),
(17, 'Martina', 'Villa', 'Consultant', 'm.villa@azienda.it'),
(18, 'Francesco', 'Leone', 'Architect', 'f.leone@azienda.it'),
(19, 'Alessia', 'Serra', 'Security Analyst', 'a.serra@azienda.it'),
(20, 'Roberto', 'Grassi', 'Team Leader', 'r.grassi@azienda.it');

-- --------------------------------------------------------

--
-- Struttura della tabella `tutor_scolastico`
--

CREATE TABLE `tutor_scolastico` (
  `codice_docente` int(11) NOT NULL,
  `nome` varchar(30) NOT NULL,
  `cognome` varchar(30) NOT NULL,
  `tipo` enum('dipartimento','area disciplinare') NOT NULL,
  `numero_studenti` int(11) NOT NULL CHECK (`numero_studenti` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `tutor_scolastico`
--

INSERT INTO `tutor_scolastico` (`codice_docente`, `nome`, `cognome`, `tipo`, `numero_studenti`) VALUES
(1, 'Giovanni', 'Ricci', 'dipartimento', 10),
(2, 'Paola', 'Rossi', 'area disciplinare', 8),
(3, 'Marco', 'Belli', 'dipartimento', 12),
(4, 'Laura', 'Nanni', 'area disciplinare', 7),
(5, 'Stefano', 'Fabbri', 'dipartimento', 9),
(6, 'Anna', 'Morelli', 'area disciplinare', 11),
(7, 'Davide', 'Gatti', 'dipartimento', 6),
(8, 'Lucia', 'Pellegrini', 'area disciplinare', 10),
(9, 'Andrea', 'Romano', 'dipartimento', 8),
(10, 'Chiara', 'Silvestri', 'area disciplinare', 9),
(11, 'Elisa', 'Villa', 'dipartimento', 7),
(12, 'Roberto', 'Rizzi', 'area disciplinare', 10),
(13, 'Sara', 'Corsi', 'dipartimento', 8),
(14, 'Matteo', 'Guerra', 'area disciplinare', 6),
(15, 'Paolo', 'Seri', 'dipartimento', 11),
(16, 'Valeria', 'Neri', 'area disciplinare', 9),
(17, 'Giulia', 'Donati', 'dipartimento', 10),
(18, 'Franco', 'Levi', 'area disciplinare', 7),
(19, 'Marta', 'Costa', 'dipartimento', 8),
(20, 'Lorenzo', 'Carli', 'area disciplinare', 9);

-- --------------------------------------------------------

--
-- Struttura della tabella `utenti`
--

CREATE TABLE `utenti` (
  `ID` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `utenti`
--

INSERT INTO `utenti` (`ID`, `username`, `password`) VALUES
(22, 'miccia', '$2y$12$tkqDh0WBh7q/VOs62Vhaee0BzQLQN8Dv6kHOYPwWpyr6BWy4mHtrm'),
(23, 'michele', '$2y$12$mZYnq45ozCDaloIKDU1p1e5OhjXDN7m24KfXqvruncTxo5F2prYQO');

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `v_studenti_anagrafica`
-- (Vedi sotto per la vista effettiva)
--
CREATE TABLE `v_studenti_anagrafica` (
`codice_studente` int(11)
,`cognome` varchar(30)
,`nome` varchar(30)
,`classe` varchar(10)
,`indirizzo_di_studi` varchar(50)
,`email` varchar(100)
);

-- --------------------------------------------------------

--
-- Struttura per vista `v_studenti_anagrafica`
--
DROP TABLE IF EXISTS `v_studenti_anagrafica`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_studenti_anagrafica`  AS SELECT `studente`.`codice_studente` AS `codice_studente`, `studente`.`cognome` AS `cognome`, `studente`.`nome` AS `nome`, `studente`.`classe` AS `classe`, `studente`.`indirizzo_di_studi` AS `indirizzo_di_studi`, `studente`.`email` AS `email` FROM `studente` ;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `azienda`
--
ALTER TABLE `azienda`
  ADD PRIMARY KEY (`codice_azienda`),
  ADD UNIQUE KEY `partita_iva` (`partita_iva`);

--
-- Indici per le tabelle `candidatura`
--
ALTER TABLE `candidatura`
  ADD PRIMARY KEY (`codice_candidatura`);

--
-- Indici per le tabelle `disponibilita`
--
ALTER TABLE `disponibilita`
  ADD PRIMARY KEY (`codice_disponibilita`),
  ADD KEY `fk_disponibilita_azienda` (`codice_azienda`);

--
-- Indici per le tabelle `esperienza`
--
ALTER TABLE `esperienza`
  ADD PRIMARY KEY (`codice_esperienza`),
  ADD KEY `fk_esperienza_docente` (`codice_docente`),
  ADD KEY `fk_esperienza_disponibilita` (`codice_disponibilita`),
  ADD KEY `fk_esperienza_tutor` (`codice_tutor`);

--
-- Indici per le tabelle `studente`
--
ALTER TABLE `studente`
  ADD PRIMARY KEY (`codice_studente`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_studente_esperienza` (`codice_esperienza`),
  ADD KEY `fk_studente_candidatura` (`codice_candidatura`);

--
-- Indici per le tabelle `tutor_aziendale`
--
ALTER TABLE `tutor_aziendale`
  ADD PRIMARY KEY (`codice_tutor`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indici per le tabelle `tutor_scolastico`
--
ALTER TABLE `tutor_scolastico`
  ADD PRIMARY KEY (`codice_docente`);

--
-- Indici per le tabelle `utenti`
--
ALTER TABLE `utenti`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `uq_utenti_username` (`username`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `azienda`
--
ALTER TABLE `azienda`
  MODIFY `codice_azienda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT per la tabella `candidatura`
--
ALTER TABLE `candidatura`
  MODIFY `codice_candidatura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT per la tabella `disponibilita`
--
ALTER TABLE `disponibilita`
  MODIFY `codice_disponibilita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT per la tabella `esperienza`
--
ALTER TABLE `esperienza`
  MODIFY `codice_esperienza` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT per la tabella `studente`
--
ALTER TABLE `studente`
  MODIFY `codice_studente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT per la tabella `tutor_aziendale`
--
ALTER TABLE `tutor_aziendale`
  MODIFY `codice_tutor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT per la tabella `tutor_scolastico`
--
ALTER TABLE `tutor_scolastico`
  MODIFY `codice_docente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT per la tabella `utenti`
--
ALTER TABLE `utenti`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `disponibilita`
--
ALTER TABLE `disponibilita`
  ADD CONSTRAINT `fk_disponibilita_azienda` FOREIGN KEY (`codice_azienda`) REFERENCES `azienda` (`codice_azienda`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `esperienza`
--
ALTER TABLE `esperienza`
  ADD CONSTRAINT `fk_esperienza_disponibilita` FOREIGN KEY (`codice_disponibilita`) REFERENCES `disponibilita` (`codice_disponibilita`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_esperienza_docente` FOREIGN KEY (`codice_docente`) REFERENCES `tutor_scolastico` (`codice_docente`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_esperienza_tutor` FOREIGN KEY (`codice_tutor`) REFERENCES `tutor_aziendale` (`codice_tutor`) ON UPDATE CASCADE;

--
-- Limiti per la tabella `studente`
--
ALTER TABLE `studente`
  ADD CONSTRAINT `fk_studente_candidatura` FOREIGN KEY (`codice_candidatura`) REFERENCES `candidatura` (`codice_candidatura`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_studente_esperienza` FOREIGN KEY (`codice_esperienza`) REFERENCES `esperienza` (`codice_esperienza`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
