-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 24/05/2025 às 20:21
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `lojavirtual`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente`
--

CREATE TABLE `cliente` (
  `id` int(11) UNSIGNED NOT NULL,
  `nome` varchar(50) NOT NULL,
  `endereco` varchar(80) NOT NULL,
  `telefone` varchar(35) NOT NULL,
  `email` varchar(40) NOT NULL,
  `sexo` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Despejando dados para a tabela `cliente`
--

INSERT INTO `cliente` (`id`, `nome`, `endereco`, `telefone`, `email`, `sexo`) VALUES
(3, 'Gabriel Trevisan de Lima', 'Rua cristiano pagani 1049', '14998841295', 'gabtrevis7@gmail.com', 'M'),
(4, 'Gabriel lupo', '13123', '123123', '3123@gamil.com', 'M'),
(5, 'Edimison', 'Confiança equipamentos (Ramal, Camera)', '123123', 'edsion@gmail.com', 'M');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `data_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_cliente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pedidos`
--

INSERT INTO `pedidos` (`id`, `id_produto`, `quantidade`, `data_pedido`, `id_cliente`) VALUES
(1, 3, 1, '2025-05-10 19:56:11', 0),
(2, 3, 1, '2025-05-10 19:56:22', 0),
(3, 3, 2, '2025-05-10 20:01:19', 0),
(4, 3, 2, '2025-05-10 20:08:17', 3),
(5, 3, 3, '2025-05-10 20:13:43', 3),
(6, 3, 1, '2025-05-10 20:19:53', 4),
(7, 4, 1, '2025-05-10 20:19:53', 4),
(8, 3, 1, '2025-05-10 20:20:19', 3),
(9, 4, 3, '2025-05-10 20:20:19', 3),
(10, 3, 2, '2025-05-14 22:33:55', 4),
(11, 4, 1, '2025-05-14 22:33:55', 4),
(12, 3, 1, '2025-05-14 22:37:02', 3),
(13, 4, 1, '2025-05-14 22:37:02', 3),
(14, 3, 1, '2025-05-14 22:37:20', 4),
(15, 4, 1, '2025-05-14 22:37:20', 4),
(16, 3, 1, '2025-05-15 11:20:34', 5),
(17, 4, 1, '2025-05-15 11:20:34', 5),
(18, 1, 0, '2025-05-24 18:23:35', 5),
(19, 2, 0, '2025-05-24 18:23:35', 5),
(20, 4, 2, '2025-05-24 18:23:35', 5),
(21, 5, 3, '2025-05-24 18:23:35', 5),
(22, 6, 2, '2025-05-24 18:23:35', 5),
(23, 3, 1, '2025-05-24 18:23:35', 5),
(24, 3, 1, '2025-05-24 18:23:55', 3),
(25, 3, 2, '2025-05-24 18:28:26', 5),
(26, 5, 1, '2025-05-24 18:28:26', 5),
(27, 3, 1, '2025-05-24 18:28:47', 4),
(28, 5, 1, '2025-05-24 18:28:47', 4),
(29, 3, 1, '2025-05-24 18:34:16', 5);

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto`
--

CREATE TABLE `produto` (
  `id` int(11) UNSIGNED NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descricao` varchar(80) NOT NULL,
  `valor` varchar(35) NOT NULL,
  `imagem` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Despejando dados para a tabela `produto`
--

INSERT INTO `produto` (`id`, `nome`, `descricao`, `valor`, `imagem`) VALUES
(3, 'Computador', 'COmputaodor Gamer 123123', '10000', NULL),
(4, 'Memória Ram', 'Memória ram 8gb', '50', NULL),
(5, 'Memoria', 'Memória ram 8gb', '10', 'uploads/68279dfc6aee2.jpg'),
(6, 'COmputador', 'RGB', '10000', 'uploads/682ca30079834.jpg');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `produto`
--
ALTER TABLE `produto`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de tabela `produto`
--
ALTER TABLE `produto`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
