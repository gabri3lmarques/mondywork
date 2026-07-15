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
    if (!in_array('revisada_em', $nomesColunas)) {
        $pdo->exec("ALTER TABLE vagas ADD COLUMN revisada_em DATETIME DEFAULT NULL AFTER created_at");
    }
    if (!in_array('descricao_reescrita', $nomesColunas)) {
        $pdo->exec("ALTER TABLE vagas ADD COLUMN descricao_reescrita TEXT DEFAULT NULL AFTER resumo");
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
            ['talent-acquisition', 'Talent Acquisition', 'Talent Acquisition'],
            ['tech-recruiter', 'Tech Recruiter', 'Tech Recruiter'],
            ['seguranca-informacao', 'Segurança da Informação', 'Information Security'],
            ['desenvolvedor-mobile', 'Desenvolvedor Mobile', 'Mobile Developer'],
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
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        excerpt TEXT,
        image VARCHAR(500),
        author VARCHAR(100) DEFAULT 'Mondywork',
        published_at DATETIME,
        status VARCHAR(20) DEFAULT 'rascunho',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $blogCols = $pdo->query("SHOW COLUMNS FROM blog_posts")->fetchAll(PDO::FETCH_ASSOC);
    $blogColNames = array_column($blogCols, 'Field');
    if (in_array('title_pt', $blogColNames)) {
        $pdo->exec("ALTER TABLE blog_posts ADD COLUMN title VARCHAR(255) NOT NULL AFTER slug");
        $pdo->exec("ALTER TABLE blog_posts ADD COLUMN content TEXT NOT NULL AFTER title");
        $pdo->exec("ALTER TABLE blog_posts ADD COLUMN excerpt TEXT AFTER content");
        $pdo->exec("UPDATE blog_posts SET title = title_pt, content = content_pt, excerpt = COALESCE(excerpt_pt, '')");
        $pdo->exec("ALTER TABLE blog_posts DROP COLUMN title_pt");
        $pdo->exec("ALTER TABLE blog_posts DROP COLUMN title_en");
        $pdo->exec("ALTER TABLE blog_posts DROP COLUMN content_pt");
        $pdo->exec("ALTER TABLE blog_posts DROP COLUMN content_en");
        $pdo->exec("ALTER TABLE blog_posts DROP COLUMN excerpt_pt");
        $pdo->exec("ALTER TABLE blog_posts DROP COLUMN excerpt_en");
    }

    if (!in_array('image', $blogColNames)) {
        $pdo->exec("ALTER TABLE blog_posts ADD COLUMN image VARCHAR(500) DEFAULT NULL AFTER excerpt");
    }
    if (!in_array('categoria', $blogColNames)) {
        $pdo->exec("ALTER TABLE blog_posts ADD COLUMN categoria VARCHAR(100) DEFAULT NULL AFTER image");
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS categoria_conteudo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        categoria_id INT NOT NULL UNIQUE,
        titulo_pt VARCHAR(255) NOT NULL,
        titulo_en VARCHAR(255) NOT NULL,
        conteudo_pt TEXT NOT NULL,
        conteudo_en TEXT NOT NULL,
        FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $catContCount = $pdo->query("SELECT COUNT(*) FROM categoria_conteudo")->fetchColumn();
    if ((int)$catContCount === 0) {
        $conteudos = [
            ['desenvolvimento',
             'Sobre a área de Desenvolvimento',
             'About Software Development',
             '<p>A área de Desenvolvimento de Software é uma das mais dinâmicas e em constante evolução no mercado de trabalho. Profissionais dessa área são responsáveis por criar, manter e otimizar aplicações web, mobile e desktop que impactam milhões de usuários diariamente.</p><p>As principais linguagens e frameworks incluem JavaScript (React, Node.js, Vue.js), Python (Django, Flask), Java (Spring), PHP (Laravel) e TypeScript. A demanda por desenvolvedores full-stack continua crescendo, especialmente em empresas de tecnologia e startups.</p><p>Salários variam de R$ 3.000 (júnior) a R$ 20.000+ (sênior), com oportunidades crescentes para trabalho remoto e freelance internacional.</p>',
             '<p>Software Development is one of the most dynamic and constantly evolving fields in the job market. Professionals in this area are responsible for creating, maintaining, and optimizing web, mobile, and desktop applications that impact millions of users daily.</p><p>Key languages and frameworks include JavaScript (React, Node.js, Vue.js), Python (Django, Flask), Java (Spring), PHP (Laravel), and TypeScript. Demand for full-stack developers continues to grow, especially in tech companies and startups.</p><p>Salaries range from entry-level to senior positions, with growing opportunities for remote work and international freelancing.</p>'],
            ['design',
             'Sobre a área de Design',
             'About Design',
             '<p>A área de Design, especialmente UX/UI e Product Design, vivenciou um crescimento significativo nos últimos anos. Com a digitalização acelerada dos negócios, a demanda por profissionais que consigam criar experiências digitais intuitivas e agradáveis nunca foi tão alta.</p><p>Os principais skills incluem Figma, Sketch, Adobe XD, pesquisa com usuários, design thinking, prototipação e system design. Designers de produto são cada vez mais valorizados por seu impacto direto nos resultados de negócio.</p><p>O trabalho remoto abriu portas para designers brasileiros trabalharem para empresas globais, com salários competitivos em dólares e euros.</p>',
             '<p>The Design field, especially UX/UI and Product Design, has experienced significant growth in recent years. With accelerated business digitization, the demand for professionals who can create intuitive and pleasant digital experiences has never been higher.</p><p>Key skills include Figma, Sketch, Adobe XD, user research, design thinking, prototyping, and system design. Product designers are increasingly valued for their direct impact on business results.</p><p>Remote work has opened doors for Brazilian designers to work for global companies, with competitive salaries in dollars and euros.</p>'],
            ['marketing-digital',
             'Sobre a área de Marketing Digital',
             'About Digital Marketing',
             '<p>O Marketing Digital é uma área em permanente expansão, impulsionada pelo crescimento do e-commerce e pela necessidade de presença digital forte. Profissionais de marketing dominam ferramentas como Google Ads, Meta Ads, HubSpot, Google Analytics e plataformas de automação.</p><p>As especializações mais procuradas incluem Growth Marketing, Performance, SEO, Marketing de Conteúdo e Growth Hacking. A combinação de criatividade com análise de dados é o diferencial mais valorizado no mercado.</p><p>O mercado oferece oportunidades tanto em agências quanto em empresas de tecnologia, com salários competitivos e possibilidade de trabalho remoto.</p>',
             '<p>Digital Marketing is a constantly expanding field, driven by e-commerce growth and the need for a strong digital presence. Marketing professionals master tools like Google Ads, Meta Ads, HubSpot, Google Analytics, and automation platforms.</p><p>Most sought-after specializations include Growth Marketing, Performance, SEO, Content Marketing, and Growth Hacking. The combination of creativity with data analysis is the most valued differentiator in the market.</p><p>The market offers opportunities in both agencies and technology companies, with competitive salaries and remote work possibilities.</p>'],
            ['produto',
             'Sobre a área de Produto',
             'About Product Management',
             '<p>Product Management é uma das áreas mais strategicamente relevantes nas organizações de tecnologia. O Product Manager é responsável por definir a visão do produto, priorizar funcionalidades e coordenar equipes multidisciplinares para entregar valor ao usuário.</p><p>As habilidades essenciais incluem pensamento estratégico, análise de dados, comunicação, liderança e conhecimento técnico. Ferramentas como Jira, Confluence, Miro e analytics platforms são fundamentais no dia a dia.</p><p>Salários para PMs no Brasil variam de R$ 8.000 (júnior) a R$ 35.000+ (sênior em big techs), com oportunidades crescentes para trabalho remoto internacional.</p>',
             '<p>Product Management is one of the most strategically relevant areas in technology organizations. The Product Manager is responsible for defining product vision, prioritizing features, and coordinating multidisciplinary teams to deliver value to users.</p><p>Essential skills include strategic thinking, data analysis, communication, leadership, and technical knowledge. Tools like Jira, Confluence, Miro, and analytics platforms are fundamental in daily work.</p><p>Salaries for PMs range from entry-level to senior positions at major tech companies, with growing opportunities for international remote work.</p>'],
            ['dados',
             'Sobre a área de Dados',
             'About Data',
             '<p>A área de Dados passou por uma transformação radical com a ascensão da Inteligência Artificial Generativa. Profissionais de dados são fundamentais para tomada de decisão baseada em evidências em todas as indústrias.</p><p>As principais especializações incluem Data Engineering, Data Science, Business Intelligence, Machine Learning Engineering e Analytics. Ferramentas como SQL, Python, Spark, dbt e plataformas de cloud (AWS, GCP, Azure) são essenciais.</p><p>O mercado de dados continua com alta demanda e salários entre os mais competitivos do setor de tecnologia, com muitas oportunidades para trabalho remoto.</p>',
             '<p>The Data field has undergone a radical transformation with the rise of Generative AI. Data professionals are fundamental for evidence-based decision-making across all industries.</p><p>Key specializations include Data Engineering, Data Science, Business Intelligence, Machine Learning Engineering, and Analytics. Tools like SQL, Python, Spark, dbt, and cloud platforms (AWS, GCP, Azure) are essential.</p><p>The data market continues with high demand and salaries among the most competitive in the technology sector, with many remote work opportunities.</p>'],
            ['engenharia',
             'Sobre a área de Engenharia',
             'About Engineering',
             '<p>A Engenharia de Software vai além do desenvolvimento tradicional, focando em escalabilidade, performance e arquitetura de sistemas. Engenheiros de software são responsáveis por projetar infraestruturas que suportam milhões de usuários simultâneos.</p><p>As habilidades incluem arquitetura de microsserviços, DevOps, cloud computing, segurança de aplicações e otimização de performance. Conhecimento em containerização (Docker, Kubernetes) e CI/CD é cada vez mais exigido.</p><p>Engenheiros de software sênior são profissionais raros e muito bem remunerados, com oportunidades em grandes tech companies globais.</p>',
             '<p>Software Engineering goes beyond traditional development, focusing on scalability, performance, and system architecture. Software engineers are responsible for designing infrastructures that support millions of simultaneous users.</p><p>Skills include microservices architecture, DevOps, cloud computing, application security, and performance optimization. Knowledge of containerization (Docker, Kubernetes) and CI/CD is increasingly required.</p><p>Senior software engineers are rare and highly compensated professionals, with opportunities at major global tech companies.</p>'],
            ['conteudo',
             'Sobre a área de Conteúdo',
             'About Content',
             '<p>A área de Conteúdo e Social Media é essencial para construir presença digital e engajamento com audiências. Profissionais criam estratégias de conteúdo, gerenciam redes sociais e desenvolvem narrativas de marca impactantes.</p><p>As principais habilidades incluem copywriting, storytelling, gestão de comunidades, análise de métricas, produção audiovisual e conhecimento de algoritmos de cada plataforma.</p><p>Com o crescimento do marketing de influenciamento e do commerce via social, essa área continua gerando novas oportunidades de carreira.</p>',
             '<p>The Content and Social Media area is essential for building digital presence and audience engagement. Professionals create content strategies, manage social networks, and develop impactful brand narratives.</p><p>Key skills include copywriting, storytelling, community management, metrics analysis, audiovisual production, and knowledge of each platform algorithms.</p><p>With the growth of influencer marketing and social commerce, this area continues to generate new career opportunities.</p>'],
            ['ia',
             'Sobre a área de Inteligência Artificial',
             'About Artificial Intelligence',
             '<p>A área de Inteligência Artificial é atualmente a que mais cresce no mercado de tecnologia. A revolução dos modelos generativos (GPT, Claude, Gemini) criou uma demanda massiva por profissionais especializados em IA.</p><p>As principais áreas de atuação incluem Machine Learning Engineering, MLOps, Prompt Engineering, AI Research e Applied AI. Python, TensorFlow, PyTorch e conhecimento de LLMs são skills essenciais.</p><p>Salários na área de IA são os mais altos do setor de tecnologia, com muitas oportunidades de trabalho remoto para empresas internacionais.</p>',
             '<p>Artificial Intelligence is currently the fastest-growing field in the technology market. The revolution in generative models (GPT, Claude, Gemini) has created massive demand for AI-specialized professionals.</p><p>Key areas of practice include Machine Learning Engineering, MLOps, Prompt Engineering, AI Research, and Applied AI. Python, TensorFlow, PyTorch, and LLM knowledge are essential skills.</p><p>AI salaries are the highest in the technology sector, with many remote work opportunities at international companies.</p>'],
            ['agil',
             'Sobre a área de Agilidade',
             'About Agile',
             '<p>A área de Agilidade e Transformação Digital é fundamental para organizações que buscam eficiência e adaptação rápida. Profissionais de agile facilitam processos, eliminam gargalos e promovem cultura de melhoria contínua.</p><p>As principais certificações incluem CSM, PSM, SAFe, ICP e Kanban. Conhecimento de Scrum, Kanban, XP e frameworks ágeis é essencial, assim como soft skills de liderança e facilitação.</p><p>Agile coaches e Scrum Masters sênior são muito valorizados, especialmente em empresas de tecnologia que adotam metodologias ágeis em escala.</p>',
             '<p>The Agile and Digital Transformation area is fundamental for organizations seeking efficiency and rapid adaptation. Agile professionals facilitate processes, eliminate bottlenecks, and promote a culture of continuous improvement.</p><p>Key certifications include CSM, PSM, SAFe, ICP, and Kanban. Knowledge of Scrum, Kanban, XP, and agile frameworks is essential, as are leadership and facilitation soft skills.</p><p>Senior Agile coaches and Scrum Masters are highly valued, especially in technology companies that adopt agile methodologies at scale.</p>'],
            ['gestao-projetos',
             'Sobre a área de Gestão de Projetos',
             'About Project Management',
             '<p>A Gestão de Projetos é essencial para garantir que iniciativas estratégicas sejam entregues no prazo, dentro do escopo e com qualidade. Profissionais de PM coordenam equipes, gerenciam riscos e comunicam stakeholders.</p><p>As principais metodologias incluem PMBOK, PRINCE2, Scrum e Kanban. Ferramentas como Jira, Asana, Monday e MS Project são amplamente utilizadas no dia a dia.</p><p>Certificações como PMP e PgMP são diferenciadores importantes no mercado, com demanda crescente em empresas de tecnologia e consultoria.</p>',
             '<p>Project Management is essential to ensure strategic initiatives are delivered on time, within scope, and with quality. PM professionals coordinate teams, manage risks, and communicate with stakeholders.</p><p>Key methodologies include PMBOK, PRINCE2, Scrum, and Kanban. Tools like Jira, Asana, Monday, and MS Project are widely used in daily work.</p><p>Certifications like PMP and PgMP are important differentiators in the market, with growing demand in technology and consulting companies.</p>'],
            ['comercial',
             'Sobre a área Comercial',
             'About Sales',
             '<p>A área Comercial é responsável por gerar receita e expandir a base de clientes. Profissionais de vendas B2B e B2C são fundamentais para o crescimento sustentável de qualquer organização.</p><p>As principais habilidades incluem prospecção, negociação, CRM (Salesforce, HubSpot), sales enablement e consultoria de valor. A abordagem consultiva e data-driven é cada vez mais valorizada.</p><p>Vendedores consultivos e Sales Managers sênior têm potencial de earning muito alto, com OTE (On-Target Earnings) que pode superar R$ 30.000 mensais em empresas de tecnologia.</p>',
             '<p>The Sales area is responsible for generating revenue and expanding the customer base. B2B and B2C sales professionals are fundamental for sustainable growth of any organization.</p><p>Key skills include prospecting, negotiation, CRM (Salesforce, HubSpot), sales enablement, and value consulting. The consultative and data-driven approach is increasingly valued.</p><p>Consultative sellers and senior Sales Managers have very high earning potential, with OTE (On-Target Earnings) that can exceed monthly salaries in technology companies.</p>'],
            ['customer-success',
             'Sobre a área de Customer Success',
             'About Customer Success',
             '<p>Customer Success é a área responsável por garantir que os clientes atinjam seus objetivos ao usar o produto ou serviço. É uma função estratégica para retenção, expansão e satisfação do cliente.</p><p>As principais habilidades incluem gestão de contas, análise de churn, NPS, onboarding, upsell e cross-sell. Conhecimento de CS tools como Gainsight, Totango e ChurnZero é diferencial.</p><p>O CS está se tornando cada vez mais estratégico em empresas SaaS, com profissionais atuando diretamente no crescimento da receita recorrente (MRR/ARR).</p>',
             '<p>Customer Success is the area responsible for ensuring clients achieve their goals when using the product or service. It is a strategic function for retention, expansion, and customer satisfaction.</p><p>Key skills include account management, churn analysis, NPS, onboarding, upsell, and cross-sell. Knowledge of CS tools like Gainsight, Totango, and ChurnZero is a differentiator.</p><p>CS is becoming increasingly strategic in SaaS companies, with professionals directly contributing to recurring revenue growth (MRR/ARR).</p>'],
            ['suporte-tecnico',
             'Sobre a área de Suporte Técnico',
             'About Technical Support',
             '<p>O Suporte Técnico é essencial para garantir a satisfação e retenção de clientes. Profissionais de suporte resolvem problemas técnicos, documentam soluções e identificam padrões que podem levar a melhorias no produto.</p><p>As principais habilidades incluem troubleshooting, atendimento ao cliente, documentação técnica, conhecimento de ITIL e ferramentas de ticketing (Zendesk, Freshdesk, Intercom).</p><p>O suporte técnico evoluiu de uma função reativa para proativa, com profissionais de alto nível atuando em Customer Engineering e Support Engineering.</p>',
             '<p>Technical Support is essential to ensure customer satisfaction and retention. Support professionals resolve technical issues, document solutions, and identify patterns that can lead to product improvements.</p><p>Key skills include troubleshooting, customer service, technical documentation, ITIL knowledge, and ticketing tools (Zendesk, Freshdesk, Intercom).</p><p>Technical support has evolved from a reactive to a proactive function, with high-level professionals working in Customer Engineering and Support Engineering.</p>'],
            ['qa-testes',
             'Sobre a área de QA e Testes',
             'About QA and Testing',
             '<p>QA e Testes de Software são fundamentais para garantir a qualidade e confiabilidade de aplicações. Profissionais de QA garantem que o produto entregue atenda aos requisitos e esteja livre de defeitos críticos.</p><p>As principais habilidades incluem testes manuais e automatizados, Selenium, Cypress, Playwright, Postman, JMeter e conhecimento de pipelines CI/CD. Testes de performance e segurança são diferenciais.</p><p>Com a adoção de DevOps e deploy contínuo, a demanda por QAs automatizadores e SDETs continua crescendo.</p>',
             '<p>QA and Software Testing are fundamental to ensure the quality and reliability of applications. QA professionals ensure that the delivered product meets requirements and is free of critical defects.</p><p>Key skills include manual and automated testing, Selenium, Cypress, Playwright, Postman, JMeter, and CI/CD pipeline knowledge. Performance and security testing are differentiators.</p><p>With the adoption of DevOps and continuous deployment, the demand for automation QAs and SDETs continues to grow.</p>'],
            ['infra-devops',
             'Sobre a área de Infraestrutura e DevOps',
             'About Infrastructure and DevOps',
             '<p>Infraestrutura e DevOps são responsáveis por criar, manter e otimizar ambientes de TI que suportam aplicações em escala. Essa área é fundamental para a confiabilidade e performance de sistemas.</p><p>As principais tecnologias incluem AWS, GCP, Azure, Docker, Kubernetes, Terraform, Ansible, CI/CD (GitHub Actions, GitLab CI, Jenkins) e monitoramento (Datadog, Grafana, Prometheus).</p><p>Engenheiros de DevOps e SREs são profissionais muito requisitados, com salários entre os mais altos do setor de tecnologia.</p>',
             '<p>Infrastructure and DevOps are responsible for creating, maintaining, and optimizing IT environments that support applications at scale. This area is fundamental for system reliability and performance.</p><p>Key technologies include AWS, GCP, Azure, Docker, Kubernetes, Terraform, Ansible, CI/CD (GitHub Actions, GitLab CI, Jenkins), and monitoring (Datadog, Grafana, Prometheus).</p><p>DevOps engineers and SREs are highly sought-after professionals, with salaries among the highest in the technology sector.</p>'],
            ['financeiro',
             'Sobre a área Financeira',
             'About Finance',
             '<p>A área Financeira em empresas de tecnologia combina conhecimento tradicional de finanças com ferramentas digitais avançadas. Profissionais de FP&A, controlling e finanças corporativas são essenciais para a saúde financeira da organização.</p><p>As principais habilidades incluem modelagem financeira, análise de indicadores (MRR, ARR, LTV, CAC), ERP (SAP, Oracle) e ferramentas de BI. Certificações como CFA e CPA-20 são diferenciais.</p><p>O setor financeiro oferece oportunidades estáveis com salários competitivos, especialmente em fintechs e grandes empresas de tecnologia.</p>',
             '<p>The Finance area in technology companies combines traditional financial knowledge with advanced digital tools. FP&A, controlling, and corporate finance professionals are essential for the organization\'s financial health.</p><p>Key skills include financial modeling, metrics analysis (MRR, ARR, LTV, CAC), ERP (SAP, Oracle), and BI tools. Certifications like CFA and CPA-20 are differentiators.</p><p>The financial sector offers stable opportunities with competitive salaries, especially in fintechs and large technology companies.</p>'],
            ['administrativo',
             'Sobre a área Administrativa',
             'About Administrative',
             '<p>A área Administrativa é responsável por garantir o funcionamento eficiente de todas as operações organizacionais. Profissionais administrativos gerenciam processos, recursos humanos, compras e facility management.</p><p>As principais habilidades incluem gestão de processos, Office 365, ERPs administrativos, compliance e gestão de pessoas. O conhecimento de ferramentas de automação e IA está se tornando cada vez mais relevante.</p><p>A digitalização dos processos administrativos tem criado novas oportunidades para profissionais que dominam tecnologia e gestão.</p>',
             '<p>The Administrative area is responsible for ensuring the efficient functioning of all organizational operations. Administrative professionals manage processes, human resources, procurement, and facility management.</p><p>Key skills include process management, Office 365, administrative ERPs, compliance, and people management. Knowledge of automation and AI tools is becoming increasingly relevant.</p><p>The digitization of administrative processes has created new opportunities for professionals who master technology and management.</p>'],
             ['juridico',
              'Sobre a área Jurídica',
              'About Legal',
              '<p>A área Jurídica em empresas de tecnologia lida com contratos, compliance, proteção de dados (LGPD/GDPR), propriedade intelectual e regulação digital. É uma área estratégica para empresas que operam em múltiplos mercados.</p><p>As principais especializações incluem direito digital, proteção de dados, contratos de tecnologia, compliance e propriedade intelectual. Conhecimento de regulações internacionais é diferencial.</p><p>Advogados especializados em direito digital e tech law estão entre os profissionais jurídicos mais bem remunerados do mercado.</p>',
              '<p>The Legal area in technology companies deals with contracts, compliance, data protection (LGPD/GDR), intellectual property, and digital regulation. It is a strategic area for companies operating in multiple markets.</p><p>Key specializations include digital law, data protection, technology contracts, compliance, and intellectual property. Knowledge of international regulations is a differentiator.</p><p>Lawyers specialized in digital law and tech law are among the highest-paid legal professionals in the market.</p>'],
             ['talent-acquisition',
              'Sobre a área de Talent Acquisition',
              'About Talent Acquisition',
              '<p>Talent Acquisition é a área estratégica responsável por atrair, selecionar e contratar os melhores profissionais para a organização. Diferente do recrutamento tradicional, o TA atua como parceiro estratégico do negócio, alinhando aquisição de talentos com os objetivos de longo prazo da empresa.</p><p>As principais habilidades incluem sourcing avançado, employer branding, análise de mercado de trabalho, gestão de pipeline de talentos e experiência do candidato (Candidate Experience). Ferramentas como LinkedIn Recruiter, ATS (Greenhouse, Lever, Ashby) e plataformas de assessments são essenciais.</p><p>Profissionais de TA em empresas de tecnologia são muito valorizados, especialmente aqueles que dominam tech sourcing, workforce planning e métricas de recrutamento como time-to-hire e cost-per-hire.</p>',
              '<p>Talent Acquisition is the strategic area responsible for attracting, selecting, and hiring the best professionals for the organization. Unlike traditional recruitment, TA acts as a strategic business partner, aligning talent acquisition with the company\'s long-term objectives.</p><p>Key skills include advanced sourcing, employer branding, labor market analysis, talent pipeline management, and candidate experience. Tools like LinkedIn Recruiter, ATS (Greenhouse, Lever, Ashby), and assessment platforms are essential.</p><p>TA professionals in technology companies are highly valued, especially those who master tech sourcing, workforce planning, and recruitment metrics like time-to-hire and cost-per-hire.</p>'],
             ['tech-recruiter',
              'Sobre a área de Tech Recruiter',
              'About Tech Recruiter',
              '<p>O Tech Recruiter é um profissional especializado em recrutar talentos de tecnologia, desde desenvolvedores até engenheiros de IA e profissionais de DevOps. Ele combina conhecimento técnico com habilidades de recrutamento para avaliar e atrair candidatos altamente qualificados.</p><p>As principais habilidades incluem technical screening, análise de perfis técnicos (GitHub, portfólios, blogs), conhecimento de stacks e arquiteturas de software, networking em comunidades tech e eventos. Domínio de ferramentas como LinkedIn Recruiter, Gem, Ashby e plataformas de teste técnico é diferencial.</p><p>Tech Recruiters são profissionais escassos e muito bem remunerados, especialmente aqueles que conseguem mapear e acessar talentos passivos em mercados competitivos como IA, engenharia de dados e cloud computing.</p>',
         '<p>The Tech Recruiter is a professional specialized in recruiting technology talent, from developers to AI engineers and DevOps professionals. They combine technical knowledge with recruitment skills to evaluate and attract highly qualified candidates.</p><p>Key skills include technical screening, analysis of technical profiles (GitHub, portfolios, blogs), knowledge of software stacks and architectures, networking in tech communities and events. Proficiency with tools like LinkedIn Recruiter, Gem, Ashby, and technical assessment platforms is a differentiator.</p><p>Tech Recruiters are scarce and highly paid professionals, especially those who can map and access passive talent in competitive markets like AI, data engineering, and cloud computing.</p>'],
        ['seguranca-informacao',
         'Sobre a área de Segurança da Informação',
         'About Information Security',
         '<p>A área de Segurança da Informação é uma das mais estratégicas e requisitadas no mercado de tecnologia. Com o aumento dos ataques cibernéticos, roubo de dados e regulações como LGPD e GDPR, empresas de todos os portes investem pesadamente em profissionais que protejam seus ativos digitais.</p><p>As principais especializações incluem Segurança de Redes, Segurança em Cloud (AWS, Azure, GCP), Segurança Ofensiva (Penetration Testing, Red Team), Segurança Defensiva (SOC, Blue Team), AppSec e Governança de Segurança. Ferramentas como SIEM (Splunk, QRadar), firewalls, EDR e plataformas de Vulnerability Management são essenciais.</p><p>Certificações como CISSP, CEH, OSCP, CompTIA Security+ e AWS Security Specialty são diferenciadores importantes. Profissionais de segurança da informação estão entre os mais bem remunerados do setor, com demanda crescente especialmente em fintechs, healthtechs e empresas de grande porte.</p>',
          '<p>The Information Security area is one of the most strategic and in-demand fields in the technology market. With the rise of cyberattacks, data breaches, and regulations like LGPD and GDPR, companies of all sizes invest heavily in professionals who can protect their digital assets.</p><p>Key specializations include Network Security, Cloud Security (AWS, Azure, GCP), Offensive Security (Penetration Testing, Red Team), Defensive Security (SOC, Blue Team), AppSec, and Security Governance. Tools like SIEM (Splunk, QRadar), firewalls, EDR, and Vulnerability Management platforms are essential.</p><p>Certifications like CISSP, CEH, OSCP, CompTIA Security+, and AWS Security Specialty are important differentiators. Information security professionals are among the highest-paid in the sector, with growing demand especially in fintechs, healthtechs, and large enterprises.</p>'],
         ['desenvolvedor-mobile',
          'Sobre a área de Desenvolvimento Mobile',
          'About Mobile Development',
          '<p>O Desenvolvimento Mobile é uma das áreas mais dinâmicas e em constante evolução do mercado de tecnologia. Com bilhões de dispositivos smartphones no mundo, a demanda por desenvolvedores mobile qualificados continua crescendo exponencialmente.</p><p>As principais stacks incluem Flutter (Dart), React Native (JavaScript/TypeScript), Kotlin (Android nativo), Swift (iOS nativo) e frameworks híbridos como Ionic e Capacitor. Conhecimento de arquitetura mobile (MVVM, Clean Architecture), CI/CD mobile (Fastlane, Bitrise, Codemagic) e publicação em App Store/Google Play são essenciais.</p><p>Desenvolvedores mobile sênior são profissionais muito valorizados, com salários competitivos e muitas oportunidades de trabalho remoto para empresas internacionais. A especialização em cross-platform ou nativo é uma decisão estratégica de carreira.</p>',
          '<p>Mobile Development is one of the most dynamic and constantly evolving fields in the technology market. With billions of smartphones worldwide, the demand for qualified mobile developers continues to grow exponentially.</p><p>Key stacks include Flutter (Dart), React Native (JavaScript/TypeScript), Kotlin (Android native), Swift (iOS native), and hybrid frameworks like Ionic and Capacitor. Knowledge of mobile architecture (MVVM, Clean Architecture), mobile CI/CD (Fastlane, Bitrise, Codemagic), and App Store/Google Play publishing are essential.</p><p>Senior mobile developers are highly valued professionals, with competitive salaries and many remote work opportunities at international companies. Specializing in cross-platform or native is a strategic career decision.</p>'],
        ];
        foreach ($conteudos as $c) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO categoria_conteudo (categoria_id, titulo_pt, titulo_en, conteudo_pt, conteudo_en)
                SELECT c.id, :titulo_pt, :titulo_en, :conteudo_pt, :conteudo_en FROM categorias c WHERE c.slug = :slug");
            $stmt->execute([':slug' => $c[0], ':titulo_pt' => $c[1], ':titulo_en' => $c[2], ':conteudo_pt' => $c[3], ':conteudo_en' => $c[4]]);
        }
    }

    $novosConteudos = [
        ['talent-acquisition',
         'Sobre a área de Talent Acquisition',
         'About Talent Acquisition',
         '<p>Talent Acquisition é a área estratégica responsável por atrair, selecionar e contratar os melhores profissionais para a organização. Diferente do recrutamento tradicional, o TA atua como parceiro estratégico do negócio, alinhando aquisição de talentos com os objetivos de longo prazo da empresa.</p><p>As principais habilidades incluem sourcing avançado, employer branding, análise de mercado de trabalho, gestão de pipeline de talentos e experiência do candidato (Candidate Experience). Ferramentas como LinkedIn Recruiter, ATS (Greenhouse, Lever, Ashby) e plataformas de assessments são essenciais.</p><p>Profissionais de TA em empresas de tecnologia são muito valorizados, especialmente aqueles que dominam tech sourcing, workforce planning e métricas de recrutamento como time-to-hire e cost-per-hire.</p>',
         '<p>Talent Acquisition is the strategic area responsible for attracting, selecting, and hiring the best professionals for the organization. Unlike traditional recruitment, TA acts as a strategic business partner, aligning talent acquisition with the company\'s long-term objectives.</p><p>Key skills include advanced sourcing, employer branding, labor market analysis, talent pipeline management, and candidate experience. Tools like LinkedIn Recruiter, ATS (Greenhouse, Lever, Ashby), and assessment platforms are essential.</p><p>TA professionals in technology companies are highly valued, especially those who master tech sourcing, workforce planning, and recruitment metrics like time-to-hire and cost-per-hire.</p>'],
        ['tech-recruiter',
         'Sobre a área de Tech Recruiter',
         'About Tech Recruiter',
         '<p>O Tech Recruiter é um profissional especializado em recrutar talentos de tecnologia, desde desenvolvedores até engenheiros de IA e profissionais de DevOps. Ele combina conhecimento técnico com habilidades de recrutamento para avaliar e atrair candidatos altamente qualificados.</p><p>As principais habilidades incluem technical screening, análise de perfis técnicos (GitHub, portfólios, blogs), conhecimento de stacks e arquiteturas de software, networking em comunidades tech e eventos. Domínio de ferramentas como LinkedIn Recruiter, Gem, Ashby e plataformas de teste técnico é diferencial.</p><p>Tech Recruiters são profissionais escassos e muito bem remunerados, especialmente aqueles que conseguem mapear e acessar talentos passivos em mercados competitivos como IA, engenharia de dados e cloud computing.</p>',
         '<p>The Tech Recruiter is a professional specialized in recruiting technology talent, from developers to AI engineers and DevOps professionals. They combine technical knowledge with recruitment skills to evaluate and attract highly qualified candidates.</p><p>Key skills include technical screening, analysis of technical profiles (GitHub, portfolios, blogs), knowledge of software stacks and architectures, networking in tech communities and events. Proficiency with tools like LinkedIn Recruiter, Gem, Ashby, and technical assessment platforms is a differentiator.</p><p>Tech Recruiters are scarce and highly paid professionals, especially those who can map and access passive talent in competitive markets like AI, data engineering, and cloud computing.</p>'],
        ['seguranca-informacao',
         'Sobre a área de Segurança da Informação',
         'About Information Security',
         '<p>A área de Segurança da Informação é uma das mais estratégicas e requisitadas no mercado de tecnologia. Com o aumento dos ataques cibernéticos, roubo de dados e regulações como LGPD e GDPR, empresas de todos os portes investem pesadamente em profissionais que protejam seus ativos digitais.</p><p>As principais especializações incluem Segurança de Redes, Segurança em Cloud (AWS, Azure, GCP), Segurança Ofensiva (Penetration Testing, Red Team), Segurança Defensiva (SOC, Blue Team), AppSec e Governança de Segurança. Ferramentas como SIEM (Splunk, QRadar), firewalls, EDR e plataformas de Vulnerability Management são essenciais.</p><p>Certificações como CISSP, CEH, OSCP, CompTIA Security+ e AWS Security Specialty são diferenciadores importantes. Profissionais de segurança da informação estão entre os mais bem remunerados do setor, com demanda crescente especialmente em fintechs, healthtechs e empresas de grande porte.</p>',
         '<p>The Information Security area is one of the most strategic and in-demand fields in the technology market. With the rise of cyberattacks, data breaches, and regulations like LGPD and GDPR, companies of all sizes invest heavily in professionals who can protect their digital assets.</p><p>Key specializations include Network Security, Cloud Security (AWS, Azure, GCP), Offensive Security (Penetration Testing, Red Team), Defensive Security (SOC, Blue Team), AppSec, and Security Governance. Tools like SIEM (Splunk, QRadar), firewalls, EDR, and Vulnerability Management platforms are essential.</p><p>Certifications like CISSP, CEH, OSCP, CompTIA Security+, and AWS Security Specialty are important differentiators. Information security professionals are among the highest-paid in the sector, with growing demand especially in fintechs, healthtechs, and large enterprises.</p>'],
        ['desenvolvedor-mobile',
         'Sobre a área de Desenvolvimento Mobile',
         'About Mobile Development',
         '<p>O Desenvolvimento Mobile é uma das áreas mais dinâmicas e em constante evolução do mercado de tecnologia. Com bilhões de dispositivos smartphones no mundo, a demanda por desenvolvedores mobile qualificados continua crescendo exponencialmente.</p><p>As principais stacks incluem Flutter (Dart), React Native (JavaScript/TypeScript), Kotlin (Android nativo), Swift (iOS nativo) e frameworks híbridos como Ionic e Capacitor. Conhecimento de arquitetura mobile (MVVM, Clean Architecture), CI/CD mobile (Fastlane, Bitrise, Codemagic) e publicação em App Store/Google Play são essenciais.</p><p>Desenvolvedores mobile sênior são profissionais muito valorizados, com salários competitivos e muitas oportunidades de trabalho remoto para empresas internacionais. A especialização em cross-platform ou nativo é uma decisão estratégica de carreira.</p>',
         '<p>Mobile Development is one of the most dynamic and constantly evolving fields in the technology market. With billions of smartphones worldwide, the demand for qualified mobile developers continues to grow exponentially.</p><p>Key stacks include Flutter (Dart), React Native (JavaScript/TypeScript), Kotlin (Android native), Swift (iOS native), and hybrid frameworks like Ionic and Capacitor. Knowledge of mobile architecture (MVVM, Clean Architecture), mobile CI/CD (Fastlane, Bitrise, Codemagic), and App Store/Google Play publishing are essential.</p><p>Senior mobile developers are highly valued professionals, with competitive salaries and many remote work opportunities at international companies. Specializing in cross-platform or native is a strategic career decision.</p>'],
    ];
    foreach ($novosConteudos as $nc) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO categoria_conteudo (categoria_id, titulo_pt, titulo_en, conteudo_pt, conteudo_en)
            SELECT c.id, :titulo_pt, :titulo_en, :conteudo_pt, :conteudo_en FROM categorias c WHERE c.slug = :slug");
        $stmt->execute([':slug' => $nc[0], ':titulo_pt' => $nc[1], ':titulo_en' => $nc[2], ':conteudo_pt' => $nc[3], ':conteudo_en' => $nc[4]]);
    }
}
