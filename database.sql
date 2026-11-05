CREATE DATABASE IF NOT EXISTS petcare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE petcare;

CREATE TABLE tutores (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(120) NOT NULL,
 cpf VARCHAR(14),
 telefone VARCHAR(20) NOT NULL,
 email VARCHAR(120),
 endereco VARCHAR(180),
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE animais (
 id INT AUTO_INCREMENT PRIMARY KEY,
 tutor_id INT NOT NULL,
 nome VARCHAR(80) NOT NULL,
 especie VARCHAR(30) DEFAULT 'Cão',
 raca VARCHAR(80),
 cor VARCHAR(50),
 porte ENUM('Pequeno','Médio','Grande') DEFAULT 'Médio',
 sexo ENUM('Macho','Fêmea') DEFAULT 'Macho',
 observacoes TEXT,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (tutor_id) REFERENCES tutores(id) ON DELETE CASCADE
);

CREATE TABLE planos (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(100) NOT NULL,
 descricao TEXT,
 valor DECIMAL(10,2) NOT NULL DEFAULT 0,
 quantidade_servicos INT NOT NULL DEFAULT 1,
 ativo TINYINT(1) DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE agendamentos (
 id INT AUTO_INCREMENT PRIMARY KEY,
 animal_id INT NOT NULL,
 servico ENUM('Banho','Tosa','Banho e Tosa') NOT NULL,
 data_agendamento DATE NOT NULL,
 hora TIME NOT NULL,
 status ENUM('Aguardando','Em atendimento','Pronto','Concluído','Cancelado') DEFAULT 'Aguardando',
 observacoes TEXT,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (animal_id) REFERENCES animais(id) ON DELETE CASCADE
);

CREATE TABLE atendimentos (
 id INT AUTO_INCREMENT PRIMARY KEY,
 agendamento_id INT NOT NULL UNIQUE,
 inicio DATETIME,
 fim DATETIME,
 observacoes TEXT,
 valor DECIMAL(10,2) DEFAULT 0,
 FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE CASCADE
);

INSERT INTO tutores (nome,cpf,telefone,email,endereco) VALUES
('João Silva','000.000.000-00','(66) 99999-1111','joao@email.com','Barra do Garças - MT'),
('Mariana Costa','111.111.111-11','(66) 99999-2222','mariana@email.com','Barra do Garças - MT'),
('Carlos Souza','222.222.222-22','(66) 99999-3333','carlos@email.com','Barra do Garças - MT'),
('Ana Paula','333.333.333-33','(66) 99999-4444','ana@email.com','Barra do Garças - MT');

INSERT INTO animais (tutor_id,nome,especie,raca,cor,porte,sexo) VALUES
(1,'Thor','Cão','Golden Retriever','Dourado','Grande','Macho'),
(2,'Luna','Cão','Poodle','Branca','Pequeno','Fêmea'),
(3,'Mel','Cão','Shih-tzu','Caramelo','Pequeno','Fêmea'),
(4,'Max','Cão','Labrador','Preto','Grande','Macho');

INSERT INTO planos (nome,descricao,valor,quantidade_servicos) VALUES
('Plano Básico','4 banhos por mês',99.90,4),
('Plano Completo','4 banhos + 2 tosas por mês',159.90,6),
('Plano Premium','Serviços ilimitados conforme regras do estabelecimento',229.90,10);

INSERT INTO agendamentos (animal_id,servico,data_agendamento,hora,status) VALUES
(1,'Banho e Tosa',CURDATE(),'08:00:00','Aguardando'),
(2,'Tosa',CURDATE(),'09:00:00','Em atendimento'),
(3,'Banho',CURDATE(),'10:30:00','Pronto'),
(4,'Banho e Tosa',CURDATE(),'13:00:00','Aguardando');
