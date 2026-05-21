<?php

function conectarBanco(array $dbConfig): PDO
{
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['db']};charset=utf8mb4",
        $dbConfig['user'],
        $dbConfig['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdo->exec("SET SESSION wait_timeout = 28800, interactive_timeout = 28800");
    return $pdo;
}

function setupSchema(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS vagas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        vaga_id_externo VARCHAR(255) NOT NULL UNIQUE,
        titulo VARCHAR(255) NOT NULL,
        empresa VARCHAR(255) NOT NULL,
        localizacao VARCHAR(255),
        modelo_trabalho VARCHAR(50),
        url_vaga TEXT,
        descricao TEXT,
        resumo TEXT,
        data_coleta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        publicado_em DATETIME,
        status VARCHAR(20) DEFAULT 'ativa'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("ALTER TABLE vagas MODIFY COLUMN status VARCHAR(20) DEFAULT 'ativa'");

    $colunas = $pdo->query("SHOW COLUMNS FROM vagas")->fetchAll(PDO::FETCH_ASSOC);
    $nomesColunas = array_column($colunas, 'Field');

    if (!in_array('resumo', $nomesColunas)) {
        $pdo->exec("ALTER TABLE vagas ADD COLUMN resumo TEXT AFTER descricao");
    }
    if (!in_array('modelo_trabalho', $nomesColunas)) {
        $pdo->exec("ALTER TABLE vagas ADD COLUMN modelo_trabalho VARCHAR(50) AFTER localizacao");
    }
    if (!in_array('publicado_em', $nomesColunas)) {
        $pdo->exec("ALTER TABLE vagas ADD COLUMN publicado_em DATETIME AFTER data_coleta");
    }
    if (!in_array('origem', $nomesColunas)) {
        $pdo->exec("ALTER TABLE vagas ADD COLUMN origem VARCHAR(20) DEFAULT 'nacional' AFTER status");
    }
    if (!in_array('area', $nomesColunas)) {
        $pdo->exec("ALTER TABLE vagas ADD COLUMN area VARCHAR(20) DEFAULT NULL AFTER origem");
    }

    $indexExists = $pdo->query("SHOW INDEX FROM vagas WHERE Key_name = 'idx_busca'")->fetchAll();
    if (empty($indexExists)) {
        $pdo->exec("ALTER TABLE vagas ADD FULLTEXT INDEX idx_busca (titulo, empresa, localizacao, descricao, resumo)");
    }

    $idxTitulo = $pdo->query("SHOW INDEX FROM vagas WHERE Key_name = 'idx_busca_titulo'")->fetchAll();
    if (empty($idxTitulo)) {
        $pdo->exec("ALTER TABLE vagas ADD FULLTEXT INDEX idx_busca_titulo (titulo)");
    }

    $idxDescricao = $pdo->query("SHOW INDEX FROM vagas WHERE Key_name = 'idx_busca_descricao'")->fetchAll();
    if (empty($idxDescricao)) {
        $pdo->exec("ALTER TABLE vagas ADD FULLTEXT INDEX idx_busca_descricao (descricao, resumo)");
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS newsletters (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        area VARCHAR(20) DEFAULT NULL,
        origem VARCHAR(20) NOT NULL DEFAULT 'brasil',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $colNews = $pdo->query("SHOW COLUMNS FROM newsletters")->fetchAll(PDO::FETCH_ASSOC);
    $nomesColNews = array_column($colNews, 'Field');
    if (!in_array('area', $nomesColNews)) {
        $pdo->exec("ALTER TABLE newsletters ADD COLUMN area VARCHAR(20) DEFAULT NULL AFTER email");
    }
    if (!in_array('origem', $nomesColNews)) {
        $pdo->exec("ALTER TABLE newsletters ADD COLUMN origem VARCHAR(20) NOT NULL DEFAULT 'brasil' AFTER area");
    }
}
