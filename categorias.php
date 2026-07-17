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
        '"rest api"', 'dba',
        // Equivalentes em Inglês (Complemento)
        'programmer', '"software developer"', '"systems developer"', '"application developer"'
    ],

    'Desenvolvedor Mobile' => [
        // Frameworks e Tecnologias Mobile
        'flutter', '"react native"', '"ionic"', '"capacitor"', '"kotlin multiplatform"', 'kmm',
        'swiftui', 'uikit', 'jetpack compose', 'xamarin', 'maui', 'nativescript',
        // Plataformas e Sistemas
        'ios', 'android', '"windows phone"', 'wearos', 'watchos', 'tizen',
        // Cargos e Funções
        '"mobile developer"', '"desenvolvedor mobile"', '"desenvolvedora mobile"',
        '"ios developer"', '"desenvolvedor ios"', '"desenvolvedor ios"',
        '"android developer"', '"desenvolvedor android"', '"desenvolvedora android"',
        '"mobile engineer"', '"engenheiro mobile"', '"engenheira mobile"',
        '"mobile app developer"', '"app developer"', '"desenvolvedor de aplicativos"',
        '"cross-platform developer"', '"desenvolvedor cross-platform"',
        // Especialidades
        '"mobile ux"', '"mobile ui"', '"mobile architecture"', '"arquitetura mobile"',
        '"push notification"', '"mobile analytics"', '"mobile testing"',
        '"app store optimization"', 'aso', '"google play"',
        // Ferramentas e CI/CD
        '"fastlane"', '"firebase"', '"bitrise"', '"appcenter"', '"codemagic"',
        // Equivalentes em Inglês (Complemento)
        '"mobile software engineer"', '"flutter developer"', '"react native developer"',
        '"ios engineer"', '"android engineer"'
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

    'Product Owner' => [
        // Cargo e Identidade
        '"product owner"', '"po"', '"dono do produto"', '"dona do produto"',
        '"certified scrum product owner"', '"cspo"', '"pspo"', '"professional scrum product owner"',
        '"product owner de squads"', '"product owner de tribes"', '"product owner de chapters"', '"product owner de guilds"',
        '"delivery product owner"', '"technical product owner"', '"program product owner"', '"enterprise product owner"',
        '"product owner para startups"', '"product owner remoto"', '"product owner distribuído"', '"multi-team product owner"',
        '"product owner de produto"', '"senior product owner"', '"lead product owner"', '"principal product owner"',
        '"chief product owner"', '"product owner coach"', '"product ownership"',
        // Equivalentes em Inglês (Complemento)
        '"po analyst"', '"analista de product owner"'
    ],

    'Product Manager' => [
        // Cargo e Identidade
        '"product manager"', '"pm"', '"gerente de produto"', '"gerente de produtos"',
        '"associate product manager"', '"apm"', '"group product manager"', '"gpm"',
        '"vp of product"', '"cpo"', '"chief product officer"', '"director of product"',
        '"diretor de produto"', '"product leader"', '"líder de produto"', '"product management"',
        '"product lead"', '"senior product manager"', '"lead product manager"', '"principal product manager"',
        '"chief product manager"', '"product manager coach"', '"technical product manager"', '"tpm"',
        '"product marketing manager"', '"pmm"', '"head of product"', '"product growth manager"', '"growth product manager"',
        // Equivalentes em Inglês (Complemento)
        '"pm analyst"', '"analista de product manager"'
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
        '"analista de testes"', 'testador', '"qualidade de software"', '"quality engineer"', '"software tester"', 'tester',
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
        // Equivalentes em Inglês (Complemento)
        '"infrastructure analyst"', '"infrastructure engineer"'
    ],

    'Segurança da Informação' => [
        // Cargos e Funções
        '"segurança da informação"', '"information security"', 'cybersecurity', '"cyber security"', 'cibersegurança',
        '"analista de segurança"', '"security analyst"', '"security engineer"', '"engenheiro de segurança"',
        '"analista de segurança da informação"', '"information security analyst"',
        '"especialista em segurança"', '"security specialist"', '"security consultant"', '"consultor de segurança"',
        // Liderança
        '"chief information security officer"', 'ciso', '"diretor de segurança"', '"head of security"',
        '"security manager"', '"gerente de segurança"', '"security architect"', '"arquiteto de segurança"',
        // Segurança de Redes e Cloud
        '"segurança de redes"', '"network security"', '"cloud security"', '"segurança em nuvem"',
        '"firewall"', '"ids"', '"ips"', '"siem"', '"soc"',
        // Segurança Ofensiva e Defensiva
        '"penetration tester"', '"pentester"', '"testador de penetração"', '"ethical hacker"',
        '"hacker ético"', '"red team"', '"blue team"', '"purple team"',
        '"vulnerability analyst"', '"analista de vulnerabilidades"', '"bug bounty"',
        // Compliance e Governança
        '"security compliance"', '"compliance de segurança"', '"risco de segurança"',
        'lgpd', 'gdpr', '"data protection"', '"proteção de dados"',
        'iso 27001', 'nist', 'soc 2', 'itil',
        // DevSecOps
        'devsecops', '"shift left"', '"secure coding"', '"código seguro"',
        '"sast"', '"dast"', '"devsecops engineer"',
        // Equivalentes em Inglês (Complemento)
        '"information security officer"', '"cybersecurity analyst"', '"cybersecurity engineer"',
        '"security operations"', '"appsec"', '"application security"'
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
        '"assistente administrativo"', 'compras', '"auxiliar administrativo"', '"analista administrativo"', '"administrative assistant"', 'admin',
        '"gerente administrativo"', '"office manager"', 'secretária', 'secretário', 'recepcionista', '"receptionist"', '"executive assistant"', '"data entry"',
        // Compras e Facilities
        'compras', 'procurement', '"analista de compras"', 'logística', '"supply chain"', 'facilities',
        // RH e Departamento Pessoal
        'rh', '"recursos humanos"', '"human resources"',
        '"departamento pessoal"', 'dp', '"business partner"',
        // Equivalentes em Inglês (Complemento)
        '"administrative manager"', 'secretary', '"purchasing analyst"', 'logistics', '"personnel department"', 'administrative', 'purchasing', 'procurement'
    ],

    'Talent Acquisition' => [
        // Cargos e Funções
        '"talent acquisition"', '"talent acquisition specialist"', '"talent acquisition manager"', '"talent acquisition partner"',
        '"talent acquisition analyst"', '"analista de aquisição de talentos"', '"especialista em aquisição de talentos"',
        '"gerente de aquisição de talentos"', '"diretor de talent acquisition"', '"head of talent acquisition"',
        // Recrutamento e Seleção
        'recruiter', '"tech recruiter"', 'recrutador', 'recrutadora', '"recruitment specialist"', '"especialista em recrutamento"',
        '"recruitment analyst"', '"analista de recrutamento"', '"recruitment manager"', '"gerente de recrutamento"',
        '"recruitment coordinator"', '"coordenador de recrutamento"',
        // Sourcing e Employeer Branding
        'sourcer', '"sourcing specialist"', '"especialista em sourcing"', '"talent sourcer"',
        '"employer branding"', '"marca empregadora"', '"employer brand manager"',
        // Métricas e Estratégia
        '"time to hire"', '"cost per hire"', '"recruitment funnel"', '"pipeline de talentos"',
        '"workforce planning"', '"planejamento de workforce"', '"talent pipeline"',
        // Equivalentes em Inglês (Complemento)
        '"talent recruiter"', '"hiring manager"', '"talent partner"', '"recruitment lead"'
    ],

    'Tech Recruiter' => [
        // Cargos Específicos de Tech Recruitment
        '"tech recruiter"', '"technical recruiter"', '"recrutador técnico"', '"recrutadora técnica"',
        '"tech recruitment specialist"', '"especialista em recrutamento técnico"', '"recrutamento de ti"',
        '"technical recruitment"', '"it recruiter"', '"software recruiter"',
        // Liderança de Tech Recruitment
        '"tech recruiting manager"', '"head of tech recruiting"', '"gerente de recrutamento técnico"',
        '"tech recruiting lead"', '"tech talent acquisition"',
        // Skills e Atividades Específicas
        '"technical screening"', '"entrevista técnica"', '"avaliação técnica"',
        '"tech hiring"', '"hiring developers"', '"recruiting engineers"',
        '"sourcing técnico"', '"tech sourcing"',
        // Ferramentas e Plataformas
        'linkedin', 'greenhouse', 'lever', 'workday', 'ashby', 'gem',
        // Equivalentes em Inglês (Complemento)
        '"engineering recruiter"', '"developer recruiter"', '"IT recruitment"'
    ],

        'Comunicação' => [
        // Comunicação Corporativa e Institucional
        '"comunicação corporativa"', '"corporate communication"', '"comunicação interna"', '"internal communication"',
        '"comunicação institucional"', '"institutional communication"', '"comunicação empresarial"',
        '"relações públicas"', '"public relations"', '"pr corporativo"', '"corporate pr"',
        '"assessoria de imprensa"', '"press office"', '"media relations"', '"relações com a imprensa"',
        '"media analyst"', '"analista de mídia"',
        // Gestão de Comunicação
        '"gerente de comunicação"', '"communication manager"', '"diretor de comunicação"', '"communication director"',
        '"head of communications"', '"head of corporate communications"', '"coordenador de comunicação"',
        '"communication strategist"', '"estrategista de comunicação"',
        '"communication specialist"', '"especialista em comunicação"',
        '"analista de comunicação"', '"communication analyst"',
        // Comunicação de Crise e Reputation
        '"comunicação de crise"', '"crisis communication"', '"crisis management"',
        '"gestão de reputação"', '"reputation management"', '"reputation manager"',
        '"corporate affairs"', '"assessoria corporativa"', '"government relations"',
        '"public affairs"', '"relações governamentais"',
        // Comunicação Digital e Eventos
        '"digital communication"', '"comunicação digital"',
        '"corporate events"', '"eventos corporativos"', '"event planner"', '"planejador de eventos"',
        '"brand communication"', '"comunicação de marca"', '"branding corporativo"',
        '"employee communication"', '"comunicação com colaboradores"', '"comunicação com funcionários"',
        '"change communication"', '"comunicação de mudanças"',
        // Audiovisual Corporativo
        '"corporate video"', '"vídeo corporativo"', '"audiovisual corporativo"',
        '"internal newsletter"', '"newsletter interna"', '"boletim interno"',
        '"corporate blog"', '"blog corporativo"',
        // Equivalentes em Inglês (Complemento)
        '"communications manager"', '"communications director"', '"corporate communications manager"',
        '"internal comms"', '"external comms"', '"stakeholder communication"',
        '"employee engagement"', '"engajamento de funcionários"'
    ],

    'Social Mídia' => [
        // Plataformas e Gestão
        '"social media"', '"redes sociais"', '"gestão de redes sociais"', '"social media management"',
        '"social media manager"', '"gestor de redes sociais"', '"social media specialist"',
        '"analista de redes sociais"', '"social media analyst"', '"social media coordinator"',
        '"social media strategist"', '"estrategista de redes sociais"',
        // Criação e Produção
        '"social media content"', '"conteúdo para redes sociais"', '"criador de conteúdo digital"',
        '"content creator"', '"criador de conteúdo"', '"social media creator"',
        '"produção de conteúdo para redes sociais"', '"social media production"',
        // Community Management
        '"community manager"', '"gerente de comunidade"', '"gestor de comunidade"',
        '"moderador de redes sociais"', '"social media moderator"',
        '"community specialist"', '"especialista em comunidade"',
        // Pago e Performance
        '"social media ads"', '"anúncios em redes sociais"', '"facebook ads"', '"meta ads"',
        '"instagram ads"', '"linkedin ads"', '"tiktok ads"', '"twitter ads"', '"x ads"',
        '"social media paid"', '"mídia paga social"', '"paid social"',
        '"social media performance"', '"performance de redes sociais"',
        // Métricas e Analytics
        '"social media metrics"', '"métricas de redes sociais"', '"social media analytics"',
        '"social media reporting"', '"relatórios de redes sociais"',
        '"engajamento"', '"engagement"', '" alcance"', '"reach"', '"impressões"', '"impressions"',
        // Estratégia e Planejamento
        '"social media strategy"', '"estratégia de redes sociais"', '"social media plan"',
        '"planejamento de redes sociais"', '"content calendar"', '"calendário editorial"',
        '"social media campaign"', '"campanha de redes sociais"',
        // Plataformas Específicas
        'instagram', 'tiktok', 'facebook', 'linkedin', 'twitter', 'youtube', 'pinterest',
        'threads', 'bluesky', 'mastodon', '"google my business"',
        // Equivalentes em Inglês (Complemento)
        '"social media director"', '"head of social media"', '"social media lead"',
        '"digital community manager"', '"brand social media"', '"corporate social media"'
    ],

    'Marketing' => [
        // Marketing Tradicional e Estratégico
        '"marketing manager"', '"gerente de marketing"', '"marketing director"', '"diretor de marketing"',
        '"head of marketing"', '"vp of marketing"', '"chief marketing officer"', 'cmo',
        '"marketing strategist"', '"estrategista de marketing"', '"marketing consultant"',
        '"consultor de marketing"', '"marketing analyst"', '"analista de marketing"',
        // Brand Management
        '"brand manager"', '"gerente de marca"', '"branding manager"',
        '"brand strategist"', '"estrategista de marca"', '"brand analyst"',
        '"gerente de produto"', '"product manager"', '"product marketing manager"',
        '"product marketing"', '"marketing de produto"',
        // Market Research e Inteligência de Mercado
        '"market research"', '"pesquisa de mercado"', '"market analyst"', '"analista de mercado"',
        '"market intelligence"', '"inteligência de mercado"', '"competitive intelligence"',
        '"consumer insights"', '"insights do consumidor"', '"market specialist"',
        '"especialista de mercado"',
        // Trade Marketing e Varejo
        '"trade marketing"', '"trade marketing manager"', '"gerente de trade marketing"',
        '"shopper marketing"', '"category manager"', '"gestor de categoria"',
        '"visual merchandising"', '"merchandising"', '"trade analyst"',
        // Eventos e Relações Institucionais
        '"event marketing"', '"marketing de eventos"', '"event manager"', '"gerente de eventos"',
        '"corporate events"', '"eventos corporativos"', '"event planner"',
        '"marketing relacional"', '"relational marketing"',
        // Branding e Identidade
        '"brand identity"', '"identidade de marca"', '"brand experience"', '"experiência de marca"',
        '"brand positioning"', '"posicionamento de marca"', '"brand equity"',
        '"valor de marca"', '"brand culture"', '"cultura de marca"',
        // Pricing e Canal
        '"pricing strategy"', '"estratégia de precificação"', '"pricing analyst"',
        '"channel marketing"', '"marketing de canal"', '"field marketing"',
        '"marketing operacional"', '"marketing tático"',
        // Equivalentes em Inglês (Complemento)
        '"marketing specialist"', '"brand director"', '"marketing operations"',
        '"marketing coordinator"', '"marketing communications"', '"marcom"',
        '"go-to-market"', '"gtm strategy"', '"market development"',
        '"business development manager"', '"b2b marketing manager"'
    ],

    'Analista de SEO' => [
        // SEO On-Page e Técnico
        'seo', '"search engine optimization"', '"otimização para mecanismos de busca"',
        '"seo on-page"', '"on-page seo"', '"seo off-page"', '"off-page seo"',
        '"seo técnico"', '"technical seo"', '"seo técnico"',
        '"keyword research"', '"pesquisa de palavras-chave"', '" keyword planning"',
        '"keyword mapping"', '"keyword clustering"', '"keyword gap"',
        // Auditoria e Análise
        '"seo audit"', '"auditoria seo"', '"seo analysis"', '"análise seo"',
        '"seo report"', '"relatório seo"', '"seo metrics"', '"métricas seo"',
        '"seo performance"', '"performance seo"', '"seo dashboard"',
        // Conteúdo e Estratégia
        '"seo content"', '"conteúdo para seo"', '"content seo"', '"seo copywriting"',
        '"content strategy"', '"estratégia de conteúdo"', '"content optimization"',
        '"topic cluster"', '"pillar page"', '"cluster de conteúdo"',
        '"seo strategy"', '"estratégia seo"', '"seo plan"', '"plano seo"',
        // Link Building e Off-Page
        '"link building"', '"construção de links"', '"backlink"', '"backlinks"',
        '"guest post"', '"guest posting"', '"digital pr"', '"pr digital"',
        '"link earning"', '"earning de links"', '"domain authority"', '"autoridade de domínio"',
        // SEO Local e E-commerce
        '"local seo"', '"seo local"', '"google my business"', '"google business profile"',
        '"maps seo"', '"seo para e-commerce"', '"ecommerce seo"',
        // Ferramentas
        '"google search console"', '"gsc"', '"google analytics"', '"ga4"',
        'ahrefs', 'semrush', 'moz', '"screaming frog"', '"screaming frog seo"',
        '"sitebulb"', 'screaming', 'frog',
        // Core Web Vitals e Performance
        '"core web vitals"', '"cwv"', '"page speed"', '"velocidade da página"',
        '"lcp"', '"fid"', '"cls"', '"INP"',
        // Equivalentes em Inglês (Complemento)
        '"seo specialist"', '"seo analyst"', '"seo manager"', '"seo consultant"',
        '"search engine marketing specialist"', '"sem specialist"',
        '"technical seo specialist"', '"content seo specialist"', '"link building specialist"'
    ],

    'Gestor de Tráfego' => [
        // Gestão de Tráfego Pago
        '"gestor de tráfego"', '"traffic manager"', '"traffic specialist"',
        '"gestão de tráfego pago"', '"paid traffic management"',
        '"tráfego pago"', '"paid traffic"', '"mídia paga"', '"paid media"',
        '"paid media manager"', '"gestor de mídia paga"',
        // Plataformas de Anúncios
        '"google ads"', '"google ads manager"', '"google ads specialist"',
        '"meta ads"', '"facebook ads"', '"instagram ads"', '"meta business"',
        '"linkedin ads"', '"linkedin campaign manager"',
        '"tiktok ads"', '"tiktok advertising"',
        '"twitter ads"', '"x ads"', '"x advertising"',
        '"pinterest ads"', '"snapchat ads"', '"reddit ads"',
        '"microsoft ads"', '"bing ads"', '"microsoft advertising"',
        // Estratégia e Planejamento
        '"paid media strategy"', '"estratégia de mídia paga"',
        '"media plan"', '"planejamento de mídia"', '"midia plan"',
        '"budget management"', '"gestão de orçamento"', '"gestão de verba"',
        '"campaign strategy"', '"estratégia de campanha"',
        '"performance marketing"', '"marketing de performance"',
        // Análise e Otimização
        '"roas"', '"roi"', '"cac"', '"cpa"', '"cpc"', '"cpm"', '"ctr"',
        '"conversion rate"', '"taxa de conversão"', '"cost per lead"', '"cpl"',
        '"cost per acquisition"', '"custo por aquisição"',
        '"a/b testing"', '"teste a/b"', '"split testing"',
        '"remarketing"', '"retargeting"', '"retargeting ads"',
        // Funis e Conversão
        '"funil de vendas"', '"sales funnel"', '"marketing funnel"',
        '"conversion funnel"', '"funil de conversão"',
        '"landing page"', '"página de destino"', '"lp"',
        '"lead generation"', '"geração de leads"', '"lead gen"',
        // Automação e Ferramentas
        '"marketing automation"', '"automação de marketing"',
        '"google tag manager"', '"gtm"', '"utm parameters"', '"utm"',
        '"google analytics"', '"ga4"', '"hotjar"', '"clarity"',
        '"hubspot"', '"rd station"', '"resultados digitais"',
        // Equivalentes em Inglês (Complemento)
        '"paid media specialist"', '"paid media analyst"', '"ppc specialist"',
        '"ppc manager"', '"ads specialist"', '"advertising specialist"',
        '"performance specialist"', '"performance analyst"',
        '"media buyer"', '"comprador de mídia"', '"digital media planner"'
    ],

    'Gerente de Projetos' => [
        // Cargos e Funções
        '"gerente de projetos"', '"project manager"', '"pm"', '"gestor de projetos"',
        '"gestora de projetos"', '"project coordinator"', '"coordenador de projetos"',
        '"project analyst"', '"analista de projetos"',
        // Liderança e Direção
        '"director of projects"', '"diretor de projetos"', '"head of projects"',
        '"program manager"', '"gerente de programas"', '"portfolio manager"',
        '"gerente de portfólio"', '"delivery manager"', '"gerente de entrega"',
        '"it project manager"', '"gerente de projetos de ti"',
        // Metodologias e Frameworks
        '"gestão de projetos"', '"project management"', '"pmo"',
        '"pmp"', '"prince2"', '"certified associate in project management"', '"capm"',
        '"lean project management"', '"six sigma"', '"lean six sigma"',
        '"waterfall"', '"cascata"', '"hibrido"', '"hybrid project management"',
        // Planejamento e Controle
        '"cronograma"', '"schedule management"', '"gestão de prazos"',
        '"orçamento de projetos"', '"budget management"', '"gestão de custos"',
        '"risco"', '"risk management"', '"gestão de riscos"',
        '"alcance"', '"scope management"', '"gestão de escopo"',
        '"qualidade"', '"quality management"', '"gestão de qualidade"',
        '"recursos"', '"resource management"', '"gestão de recursos"',
        // Comunicação e Stakeholders
        '"stakeholder management"', '"gestão de stakeholders"',
        '"comunicação de projetos"', '"project communication"',
        '"reporting"', '"relatórios de projetos"',
        // Ferramentas
        'jira', 'asana', 'trello', 'monday', '"microsoft project"', '"ms project"',
        'clickup', 'notion', 'basecamp', 'wrike', '"smartsheet"',
        'power bi', 'excel',
        // Métricas e KPIs
        '"earned value"', '"valor agregado"', '"evm"',
        '"burn rate"', '"velocity"', '"lead time"', '"cycle time"',
        '"on time"', '"on budget"', '"scope creep"',
        // Equivalentes em Inglês (Complemento)
        '"project director"', '"project leader"', '"project lead"',
        '"technical project manager"', '"tpm"', '"scrum master"',
        '"agile project manager"', '"apm"', '"project management office"',
        '"project controls manager"', '"project scheduler"'
    ],

    'Analista de Sistemas' => [
        // Cargos e Funções
        '"analista de sistemas"', '"systems analyst"', '"analista de system"',
        '"analista de requisitos"', '"requirements analyst"', '"business analyst"',
        '"analista de negócios"', '"analista funcional"', '"functional analyst"',
        '"systems engineer"', '"engenheiro de sistemas"',
        // Análise e Design
        '"análise de sistemas"', '"system analysis"', '"análise de requisitos"',
        '"requirements gathering"', '"levantamento de requisitos"',
        '"modelagem de processos"', '"process modeling"', '"modelagem de dados"',
        '"data modeling"', '"use case"', '"caso de uso"', '"user story"',
        '"história de usuário"', '"backlog"', '"specification"', '"especificação"',
        // Desenvolvimento e Integração
        '"desenvolvimento de sistemas"', '"system development"',
        '"integração de sistemas"', '"system integration"',
        '"api"', '"web service"', '"microservices"', '"microsserviços"',
        '"middleware"', '"erp"', '"crm"', '"sap"', '"salesforce"',
        // Infraestrutura e Operação
        '"infraestrutura de ti"', '"it infrastructure"',
        '"administração de bancos de dados"', '"database administration"',
        '"sql server"', '"oracle"', '"mysql"', '"postgresql"',
        '"linux"', '"windows server"', '"active directory"',
        // Segurança e Compliance
        '"segurança da informação"', '"information security"',
        '"controles internos"', '"internal controls"',
        '"política de ti"', '"it policy"', '"pliance de ti"',
        // Equivalentes em Inglês (Complemento)
        '"application analyst"', '"analista de aplicações"',
        '"it analyst"', '"analista de ti"', '"technical analyst"',
        '"solution architect"', '"arquiteto de soluções"',
        '"it consultant"', '"consultor de ti"'
    ],

    'Governança de TI' => [
        // Frameworks e Normas
        '"governança de ti"', '"it governance"', '"it governança"',
        '"itil"', '"itil v4"', '"cobit"', '"cobit 2019"',
        '"iso 27001"', '"iso 27002"', '"iso 20000"',
        '"tic q"', '"tic q"', '"ticq"',
        // Gestão de Serviços
        '"gestão de serviços de ti"', '"itsm"', '"it service management"',
        '"service desk"', '"help desk"', '"mesa de ajuda"',
        '"gestão de incidentes"', '"incident management"',
        '"gestão de problemas"', '"problem management"',
        '"gestão de mudanças"', '"change management"',
        '"gestão de configuração"', '"configuration management"',
        '"gestão de continuidade"', '"business continuity"',
        '"disaster recovery"', '"recuperação de desastres"',
        // Auditoria e Compliance
        '"auditoria de ti"', '"it audit"', '"it auditing"',
        '"compliance de ti"', '"it compliance"',
        '"controles de ti"', '"it controls"',
        '"risco de ti"', '"it risk management"',
        '"gestão de riscos de ti"', '"it risk"',
        // Métricas e Indicadores
        '"slas"', '"service level agreement"', '"acordo de nível de serviço"',
        '"ola"', '"operational level agreement"',
        '"kpi de ti"', '"it kpi"', '"balanced scorecard"',
        '"valor de ti"', '"it value"', '"value of it"',
        // Estratégia e Alinhamento
        '"alinhamento de ti"', '"it alignment"', '"it-business alignment"',
        '"estratégia de ti"', '"it strategy"', '"roadmap de ti"',
        '"portfolio de ti"', '"it portfolio management"',
        '"arquitetura corporativa"', '"enterprise architecture"',
        // Equivalentes em Inglês (Complemento)
        '"governance analyst"', '"it governance analyst"',
        '"compliance analyst"', '"it compliance analyst"',
        '"itsm analyst"', '"service management analyst"',
        '"it governance manager"', '"governance manager"',
        '"it director"', '"diretor de ti"', '"cto"', '"cio"'
    ],

    'Frontend' => [
        // Linguagens e Fundamentos
        '"front end"', 'frontend', '"front-end"', '"frontender"',
        'html', 'css', 'javascript', 'typescript',
        // Frameworks e Bibliotecas
        'react', 'angular', 'vue', 'svelte', '"next.js"', '"nuxt.js"',
        '"solid.js"', 'preact', 'lit', 'ember', 'backbone',
        // CSS e Estilização
        'sass', 'less', 'tailwind', '"tailwind css"', 'bootstrap', 'materialize',
        '"styled-components"', '"emotion"', '"css modules"', '"css-in-js"',
        '"postcss"', '"sass/scss"',
        // Ferramentas de Build e Bundlers
        'webpack', 'vite', 'parcel', 'esbuild', 'rollup', 'turbopack',
        '"babel"', '"postcss"',
        // Cargos e Funções
        '"desenvolvedor front end"', '"desenvolvedora front end"',
        '"frontend developer"', '"front end developer"',
        '"frontend engineer"', '"engenheiro frontend"',
        '"ui developer"', '"desenvolvedor ui"',
        '"react developer"', '"angular developer"', '"vue developer"',
        // Performance e Acessibilidade
        '"web performance"', '"performance web"', '"core web vitals"',
        'accessibility', '"acessibilidade web"', 'wcag', 'a11y',
        // Testes
        '"frontend testing"', '"testes frontend"',
        'jest', 'cypress', 'playwright', '"vitest"',
        // Equivalentes em Inglês (Complemento)
        '"senior frontend"', '"frontend lead"', '"frontend architect"',
        '"ui engineer"', '"web developer"', '"javascript developer"',
        '"react engineer"', '"vue engineer"'
    ],

    'Backend' => [
        // Linguagens e Stacks
        'php', 'java', 'python', 'ruby', 'golang', 'rust', 'c#', 'node',
        '"node.js"', 'dotnet', '".net"', 'delphi', 'elixir', 'scala',
        // Frameworks
        'laravel', '"spring boot"', 'django', 'flask', 'rails', '"ruby on rails"',
        'nestjs', 'express', 'fastapi', 'symfony', 'adonis',
        // Bancos de Dados
        'sql', 'mysql', 'postgresql', 'oracle', '"sql server"',
        'mongodb', 'redis', 'elasticsearch', 'cassandra', 'dynamodb',
        'firebase', 'supabase', 'planetscale',
        // Cargos e Funções
        '"desenvolvedor back end"', '"desenvolvedora back end"',
        '"backend developer"', '"back end developer"',
        '"backend engineer"', '"engenheiro backend"',
        '"server side"', '"lado do servidor"',
        '"api developer"', '"desenvolvedor de api"',
        '"desenvolvedor php"', '"php developer"',
        '"desenvolvedor java"', '"java developer"',
        '"desenvolvedor python"', '"python developer"',
        '"desenvolvedor node"', '"node developer"',
        // Arquitetura e Padrões
        '"arquitetura de software"', '"software architecture"',
        '"clean architecture"', '"arquitetura limpa"',
        '"design patterns"', '"padrões de projeto"',
        '"domain driven design"', 'ddd', '"microservices"', '"microsserviços"',
        '"serverless"', '"event driven"',
        // Segurança e Performance
        '"api security"', '"segurança de api"',
        '"oauth"', '"jwt"', '"rate limiting"',
        '"caching"', '"cache"',
        // Equivalentes em Inglês (Complemento)
        '"senior backend"', '"backend lead"', '"backend architect"',
        '"api engineer"', '"server engineer"',
        '"full stack developer"', '"fullstack developer"'
    ],

    'Office Suite' => [
        // Microsoft Office
        'excel', 'word', 'powerpoint', 'outlook', 'teams',
        '"microsoft office"', '"ms office"', '"office 365"', '"microsoft 365"',
        '"excel avançado"', '"advanced excel"', '"excel intermediário"',
        'vba', '"macros vba"', '"power query"', '"power pivot"',
        'access', '"microsoft access"',
        // Google Workspace
        '"google sheets"', '"google docs"', '"google slides"',
        '"google workspace"', '"gsuite"', '"google drive"',
        '"google forms"', '"google meet"',
        // Outros
        'notion', 'airtable', 'trello', 'asana', 'monday',
        '"microsoft project"', '"ms project"',
        'canva', '"figma"', '"miro"',
        // Cargos e Funções
        '"analista de ti"', '"it analyst"', '"analista de sistemas"',
        '"analista administrativo"', '"analista de dados"',
        '"assistente administrativo"', '"administrative assistant"',
        '"auxiliar administrativo"',
        // Habilidades
        '"pacote office"', '"office suite"', '"pacote microsoft"',
        '"planilha"', '"spreadsheet"', '"apresentação"', '"presentation"',
        '"documento"', '"document"', '"email"', '"email marketing"',
        // Equivalentes em Inglês (Complemento)
        '"microsoft excel specialist"', '"office specialist"',
        '"administrative specialist"', '"office coordinator"',
        '"office manager"', '"gerente administrativo"',
        '"executive assistant"', '"assistente executivo"'
    ],

    'Fullstack' => [
        // Cargos e Funções
        '"full stack"', 'fullstack', '"full-stack"', '"full stack developer"',
        '"fullstack developer"', '"desenvolvedor full stack"', '"desenvolvedora full stack"',
        '"full stack engineer"', '"engenheiro full stack"',
        '"full stack javascript"', '"javascript full stack"',
        // Stacks Completas
        '"react + node"', '"react + node.js"', '"vue + node"', '"angular + node"',
        '"next.js + prisma"', '"next.js + node"',
        '"laravel + vue"', '"laravel + react"', '"laravel + angular"',
        '"django + react"', '"django + vue"', '"flask + react"',
        '"spring + angular"', '"spring + react"', '"spring + vue"',
        '"rails + react"', '"rails + vue"', '"rails + angular"',
        '"php + javascript"', '"python + javascript"', '"java + javascript"',
        '"dotnet + angular"', '"dotnet + react"', '".net + angular"', '".net + react"',
        // Frontend + Backend Juntos
        '"html css javascript php"', '"html css javascript node"',
        '"html css javascript python"', '"html css javascript java"',
        '"react php"', '"vue php"', '"angular php"',
        '"react python"', '"vue python"', '"angular python"',
        '"react java"', '"vue java"', '"angular java"',
        // Metodologias e Arquitetura
        '"clean architecture"', '"arquitetura limpa"',
        '"domain driven design"', 'ddd',
        '"microservices"', '"microsserviços"', '"serverless"',
        '"rest api"', '"restful"', '"graphql"', '"grpc"',
        // DevOps e Infra Básica
        '"git"', '"github"', '"gitlab"', '"bitbucket"',
        '"docker"', '"ci/cd"', '"github actions"',
        'aws', 'azure', 'gcp',
        // Equivalentes em Inglês (Complemento)
        '"senior fullstack"', '"fullstack lead"', '"fullstack architect"',
        '"polyglot developer"', '"swiss army knife developer"',
        '"end to end developer"', '"e2e developer"'
    ],

    'Business Intelligence' => [
        // Ferramentas de BI
        '"power bi"', '"powerbi"', '"microsoft power bi"',
        'tableau', 'looker', 'qlikview', 'qliksense',
        'metabase', 'superset', '"google data studio"', '"looker studio"',
        '"ibm cognos"', '"sap business objects"', '"oracle bi"',
        'microstrategy', 'sisense', 'domo',
        // Cargos e Funções
        '"business intelligence"', 'bi', '"bi analyst"', '"analista de bi"',
        '"analista de business intelligence"',
        '"bi developer"', '"desenvolvedor bi"', '"bi engineer"',
        '"bi manager"', '"gerente de bi"', '"bi director"',
        '"diretor de bi"', '"head of bi"',
        '"data analyst"', '"analista de dados"',
        '"analytics engineer"', '"engenheiro de analytics"',
        '"reporting analyst"', '"analista de relatórios"',
        '"reporting developer"', '"desenvolvedor de relatórios"',
        // Modelagem e ETL
        '"data warehouse"', '"data lake"', '"data mart"',
        '"etl developer"', '"etl engineer"', '"etl"',
        '"data pipeline"', '"pipeline de dados"',
        '"data modeling"', '"modelagem de dados"',
        '"star schema"', '"snowflake schema"',
        'dbt', 'talend', 'informatica', 'ssis', '"sql server integration services"',
        'airflow', 'prefect', 'dagster',
        // SQL e Análise
        'sql', '"advanced sql"', '"sql avançado"',
        '"window functions"', '"funções de janela"',
        '"cubes"', '"olap"', '"oltp"',
        '"kpi"', '"dashboards"', '"relatórios"',
        // Métricas e Negócio
        '"revenue"', '"receita"', '"mrr"', '"arr"',
        '"churn"', '"retention"', '"retenção"',
        '"conversion"', '"conversão"',
        '"funnel"', '"funil"',
        '"cohort"', '"cohort analysis"',
        // Equivalentes em Inglês (Complemento)
        '"bi specialist"', '"bi consultant"',
        '"data visualization"', '"visualização de dados"',
        '"business analyst"', '"analista de negócios"',
        '"decision support"', '"suporte à decisão"'
    ],

    'Account Manager' => [
        // Cargos e Funções
        '"account manager"', '"gerente de contas"', '"gestor de contas"',
        '"account executive"', '"executivo de contas"', '"executiva de contas"',
        '"account director"', '"diretor de contas"',
        '"key account manager"', '"gerente de contas-chave"',
        '"key account"', '"contas-chave"', '"grandes contas"',
        '"senior account manager"', '"junior account manager"',
        // Relacionamento e Retenção
        '"relationship manager"', '"gerente de relacionamento"',
        '"client relationship"', '"relacionamento com clientes"',
        '"client success"', '"sucesso do cliente"',
        '"client retention"', '"retenção de clientes"',
        '"churn prevention"', '"prevenção de churn"',
        '"client satisfaction"', '"satisfação do cliente"',
        '"nps"', '"net promoter score"',
        // Expansão e Vendas
        '"upsell"', '"cross-sell"', '"expansão de contas"',
        '"renewal"', '"renovação"', '"renovação de contratos"',
        '"account growth"', '"crescimento de contas"',
        '"revenue growth"', '"crescimento de receita"',
        '"quota"', '"meta"', '"target"', '"objetivo"',
        '"proposal"', '"proposta"', '"proposta comercial"',
        '"negociação"', '"negotiation"', '"closing"', '"fechamento"',
        // Operação e Processo
        '"account planning"', '"planejamento de contas"',
        '"account review"', '"review de contas"',
        '"business review"', '"revisão de negócio"',
        '"sla"', '"service level agreement"',
        '"onboarding"', '"handoff"', '"transição"',
        '"ticket"', '"chamado"', '"demanda"',
        // Ferramentas
        'salesforce', 'hubspot', 'zendesk', 'intercom', 'gainsight',
        'pipedrive', 'zoho', 'freshdesk', 'crms',
        // Setores e Tipos
        '"b2b"', '"b2c"', '"enterprise"', '"mid-market"',
        '"smb"', '"pmes"', '"grandes empresas"',
        '"saas"', '"tech"', '"fintech"', '"healthtech"',
        // Equivalentes em Inglês (Complemento)
        '"account director"', '"client director"', '"strategic account manager"',
        '"enterprise account manager"', '"commercial manager"',
        '"business account manager"', '"corporate account manager"',
        '"client partner"', '"customer success manager"'
    ],

    'Recursos Humanos' => [
        // Gestão de Pessoas
        '"recursos humanos"', '"human resources"', '"rh"',
        '"gestão de pessoas"', '"people management"',
        '"gerente de rh"', '"rh manager"', '"hr manager"',
        '"diretor de rh"', '"rh director"', '"hr director"',
        '"head of people"', '"head of hr"', '"chief people officer"', '"cpo"',
        '"vp of people"', '"vp of hr"',
        // Cargos e Funções
        '"analista de rh"', '"hr analyst"', '"analista de recursos humanos"',
        '"assistente de rh"', '"hr assistant"',
        '"coordenador de rh"', '"hr coordinator"',
        '"especialista de rh"', '"hr specialist"',
        '"business partner"', '"hr business partner"', '"hrbp"',
        '"people partner"', '"people business partner"',
        // Atração e Recrutamento
        '"recrutamento e seleção"', '"recruitment and selection"',
        '"recrutador"', '"recruiter"', '"recrutador interno"',
        '"headhunter"', '"head hunting"',
        '"employer branding"', '"marca empregadora"',
        '"candidate experience"', '"experiência do candidato"',
        '"job description"', '"descrição de vaga"',
        // Desenvolvimento e Treinamento
        '"treinamento e desenvolvimento"', '"training and development"',
        '"t&d"', '"l&d"', '"learning and development"',
        '"plano de desenvolvimento individual"', '"pdi"',
        '"plano de carreira"', '"career plan"',
        '"sucessão"', '"succession planning"',
        '"mentoring"', '"mentoria"', '"coaching"',
        '"onboarding"', '"integração"',
        // Compensação e Benefícios
        '"comp & ben"', '"compensation and benefits"',
        '"remuneração"', '"remuneration"', '"compensação"',
        '"benefícios"', '"benefits"',
        '"cargo e salário"', '"job pricing"',
        '"survey salarial"', '"salary survey"',
        '"payroll"', '"folha de pagamento"',
        // Clima e Cultura
        '"clima organizacional"', '"organizational climate"',
        '"engajamento"', '"employee engagement"',
        '"cultura organizacional"', '"organizational culture"',
        '"e-nps"', '"employee nps"',
        '"retenção de talentos"', '"talent retention"',
        '"turnover"', '"rotatividade"',
        '"wellbeing"', '"bem-estar"', '"saúde ocupacional"',
        // Processos e Compliance
        '"legislação trabalhista"', '"labor law"',
        '"relações trabalhistas"', '"labor relations"',
        '"dp"', '"departamento pessoal"',
        '"admissão"', '"demissão"', '"avaliação de desempenho"',
        '"okrs"', '"kpis de rh"',
        // Ferramentas
        'workday', 'sap successfactors', 'bamboo hr', 'factorial',
        'totvs', '"linguagem corporativa"', '"gupy"', '"kenoby"',
        // Equivalentes em Inglês (Complemento)
        '"people operations"', '"people ops"',
        '"talent manager"', '"people manager"',
        '"hr operations"', '"rh operations"',
        '"organizational development"', '"desenvolvimento organizacional"'
    ],

    'People Analyst' => [
        // Cargos e Funções
        '"people analyst"', '"analista de pessoas"',
        '"people operations analyst"', '"analista de people operations"',
        '"people data analyst"', '"analista de dados de pessoas"',
        '"hr analyst"', '"analista de rh"',
        '"hr data analyst"', '"analista de dados de rh"',
        '"people analytics specialist"', '"especialista em people analytics"',
        '"people analytics manager"', '"gerente de people analytics"',
        '"workforce analyst"', '"analista de workforce"',
        '"hr metrics analyst"', '"analista de métricas de rh"',
        // People Analytics
        '"people analytics"', '"analytics de pessoas"',
        '"workforce analytics"', '"analytics de workforce"',
        '"hr analytics"', '"analytics de rh"',
        '"talent analytics"', '"analytics de talentos"',
        '"employee analytics"', '"analytics de funcionários"',
        '"people insights"', '"insights de pessoas"',
        '"data driven hr"', '"rh baseado em dados"',
        // Métricas e KPIs
        '"turnover rate"', '"taxa de rotatividade"',
        '"attrition rate"', '"taxa de atrito"',
        '"time to hire"', '"tempo para contratar"',
        '"time to fill"', '"tempo para preencher"',
        '"cost per hire"', '"custo por contratação"',
        '"employee lifetime value"', '"valor vitalício do funcionário"',
        '"engagement score"', '"score de engajamento"',
        '"e-nps"', '"employee nps"',
        '"absenteeism"', '"absenteísmo"',
        '"headcount"', '"quadro de funcionários"',
        '"headcount planning"', '"planejamento de headcount"',
        '"pay equity"', '"equidade salarial"',
        '"diversity metrics"', '"métricas de diversidade"',
        '"inclusion index"', '"índice de inclusão"',
        // Visualização e Relatórios
        '"dashboard de rh"', '"hr dashboard"',
        '"people dashboard"', '"dashboard de pessoas"',
        '"relatório de rh"', '"hr report"',
        '"workforce report"', '"relatório de workforce"',
        '"executive report"', '"relatório executivo"',
        '"data visualization"', '"visualização de dados"',
        '"power bi"', '"tableau"', '"google data studio"',
        // Ferramentas e Plataformas
        'workday', 'bamboo hr', 'sap successfactors', 'factorial',
        'lattice', '15five', 'culture amp', 'leapsome', 'peakon',
        'visier', 'one model', 'orgnostic',
        // Projetos e Programas
        '"attrition analysis"', '"análise de turnover"',
        '"retention analysis"', '"análise de retenção"',
        '"compensation analysis"', '"análise de compensação"',
        '"salary benchmarking"', '"benchmark salarial"',
        '"diversity report"', '"relatório de diversidade"',
        '"people strategy"', '"estratégia de pessoas"',
        '"workforce planning"', '"planejamento de workforce"',
        '"organizational design"', '"design organizacional"',
        // Equivalentes em Inglês (Complemento)
        '"people operations analyst"', '"workforce planning analyst"',
        '"talent analytics analyst"', '"hr business intelligence"',
        '"people insights analyst"', '"organizational analytics"',
        '"strategic workforce planning"',         '"people data scientist"'
    ],

    'Audiovisual' => [
        // Produção e Criação
        '"produção audiovisual"', '"audiovisual"', '"audio visual"',
        '"produção de vídeo"', '"video production"',
        '"videomaker"', '"videographer"', '"cinegrafista"',
        '"diretor de fotografia"', '"director of photography"',
        '"câmera"', '"camera operator"',
        // Edição e Pós-Produção
        '"edição de vídeo"', '"video editing"',
        '"editor de vídeo"', '"video editor"',
        '"final cut"', '"davinci resolve"', '"premiere pro"',
        '"after effects"', '"motion graphics"',
        '"color grading"', '"colorista"',
        '"mixagem de áudio"', '"audio mixing"',
        '"sound design"', '"design de som"',
        // Animação e Motion
        '"animação"', '"animation"', '"animator"',
        '"motion designer"', '"motion graphics designer"',
        '"2d animation"', '"animação 2d"',
        '"3d animation"', '"animação 3d"',
        '"blender"', '"cinema 4d"', '"maya"',
        '"after effects"',
        // Podcast e Áudio
        '"podcast"', '"podcaster"', '"produção de podcast"',
        '" locução"', '"locutor"', '"voice over"',
        '"áudio"', '"audio production"',
        '"garrafa de som"', '"home studio"',
        // Streaming e Live
        '"live"', '"live streaming"', '"transmissão ao vivo"',
        '"obs studio"', '"streamlabs"',
        '"transmissao"', '"live production"',
        // Cargos e Funções
        '"produzidor audiovisual"', '"audiovisual producer"',
        '"diretor de arte"', '"art director"',
        '"diretor criativo"', '"creative director"',
        '"motion lead"', '"head of audiovisual"',
        '"content producer"', '"produtor de conteúdo"',
        '"social media video"', '"vídeo para redes sociais"',
        // Fotografia
        '"fotografia"', '"photography"', '"fotógrafo"',
        '"editor de imagem"', '"image editor"',
        '"photoshop"', '"lightroom"',
        // Equivalentes em Inglês (Complemento)
        '"multimedia producer"', '"digital content producer"',
        '"video specialist"', '"multimedia specialist"',
        '"creative producer"', '"video content creator"'
    ],

    'Branding' => [
        // Identidade e Estratégia
        '"branding"', '"brand identity"', '"identidade de marca"',
        '"brand strategy"', '"estratégia de marca"',
        '"brand management"', '"gestão de marca"',
        '"brand positioning"', '"posicionamento de marca"',
        '"brand architecture"', '"arquitetura de marca"',
        '"rebranding"', '"repozição de marca"',
        // Cargos e Funções
        '"brand manager"', '"gerente de marca"',
        '"brand director"', '"diretor de marca"',
        '"brand strategist"', '"estrategista de marca"',
        '"brand designer"', '"designer de marca"',
        '"brand specialist"', '"especialista em marca"',
        '"brand analyst"', '"analista de marca"',
        '"creative director"', '"diretor criativo"',
        '"art director"', '"diretor de arte"',
        // Visual e Design
        '"logo"', '"logotipo"', '"logomarca"',
        '"manual de marca"', '"brand guideline"',
        '"brand book"', '"guia de marca"',
        '"paleta de cores"', '"color palette"',
        '"tipografia"', '"typography"',
        '"elementos visuais"', '"visual elements"',
        '"design gráfico"', '"graphic design"',
        '"ilustração"', '"illustration"',
        '"branding visual"', '"visual branding"',
        // Branding Digital
        '"branding digital"', '"digital branding"',
        '"social media branding"',
        '"personal branding"', '"branding pessoal"',
        '"employer branding"', '"marca empregadora"',
        '"brand experience"', '"experiência de marca"',
        '"brand touchpoint"', '"ponto de contato da marca"',
        '"brand consistency"', '"consistência da marca"',
        // Pesquisa e Análise
        '"brand research"', '"pesquisa de marca"',
        '"brand equity"', '"valor de marca"',
        '"brand awareness"', '"notoriedade de marca"',
        '"brand perception"', '"percepção de marca"',
        '"brand tracking"', '"monitoramento de marca"',
        '"brand audit"', '"auditoria de marca"',
        '"naming"', '"nomenclatura"',
        '"brand valuation"', '"avaliação de marca"',
        // Branding e Cultura
        '"brand culture"', '"cultura de marca"',
        '"brand voice"', '"tom de voz da marca"',
        '"brand tone"', '"tom da marca"',
        '"brand personality"', '"personalidade da marca"',
        '"brand story"', '"história da marca"',
        '"brand purpose"', '"propósito da marca"',
        '"brand values"', '"valores da marca"',
        // Ferramentas
        'figma', 'canva', 'adobe illustrator', 'photoshop',
        '"indesign"', '"corel draw"',
        // Equivalentes em Inglês (Complemento)
        '"brand lead"', '"head of brand"',
        '"corporate brand manager"',
        '"global brand manager"',
        '"brand and communications"',
        '"visual identity designer"'
    ],

    'Redação' => [
        // Redação e Criação de Texto
        '"redação"', '"redação publicitária"', '"redação criativa"',
        '"redator"', '"redator publicitário"', '"redator criativo"',
        '"copywriter"', '"copy"', '"redação de texto"',
        '"textos para web"', '"web copy"',
        '"textos publicitários"', '"ad copy"',
        '"redação de artigos"', '"artigos"',
        '"redação de posts"', '"posts para redes sociais"',
        '"roteiro"', '"roteirista"', '"script writer"',
        '"roteiro de vídeo"', '"roteiro para reels"',
        '"roteiro de podcast"', '"roteiro de apresentação"',
        // Conteúdo e SEO
        '"redação para SEO"', '"SEO writing"',
        '"blog writing"', '"redação de blog"',
        '"ghostwriting"', '"escritor fantasma"',
        '"white paper"', '"ebook"',
        '"case study"', '"estudo de caso"',
        '"newsletter"', '"redação de newsletter"',
        '"email marketing copy"',
        // Estilo e Tom de Voz
        '"brand voice"', '"tom de voz"',
        '"estilo editorial"', '"guidelines editoriais"',
        '"tone of voice"', '"escrita persuasiva"',
        '"copy persuasiva"', '"escrita emotiva"',
        // Ferramentas
        '"grammarly"', '"hemingway editor"',
        '"google docs"', '"notion"',
        // Equivalentes em Inglês (Complemento)
        '"content writer"', '"copy editor"',
        '"technical writer"', '"medical writer"',
        '"creative writer"', '"staff writer"',
        '"senior copywriter"', '"head of copy"',
        '"lead copywriter"', '"copy lead"',
        '"brand copywriter"', '"marketing copywriter"'
    ],

    'Content Writer' => [
        // Escrita e Conteúdo
        '"content writer"', '"escritor de conteúdo"',
        '"escritor de conteúdo digital"',
        '"redação de conteúdo"', '"content creation"',
        '"criação de conteúdo"', '"criador de conteúdo"',
        '"digital writer"', '"escritor digital"',
        '"content creator"', '"criador de conteúdo"',
        '"blog writer"', '"escritor de blog"',
        '"web writer"', '"escritor web"',
        // Tipos de Conteúdo
        '"artigos"', '"articles"', '"posts"',
        '"blog posts"', '"páginas de vendas"',
        '"landing pages"', '"landing page copy"',
        '"case studies"', '"white papers"',
        '"ebooks"', '"guias"', '"guides"',
        '"tutoriais"', '"tutorials"',
        '"newsletters"', '"email sequences"',
        '"social media content"',
        '"conteúdo para redes sociais"',
        '"scripts para vídeos"',
        '"video scripts"',
        '"product descriptions"',
        '"descrições de produto"',
        // SEO e Performance
        '"SEO content"', '"conteúdo para SEO"',
        '"keyword research"', '"pesquisa de palavras-chave"',
        '"content strategy"', '"estratégia de conteúdo"',
        '"content calendar"', '"calendário editorial"',
        '"content audit"', '"auditoria de conteúdo"',
        '"pillar content"', '"evergreen content"',
        // Ferramentas
        '"grammarly"', '"hemingway"',
        '"surfer seo"', '"semrush"',
        '"ahrefs"', '"answerthepublic"',
        '"google docs"', '"notion"', '"wordpress"',
        '"trello"', '"asana"',
        // Equivalentes em Inglês (Complemento)
        '"senior content writer"',
        '"staff content writer"',
        '"lead content writer"',
        '"content specialist"',
        '"content marketing writer"',
        '"UX writer"',
        '"technical content writer"',
        '"product content writer"',
        '"enterprise content writer"',
        '"freelance content writer"',
        '"content and copywriter"'
    ],

    'Ecommerce Analyst' => [
        // Dados e Métricas
        '"ecommerce analyst"', '"analista de ecommerce"',
        '"analista e-commerce"', '"e-commerce analyst"',
        '"analista de loja virtual"', '"analista de marketplace"',
        '"data analyst ecommerce"',
        '"analista de performance ecommerce"',
        '"analista de conversão"', '"conversion analyst"',
        '"analista de tráfego ecommerce"',
        '"analista de compras online"',
        // Métricas e KPIs
        '"métricas de ecommerce"', '"ecommerce metrics"',
        '"taxa de conversão"', '"conversion rate"',
        '"ticket médio"', '"average order value"',
        '"AOV"', '"carrinho abandonado"', '"cart abandonment"',
        '"taxa de rejeição"', '"bounce rate"',
        '"customer lifetime value"', '"CLV"',
        '"custo de aquisição"', '"CAC"', '"customer acquisition cost"',
        '"ROI"', '"ROAS"', '"return on ad spend"',
        '"revenue per visitor"', '"RPV"',
        '"taxa de retorno"', '"return rate"',
        // Plataformas e Analytics
        '"google analytics"', '"GA4"',
        '"hotjar"', '"clarity"', '"mixed panel"',
        '"shopify analytics"', '"magento analytics"',
        '"woocommerce analytics"',
        '"bigcommerce analytics"',
        '"google tag manager"', '"GTM"',
        '"pixel do facebook"', '"meta pixel"',
        '"google ads"', '"meta ads"',
        '"relatórios de vendas"', '"sales reports"',
        '"dashboard de ecommerce"',
        // Análise de Comportamento
        '"comportamento do comprador"',
        '"buyer behavior"', '"funil de conversão"',
        '"conversion funnel"', '"journey do cliente"',
        '"customer journey map"',
        '"análise de cohort"', '"cohort analysis"',
        '"segmentação de clientes"',
        '"customer segmentation"',
        '"análise de produto"', '"product analytics"',
        '"análise de pricing"', '"price analysis"',
        // Ferramentas
        '"excel"', '"google sheets"',
        '"power bi"', '"tableau"',
        '"looker studio"', '"data studio"',
        '"sql para análise"', '"sql queries"',
        '"python para dados"',
        // Equivalentes em Inglês (Complemento)
        '"senior ecommerce analyst"',
        '"ecommerce data analyst"',
        '"digital commerce analyst"',
        '"analytics manager ecommerce"',
        '"performance analyst ecommerce"'
    ],

    'Ecommerce Manager' => [
        // Gestão e Estratégia
        '"ecommerce manager"', '"gerente de ecommerce"',
        '"e-commerce manager"', '"gerente e-commerce"',
        '"diretor de ecommerce"',
        '"head of ecommerce"',
        '"líder de ecommerce"',
        '"gestão de loja virtual"',
        '"gestão de marketplace"',
        '"gestão de e-commerce"',
        '"diretor de vendas online"',
        // Estratégia de Vendas Online
        '"estratégia de ecommerce"',
        '"ecommerce strategy"',
        '"gestão de catálogo"', '"catalog management"',
        '"gestão de preço"', '"pricing management"',
        '"dynamic pricing"', '"precificação dinâmica"',
        '"gestão de promoções"',
        '"promotion management"',
        '"black friday"', '"cyber monday"',
        '"campanhas sazonais"',
        '"gestão de frete"', '"shipping management"',
        '"logística reversa"',
        // Plataformas
        '"shopify"', '"magento"', '"woocommerce"',
        '"vtex"', '"mercadolivre"', '"mercado pago"',
        '"amazon seller"', '"amazon fba"',
        '"magazine luiza"', '"magalu"',
        '"americanas"', '"shopee"',
        '"aliexpress"', '"tiktok shop"',
        '"blablacar"', '"OLX"',
        // Gestão de Equipe
        '"gestão de equipe comercial"',
        '"liderança de equipe"',
        '"people management"',
        '"OKR"', '"KPI de ecommerce"',
        '"metas de vendas"',
        '"reuniões de performance"',
        '"dashboards de acompanhamento"',
        // Marketing e Performance
        '"tráfego pago"', '"paid traffic"',
        '"google ads"', '"meta ads"',
        '"influencer marketing"',
        '"afiliados"', '"affiliate marketing"',
        '"email marketing"', '"push notification"',
        '"sms marketing"',
        '"remarketing"', '"retargeting"',
        // UX e Conversão
        '"otimização de conversão"',
        '"CRO"', '"conversion rate optimization"',
        '"A/B testing"', '"teste A/B"',
        '"UX de ecommerce"',
        '"checkout optimization"',
        '"mobile commerce"',
        '"m-commerce"',
        // Equivalentes em Inglês (Complemento)
        '"senior ecommerce manager"',
        '"head of ecommerce"',
        '"director of ecommerce"',
        '"global ecommerce manager"',
        '"regional ecommerce manager"'
    ],

    'Web Master' => [
        // Infraestrutura e Servidor
        '"webmaster"', '"web master"',
        '"administrador de sites"',
        '"administrador web"',
        '"gestor de sites"',
        '"site manager"',
        '"manutenção de sites"',
        '"site maintenance"',
        '"hospedagem"', '"hosting"',
        '"servidor"', '"server"',
        '"cloud hosting"', '"hospedagem na nuvem"',
        '"AWS"', '"google cloud"', '"azure"',
        '"CDN"', '"cloudflare"',
        '"domínio"', '"domain"',
        '"SSL"', '"certificado digital"',
        '"https"', '"dns"',
        '"servidor dedicado"', '"dedicated server"',
        '"vps"', '"shared hosting"',
        '"managed hosting"',
        // Segurança
        '"segurança de sites"',
        '"web security"',
        '"firewall"', '"WAF"',
        '"proteção contra ataques"',
        '"DDoS protection"',
        '"backup"', '"restauração"',
        '"antimalware"',
        '"hardening de servidor"',
        // Performance e Otimização
        '"performance de sites"',
        '"web performance"',
        '"velocidade de carregamento"',
        '"page speed"',
        '"Core Web Vitals"',
        '"LCP"', '"FID"', '"CLS"',
        '"otimização de imagens"',
        '"minificação"',
        '"cache de servidor"',
        '"server-side cache"',
        '"lazy loading"',
        '"compressão gzip"',
        '"brotli compression"',
        // CMS e Plataformas
        '"wordpress"', '"joomla"', '"drupal"',
        '"wix"', '"squarespace"',
        '"webflow"', '"shopify"',
        '"magento"', '"prestashop"',
        '"gatsby"', '"next.js"',
        '"cms management"',
        // Monitoramento e Analytics
        '"monitoramento de sites"',
        '"uptime monitoring"',
        '"google search console"',
        '"gsc"', '"analytics"',
        '"logs de servidor"',
        '"error tracking"',
        '"sentry"',
        '"new relic"',
        '"datadog"',
        // Deploy e Versionamento
        '"git"', '"github"',
        '"CI/CD"', '"deploy"',
        '"pipeline de deploy"',
        '"automatização de deploy"',
        '"docker"', '"kubernetes"',
        '"nginx"', '"apache"',
        '"php"', '"mysql"',
        '"cpanel"', '"plesk"',
        // Equivalentes em Inglês (Complemento)
        '"senior webmaster"',
        '"web operations engineer"',
        '"site reliability engineer"',
        '"SRE"', '"infrastructure engineer"',
        '"platform engineer"',
        '"devops engineer"',
        '"web operations manager"'
    ],

    'Content Manager' => [
        // Gestão de Conteúdo
        '"content manager"', '"gerente de conteúdo"',
        '"content lead"', '"líder de conteúdo"',
        '"head of content"', '"diretor de conteúdo"',
        '"content director"',
        '"content operations"',
        '"content ops"',
        '"gestão editorial"',
        '"editorial management"',
        '"gestão de conteúdo digital"',
        '"digital content management"',
        // Estratégia de Conteúdo
        '"content strategy"', '"estratégia de conteúdo"',
        '"content planning"', '"planejamento de conteúdo"',
        '"content calendar"', '"calendário editorial"',
        '"content audit"', '"auditoria de conteúdo"',
        '"content pillar"', '"pilares de conteúdo"',
        '"content mapping"', '"mapeamento de conteúdo"',
        '"buyer persona"', '"persona do cliente"',
        '"customer journey"',
        '"funil de conteúdo"',
        '"content funnel"',
        // Produção e Publicação
        '"content production"',
        '"produção de conteúdo"',
        '"content creation"',
        '"criação de conteúdo"',
        '"blog management"',
        '"gestão de blog"',
        '"content curation"',
        '"curadoria de conteúdo"',
        '"content localization"',
        '"localização de conteúdo"',
        '"content governance"',
        '"governança de conteúdo"',
        // Métricas e Performance
        '"content metrics"',
        '"métricas de conteúdo"',
        '"content performance"',
        '"performance de conteúdo"',
        '"content ROI"',
        '"ROI de conteúdo"',
        '"content attribution"',
        '"atribuição de conteúdo"',
        '"tráfego orgânico"',
        '"organic traffic"',
        '"engajamento de conteúdo"',
        '"content engagement"',
        // Equipe e Processo
        '"gestão de equipe de conteúdo"',
        '"content team management"',
        '"briefing de conteúdo"',
        '"content briefing"',
        '"revisão de conteúdo"',
        '"content review"',
        '"workflow de conteúdo"',
        '"content workflow"',
        '"APROVAÇÃO de conteúdo"',
        '"content approval"',
        '"redação revisão"',
        // Plataformas
        '"wordpress"', '"contentful"',
        '"strapi"', '"notion"',
        '"trello"', '"asana"',
        '"monday.com"', '"airtable"',
        '"google docs"', '"figma"',
        '"canva"', '"adobe creative cloud"',
        // Equivalentes em Inglês (Complemento)
        '"senior content manager"',
        '"global content manager"',
        '"content and communications manager"',
        '"editorial director"',
        '"content strategist manager"',
        '"content and brand manager"',
        '"head of content marketing"',
        '"VP of content"'
    ],

    'Engenheiro de Automação' => [
        // Automação de Processos
        '"engenheiro de automação"', '"automation engineer"',
        '"automação de processos"', '"process automation"',
        '"automação de tarefas"', '"task automation"',
        '"automação de workflows"', '"workflow automation"',
        '"automação de TI"', '"IT automation"',
        '"automação de infraestrutura"',
        '"infrastructure automation"',
        '"automação de deploy"',
        '"deployment automation"',
        '"automação de testes"',
        '"test automation"',
        '"automação de testes automatizados"',
        // Ferramentas de Automação
        '"ansible"', '"terraform"',
        '"jenkins"', '"github actions"',
        '"gitlab ci"', '"circleci"',
        '"puppet"', '"chef"',
        '"saltstack"',
        '"powershell"', '"bash"',
        '"python para automação"',
        '"rpa"', '"robotic process automation"',
        '"uipath"', '"automation anywhere"',
        '"blue prism"', '"power automate"',
        '"zapier"', '"make (integromat)"',
        '"n8n"', '"node-red"',
        // Automação de Infraestrutura
        '"iac"', '"infrastructure as code"',
        '"cloud automation"',
        '"automação de cloud"',
        '"kubernetes operator"',
        '"helm"', '"terraform cloud"',
        '"pulumi"', '"crossplane"',
        '"autoscaling"', '"auto scaling"',
        '"auto-healing"', '"self-healing infrastructure"',
        '"monitoramento automatizado"',
        '"alertas automatizados"',
        // Automação de CI/CD
        '"ci/cd pipeline"',
        '"continuous integration"',
        '"continuous delivery"',
        '"continuous deployment"',
        '"pipeline de integração contínua"',
        '"deploy automatizado"',
        '"rollback automatizado"',
        '"blue-green deployment"',
        '"canary deployment"',
        '"feature flags"',
        // Automação de Testes
        '"selenium"', '"cypress"',
        '"playwright"', '"puppeteer"',
        '"junit"', '"pytest"',
        '"testng"', '"robot framework"',
        '"automação de testes de regressão"',
        '"regression test automation"',
        '"automação de testes de API"',
        '"api test automation"',
        '"automação de testes de performance"',
        '"load testing automation"',
        '"jmeter"', '"gatling"',
        // Automação de Rede
        '"automação de rede"',
        '"network automation"',
        '"cisco dna center"',
        '"sdn"', '"software defined networking"',
        '"netconf"', '"restconf"',
        '"ansible network"',
        '"pyntc"',
        // Automação de Segurança
        '"automação de segurança"',
        '"security automation"',
        '"soar"', '"security orchestration"',
        '"siem automation"',
        '"automação de compliance"',
        '"automação de vulnerabilidade"',
        '"incident response automation"',
        // Automação Industrial (Complemento)
        '"plc"', '"programmable logic controller"',
        '"scada"', '"supervisory control"',
        '"hmi"', '"human machine interface"',
        '"automação industrial"',
        '"industrial automation"',
        '"iot automation"',
        '"indústria 4.0"',
        '"industry 4.0"',
        '"digital twin"',
        '"gemelo digital"',
        // DevOps e Automação
        '"devops automation"',
        '"sre automation"',
        '"platform engineering"',
        '"internal developer platform"',
        '"idp"',
        '"gitops"',
        '"argocd"', '"flux"',
        '"automação de configuração"',
        '"configuration automation"',
        '"desired state configuration"',
        // Equivalentes em Inglês (Complemento)
        '"senior automation engineer"',
        '"lead automation engineer"',
        '"automation architect"',
        '"head of automation"',
        '"automation platform engineer"',
        '"devops automation specialist"',
        '"infrastructure automation engineer"',
        '"test automation lead"',
        '"rpa developer"',
        '"automation consultant"',
        '"automation solutions engineer"'
    ],

    'Scrum Master' => [
        // Cargo e Identidade
        '"scrum master"', '" scrum master"',
        '"sm"', '"servant leader"',
        '"facilitador de agile"', '"facilitator"',
        '"coach de agile"', '"agile coach"',
        '"agile master"',
        // Scrum Framework
        '"scrum"', '"scrum framework"',
        '"sprint"', '"sprint planning"',
        '"sprint review"', '"sprint retrospective"',
        '"daily scrum"', '"daily standup"',
        '"standup"', '"daily"',
        '"backlog"', '"product backlog"',
        '"sprint backlog"',
        '"increment"', '"incremento"',
        '"definition of done"', '"definição de pronto"',
        '"definition of ready"',
        '"user story"', '"história de usuário"',
        '"epic"', '"épico"',
        '"task"', '"tarefa"',
        '"subtask"', '"subtarefa"',
        '"story points"', '"pontos de história"',
        '"velocity"', '"velocidade"',
        '"burndown chart"', '"gráfico de burndown"',
        '"burndown"', '"burndown"',
        '"burnup chart"', '"gráfico de burnup"',
        '"release"', '"lançamento"',
        '"product owner"', '"dono do produto"',
        '"development team"', '"time de desenvolvimento"',
        '"cross-functional team"',
        '"time multifuncional"',
        '"self-organizing team"',
        '"time auto-organizável"',
        // Eventos e Cerimônias
        '"refinement"', '"refinamento"',
        '"backlog refinement"',
        '"grooming"', '"3-amigos"',
        '"three amigos"',
        '"retrospectiva"', '"retrospective"',
        '"planning"', '"planejamento"',
        '"review"', '"revisão"',
        '"demonstração"', '"demo"',
        '"timebox"', '"caixa de tempo"',
        '"cadência"', '"cadence"',
        '"cerimônia"', '"ceremony"',
        // Kanban e Outros Frameworks
        '"kanban"', '"ban board"',
        '"quadro kanban"', '"kanban board"',
        '"wip limit"', '"limite de wip"',
        '"lead time"', '"tempo de lead"',
        '"cycle time"', '"tempo de ciclo"',
        '"throughput"',
        '"pull system"', '"sistema puxado"',
        '"xp"', '"extreme programming"',
        '"lean"', '"lean software"',
        '"safe"', '"scaled agile framework"',
        '"less"', '"large-scale scrum"',
        '"scrumban"',
        // Métricas e Performance
        '"velocity tracking"',
        '"rastreamento de velocidade"',
        '"cycle time analysis"',
        '"análise de lead time"',
        '"predictability"', '"previsibilidade"',
        '"throughput metrics"',
        '"métricas de throughput"',
        '"team health check"',
        '"check-up de saúde do time"',
        '"happiness metric"',
        '"métrica de felicidade"',
        // Agile Coach e Liderança
        '"servant leadership"',
        '"liderança servidora"',
        '"coaching de time"',
        '"team coaching"',
        '"facilitação"',
        '"facilitation"',
        '"facilitação de reuniões"',
        '"meeting facilitation"',
        '"conflito de time"',
        '"team conflict resolution"',
        '"mediation"', '"mediação"',
        '"empowerment"', '"empoderamento"',
        '"psychological safety"',
        '"segurança psicológica"',
        // Ferramentas
        '"jira"', '"jira software"',
        '"trello"', '"azure devops"',
        '"asana"', '"monday.com"',
        '"clickup"', '"linear"',
        '"figma"', '"mural"',
        '"miro"', '"miro"',
        '"slack"', '"microsoft teams"',
        '"confluence"', '"notion"',
        // Certificações e Formação
        '"psm"', '"professional scrum master"',
        '"psm i"', '"psm ii"', '"psm iii"',
        '"csm"', '"certified scrum master"',
        '"aic"', '"agile inland certification"',
        '"safe scrum master"', '"ssm"',
        '"iac"', '"ICAgile"',
        '"pmi-acp"', '"agile certified practitioner"',
        '"certificação scrum"',
        '"scrum certification"',
        // Áreas de Atuação
        '"scrum master de squads"',
        '"scrum master de tribes"',
        '"scrum master de chapters"',
        '"scrum master de guilds"',
        '"scrum master de squads"',
        '"delivery scrum master"',
        '"technical scrum master"',
        '"program scrum master"',
        '"enterprise scrum master"',
        '"scrum master para startups"',
        '"scrum master remoto"',
        '"scrum master distribuído"',
        '"multi-team scrum master"',
        '"scrum master de produto"',
        // Equivalente em Inglês
        '"senior scrum master"',
        '"lead scrum master"',
        '"principal scrum master"',
        '"head of agile"',
        '"director of agile"',
        '"agile delivery manager"',
        '"agile program manager"',
        '"vp of agile"',
        '"chief scrum master"',
        '"agile transformation lead"',
        '"agile practice lead"',
        '"scrum master coach"'
    ],

    'Web Designer' => [
        // Design de Interface
        '"web designer"', '"designer web"',
        '"design de interfaces"', '"interface design"',
        '"design de interfaces web"',
        '"ui designer"', '"designer de interface"',
        '"design de sites"', '"website design"',
        '"design de landing pages"',
        '"landing page design"',
        '"design de apps web"',
        '"web app design"',
        '"design de dashboards"',
        '"dashboard design"',
        '"design de forms"', '"form design"',
        // Layout e Prototipação
        '"layout web"', '"web layout"',
        '"wireframe"', '"wireframes"',
        '"mockup"', '"mockups"',
        '"prototipação"', '"prototyping"',
        '"high fidelity"', '"high-fidelity prototype"',
        '"low fidelity"', '"low-fidelity prototype"',
        '"design system"', '"sistema de design"',
        '"pattern library"',
        '"component library"',
        '"design tokens"',
        // Responsividade e Mobile
        '"design responsivo"', '"responsive design"',
        '"mobile first"', '"mobile-first design"',
        '"adaptive design"', '"design adaptativo"',
        '"breakpoint"', '"ponto de quebra"',
        '"mobile web design"',
        '"progressive web app"', '"pwa design"',
        // UX e Usabilidade
        '"ux design"', '"design de experiência"',
        '"user experience design"',
        '"usabilidade"', '"usability"',
        '"user research"', '"pesquisa com usuários"',
        '"usability testing"', '"teste de usabilidade"',
        '"user testing"',
        '"heuristic evaluation"',
        '"avaliação heurística"',
        '"accessibility"', '"acessibilidade"',
        '"wcag"', '"a11y"',
        '"user flow"', '"fluxo do usuário"',
        '"information architecture"',
        '"arquitetura da informação"',
        '"navigation design"',
        '"design de navegação"',
        // Ferramentas de Design
        '"figma"', '"sketch"',
        '"adobe xd"', '"adobe photoshop"',
        '"adobe illustrator"',
        '"invision"', '"zeplin"',
        '"marvel app"', '"balsamiq"',
        '"axure"', '"origami"',
        '"principle"', '"framer"',
        '"canva"', '"penpot"',
        '"svg"', '"css design"',
        '"html design"',
        '"sass design"',
        // Design Systems e Componentes
        '"material design"',
        '"human interface guidelines"',
        '"apple hig"',
        '"bootstrap design"',
        '"tailwind design"',
        '"component design"',
        '"button design"', '"design de botões"',
        '"typography web"', '"tipografia web"',
        '"color system"', '"sistema de cores"',
        '"icon design"', '"design de ícones"',
        '"iconography"', '"iconografia"',
        // Colaboração e Processo
        '"handoff"', '"design handoff"',
        '"design-to-dev"',
        '"entrega de design"',
        '"design review"',
        '"revisão de design"',
        '"stakeholder feedback"',
        '"feedback de stakeholders"',
        '"design iteration"',
        '"iteração de design"',
        '"figma file"',
        '"design ops"',
        // Equivalentes em Inglês (Complemento)
        '"senior web designer"',
        '"lead web designer"',
        '"web design lead"',
        '"ui/ux designer"',
        '"product designer"',
        '"digital designer"',
        '"interactive designer"',
        '"front-end designer"',
        '"visual web designer"',
        '"creative web designer"'
    ],

    'Designer Gráfico' => [
        // Identidade Visual
        '"designer gráfico"', '"graphic designer"',
        '"design gráfico"', '"graphic design"',
        '"identidade visual"', '"visual identity"',
        '"branding visual"', '"visual branding"',
        '"logo design"', '"design de logo"',
        '"logotipo"', '"logomarca"',
        '"logotype"', '"logomark"',
        '"symbol design"', '"símbolo"',
        '"wordmark"', '"lettermark"',
        '"monogram"', '"monograma"',
        // Material Impresso
        '"design impresso"', '"print design"',
        '"flyer"', '"flyer design"',
        '"folder"', '"brochure"',
        '"cartão de visita"', '"business card"',
        '"banner impresso"',
        '"outdoor"', '"outdoor design"',
        '"billboard"', '"outdoor"',
        '"encartes"', '"leaflet"',
        '"catálogo"', '"catalog"',
        '"revista"', '"magazine"',
        '"poster"', '"pôster"',
        '"outdoor"', '"outdoor"',
        '"vinyl"', '"adesivo"',
        '"etiqueta"', '"label"',
        '"embalagem"', '"packaging"',
        '"packaging design"',
        '"rótulo"', '"label design"',
        // Design para Redes Sociais
        '"design para redes sociais"',
        '"social media design"',
        '"instagram design"',
        '"facebook design"',
        '"linkedin design"',
        '"twitter design"',
        '"stories design"',
        '"reels cover"',
        '"thumbnail"', '"miniatura"',
        '"capa de vídeo"',
        '"video thumbnail"',
        '"post design"',
        '"design de posts"',
        '"carrossel"', '"carousel design"',
        '"template de posts"',
        // Ilustração e Arte
        '"ilustração"', '"illustration"',
        '"ilustração digital"',
        '"digital illustration"',
        '"ilustração vetorial"',
        '"vector illustration"',
        '"pixel art"',
        '"3d illustration"',
        '"ilustração 3d"',
        '"flat illustration"',
        '"ilustração flat"',
        '"icon illustration"',
        '"infográfico"', '"infographic"',
        '"mapa mental"',
        '"mind map"',
        '"data visualization"',
        '"visualização de dados"',
        // Tipografia
        '"tipografia"', '"typography"',
        '"fonte"', '"font"',
        '"fonte personalizada"',
        '"custom font"',
        '"font pairing"',
        '"combinção de fontes"',
        '"lettering"',
        '"caligrafia"', '"calligraphy"',
        '"typographic design"',
        '"design tipográfico"',
        // Cor e Paleta
        '"color palette"', '"paleta de cores"',
        '"color theory"', '"teoria das cores"',
        '"color psychology"',
        '"psicologia das cores"',
        '"pantone"', '"pantone colors"',
        '"cmyk"', '"rgb"', '"hex"',
        '"gradient design"',
        '"design de gradientes"',
        // Ferramentas
        '"adobe photoshop"',
        '"adobe illustrator"',
        '"adobe indesign"',
        '"corel draw"',
        '"canva"',
        '"affinity designer"',
        '"affinity photo"',
        '"procreate"',
        '"clip studio paint"',
        '"krita"',
        '"inkscape"',
        '"gimp"',
        '"figma"',
        '"sketch"',
        '"photopea"',
        // Motion e Animção
        '"motion design"',
        '"animação 2d"',
        '"gif design"',
        '"animated gif"',
        '"animated banner"',
        '"banner animado"',
        '"social media animation"',
        '"logo animation"',
        '"animated logo"',
        '"kinetic typography"',
        '"tipografia cinética"',
        // Print e Produção
        '"CMYK"', '"impressão offset"',
        '"digital printing"',
        '"impressão digital"',
        '"risografia"', '"risograph"',
        '"serigrafia"', '"serigraphy"',
        '"hot stamping"', '"douramento"',
        '"embossing"', '"relevo"',
        '"corte especial"', '"die cut"',
        '"acabamento"', '"finishing"',
        // Equivalentes em Inglês (Complemento)
        '"senior graphic designer"',
        '"lead graphic designer"',
        '"graphic design lead"',
        '"art director"',
        '"creative designer"',
        '"visual designer"',
        '"brand designer"',
        '"packaging designer"',
        '"print designer"',
        '"digital graphic designer"',
        '"motion graphic designer"',
        '"senior visual designer"',
        '"head of design"',
        '"creative lead"',
        '"design director"'
    ],

    'Cloud Solutions' => [
        // Provedores e Plataformas
        '"cloud solutions"', '"soluções em nuvem"',
        '"cloud computing"', '"computação em nuvem"',
        '"aws"', '"amazon web services"',
        '"google cloud"', '"gcp"',
        '"microsoft azure"', '"azure"',
        '"oracle cloud"', '"oci"',
        '"ibm cloud"', '"alibaba cloud"',
        '"digitalocean"', '"linode"',
        '"vultr"', '"heroku"',
        '"netlify"', '"vercel"',
        '"cloudflare workers"',
        '"firebase"', '"supabase"',
        // Serviços de Computação
        '"ec2"', '"compute engine"',
        '"virtual machines"', '"máquinas virtuais"',
        '"lambda"', '"cloud functions"',
        '"serverless"', '"sem servidor"',
        '"fargate"', '"cloud run"',
        '"kubernetes"', '"eks"', '"gke"', '"aks"',
        '"docker"', '"containerization"',
        '"containers"', '"ecs"',
        '"app engine"', '"elastic beanstalk"',
        '"cloud foundry"',
        // Armazenamento
        '"s3"', '"cloud storage"',
        '"blob storage"', '"object storage"',
        '"storage bucket"',
        '"block storage"',
        '"file storage"', '"efs"',
        '"ebs"', '"persistent disk"',
        '"cloud storage classes"',
        '"lifecycle policies"',
        // Bancos de Dados
        '"rds"', '"cloud sql"',
        '"cloud spanner"', '"aurora"',
        '"dynamodb"', '"cosmos db"',
        '"firestore"', '"bigtable"',
        '"neptune"', '"graph database"',
        '"elasticache"', '"cloud redis"',
        '"memorystore"', '"cloud memcached"',
        '"cloud database"', '"managed database"',
        '"database as a service"', '"dbas"',
        // Rede e CDN
        '"vpc"', '"virtual private cloud"',
        '"cloudfront"', '"cdn"',
        '"load balancer"', '"balanceamento de carga"',
        '"route 53"', '"cloud dns"',
        '"api gateway"', '"cloudflare"',
        '"cloud networking"',
        '"cloud firewall"',
        '"security group"', '"nsg"',
        '"nat gateway"', '"internet gateway"',
        '"vpn cloud"', '"direct connect"',
        '"expressroute"', '"cloud interconnect"',
        // Segurança e Identidade
        '"iam"', '"identity and access management"',
        '"cloud security"',
        '"segurança na nuvem"',
        '"cloud encryption"',
        '"kms"', '"key management"',
        '"certificate manager"',
        '"ssl cloud"',
        '"waf"', '"web application firewall"',
        '"shield"', '"ddos protection"',
        '"security hub"',
        '"cloud security posture"',
        '"cspm"', '"cloud security posture management"',
        '"siem cloud"',
        '"zero trust cloud"',
        // DevOps e Automação
        '"cloud devops"',
        '"infrastructure as code"',
        '"iac cloud"',
        '"terraform cloud"',
        '"cloudformation"',
        '"cloud deployment manager"',
        '"pulumi cloud"',
        '"cloud automation"',
        '"ci/cd cloud"',
        '"cloud pipeline"',
        '"cloud build"',
        '"codepipeline"',
        '"github actions cloud"',
        // Analytics e Dados
        '"bigquery"', '"redshift"',
        '"snowflake cloud"',
        '"cloud data warehouse"',
        '"cloud data lake"',
        '"databricks cloud"',
        '"cloud analytics"',
        '"cloud BI"',
        '"looker cloud"',
        '"power bi cloud"',
        '"cloud data engineering"',
        '"cloud data pipeline"',
        '"cloud etl"',
        '"cloud data processing"',
        // IA e Machine Learning
        '"sagemaker"', '"vertex ai"',
        '"azure ml"', '"cloud ml"',
        '"cloud ai"',
        '"machine learning cloud"',
        '"deep learning cloud"',
        '"nlp cloud"',
        '"computer vision cloud"',
        '"cloud ai services"',
        '"cloud speech"',
        '"cloud vision"',
        '"cloud natural language"',
        '"bedrock"', '"cloud llm"',
        // Migração e Hybrid
        '"cloud migration"',
        '"migração para nuvem"',
        '"lift and shift"',
        '"replatforming"',
        '"refactoring for cloud"',
        '"cloud hybrid"', '"hybrid cloud"',
        '"cloud multi"', '"multi-cloud"',
        '"cloud on-premise"',
        '"cloud strategy"',
        '"cloud readiness assessment"',
        '"cloud cost optimization"',
        '"finops"', '"cloud finops"',
        '"cloud governance"',
        '"cloud compliance"',
        '"cloud landing zone"',
        '"cloud foundation"',
        // Certificações
        '"aws certified"', '"azure certified"',
        '"gcp certified"', '"cloud certified"',
        '"aws solutions architect"',
        '"azure solutions architect"',
        '"gcp cloud architect"',
        '"aws devops engineer"',
        '"azure devops engineer"',
        '"kubernetes certification"', '"cka"',
        '"terraform certification"',
        '"cloud+ certification"',
        // Equivalentes em Inglês (Complemento)
        '"cloud solutions architect"',
        '"cloud engineer"',
        '"cloud infrastructure engineer"',
        '"cloud platform engineer"',
        '"cloud operations engineer"',
        '"cloud security engineer"',
        '"cloud data engineer"',
        '"cloud native engineer"',
        '"cloud devops engineer"',
        '"cloud consultant"',
        '"head of cloud"',
        '"cloud practice lead"',
        '"cloud center of excellence"',
        '"ccoe"',
        '"chief cloud architect"',
        '"cloud program manager"',
        '"cloud transformation lead"'
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
            $raw = $termo;
            $termo = mb_strtolower(removerAcentosCat(trim($termo, '"')));

            if (str_starts_with($raw, '"')) {
                if (str_contains($titulo, $termo)) {
                    $tags[] = $categoria;
                    break;
                }
            } else {
                if (preg_match('/\b' . preg_quote($termo, '/') . '\b/u', $titulo)) {
                    $tags[] = $categoria;
                    break;
                }
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
        'Desenvolvedor Mobile' => 'desenvolvedor-mobile',
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
        'Financeiro' => 'financeiro',
        'Administrativo' => 'administrativo',
        'Jurídico' => 'juridico',
        'Talent Acquisition' => 'talent-acquisition',
        'Tech Recruiter' => 'tech-recruiter',
        'Comunicação' => 'comunicacao',
        'Social Mídia' => 'social-midia',
        'Marketing' => 'marketing',
        'Analista de SEO' => 'analista-de-seo',
        'Gestor de Tráfego' => 'gestor-de-trafego',
        'Gerente de Projetos' => 'gerente-de-projetos',
        'Analista de Sistemas' => 'analista-de-sistemas',
        'Governança de TI' => 'governanca-de-ti',
        'Frontend' => 'frontend',
        'Backend' => 'backend',
        'Office Suite' => 'office-suite',
        'Fullstack' => 'fullstack',
        'Business Intelligence' => 'business-intelligence',
        'Account Manager' => 'account-manager',
        'Recursos Humanos' => 'recursos-humanos',
        'People Analyst' => 'people-analyst',
        'Audiovisual' => 'audiovisual',
        'Branding' => 'branding',
        'Redação' => 'redacao',
        'Content Writer' => 'content-writer',
        'Ecommerce Analyst' => 'ecommerce-analyst',
        'Ecommerce Manager' => 'ecommerce-manager',
        'Web Master' => 'web-master',
        'Content Manager' => 'content-manager',
        'Engenheiro de Automação' => 'engenheiro-de-automacao',
        'Scrum Master' => 'scrum-master',
        'Web Designer' => 'web-designer',
        'Designer Gráfico' => 'designer-grafico',
        'Cloud Solutions' => 'cloud-solutions',
        'Segurança da Informação' => 'seguranca-informacao',
        'Product Owner' => 'product-owner',
        'Product Manager' => 'product-manager',
        'Sem Categoria' => 'sem-categoria',
    ];
    return $map[$nome] ?? 'sem-categoria';
}
?>