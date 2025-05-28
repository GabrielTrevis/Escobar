-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 28/05/2025 às 23:52
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
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `nome`, `descricao`, `data_criacao`) VALUES
(2, 'Computador', NULL, '2025-05-28 01:21:23'),
(3, 'Perifericos', NULL, '2025-05-28 01:56:17'),
(4, 'Peças', NULL, '2025-05-28 21:47:29');

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
(6, 'Gabriel Trevisan', 'Rua Cristiane Pagani 1049', '14998841295', 'gabtrevis7@gmail.co', 'M'),
(10, 'Escobar', 'Ite', '123123123', 'Escobar@professor.com', 'M');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `data_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_cliente` int(11) NOT NULL,
  `grupo_pedido_id` varchar(50) DEFAULT NULL COMMENT 'Identificador único para agrupar itens da mesma compra'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pedidos`
--

INSERT INTO `pedidos` (`id`, `id_produto`, `quantidade`, `data_pedido`, `id_cliente`, `grupo_pedido_id`) VALUES
(1, 3, 1, '2025-05-10 19:56:11', 0, NULL),
(2, 3, 1, '2025-05-10 19:56:22', 0, NULL),
(3, 3, 2, '2025-05-10 20:01:19', 0, NULL),
(4, 3, 2, '2025-05-10 20:08:17', 3, NULL),
(5, 3, 3, '2025-05-10 20:13:43', 3, NULL),
(6, 3, 1, '2025-05-10 20:19:53', 4, NULL),
(7, 4, 1, '2025-05-10 20:19:53', 4, NULL),
(8, 3, 1, '2025-05-10 20:20:19', 3, NULL),
(9, 4, 3, '2025-05-10 20:20:19', 3, NULL),
(10, 3, 2, '2025-05-14 22:33:55', 4, NULL),
(11, 4, 1, '2025-05-14 22:33:55', 4, NULL),
(12, 3, 1, '2025-05-14 22:37:02', 3, NULL),
(13, 4, 1, '2025-05-14 22:37:02', 3, NULL),
(14, 3, 1, '2025-05-14 22:37:20', 4, NULL),
(15, 4, 1, '2025-05-14 22:37:20', 4, NULL),
(16, 3, 1, '2025-05-15 11:20:34', 5, NULL),
(17, 4, 1, '2025-05-15 11:20:34', 5, NULL),
(18, 1, 0, '2025-05-24 18:23:35', 5, NULL),
(19, 2, 0, '2025-05-24 18:23:35', 5, NULL),
(20, 4, 2, '2025-05-24 18:23:35', 5, NULL),
(21, 5, 3, '2025-05-24 18:23:35', 5, NULL),
(22, 6, 2, '2025-05-24 18:23:35', 5, NULL),
(23, 3, 1, '2025-05-24 18:23:35', 5, NULL),
(24, 3, 1, '2025-05-24 18:23:55', 3, NULL),
(25, 3, 2, '2025-05-24 18:28:26', 5, NULL),
(26, 5, 1, '2025-05-24 18:28:26', 5, NULL),
(27, 3, 1, '2025-05-24 18:28:47', 4, NULL),
(28, 5, 1, '2025-05-24 18:28:47', 4, NULL),
(29, 3, 1, '2025-05-24 18:34:16', 5, NULL),
(30, 3, 1, '2025-05-25 00:33:44', 5, NULL),
(31, 3, 1, '2025-05-25 00:41:20', 3, NULL),
(32, 6, 1, '2025-05-25 00:41:20', 3, NULL),
(33, 5, 1, '2025-05-25 00:41:20', 3, NULL),
(34, 3, 2, '2025-05-27 03:39:43', 5, NULL),
(35, 6, 1, '2025-05-27 03:44:55', 5, 'pedido_6834eee79512d5.49695994_1748299495'),
(36, 5, 1, '2025-05-27 03:44:55', 5, 'pedido_6834eee79512d5.49695994_1748299495'),
(37, 3, 1, '2025-05-27 04:07:51', 5, 'pedido_6834f447dafa46.92576202_1748300871'),
(38, 5, 1, '2025-05-27 04:07:51', 5, 'pedido_6834f447dafa46.92576202_1748300871'),
(39, 7, 1, '2025-05-28 06:27:59', 6, 'pedido_6836669fbb57f5.07465307_1748395679'),
(40, 8, 1, '2025-05-28 06:27:59', 6, 'pedido_6836669fbb57f5.07465307_1748395679'),
(41, 7, 1, '2025-05-28 06:32:24', 6, 'pedido_683667a86accb3.73823402_1748395944'),
(42, 7, 1, '2025-05-28 06:36:05', 6, 'pedido_68366885e61de0.85106814_1748396165'),
(43, 7, 1, '2025-05-28 06:53:43', 6, 'pedido_68366ca7de6646.37421228_1748397223'),
(44, 9, 1, '2025-05-28 06:55:09', 6, 'pedido_68366cfd3438e0.98477918_1748397309'),
(45, 8, 1, '2025-05-28 06:55:09', 6, 'pedido_68366cfd3438e0.98477918_1748397309'),
(46, 7, 1, '2025-05-28 06:55:09', 6, 'pedido_68366cfd3438e0.98477918_1748397309'),
(47, 9, 1, '2025-05-28 06:56:31', 6, 'pedido_68366d4f8ea6b1.51356624_1748397391'),
(48, 8, 1, '2025-05-28 06:56:31', 6, 'pedido_68366d4f8ea6b1.51356624_1748397391'),
(49, 7, 1, '2025-05-28 06:56:31', 6, 'pedido_68366d4f8ea6b1.51356624_1748397391'),
(50, 8, 1, '2025-05-28 07:14:48', 6, 'pedido_68367198bed100.65079474_1748398488'),
(51, 7, 4, '2025-05-28 07:14:48', 6, 'pedido_68367198bed100.65079474_1748398488'),
(52, 9, 1, '2025-05-28 07:14:48', 6, 'pedido_68367198bed100.65079474_1748398488'),
(53, 7, 1, '2025-05-29 02:33:20', 6, 'pedido_68378120d0e811.98570688_1748468000'),
(54, 8, 1, '2025-05-29 02:33:20', 6, 'pedido_68378120d0e811.98570688_1748468000'),
(55, 9, 1, '2025-05-29 02:33:20', 6, 'pedido_68378120d0e811.98570688_1748468000'),
(56, 12, 2, '2025-05-29 02:49:41', 10, 'pedido_683784f5a87365.74783435_1748468981');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto`
--

CREATE TABLE `produto` (
  `id` int(11) UNSIGNED NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descricao` varchar(80) NOT NULL,
  `valor` varchar(35) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `imagem` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Despejando dados para a tabela `produto`
--

INSERT INTO `produto` (`id`, `nome`, `descricao`, `valor`, `categoria_id`, `imagem`) VALUES
(12, 'Computador', 'Computador Gamer ', '5350', 2, 'prod_683784487b72e.png'),
(13, 'Memória Ram', 'Memória ram ddr4 8gb', '150', 4, 'prod_683784ab69892.jpg');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_produto_categoria` (`categoria_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de tabela `produto`
--
ALTER TABLE `produto`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `produto`
--
ALTER TABLE `produto`
  ADD CONSTRAINT `fk_produto_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
