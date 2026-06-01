-- =============================================
-- ALPHA CONEXÃO - Sistema de Consulta CNPJ
-- Banco de Dados - Schema v1.0
-- =============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "-03:00";

CREATE DATABASE IF NOT EXISTS `alpha_conexao` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `alpha_conexao`;

-- =============================================
-- Tabela: usuarios
-- =============================================
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `status` enum('pendente','ativo','bloqueado') DEFAULT 'pendente',
  `role` enum('admin','usuario') DEFAULT 'usuario',
  `remember_token` varchar(100) DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `ultimo_login` datetime DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Administrador principal
INSERT INTO `usuarios` (`nome`, `email`, `senha`, `status`, `role`) VALUES
('Tácio Cleiner', 'tacio.cleiner19@gmail.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ativo', 'admin');
-- Senha padrão: password (trocar após primeiro acesso)

-- =============================================
-- Tabela: permissoes
-- =============================================
CREATE TABLE `permissoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `consultar_cnpj` tinyint(1) DEFAULT 1,
  `ver_historico` tinyint(1) DEFAULT 1,
  `ver_logs` tinyint(1) DEFAULT 0,
  `gerenciar_usuarios` tinyint(1) DEFAULT 0,
  `acessar_configuracoes` tinyint(1) DEFAULT 0,
  `exportar_dados` tinyint(1) DEFAULT 1,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `fk_permissoes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permissões do admin
INSERT INTO `permissoes` (`usuario_id`, `consultar_cnpj`, `ver_historico`, `ver_logs`, `gerenciar_usuarios`, `acessar_configuracoes`, `exportar_dados`) VALUES
(1, 1, 1, 1, 1, 1, 1);

-- =============================================
-- Tabela: consultas
-- =============================================
CREATE TABLE `consultas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `cnpj` varchar(18) NOT NULL,
  `razao_social` varchar(200) DEFAULT NULL,
  `nome_fantasia` varchar(200) DEFAULT NULL,
  `situacao` varchar(50) DEFAULT NULL,
  `dados_json` longtext DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_cnpj` (`cnpj`),
  KEY `idx_criado_em` (`criado_em`),
  CONSTRAINT `fk_consultas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabela: logs
-- =============================================
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `acao` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_acao` (`acao`),
  KEY `idx_criado_em` (`criado_em`),
  CONSTRAINT `fk_logs_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabela: configuracoes
-- =============================================
CREATE TABLE `configuracoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `atualizado_em` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `configuracoes` (`chave`, `valor`, `descricao`) VALUES
('site_nome', 'Alpha Conexão', 'Nome do sistema'),
('site_logo', '', 'URL do logotipo'),
('cor_primaria', '#0F172A', 'Cor primária do sistema'),
('cor_secundaria', '#2563EB', 'Cor secundária do sistema'),
('smtp_host', '', 'Servidor SMTP'),
('smtp_porta', '587', 'Porta SMTP'),
('smtp_usuario', '', 'Usuário SMTP'),
('smtp_senha', '', 'Senha SMTP'),
('smtp_from', '', 'E-mail remetente'),
('smtp_from_nome', 'Alpha Conexão', 'Nome remetente'),
('api_cnpj_url', 'https://publica.cnpj.ws/cnpj/', 'URL da API Open CNPJ'),
('rate_limit_consultas', '60', 'Máximo de consultas por hora por usuário'),
('sessao_tempo', '3600', 'Tempo de sessão em segundos'),
('notif_email_admin', 'tacio.cleiner19@gmail.com', 'E-mail para notificações do admin');

COMMIT;
