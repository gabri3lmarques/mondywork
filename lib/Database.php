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
        status VARCHAR(20) DEFAULT 'inativa',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("ALTER TABLE vagas MODIFY COLUMN status VARCHAR(20) DEFAULT 'inativa'");

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
    if (!in_array('created_at', $nomesColunas)) {
        $pdo->exec("ALTER TABLE vagas ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER area");
        $pdo->exec("UPDATE vagas SET created_at = '2020-01-01 00:00:00'");
    }

    $indexExists = $pdo->query("SHOW INDEX FROM vagas WHERE Key_name = 'idx_busca'")->fetchAll();
    if (empty($indexExists)) {
        $pdo->exec("ALTER TABLE vagas ADD FULLTEXT INDEX idx_busca (titulo, empresa, localizacao, descricao, resumo)");
    }

    $idxTituloExists = $pdo->query("SHOW INDEX FROM vagas WHERE Key_name = 'idx_titulo'")->fetchAll();
    if (empty($idxTituloExists)) {
        $pdo->exec("ALTER TABLE vagas ADD FULLTEXT INDEX idx_titulo (titulo)");
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS categorias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(30) NOT NULL UNIQUE,
        nome_pt VARCHAR(50) NOT NULL,
        nome_en VARCHAR(50) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS vaga_categorias (
        vaga_id INT NOT NULL,
        categoria_id INT NOT NULL,
        PRIMARY KEY (vaga_id, categoria_id),
        FOREIGN KEY (vaga_id) REFERENCES vagas(id) ON DELETE CASCADE,
        FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $catCount = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
    if ((int)$catCount === 0) {
        $pdo->exec("INSERT INTO categorias (slug, nome_pt, nome_en) VALUES
            ('desenvolvimento', 'Desenvolvimento', 'Development'),
            ('engenharia', 'Engenharia', 'Engineering'),
            ('dados', 'Dados', 'Data'),
            ('ia', 'IA', 'AI'),
            ('design', 'Design', 'Design'),
            ('marketing-digital', 'Marketing Digital', 'Digital Marketing'),
            ('conteudo', 'Conteúdo', 'Content'),
            ('produto', 'Produto', 'Product'),
            ('agil', 'Ágil', 'Agile'),
            ('gestao-projetos', 'Gestão Projetos', 'Project Management'),
            ('comercial', 'Comercial', 'Sales'),
            ('customer-success', 'Customer Success', 'Customer Success'),
            ('suporte-tecnico', 'Suporte Técnico', 'Technical Support'),
            ('qa-testes', 'QA/Testes', 'QA/Testing'),
            ('infra-devops', 'Infra/DevOps', 'Infra/DevOps'),
            ('sem-categoria', 'Sem Categoria', 'Uncategorized'),
            ('financeiro', 'Financeiro', 'Finance'),
            ('administrativo', 'Administrativo', 'Administrative'),
            ('juridico', 'Jurídico', 'Legal')");
    } else {
        $novasCats = [
            ['financeiro', 'Financeiro', 'Finance'],
            ['administrativo', 'Administrativo', 'Administrative'],
            ['juridico', 'Jurídico', 'Legal'],
        ];
        foreach ($novasCats as $nc) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO categorias (slug, nome_pt, nome_en) VALUES (:slug, :pt, :en)");
            $stmt->execute([':slug' => $nc[0], ':pt' => $nc[1], ':en' => $nc[2]]);
        }
    }

    $migrated = $pdo->query("SHOW COLUMNS FROM vagas WHERE Field = 'area_migrada'")->fetchAll();
    if (empty($migrated)) {
        $pdo->exec("INSERT INTO vaga_categorias (vaga_id, categoria_id)
            SELECT v.id, c.id FROM vagas v
            JOIN categorias c ON c.slug = CASE v.area
                WHEN 'dev' THEN 'desenvolvimento'
                WHEN 'ia' THEN 'ia'
                WHEN 'marketing' THEN 'marketing-digital'
                WHEN 'social-media' THEN 'conteudo'
                WHEN 'agile' THEN 'agil'
                WHEN 'gestao' THEN 'gestao-projetos'
                WHEN 'vendas' THEN 'comercial'
                WHEN 'suporte' THEN 'suporte-tecnico'
                WHEN 'qa' THEN 'qa-testes'
                WHEN 'infra' THEN 'infra-devops'
                ELSE v.area
            END
            WHERE v.area IS NOT NULL AND v.area != ''
            ON DUPLICATE KEY UPDATE vaga_categorias.vaga_id = vaga_categorias.vaga_id");
        $pdo->exec("ALTER TABLE vagas ADD COLUMN area_migrada TINYINT(1) DEFAULT 1 AFTER area");
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

    $pdo->exec("CREATE TABLE IF NOT EXISTS blog_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(255) NOT NULL UNIQUE,
        title_pt VARCHAR(255) NOT NULL,
        title_en VARCHAR(255) NOT NULL,
        content_pt TEXT NOT NULL,
        content_en TEXT NOT NULL,
        excerpt_pt TEXT,
        excerpt_en TEXT,
        image VARCHAR(500),
        author VARCHAR(100) DEFAULT 'Mondywork',
        published_at DATETIME,
        status VARCHAR(20) DEFAULT 'rascunho',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}
