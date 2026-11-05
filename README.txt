PETCARE - Sistema de Banho, Tosa e Planos
==============================================

Tecnologias:
- PHP 8+
- MySQL/MariaDB
- HTML/CSS/JavaScript
- PDO

INSTALAÇÃO NO WAMP
1. Copie a pasta PetCare para C:\wamp64\www\
2. Abra o WAMP e deixe Apache e MySQL verdes.
3. Abra http://localhost/phpmyadmin
4. Importe o arquivo database.sql (ou execute seu conteúdo).
5. Acesse: http://localhost/PetCare/

BANCO:
- Banco: petcare
- Usuário padrão WAMP: root
- Senha padrão: vazia
Se o seu MySQL tiver senha, altere config/db.php.

FUNCIONALIDADES:
- Dashboard com dados reais do banco
- CRUD de tutores
- CRUD de animais
- CRUD de agendamentos
- Fila de atendimento com alteração de status
- CRUD de planos
- Banco relacional com chaves estrangeiras
- Interface responsiva
