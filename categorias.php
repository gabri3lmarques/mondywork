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
        'Segurança da Informação' => 'seguranca-informacao',
        'Sem Categoria' => 'sem-categoria',
    ];
    return $map[$nome] ?? 'sem-categoria';
}
?>