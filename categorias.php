<?php

/**
 * DICIONÁRIO DE CATEGORIZAÇÃO MONDYWORK - VERSÃO EXTENDIDA (BILÍNGUE)
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
        // Cargos e Variações (PT/EN misturados no original)
        'desenvolvedor', 'desenvolvedora', 'programador', 'programadora', 'developer', 'dev', '"software engineer"', 
        '"engenheiro de software"', '"engenheira de software"', '"front end"', 'frontend', '"back end"', 'backend', 
        '"full stack"', 'fullstack', '"web developer"', '"desenvolvedor web"', '"desenvolvedora web"', 
        '"mobile developer"', '"ios developer"', '"android developer"', 'ios', 'android', '"rest api"', 'dba',
        // Equivalentes em Inglês (Complemento)
        'programmer', '"software developer"', '"systems developer"', '"application developer"'
    ],

    'Engenharia' => [
        // Liderança e Arquitetura
        '"engineering manager"', '"gerente de engenharia"', '"tech lead"', '"technical lead"', '"líder técnico"', 
        '"líder de engenharia"', '"staff engineer"', '"principal engineer"', '"software architect"', '"arquiteto de software"', 
        '"arquiteta de software"', '"solutions architect"', '"arquiteto de soluções"', '"engineering director"', 
        '"diretor de engenharia"', '"vp of engineering"', '"head of engineering"', '"engineering lead"', '"software manager"', 
        '"gerente de desenvolvimento"',
        // Equivalentes em Inglês (Complemento)
        '"lead engineer"', '"software development manager"', '"director of engineering"'
    ],

    'Dados' => [
        // Cargos de Dados
        '"analista de dados"', 'dados', '"data analyst"', '"cientista de dados"', '"data scientist"', '"engenheiro de dados"', 
        '"engenheira de dados"', '"data engineer"', '"business intelligence"', 'bi', '"analista de bi"', 
        '"analytics engineer"', '"engenheiro de analytics"', '"arquiteto de dados"', '"data architect"', 
        '"data ops"', 'dataops', '"master data"', '"data governance"', '"governança de dados"',
        // Ferramentas e Conceitos de Dados
        '"data warehouse"', '"etl developer"', '"sql developer"', 'tableau', '"power bi"', 'looker', 'dbt', 'snowflake', 
        'databricks', 'bigquery', 'redshift', 'pyspark', 'hadoop', 'kafka', 'qlikview', 'qliksense', 'metabase',
        // Equivalentes em Inglês (Complemento)
        '"data specialist"', '"bi analyst"', '"bi developer"', '"data management"', 'data'
    ],

    'IA' => [
        // IA Clássica e Moderna
        'ia', 'ai', '"inteligência artificial"', '"inteligencia artificial"', '"artificial intelligence"', '"ia generativa"', 
        '"generative ai"', '"machine learning"', '"ml engineer"', '"engenheiro de machine learning"', '"engenheira de machine learning"', 
        '"deep learning"', 'nlp', '"processamento de linguagem natural"', '"computer vision"', '"visão computacional"', 
        // Novas Profissões IA
        'llm', '"prompt engineer"', '"engenheiro de prompt"', 'mlops', '"data science"', '"ai engineer"', '"engenheiro de ia"', 
        // Ferramentas/Redes
        '"redes neurais"', '"neural networks"', 'pytorch', 'tensorflow', 'keras', 'chatgpt', 'openai', 'midjourney',
        // Equivalentes em Inglês (Complemento)
        '"ai researcher"', '"machine learning specialist"', '"machine learning researcher"'
    ],

    'Design' => [
        // Design de Produto e Interfaces
        'ux', 'ui', '"ux/ui"', 'designer', 'motion', '"product design"', '"product designer"', '"designer de produto"', '"ui designer"', '"ux designer"', 
        '"web designer"', '"web design"', '"interaction designer"', '"designer de interação"', '"design de experiência"', 
        // Pesquisa e Experiência
        '"ux research"', '"ux researcher"', '"pesquisador de ux"', '"customer experience designer"', '"ux writer"', '"ux writing"', 
        '"service design"', '"designer de serviços"', '"product researcher"', '"user experience"', '"user interface"', 
        // Outros Designs e Ferramentas
        '"growth designer"', '"visual designer"', '"motion designer"', '"motion graphics"', '"brand designer"', 
        '"design system"', 'prototipagem', '"arquitetura de informação"', 'figma', 'framer',
        // Equivalentes em Inglês (Complemento)
        'prototyping', '"information architecture"', '"experience designer"'
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
        '"e-commerce manager"', '"gestor de e-commerce"', '"trade marketing digital"',
        // Equivalentes em Inglês (Complemento)
        '"digital marketing"', '"paid traffic"', '"traffic manager"', '"seo analyst"', '"seo specialist"', '"performance analyst"'
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
        'tiktok', 'instagram', 'youtube',
        // Equivalentes em Inglês (Complemento)
        '"social media analyst"', '"content analyst"', '"web writer"', '"public relations"'
    ],

    'Produto' => [
        // Liderança e Gestão de Produto
        '"product manager"', 'produto', 'product', '"gerente de produto"', 'pm', '"product owner"', 'po', '"dono do produto"', '"group product manager"', 'gpm', 
        '"associate product manager"', 'apm', '"vp of product"', 'cpo', '"chief product officer"', '"director of product"', 
        '"diretor de produto"', '"product leader"', '"líder de produto"',
        // Operações e Análise
        '"product ops"', '"operações de produto"', '"head of product"', '"product marketing manager"', 'pmm', '"product analyst"', 
        '"analista de produto"',
        // Equivalentes em Inglês (Complemento)
        '"product management"'
    ],

    'Ágil' => [
        '"scrum master"', '"agile coach"', 'agilista', '"consultor ágil"', '"consultora ágil"', 'agilidade', '"agile master"', 
        '"agile expert"', '"agile delivery manager"', '"agile project manager"', '"facilitador ágil"', '"enterprise agile coach"',
        'rte', '"release train engineer"', 'kanban', '"scrum team"', 'jira', 'lean', 'safe',
        // Equivalentes em Inglês (Complemento)
        '"agile consultant"', '"agile facilitator"'
    ],

    'Gestão Projetos' => [
        '"project manager"', '"gerente de projetos"', '"gestor de projetos"', '"gestora de projetos"', '"coordenador de projetos"', 
        '"project coordinator"', 'pmo', '"project management"', '"delivery manager"', '"gerente de entrega"', '"program manager"', 
        '"gerente de programas"', '"it project manager"', '"gerente de projetos de ti"',
        // Equivalentes em Inglês (Complemento)
        '"project director"'
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
        '"sales ops"', '"operações de vendas"', '"vendas b2b"', '"sales enablement"',
        // Equivalentes em Inglês (Complemento)
        '"b2b sales"', '"sales director"', '"account manager"'
    ],

    'Customer Success' => [
        '"customer success"', '"sucesso do cliente"', 'cs', '"analista de cs"', '"cs manager"', '"gerente de sucesso do cliente"', 
        '"client success"', '"customer experience"', '"experiência do cliente"', 'cx', '"analista de cx"', '"especialista em cx"', 
        '"cx manager"', '"customer support"', '"atendimento b2b"', '"suporte ao cliente b2b"', '"customer onboarding"', 
        '"voice of customer"', 'voc', '"customer journey"',
        // Equivalentes em Inglês (Complemento)
        '"customer success analyst"', '"cx specialist"', '"customer service"', '"b2b support"'
    ],

    'Suporte Técnico' => [
        '"suporte técnico"', 'suporte', 'support', '"technical support"', '"help desk"', '"service desk"', 'helpdesk', 'servicedesk', 
        '"analista de suporte"', '"it support"', '"suporte de ti"', '"analista de ti"', '"it analyst"', 
        '"suporte n1"', '"suporte n2"', '"suporte n3"', '"field service"', 'sysadmin', '"administrador de sistemas"', 
        '"analista de infraestrutura e suporte"', '"suporte de aplicações"', '"application support"',
        // Equivalentes em Inglês (Complemento)
        '"support analyst"', '"l1 support"', '"l2 support"', '"l3 support"', '"systems administrator"'
    ],

    'QA/Testes' => [
        // Profissões de Teste
        'qa', 'tester', '"quality assurance"', '"analista de qa"', '"qa engineer"', '"engenheiro de qa"', '"engenheira de qa"', '"qa tester"', 
        '"analista de testes"', 'testador', '"qualidade de software"', '"quality engineer"', '"software tester"', tester,
        // Especialidades e Ferramentas
        '"automação de testes"', '"test automation"', '"teste de software"', '"testes manuais"', '"manual tester"', 
        'sdet', 'cypress', 'selenium', 'playwright', 'appium',
        // Equivalentes em Inglês (Complemento)
        '"qa analyst"', '"test analyst"', '"software testing"', '"manual testing"'
    ],

    'InfraDevOps' => [
        // DevOps e Cloud
        'devops', 'infra', 'cloud', 'aws', 'azure',  '"cloud engineer"', '"engenheiro cloud"', '"engenheira cloud"', '"arquiteto cloud"', '"cloud architect"', 
        '"cloud computing"', 'sre', '"site reliability engineer"', '"engenheiro de confiabilidade"', 'sysops', '"platform engineer"', 
        '"engenheiro de plataforma"', 'finops', 'aws', 'azure', 'gcp', 'kubernetes', 'docker', 'k8s', 'terraform', 'ansible', 'serverless',
        // Redes, Sistemas e DBAs
        'infraestrutura', 'infrastructure', '"analista de infraestrutura"', '"engenheiro de infraestrutura"', '"engenheiro de sistemas"', 
        '"systems engineer"', '"network engineer"', '"engenheiro de redes"', 'linux', 'unix', 'vmware', 
        'dba', '"database administrator"', '"administrador de banco de dados"',
        // Segurança (Cibersegurança aglutinada aqui)
        'devsecops', '"segurança da informação"', 'cybersecurity', '"cyber security"', '"segurança de redes"', '"cloud security"',
        // Equivalentes em Inglês (Complemento)
        '"infrastructure analyst"', '"infrastructure engineer"', '"information security"', '"network security"'
    ],

    'Financeiro' => [
        // Liderança e Gestão
        'cfo', 'financeiro', '"chief financial officer"', '"diretor financeiro"', '"diretora financeira"', '"gerente financeiro"', '"finance manager"', 'controller', 'fp&a',
        // Análise e Operações
        '"analista financeiro"', '"financial analyst"', 'tesouraria', '"analista de crédito"', 'faturamento', '"contas a pagar"', '"contas a receber"',
        '"accounts payable"', '"accounts receivable"', 'billing', 'payroll',
        // Contabilidade e Fiscal
        'contabilidade', 'contador', 'contadora', 'accountant', 'auditoria', 'fiscal', 'tributário', '"analista fiscal"',
        // Equivalentes em Inglês (Complemento)
        '"financial director"', '"treasury"', '"credit analyst"', '"accounting"', '"audit"', '"tax analyst"', 'financial', 'credit', 'accountant', 'tax'
    ],

    'Administrativo' => [
        // Auxiliar e Gestão de Escritório
        '"assistente administrativo"', 'compras' '"auxiliar administrativo"', '"analista administrativo"', '"administrative assistant"', 'admin',
        '"gerente administrativo"', '"office manager"', 'secretária', 'secretário', 'recepcionista', '"receptionist"', '"executive assistant"', '"data entry"',
        // Compras e Facilities
        'compras', 'procurement', '"analista de compras"', 'logística', '"supply chain"', 'facilities',
        // RH e Departamento Pessoal
        'rh', '"recursos humanos"', '"human resources"', 'recruiter', '"tech recruiter"', 'recrutador', 'recrutadora', '"talent acquisition"',
        '"departamento pessoal"', 'dp', '"business partner"',
        // Equivalentes em Inglês (Complemento)
        '"administrative manager"', 'secretary', '"purchasing analyst"', 'logistics', '"personnel department"', 'administrative', 'purchasing', 'procurement'
    ]
];

?>