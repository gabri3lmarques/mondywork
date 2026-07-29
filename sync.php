<?php
/**
 * Worker de Sincronização - InHire + Ashby + Greenhouse + Senior
 */
require __DIR__ . '/lib/Database.php';
require __DIR__ . '/lib/VagaRepository.php';
require __DIR__ . '/lib/Log.php';

$configFile = __DIR__ . '/config.local.php';
$dbConfig = require file_exists($configFile) ? $configFile : __DIR__ . '/config.php';

$ignorar_todas = false;
$empresasInhireNacional  = require __DIR__ . '/config/empresas_inhire_nacional.php';
$ignorarInhireNacional = $ignorar_todas;

$ignorar_todas = false;
$empresasInhireExterior  = require __DIR__ . '/config/empresas_inhire_exterior.php';
$ignorarInhireExterior = $ignorar_todas;

$ignorar_todas = false;
$empresasAshbyNacional   = require __DIR__ . '/config/empresas_ashby_nacional.php';
$ignorarAshbyNacional = $ignorar_todas;

$ignorar_todas = false;
$empresasAshbyExterior    = require __DIR__ . '/config/empresas_ashby_exterior.php';
$ignorarAshbyExterior = $ignorar_todas;

$ignorar_todas = false;
$empresasGreenhouseNacional = require __DIR__ . '/config/empresas_greenhouse_nacional.php';
$ignorarGreenhouseNacional = $ignorar_todas;

$ignorar_todas = false;
$empresasGreenhouseExterior = require __DIR__ . '/config/empresas_greenhouse_exterior.php';
$ignorarGreenhouseExterior = $ignorar_todas;

$ignorar_todas = false;
$empresasSeniorNacional   = require __DIR__ . '/config/empresas_senior_nacional.php';
$ignorarSeniorNacional = $ignorar_todas;

$ignorar_todas = false;
$empresasSeniorExterior    = require __DIR__ . '/config/empresas_senior_exterior.php';
$ignorarSeniorExterior = $ignorar_todas;

$empresasIgnorar          = require __DIR__ . '/config/empresas_ignorar.php';

try {
    $pdo = conectarBanco($dbConfig);
    setupSchema($pdo);

    define('MAX_DIAS_VAGA', 45);

$ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 15,
    ]);

    echo "[" . date('Y-m-d H:i:s') . "] Iniciando sincronizacao...\n";
    logMsg("Sync iniciado");

    // ── Orphan cleanup ──
    $todasEmpresas = array_merge(
        array_values($empresasInhireNacional),
        array_values($empresasInhireExterior),
        array_values($empresasAshbyNacional),
        array_values($empresasAshbyExterior),
        array_values($empresasGreenhouseNacional),
        array_values($empresasGreenhouseExterior),
        array_values($empresasSeniorNacional),
        array_values($empresasSeniorExterior),
        array_values($empresasIgnorar)
    );
    if (!empty($todasEmpresas)) {
        $placeholders = implode(',', array_fill(0, count($todasEmpresas), '?'));
        $stmt = $pdo->prepare("UPDATE vagas SET status = 'inativa' WHERE empresa NOT IN ($placeholders) AND status = 'ativa' AND vaga_id_externo NOT LIKE 'manual-%' AND is_premium = 0 AND vaga_id_externo NOT LIKE 'mw_prem_%'");
        $stmt->execute($todasEmpresas);
        $afetadas = $stmt->rowCount();
        if ($afetadas > 0) {
            echo "[ORFAOS] $afetadas vagas inativadas (empresa fora do escopo).\n";
        }
    }

    // ── Inativar vagas ativas com mais de MAX_DIAS_VAGA dias ──
    $stmtOld = $pdo->prepare("UPDATE vagas SET status = 'inativa' WHERE status = 'ativa' AND publicado_em IS NOT NULL AND publicado_em < DATE_SUB(NOW(), INTERVAL ? DAY) AND is_premium = 0 AND vaga_id_externo NOT LIKE 'mw_prem_%'");
    $stmtOld->execute([MAX_DIAS_VAGA]);
    $oldAffected = $stmtOld->rowCount();
    if ($oldAffected > 0) {
        echo "[ANTIGAS] $oldAffected vagas inativadas (mais de " . MAX_DIAS_VAGA . " dias).\n";
    }

    // ── Remove empresas ignoradas ──
    $empresasInhireNacional = array_filter($empresasInhireNacional, fn($nome) => !in_array($nome, $empresasIgnorar));
    $empresasInhireExterior = array_filter($empresasInhireExterior, fn($nome) => !in_array($nome, $empresasIgnorar));
    $empresasAshbyNacional  = array_filter($empresasAshbyNacional,  fn($nome) => !in_array($nome, $empresasIgnorar));
    $empresasAshbyExterior  = array_filter($empresasAshbyExterior,  fn($nome) => !in_array($nome, $empresasIgnorar));
    $empresasGreenhouseNacional = array_filter($empresasGreenhouseNacional, fn($nome) => !in_array($nome, $empresasIgnorar));
    $empresasGreenhouseExterior = array_filter($empresasGreenhouseExterior, fn($nome) => !in_array($nome, $empresasIgnorar));
    $empresasSeniorNacional  = array_filter($empresasSeniorNacional,  fn($nome) => !in_array($nome, $empresasIgnorar));
    $empresasSeniorExterior  = array_filter($empresasSeniorExterior,  fn($nome) => !in_array($nome, $empresasIgnorar));

    // ── Aplicar ignorar_todas ──
    if ($ignorarInhireNacional)       { $empresasInhireNacional = [];       echo "[CONFIG] InHire Nacional ignorado.\n"; }
    if ($ignorarInhireExterior)       { $empresasInhireExterior = [];       echo "[CONFIG] InHire Exterior ignorado.\n"; }
    if ($ignorarAshbyNacional)        { $empresasAshbyNacional = [];        echo "[CONFIG] Ashby Nacional ignorado.\n"; }
    if ($ignorarAshbyExterior)        { $empresasAshbyExterior = [];        echo "[CONFIG] Ashby Exterior ignorado.\n"; }
    if ($ignorarGreenhouseNacional)   { $empresasGreenhouseNacional = [];   echo "[CONFIG] Greenhouse Nacional ignorado.\n"; }
    if ($ignorarGreenhouseExterior)   { $empresasGreenhouseExterior = [];   echo "[CONFIG] Greenhouse Exterior ignorado.\n"; }
    if ($ignorarSeniorNacional)       { $empresasSeniorNacional = [];       echo "[CONFIG] Senior Nacional ignorado.\n"; }
    if ($ignorarSeniorExterior)       { $empresasSeniorExterior = [];       echo "[CONFIG] Senior Exterior ignorado.\n"; }

    // ── InHire Nacional ──
    if (!empty($empresasInhireNacional)) {
        sincronizarInHire($pdo, $ch, $empresasInhireNacional, $dbConfig, 'nacional');
    }

    // ── InHire Exterior ──
    if (!empty($empresasInhireExterior)) {
        sincronizarInHire($pdo, $ch, $empresasInhireExterior, $dbConfig, 'exterior');
    }

    // ── Ashby Nacional ──
    if (!empty($empresasAshbyNacional)) {
        sincronizarAshby($pdo, $ch, $empresasAshbyNacional, $dbConfig, 'nacional');
    }

    // ── Ashby Exterior ──
    if (!empty($empresasAshbyExterior)) {
        sincronizarAshby($pdo, $ch, $empresasAshbyExterior, $dbConfig, 'exterior');
    }

    // ── Greenhouse Nacional ──
    if (!empty($empresasGreenhouseNacional)) {
        sincronizarGreenhouse($pdo, $ch, $empresasGreenhouseNacional, $dbConfig, 'nacional');
    }

    // ── Greenhouse Exterior ──
    if (!empty($empresasGreenhouseExterior)) {
        sincronizarGreenhouse($pdo, $ch, $empresasGreenhouseExterior, $dbConfig, 'exterior');
    }

    // ── Senior Nacional ──
    if (!empty($empresasSeniorNacional)) {
        sincronizarSenior($pdo, $ch, $empresasSeniorNacional, $dbConfig, 'nacional');
    }

    // ── Senior Exterior ──
    if (!empty($empresasSeniorExterior)) {
        sincronizarSenior($pdo, $ch, $empresasSeniorExterior, $dbConfig, 'exterior');
    }

    curl_close($ch);
    echo "\n[" . date('Y-m-d H:i:s') . "] Sincronizacao finalizada.\n";
    logMsg("Sync finalizado");

} catch (Exception $e) {
    echo "\n[ERRO FATAL] " . $e->getMessage() . "\n";
    logError("Sync falhou", ['error' => $e->getMessage()]);
}

// ─────────────────────────────────────────────
// InHire
// ─────────────────────────────────────────────

function sincronizarInHire(PDO $pdo, $ch, array $empresas, $dbConfig, string $origem = 'nacional'): void
{
    $tamanhoLote = 2;
    $chunks = array_chunk($empresas, $tamanhoLote, true);
    $totalChunks = count($chunks);

    foreach ($chunks as $chunkIndex => $chunk) {
        echo "\n--- InHire Lote " . ($chunkIndex + 1) . " de $totalChunks ---\n";
        manterConexao($pdo, $dbConfig);

        foreach ($chunk as $slug => $nomeReal) {
            echo "\nBuscando: $nomeReal...\n";
            manterConexao($pdo, $dbConfig);

            $stmtCheck = $pdo->prepare("SELECT vaga_id_externo FROM vagas WHERE empresa = :empresa");
            $stmtCheck->execute([':empresa' => $nomeReal]);
            $idsNoBanco = $stmtCheck->fetchAll(PDO::FETCH_COLUMN);

            $urlBase = "https://api.inhire.app/job-posts/public/pages";
            curl_setopt_array($ch, [
                CURLOPT_HTTPHEADER => ["Accept: application/json", "x-tenant: $slug"],
                CURLOPT_URL => "$urlBase?company=$slug",
                CURLOPT_POST => false,
            ]);

            $resLista = curl_exec($ch);
            $httpCodeLista = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCodeLista !== 200) {
                echo " - [ERRO HTTP $httpCodeLista] Falha ao acessar a lista. Pulando...\n";
                continue;
            }

            $dadosJson = json_decode($resLista, true);
            $vagasNaAPI = $dadosJson['jobsPage'] ?? [];

            $idsNaAPI = array_column($vagasNaAPI, 'jobId');
            $idsParaInativar = array_diff($idsNoBanco, $idsNaAPI);
            if (!empty($idsParaInativar)) {
                $placeholders = implode(',', array_fill(0, count($idsParaInativar), '?'));
                $stmtInativar = $pdo->prepare("UPDATE vagas SET status = 'inativa' WHERE vaga_id_externo IN ($placeholders) AND status = 'ativa' AND is_premium = 0 AND vaga_id_externo NOT LIKE 'mw_prem_%'");
                $stmtInativar->execute(array_values($idsParaInativar));
                echo " - " . count($idsParaInativar) . " vagas inativadas (encerram no site).\n";
            }

            if (empty($vagasNaAPI)) {
                echo " - Nenhuma vaga ativa encontrada na API.\n";
                continue;
            }

            foreach ($vagasNaAPI as $vaga) {
                $jobId = $vaga['jobId'];
                $titulo = $vaga['displayName'];

                if (in_array($jobId, $idsNoBanco)) {
                    echo " - [MANTIDA] $titulo\n";
                    continue;
                }

                $publicadoLista = $vaga['publishedAt'] ?? ($vaga['data']['publishedAt'] ?? null);
                if ($publicadoLista !== null && strtotime($publicadoLista) < strtotime('-'.MAX_DIAS_VAGA.' days')) {
                    echo " - [IGNORADA] $titulo (mais de " . MAX_DIAS_VAGA . " dias)\n";
                    continue;
                }

                curl_setopt($ch, CURLOPT_URL, "$urlBase/$jobId?company=$slug");
                $resDet = curl_exec($ch);

                $httpCodeDet = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($httpCodeDet === 404) {
                    echo " - [ERRO 404] Detalhes nao encontrados para '$titulo'. Pulando...\n";
                    continue;
                } elseif ($httpCodeDet !== 200) {
                    echo " - [ERRO $httpCodeDet] Falha ao buscar '$titulo'. Pulando...\n";
                    continue;
                }

                $detalhe = json_decode($resDet, true);
                $descricao = $detalhe['description'] ?? ($detalhe['data']['description'] ?? 'Descricao nao fornecida.');
                $resumo = extrairResumo($descricao);
                $modeloTrabalho = $detalhe['workplaceType'] ?? ($detalhe['data']['workplaceType'] ?? null);
                $publicadoEm = isset($detalhe['publishedAt']) ? date('Y-m-d H:i:s', strtotime($detalhe['publishedAt'])) : (isset($detalhe['data']['publishedAt']) ? date('Y-m-d H:i:s', strtotime($detalhe['data']['publishedAt'])) : null);

                if ($publicadoEm !== null && strtotime($publicadoEm) < strtotime('-'.MAX_DIAS_VAGA.' days')) {
                    echo " - [IGNORADA] $titulo (mais de " . MAX_DIAS_VAGA . " dias)\n";
                    continue;
                }

                upsertVaga($pdo, [
                    'vaga_id_externo' => $jobId,
                    'titulo'          => $titulo,
                    'empresa'         => $nomeReal,
                    'localizacao'     => $vaga['location'],
                    'modelo_trabalho' => $modeloTrabalho,
                    'url_vaga'        => "https://{$slug}.inhire.app/vagas/{$jobId}/" . slugify($titulo),
                    'descricao'       => $descricao,
                    'resumo'          => $resumo,
                    'publicado_em'    => $publicadoEm,
                    'origem'          => $origem,
                ]);

                echo " - [NOVA] $titulo\n";
                usleep(200000);
            }
        }

        if ($chunkIndex < $totalChunks - 1) {
            echo "\n--- Aguardando 2 segundos antes do proximo lote... ---\n";
            sleep(2);
        }
    }
}

// ─────────────────────────────────────────────
// Ashby (GraphQL)
// ─────────────────────────────────────────────

function fetchAshbyGraphQL($ch, string $operationName, array $variables, string $query): ?array
{
    $url = "https://jobs.ashbyhq.com/api/non-user-graphql?op=" . urlencode($operationName);
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
            'apollographql-client-name: frontend_non_user',
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'operationName' => $operationName,
            'variables'     => $variables,
            'query'         => $query,
        ]),
    ]);

    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode !== 200) {
        echo " - [ERRO HTTP $httpCode] Ashby API para '$operationName'\n";
        return null;
    }

    $decoded = json_decode($res, true);
    if (isset($decoded['errors'])) {
        echo " - [ERRO GraphQL] " . ($decoded['errors'][0]['message'] ?? 'erro desconhecido') . "\n";
        return null;
    }

    return $decoded;
}

function sincronizarAshby(PDO $pdo, $ch, array $empresas, $dbConfig, string $origem = 'nacional'): void
{
    $listQuery = '
        query ApiJobBoardWithTeams($organizationHostedJobsPageName: String!) {
            jobBoard: jobBoardWithTeams(organizationHostedJobsPageName: $organizationHostedJobsPageName) {
                jobPostings { id title locationName workplaceType }
            }
        }
    ';

    $detailQuery = '
        query ApiJobPosting($organizationHostedJobsPageName: String!, $jobPostingId: String!) {
            jobPosting(organizationHostedJobsPageName: $organizationHostedJobsPageName, jobPostingId: $jobPostingId) {
                descriptionHtml
                publishedDate
            }
        }
    ';

    foreach ($empresas as $slug => $nomeReal) {
        echo "\n[Ashby] Buscando: $nomeReal...\n";

        // IDs existentes no banco para essa empresa (prefixo ashby-)
        $stmtCheck = $pdo->prepare("SELECT vaga_id_externo FROM vagas WHERE empresa = :empresa");
        $stmtCheck->execute([':empresa' => $nomeReal]);
        $idsNoBanco = $stmtCheck->fetchAll(PDO::FETCH_COLUMN);

        // Lista de vagas da API
        $listRes = fetchAshbyGraphQL(
            $ch,
            'ApiJobBoardWithTeams',
            ['organizationHostedJobsPageName' => $slug],
            $listQuery
        );

        if (!$listRes || !isset($listRes['data']['jobBoard']['jobPostings'])) {
            echo " - Nenhuma vaga encontrada ou empresa inexistente.\n";
            continue;
        }

        $vagas = $listRes['data']['jobBoard']['jobPostings'];

        // IDs da API com prefixo
        $idsNaAPI = array_map(fn($v) => "ashby-{$v['id']}", $vagas);

        // Inativar vagas que sumiram da API
        $idsParaInativar = array_diff($idsNoBanco, $idsNaAPI);
        if (!empty($idsParaInativar)) {
            $placeholders = implode(',', array_fill(0, count($idsParaInativar), '?'));
            $stmtInativar = $pdo->prepare("UPDATE vagas SET status = 'inativa' WHERE vaga_id_externo IN ($placeholders) AND status = 'ativa' AND is_premium = 0 AND vaga_id_externo NOT LIKE 'mw_prem_%'");
            $stmtInativar->execute(array_values($idsParaInativar));
            echo " - " . count($idsParaInativar) . " vagas inativadas (removidas do site).\n";
        }

        foreach ($vagas as $vaga) {
            $jobIdPrefixed = "ashby-{$vaga['id']}";
            $titulo = $vaga['title'];

            if (in_array($jobIdPrefixed, $idsNoBanco)) {
                echo " - [MANTIDA] $titulo\n";
                continue;
            }

            $detailRes = fetchAshbyGraphQL(
                $ch,
                'ApiJobPosting',
                [
                    'organizationHostedJobsPageName' => $slug,
                    'jobPostingId'                  => $vaga['id'],
                ],
                $detailQuery
            );

            if (!$detailRes || !isset($detailRes['data']['jobPosting'])) {
                echo " - [ERRO] Falha ao buscar detalhes de '$titulo'. Pulando...\n";
                continue;
            }

            $detalhe = $detailRes['data']['jobPosting'];
            $descricao = $detalhe['descriptionHtml'] ?? 'Descricao nao fornecida.';
            $resumo = extrairResumo($descricao);

            $publicadoEm = isset($detalhe['publishedDate'])
                ? date('Y-m-d H:i:s', strtotime($detalhe['publishedDate']))
                : null;

            if ($publicadoEm !== null && strtotime($publicadoEm) < strtotime('-'.MAX_DIAS_VAGA.' days')) {
                echo " - [IGNORADA] $titulo (mais de " . MAX_DIAS_VAGA . " dias)\n";
                continue;
            }

            upsertVaga($pdo, [
                'vaga_id_externo' => $jobIdPrefixed,
                'titulo'          => $titulo,
                'empresa'         => $nomeReal,
                'localizacao'     => $vaga['locationName'] ?? null,
                'modelo_trabalho' => $vaga['workplaceType'] ?? null,
                'url_vaga'        => "https://jobs.ashbyhq.com/{$slug}/{$vaga['id']}",
                'descricao'       => $descricao,
                'resumo'          => $resumo,
                'publicado_em'    => $publicadoEm,
                'origem'          => $origem,
            ]);

            echo " - [NOVA] $titulo\n";
            usleep(500000);
        }
    }
}

// ─────────────────────────────────────────────
// Greenhouse (REST)
// ─────────────────────────────────────────────

function sincronizarGreenhouse(PDO $pdo, $ch, array $empresas, $dbConfig, string $origem = 'exterior'): void
{
    foreach ($empresas as $boardToken => $nomeReal) {
        echo "\n[Greenhouse] Buscando: $nomeReal...\n";
        manterConexao($pdo, $dbConfig);

        $url = "https://boards-api.greenhouse.io/v1/boards/{$boardToken}/jobs?content=true";
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_POST => false,
        ]);

        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode !== 200) {
            echo " - [ERRO HTTP $httpCode] Falha ao acessar vagas. Pulando...\n";
            continue;
        }

        $dados = json_decode($res, true);
        $vagasNaAPI = $dados['jobs'] ?? [];

        $stmtCheck = $pdo->prepare("SELECT vaga_id_externo FROM vagas WHERE empresa = :empresa");
        $stmtCheck->execute([':empresa' => $nomeReal]);
        $idsNoBanco = $stmtCheck->fetchAll(PDO::FETCH_COLUMN);

        $idsNaAPI = array_map(fn($v) => "greenhouse-{$v['id']}", $vagasNaAPI);
        $idsParaInativar = array_diff($idsNoBanco, $idsNaAPI);

        if (!empty($idsParaInativar)) {
            $placeholders = implode(',', array_fill(0, count($idsParaInativar), '?'));
            $stmtInativar = $pdo->prepare("UPDATE vagas SET status = 'inativa' WHERE vaga_id_externo IN ($placeholders) AND status = 'ativa' AND is_premium = 0 AND vaga_id_externo NOT LIKE 'mw_prem_%'");
            $stmtInativar->execute(array_values($idsParaInativar));
            echo " - " . count($idsParaInativar) . " vagas inativadas (removidas do site).\n";
        }

        if (empty($vagasNaAPI)) {
            echo " - Nenhuma vaga ativa encontrada.\n";
            continue;
        }

        foreach ($vagasNaAPI as $vaga) {
            $jobIdPrefixed = "greenhouse-{$vaga['id']}";
            $titulo = $vaga['title'];

            if (in_array($jobIdPrefixed, $idsNoBanco)) {
                echo " - [MANTIDA] $titulo\n";
                continue;
            }

            $publicadoEm = isset($vaga['first_published'])
                ? date('Y-m-d H:i:s', strtotime($vaga['first_published']))
                : null;

            if ($publicadoEm !== null && strtotime($publicadoEm) < strtotime('-'.MAX_DIAS_VAGA.' days')) {
                echo " - [IGNORADA] $titulo (mais de " . MAX_DIAS_VAGA . " dias)\n";
                continue;
            }

            $descricao = html_entity_decode($vaga['content'] ?? 'Descricao nao fornecida.', ENT_QUOTES, 'UTF-8');
            $resumo = extrairResumo($descricao);

            upsertVaga($pdo, [
                'vaga_id_externo' => $jobIdPrefixed,
                'titulo'          => $titulo,
                'empresa'         => $nomeReal,
                'localizacao'     => $vaga['location']['name'] ?? null,
                'modelo_trabalho' => null,
                'url_vaga'        => $vaga['absolute_url'] ?? "https://job-boards.greenhouse.io/{$boardToken}/jobs/{$vaga['id']}",
                'descricao'       => $descricao,
                'resumo'          => $resumo,
                'publicado_em'    => $publicadoEm,
                'origem'          => $origem,
            ]);

            echo " - [NOVA] $titulo\n";
            usleep(200000);
        }
    }
}

// ─────────────────────────────────────────────
// Senior (Portal de Talentos)
// ─────────────────────────────────────────────

function sincronizarSenior(PDO $pdo, $ch, array $empresas, $dbConfig, string $origem = 'nacional'): void
{
    $urlApi = "https://platform.senior.com.br/t/senior.com.br/bridge/1.0/anonymous/rest/hcm/careersmanagercandidate";

    foreach ($empresas as $tenant => $nomeReal) {
        echo "\n[Senior] Buscando: $nomeReal...\n";
        manterConexao($pdo, $dbConfig);

        $stmtCheck = $pdo->prepare("SELECT vaga_id_externo FROM vagas WHERE empresa = :empresa");
        $stmtCheck->execute([':empresa' => $nomeReal]);
        $idsNoBanco = $stmtCheck->fetchAll(PDO::FETCH_COLUMN);

        $page = 0;
        $size = 50;
        $vagasDaEmpresa = [];

        do {
            $payload = json_encode([
                'page' => $page,
                'size' => $size,
                'filter' => (object)[],
                'match' => ['localizations' => [], 'companies' => []],
            ]);

            curl_setopt_array($ch, [
                CURLOPT_URL => "$urlApi/queries/searchVacancies",
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
            ]);

            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode !== 200) {
                echo " - [ERRO HTTP $httpCode] Falha ao buscar vagas. Pulando...\n";
                break;
            }

            $dados = json_decode($res, true);
            $totalPages = $dados['totalPages'] ?? 0;
            $conteudo = $dados['contents'] ?? [];

            foreach ($conteudo as $item) {
                if (strcasecmp($item['company']['name'] ?? '', $nomeReal) === 0) {
                    $vagasDaEmpresa[] = $item;
                }
            }

            $page++;
            usleep(300000);

        } while ($page < $totalPages);

        $idsNaAPI = [];
        foreach ($vagasDaEmpresa as $vaga) {
            $vagaId = 'senior-' . $vaga['vacancy']['id'];
            $idsNaAPI[] = $vagaId;
        }

        $idsParaInativar = array_diff($idsNoBanco, $idsNaAPI);
        if (!empty($idsParaInativar)) {
            $placeholders = implode(',', array_fill(0, count($idsParaInativar), '?'));
            $stmtInativar = $pdo->prepare("UPDATE vagas SET status = 'inativa' WHERE vaga_id_externo IN ($placeholders) AND status = 'ativa' AND is_premium = 0 AND vaga_id_externo NOT LIKE 'mw_prem_%'");
            $stmtInativar->execute(array_values($idsParaInativar));
            echo " - " . count($idsParaInativar) . " vagas inativadas (removidas do site).\n";
        }

        if (empty($vagasDaEmpresa)) {
            echo " - Nenhuma vaga ativa encontrada para $nomeReal.\n";
            continue;
        }

        foreach ($vagasDaEmpresa as $vaga) {
            $vagaId = 'senior-' . $vaga['vacancy']['id'];
            $titulo = $vaga['vacancy']['title'];

            if (in_array($vagaId, $idsNoBanco)) {
                echo " - [MANTIDA] $titulo\n";
                continue;
            }

            // Buscar detalhes completos
            $payloadDet = json_encode(['id' => $vaga['vacancy']['id']]);
            curl_setopt_array($ch, [
                CURLOPT_URL => "$urlApi/queries/findVacancyById",
                CURLOPT_POSTFIELDS => $payloadDet,
            ]);

            $resDet = curl_exec($ch);
            $httpCodeDet = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            $descricao = 'Descricao nao fornecida.';
            if ($httpCodeDet === 200) {
                $detalhe = json_decode($resDet, true);
                $descricao = $detalhe['vacancy']['about']['description']
                    ?? $detalhe['vacancy']['duties']['description']
                    ?? 'Descricao nao fornecida.';
                $descricao = html_entity_decode($descricao, ENT_QUOTES, 'UTF-8');
            }

            $resumo = extrairResumo($descricao);
            $local = $vaga['vacancy']['localization'] ?? [];
            $localizacao = '';
            $partes = array_filter([$local['city'] ?? '', $local['province'] ?? '', $local['country'] ?? '']);
            if (!empty($partes)) {
                $localizacao = implode(', ', $partes);
            }

            $publicacao = $vaga['vacancy']['publication'] ?? [];
            $publicadoEm = isset($publicacao['startDate'])
                ? date('Y-m-d H:i:s', strtotime($publicacao['startDate']))
                : null;

            if ($publicadoEm !== null && strtotime($publicadoEm) < strtotime('-'.MAX_DIAS_VAGA.' days')) {
                echo " - [IGNORADA] $titulo (mais de " . MAX_DIAS_VAGA . " dias)\n";
                continue;
            }

            $modelos = $vaga['vacancy']['jobModel'] ?? [];
            $modeloTrabalho = null;
            if (!empty($modelos)) {
                $mapa = ['REMOTE' => 'remoto', 'HYBRID' => 'hibrido', 'IN_PERSON' => 'presencial'];
                $modeloTrabalho = $mapa[$modelos[0]] ?? $modelos[0];
            }

            $subdomain = !empty($vaga['company']['tenant']) ? strtolower($vaga['company']['tenant']) : strtolower($tenant);
            $urlVaga = "https://{$subdomain}.portaldetalentos.senior.com.br/vacancy/" . $vaga['vacancy']['id'];

            upsertVaga($pdo, [
                'vaga_id_externo' => $vagaId,
                'titulo'          => $titulo,
                'empresa'         => $nomeReal,
                'localizacao'     => $localizacao,
                'modelo_trabalho' => $modeloTrabalho,
                'url_vaga'        => $urlVaga,
                'descricao'       => $descricao,
                'resumo'          => $resumo,
                'publicado_em'    => $publicadoEm,
                'origem'          => $origem,
            ]);

            echo " - [NOVA] $titulo\n";
            usleep(200000);
        }
    }
}


