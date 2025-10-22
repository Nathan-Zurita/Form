-- Script para criar a tabela de contatos seguindo o padrão especificado
-- Execute este script no MySQL Workbench

CREATE DATABASE IF NOT EXISTS sindppenal_permutacao 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sindppenal_permutacao;

-- Remover tabelas anteriores se existirem
DROP TABLE IF EXISTS contatos;
DROP TABLE IF EXISTS administradores;
DROP TABLE IF EXISTS unidades;

-- Criar tabela de unidades
CREATE TABLE unidades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(10) NOT NULL,
    nome VARCHAR(255) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir unidades do sistema
INSERT INTO unidades (codigo, nome, cidade) VALUES
('UCTP', 'UCTP', 'Cariacica'),
('CPFC', 'CPFC', 'Cariacica'),
('PSC', 'PSC', 'Cariacica'),
('PAES', 'PAES', 'Viana'),
('USSP', 'USSP', 'Viana'),
('PSME I', 'PSME I', 'Viana'),
('PSME II', 'PSME II', 'Viana'),
('PSMA I', 'PSMA I', 'Viana'),
('PSMA II', 'PSMA II', 'Viana'),
('CASCUVV', 'CASCUVV', 'Vila Velha'),
('PEVV I', 'PEVV I', 'Vila Velha'),
('PEVV II', 'PEVV II', 'Vila Velha'),
('PEVV III', 'PEVV III', 'Vila Velha'),
('PEVV IV', 'PEVV IV', 'Vila Velha'),
('PEVV V', 'PEVV V', 'Vila Velha'),
('CDPS', 'CDPS', 'Serra'),
('CDPG', 'CDPG', 'Guarapari'),
('PRL', 'PRL', 'Linhares'),
('PRSM', 'PRSM', 'São Mateus'),
('PRC', 'PRC', 'Colatina'),
('PRCI', 'PRCI', 'Cachoeiro de Itapemirim'),
('CDPA', 'CDPA', 'Aracruz'),
('PRBSF', 'PRBSF', 'Barra de São Francisco'),
('CDPAC', 'CDPAC', 'Afonso Cláudio'),
('CDPM', 'CDPM', 'Marataízes'),
('PRNV', 'PRNV', 'Nova Venécia'),
('CPFCOL', 'CPFCOL', 'Colatina');

-- Criar tabela seguindo o padrão exato especificado
CREATE TABLE contatos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255),
    num_funcional VARCHAR(20),
    telefone VARCHAR(255),
    origem VARCHAR(255),
    destino VARCHAR(255),
    created DATETIME,
    updated DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar tabela de administradores
CREATE TABLE administradores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nome_completo VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    ativo BOOLEAN DEFAULT TRUE,
    ultimo_login DATETIME NULL,
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir administrador padrão (usuário: admin, senha: admin123)
-- A senha será atualizada via script PHP para garantir hash correto

-- Inserir alguns dados de teste (opcional)
-- INSERT INTO contatos (nome, num_funcional, telefone, origem, destino, created, updated) 
-- VALUES ('João Silva', '12345', '(11) 99999-9999', 'Unidade Central', 'Unidade A, Unidade B', NOW(), NOW());