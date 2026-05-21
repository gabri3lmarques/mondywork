<?php
/**
 * Worker de Sincronização - InHire + Ashby
 */
require __DIR__ . '/lib/Database.php';
require __DIR__ . '/lib/VagaRepository.php';

$configFile = __DIR__ . '/config.local.php';
$dbConfig = require file_exists($configFile) ? $configFile : __DIR__ . '/config.php';

$empresasInhireNacional  = require __DIR__ . '/config/empresas_inhire_nacional.php';
$empresasInhireExterior  = require __DIR__ . '/config/empresas_inhire_exterior.php';
$empresasAshbyNacional   = require __DIR__ . '/config/empresas_ashby_nacional.php';
$empresasAshbyExterior   = require __DIR__ . '/config/empresas_ashby_exterior.php';
$empresasIgnorar         = require __DIR__ . '/config/empresas_ignorar.php';

try {
    $pdo = conectarBanco($dbConfig);
    setupSchema($pdo);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 15,
    ]);

    echo "[" . date('Y-m-d H:i:s') . "] Iniciando sincronizacao...\n";

    // ── Orphan cleanup ──
    $todasEmpresas = array_merge(
        array_values($empresasInhireNacional),
        array_values($empresasInhireExterior),
        array_values($empresasAshbyNacional),
        array_values($empresasAshbyExterior),
        array_values($empresasIgnorar)
    );
    if (!empty($todasEmpresas)) {
        $placeholders = implode(',', array_fill(0, count($todasEmpresas), '?'));
        $stmt = $pdo->prepare("UPDATE vagas SET status = 'inativa' WHERE empresa NOT IN ($placeholders) AND status = 'ativa'");
        $stmt->execute($todasEmpresas);
        $afetadas = $stmt->rowCount();
        if ($afetadas > 0) {
            echo "[ORFAOS] $afetadas vagas inativadas (empresa fora do escopo).\n";
        }
    }

    // ── Remove empresas ignoradas ──
    $empresasInhireNacional = array_filter($empresasInhireNacional, fn($nome) => !in_array($nome, $empresasIgnorar));
    $empresasInhireExterior = array_filter($empresasInhireExterior, fn($nome) => !in_array($nome, $empresasIgnorar));
    $empresasAshbyNacional  = array_filter($empresasAshbyNacional,  fn($nome) => !in_array($nome, $empresasIgnorar));
    $empresasAshbyExterior  = array_filter($empresasAshbyExterior,  fn($nome) => !in_array($nome, $empresasIgnorar));

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

    curl_close($ch);
    echo "\n[" . date('Y-m-d H:i:s') . "] Sincronizacao finalizada.\n";

} catch (Exception $e) {
    echo "\n[ERRO FATAL] " . $e->getMessage() . "\n";
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
                $stmtInativar = $pdo->prepare("UPDATE vagas SET status = 'inativa' WHERE vaga_id_externo IN ($placeholders) AND status = 'ativa'");
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
                if ($publicadoLista !== null && strtotime($publicadoLista) < strtotime('-90 days')) {
                    echo " - [IGNORADA] $titulo (mais de 90 dias)\n";
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

                if ($publicadoEm !== null && strtotime($publicadoEm) < strtotime('-90 days')) {
                    echo " - [IGNORADA] $titulo (mais de 90 dias)\n";
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
                    'area'            => null,
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
            $stmtInativar = $pdo->prepare("UPDATE vagas SET status = 'inativa' WHERE vaga_id_externo IN ($placeholders) AND status = 'ativa'");
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
                'area'            => null,
            ]);

            echo " - [NOVA] $titulo\n";
            usleep(500000);
        }
    }
}


