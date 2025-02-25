-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 25, 2025 at 05:01 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `felixbusrecurso`
--

-- --------------------------------------------------------

--
-- Table structure for table `alertas`
--

CREATE TABLE `alertas` (
  `id` int(11) NOT NULL,
  `Texto_Alerta` varchar(255) NOT NULL,
  `Data_Emissao` datetime NOT NULL DEFAULT current_timestamp(),
  `Id_Remetente` int(11) NOT NULL,
  `Tipo_Alerta` enum('Novo Registo','Promoção','Aviso','Criar Veículo','Editar Veículo','Eliminar Veículo','Aceitar Utilizador','Editar Utilizador','Eliminar Utilizador','Eliminar Rota','Criar Rota','Editar Rota','Editar Bilhete','Cancelar Bilhete','Criar Bilhete','Rejeitar Utilizador','Compra','Reembolso') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alertas`
--

INSERT INTO `alertas` (`id`, `Texto_Alerta`, `Data_Emissao`, `Id_Remetente`, `Tipo_Alerta`) VALUES
(3, 'Novo utilizador registado', '2025-02-07 19:06:04', 3, 'Novo Registo'),
(4, 'Novo utilizador registado', '2025-02-07 19:06:28', 4, 'Novo Registo'),
(5, 'Novo utilizador registado', '2025-02-07 19:06:51', 5, 'Novo Registo'),
(97, 'Criou um novo veículo com matrícula: A12', '2025-02-22 14:27:17', 3, 'Criar Veículo'),
(98, 'Criou uma nova rota: Viagem', '2025-02-22 14:27:44', 3, 'Criar Rota'),
(99, 'Bilhete criado para a rota 6', '2025-02-22 14:28:24', 3, 'Criar Bilhete');

-- --------------------------------------------------------

--
-- Table structure for table `bilhetes`
--

CREATE TABLE `bilhetes` (
  `id_bilhete` int(11) NOT NULL,
  `id_rota` int(11) NOT NULL,
  `id_veiculo` int(11) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `data` date NOT NULL,
  `hora` time NOT NULL,
  `estado_bilhete` enum('Ativo','Expirado','Cancelado') DEFAULT 'Ativo',
  `lugaresComprados` int(11) DEFAULT 0,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bilhetes`
--

INSERT INTO `bilhetes` (`id_bilhete`, `id_rota`, `id_veiculo`, `preco`, `data`, `hora`, `estado_bilhete`, `lugaresComprados`, `data_criacao`) VALUES
(26, 6, 5, 50.00, '2025-07-31', '18:32:00', 'Ativo', 0, '2025-02-22 14:28:24');

-- --------------------------------------------------------

--
-- Table structure for table `compras_bilhetes`
--

CREATE TABLE `compras_bilhetes` (
  `id_compra` int(11) NOT NULL,
  `id_bilhete` int(11) DEFAULT NULL,
  `id_utilizador` int(11) DEFAULT NULL,
  `num_passageiros` int(11) DEFAULT NULL,
  `data_compra` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rota`
--

CREATE TABLE `rota` (
  `Id_Rota` int(11) NOT NULL,
  `Nome_Rota` varchar(255) NOT NULL,
  `Origem` varchar(255) NOT NULL,
  `Destino` varchar(255) NOT NULL,
  `Distancia` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rota`
--

INSERT INTO `rota` (`Id_Rota`, `Nome_Rota`, `Origem`, `Destino`, `Distancia`) VALUES
(6, 'Viagem', 'Castelo Branco', 'Covilhã', 50.00);

-- --------------------------------------------------------

--
-- Table structure for table `utilizadores`
--

CREATE TABLE `utilizadores` (
  `id` int(11) NOT NULL,
  `Nome` varchar(100) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Cargo` enum('Admin','Funcionario','Cliente') NOT NULL,
  `Autenticacao` enum('Pendente','Aceite','Rejeitado','Eliminado') DEFAULT 'Pendente',
  `Saldo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Estado` enum('Online','Offline') DEFAULT 'Offline',
  `data_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `utilizadores`
--

INSERT INTO `utilizadores` (`id`, `Nome`, `Email`, `Password`, `Cargo`, `Autenticacao`, `Saldo`, `Estado`, `data_registro`) VALUES
(3, 'admin', 'admin@gmail.com', '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'Admin', 'Aceite', 0.00, 'Offline', '2025-02-07 18:06:04'),
(4, 'funcionario', 'funcionario@gmail.com', '24d96a103e8552cb162117e5b94b1ead596b9c0a94f73bc47f7d244d279cacf2', 'Funcionario', 'Aceite', 0.00, 'Offline', '2025-02-07 18:06:28'),
(5, 'cliente', 'cliente@gmail.com', 'a60b85d409a01d46023f90741e01b79543a3cb1ba048eaefbe5d7a63638043bf', 'Cliente', 'Aceite', 0.00, 'Offline', '2025-02-07 18:06:51');

-- --------------------------------------------------------

--
-- Table structure for table `veiculos`
--

CREATE TABLE `veiculos` (
  `Id_Veiculo` int(11) NOT NULL,
  `Nome_Veiculo` varchar(255) NOT NULL,
  `Capacidade` int(11) NOT NULL,
  `Matricula` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `veiculos`
--

INSERT INTO `veiculos` (`Id_Veiculo`, `Nome_Veiculo`, `Capacidade`, `Matricula`) VALUES
(5, 'FelixBus', 50, 'A12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alertas`
--
ALTER TABLE `alertas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Id_Remetente` (`Id_Remetente`);

--
-- Indexes for table `bilhetes`
--
ALTER TABLE `bilhetes`
  ADD PRIMARY KEY (`id_bilhete`),
  ADD KEY `id_rota` (`id_rota`),
  ADD KEY `id_veiculo` (`id_veiculo`);

--
-- Indexes for table `compras_bilhetes`
--
ALTER TABLE `compras_bilhetes`
  ADD PRIMARY KEY (`id_compra`),
  ADD KEY `id_bilhete` (`id_bilhete`),
  ADD KEY `id_utilizador` (`id_utilizador`);

--
-- Indexes for table `rota`
--
ALTER TABLE `rota`
  ADD PRIMARY KEY (`Id_Rota`);

--
-- Indexes for table `utilizadores`
--
ALTER TABLE `utilizadores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `veiculos`
--
ALTER TABLE `veiculos`
  ADD PRIMARY KEY (`Id_Veiculo`),
  ADD UNIQUE KEY `Matricula` (`Matricula`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alertas`
--
ALTER TABLE `alertas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `bilhetes`
--
ALTER TABLE `bilhetes`
  MODIFY `id_bilhete` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `compras_bilhetes`
--
ALTER TABLE `compras_bilhetes`
  MODIFY `id_compra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `rota`
--
ALTER TABLE `rota`
  MODIFY `Id_Rota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `utilizadores`
--
ALTER TABLE `utilizadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `veiculos`
--
ALTER TABLE `veiculos`
  MODIFY `Id_Veiculo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alertas`
--
ALTER TABLE `alertas`
  ADD CONSTRAINT `alertas_ibfk_1` FOREIGN KEY (`Id_Remetente`) REFERENCES `utilizadores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bilhetes`
--
ALTER TABLE `bilhetes`
  ADD CONSTRAINT `bilhetes_ibfk_1` FOREIGN KEY (`id_rota`) REFERENCES `rota` (`Id_Rota`) ON DELETE CASCADE,
  ADD CONSTRAINT `bilhetes_ibfk_2` FOREIGN KEY (`id_veiculo`) REFERENCES `veiculos` (`Id_Veiculo`) ON DELETE CASCADE;

--
-- Constraints for table `compras_bilhetes`
--
ALTER TABLE `compras_bilhetes`
  ADD CONSTRAINT `compras_bilhetes_ibfk_1` FOREIGN KEY (`id_bilhete`) REFERENCES `bilhetes` (`id_bilhete`),
  ADD CONSTRAINT `compras_bilhetes_ibfk_2` FOREIGN KEY (`id_utilizador`) REFERENCES `utilizadores` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
