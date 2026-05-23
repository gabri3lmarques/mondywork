<?php

/**
 * DICIONÁRIO DE CATEGORIZAÇÃO MONDYWORK - VERSÃO EXTENDIDA
 * * Regra de Ouro:
 * - Termos simples (uma palavra) ficam sem aspas.
 * - Termos compostos (duas ou mais palavras) DEVEM ter aspas duplas dentro da string.
 * Exemplo: '"analista de dados"'
 */

$categorias_mondywork = [

    'Desenvolvimento' => [
        // Linguagens e Stacks
        'php', 'java', 'javascript', 'typescript', 'python', 'ruby', 'golang', 'rust', 'swift', 'kotlin', 'c#', 'c++', 
        'delphi', 'elixir', 'clojure', 'scala', 'dart', 'graphql', 'abap',
        // Frameworks e Libs (que muitas vezes viram título de vaga)
        'react', 'angular', 'vue', 'node', 'flutter', '"react native"', '"ruby on rails"', 'django', 'laravel', 
        '"spring boot"', '"next.js"', '"nuxt.js"', 'nestjs', 'svelte', '"solid.js"', 'wordpress', 'drupal', 'magento', 
        // Cargos e Variações
        'desenvolvedor', 'desenvolvedora', 'programador', 'programadora', 'developer', 'dev', '"software engineer"', 
        '"engenheiro de software"', '"engenheira de software"', '"front end"', 'frontend', '"back end"', 'backend', 
        '"full stack"', 'fullstack', '"web developer"', '"desenvolvedor web"', '"desenvolvedora web"', 
        '"mobile developer"', '"ios developer"', '"android developer"', 'ios', 'android', '"rest api"'
    ],

    'Engenharia' => [
        // Liderança e Arquitetura
        '"engineering manager"', '"gerente de engenharia"', '"tech lead"', '"technical lead"', '"líder técnico"', 
        '"líder de engenharia"', '"staff engineer"', '"principal engineer"', '"software architect"', '"arquiteto de software"', 
        '"arquiteta de software"', '"solutions architect"', '"arquiteto de soluções"', '"engineering director"', 
        '"diretor de engenharia"', '"vp of engineering"', '"head of engineering"', '"engineering lead"', '"software manager"', 
        '"gerente de desenvolvimento"'
    ],

    'Dados' => [
        // Cargos de Dados
        '"analista de dados"', '"data analyst"', '"cientista de dados"', '"data scientist"', '"engenheiro de dados"', 
        '"engenheira de dados"', '"data engineer"', '"business intelligence"', 'bi', '"analista de bi"', 
        '"analytics engineer"', '"engenheiro de analytics"', '"arquiteto de dados"', '"data architect"', 
        '"data ops"', 'dataops', '"master data"', '"data governance"', '"governança de dados"',
        // Ferramentas e Conceitos de Dados
        '"data warehouse"', '"etl developer"', '"sql developer"', 'tableau', '"power bi"', 'looker', 'dbt', 'snowflake', 
        'databricks', 'bigquery', 'redshift', 'pyspark', 'hadoop', 'kafka', 'qlikview', 'qliksense', 'metabase'
    ],

    'IA' => [
        // IA Clássica e Moderna
        'ia', 'ai', '"inteligência artificial"', '"inteligencia artificial"', '"artificial intelligence"', '"ia generativa"', 
        '"generative ai"', '"machine learning"', '"ml engineer"', '"engenheiro de machine learning"', '"engenheira de machine learning"', 
        '"deep learning"', 'nlp', '"processamento de linguagem natural"', '"computer vision"', '"visão computacional"', 
        // Novas Profissões IA
        'llm', '"prompt engineer"', '"engenheiro de prompt"', 'mlops', '"data science"', '"ai engineer"', '"engenheiro de ia"', 
        // Ferramentas/Redes
        '"redes neurais"', '"neural networks"', 'pytorch', 'tensorflow', 'keras', 'chatgpt', 'openai', 'midjourney'
    ],

    'Design' => [
        // Design de Produto e Interfaces
        'ux', 'ui', '"ux/ui"', '"product design"', '"product designer"', '"designer de produto"', '"ui designer"', '"ux designer"', 
        '"web designer"', '"web design"', '"interaction designer"', '"designer de interação"', '"design de experiência"', 
        // Pesquisa e Experiência
        '"ux research"', '"ux researcher"', '"pesquisador de ux"', '"customer experience designer"', '"ux writer"', '"ux writing"', 
        '"service design"', '"designer de serviços"', '"product researcher"', '"user experience"', '"user interface"', 
        // Outros Designs e Ferramentas
        '"growth designer"', '"visual designer"', '"motion designer"', '"motion graphics"', '"brand designer"', 
        '"design system"', 'prototipagem', '"arquitetura de informação"', 'figma', 'framer'
    ],

    'Marketing Digital' => [
        // Marketing Moderno e Growth
        '"marketing digital"', '"growth hacker"', '"growth hacking"', '"growth marketing"', '"growth manager"', '"analista de marketing"', 
        '"marketing analyst"', '"inbound marketing"', '"outbound marketing"', '"b2b marketing"', '"b2c marketing"', 'martech', 
        // Especialidades
        'seo', 'sem', '"analista de seo"', '"especialista em seo"', '"tráfego pago"', '"gestor de tráfego"', '"performance manager"', 
        '"analista de performance"', '"media buyer"', '"comprador de mídia"', '"media analyst"', '"analista de mídia"', 
        // Ferramentas e Otimização
        '"google ads"', '"meta ads"', '"tiktok ads"', '"google analytics"', 'ga4', 'crm', '"analista de crm"', '"email marketing"', 
        'cro', '"conversion rate optimization"', '"marketing operations"', '"marketing automation"', '"rd station"', 
        '"e-commerce manager"', '"gestor de e-commerce"', '"trade marketing digital"'
    ],

    'Conteúdo' => [
        // Gestão de Redes e Comunidades
        '"social media"', '"gestor de redes sociais"', '"social media manager"', '"analista de redes sociais"', 
        '"gerente de comunidade"', '"community manager"', 
        // Criação de Conteúdo
        '"content creator"', '"criador de conteúdo"', '"criadora de conteúdo"', '"estrategista de conteúdo"', '"content strategist"', 
        '"analista de conteúdo"', 'copywriter', 'copywriting', 'redator', 'redatora', '"redator web"', '"marketing de conteúdo"', 
        '"content marketing"', '"influencer marketing"', '"marketing de influência"',
        // Audiovisual e PR
        'videomaker', '"video editor"', '"editor de vídeo"', '"brand publisher"', 'comms', '"relações públicas"', 'pr', 
        'tiktok', 'instagram', 'youtube'
    ],

    'Produto' => [
        // Liderança e Gestão de Produto
        '"product manager"', '"gerente de produto"', 'pm', '"product owner"', 'po', '"dono do produto"', '"group product manager"', 'gpm', 
        '"associate product manager"', 'apm', '"vp of product"', 'cpo', '"chief product officer"', '"director of product"', 
        '"diretor de produto"', '"product leader"', '"líder de produto"',
        // Operações e Análise
        '"product ops"', '"operações de produto"', '"head of product"', '"product marketing manager"', 'pmm', '"product analyst"', 
        '"analista de produto"'
    ],

    'Ágil' => [
        '"scrum master"', '"agile coach"', 'agilista', '"consultor ágil"', '"consultora ágil"', 'agilidade', '"agile master"', 
        '"agile expert"', '"agile delivery manager"', '"agile project manager"', '"facilitador ágil"', '"enterprise agile coach"',
        'rte', '"release train engineer"', 'kanban', '"scrum team"', 'jira', 'lean', 'safe'
    ],

    'Gestão Projetos' => [
        '"project manager"', '"gerente de projetos"', '"gestor de projetos"', '"gestora de projetos"', '"coordenador de projetos"', 
        '"project coordinator"', 'pmo', '"project management"', '"delivery manager"', '"gerente de entrega"', '"program manager"', 
        '"gerente de programas"', '"it project manager"', '"gerente de projetos de ti"'
    ],

    'Comercial' => [
        // Pré-vendas e Vendas
        'sdr', 'bdr', '"sales development representative"', '"business development representative"', '"inside sales"', '"pré-vendas"', 
        '"pre sales"', '"outbound sales"', '"inbound sales"', 'closer', '"vendedor b2b"',
        // Executivos e Gestão
        '"account executive"', '"executivo de contas"', '"executiva de contas"', 'ae', '"key account"', '"sales manager"', 
        '"gerente de vendas"', '"sales executive"', '"diretor de vendas"', '"head of sales"', '"sales representative"', 
        '"representante de vendas"', '"business development"', '"executivo de expansão"', 
        // Operações de Vendas
        '"sales ops"', '"operações de vendas"', '"vendas b2b"', '"sales enablement"'
    ],

    'Customer Success' => [
        '"customer success"', '"sucesso do cliente"', 'cs', '"analista de cs"', '"cs manager"', '"gerente de sucesso do cliente"', 
        '"client success"', '"customer experience"', '"experiência do cliente"', 'cx', '"analista de cx"', '"especialista em cx"', 
        '"cx manager"', '"customer support"', '"atendimento b2b"', '"suporte ao cliente b2b"', '"customer onboarding"', 
        '"voice of customer"', 'voc', '"customer journey"', 'customer'
    ],

    'Suporte Técnico' => [
        '"suporte técnico"', '"technical support"', '"help desk"', '"service desk"', 'helpdesk', 'servicedesk', 
        '"analista de suporte"', '"it support"', '"suporte de ti"', '"analista de ti"', '"it analyst"', 
        '"suporte n1"', '"suporte n2"', '"suporte n3"', '"field service"', 'sysadmin', '"administrador de sistemas"', 
        '"analista de infraestrutura e suporte"', '"suporte de aplicações"', '"application support"', 'support', 'suporte'
    ],

    'QA/Testes' => [
        // Profissões de Teste
        'qa', '"quality assurance"', '"analista de qa"', '"qa engineer"', '"engenheiro de qa"', '"engenheira de qa"', '"qa tester"', 
        '"analista de testes"', 'testador', '"qualidade de software"', '"quality engineer"', '"software tester"', 'tester',
        // Especialidades e Ferramentas
        '"automação de testes"', '"test automation"', '"teste de software"', '"testes manuais"', '"manual tester"', 
        'sdet', 'cypress', 'selenium', 'playwright', 'appium'
    ],

    'Infra/DevOps' => [
        // DevOps e Cloud
        'devops', 'cloud', 'infra', '"cloud engineer"', '"engenheiro cloud"', '"engenheira cloud"', '"arquiteto cloud"', '"cloud architect"', 
        '"cloud computing"', 'sre', '"site reliability engineer"', '"engenheiro de confiabilidade"', 'sysops', '"platform engineer"', 
        '"engenheiro de plataforma"', 'finops', 'aws', 'azure', 'gcp', 'kubernetes', 'docker', 'k8s', 'terraform', 'ansible', 'serverless',
        // Redes, Sistemas e DBAs
        'infraestrutura', 'infrastructure', '"analista de infraestrutura"', '"engenheiro de infraestrutura"', '"engenheiro de sistemas"', 
        '"systems engineer"', '"network engineer"', '"engenheiro de redes"', 'linux', 'unix', 'vmware', 
        'dba', '"database administrator"', '"administrador de banco de dados"',
        // Segurança (Cibersegurança aglutinada aqui)
        'devsecops', '"segurança da informação"', 'cybersecurity', '"cyber security"', '"segurança de redes"', '"cloud security"'
    ]
];

function removerAcentosCat($str) {
    $acentos = [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae',
        'ç' => 'c', 'ð' => 'd', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'ñ' => 'n',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ý' => 'y', 'ÿ' => 'y', 'þ' => 'b', 'ß' => 'ss',
        'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE',
        'Ç' => 'C', 'Ð' => 'D', 'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N',
        'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O',
        'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Ÿ' => 'Y', 'Þ' => 'B',
    ];
    return strtr($str, $acentos);
}

function classificarVaga(string $titulo, array $categoriasDict): array {
    $titulo = mb_strtolower(removerAcentosCat($titulo));
    $tags = [];

    foreach ($categoriasDict as $categoria => $termos) {
        foreach ($termos as $termo) {
            $termo = mb_strtolower(removerAcentosCat(trim($termo, '"')));
            if (str_contains($titulo, $termo)) {
                $tags[] = $categoria;
                break;
            }
        }
    }

    if (empty($tags)) {
        $tags[] = 'Sem Categoria';
    }

    return array_values(array_unique($tags));
}

function categoriaSlug(string $nome): string {
    $map = [
        'Desenvolvimento' => 'desenvolvimento',
        'Engenharia' => 'engenharia',
        'Dados' => 'dados',
        'IA' => 'ia',
        'Design' => 'design',
        'Marketing Digital' => 'marketing-digital',
        'Conteúdo' => 'conteudo',
        'Produto' => 'produto',
        'Ágil' => 'agil',
        'Gestão Projetos' => 'gestao-projetos',
        'Comercial' => 'comercial',
        'Customer Success' => 'customer-success',
        'Suporte Técnico' => 'suporte-tecnico',
        'QA/Testes' => 'qa-testes',
        'Infra/DevOps' => 'infra-devops',
        'Sem Categoria' => 'sem-categoria',
    ];
    return $map[$nome] ?? 'sem-categoria';
}
?>