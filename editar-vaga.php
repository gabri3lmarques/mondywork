<?php
require_once __DIR__ . '/App/Autoloader.php';
$prodConfig  = file_exists(__DIR__ . '/config.php') ? (require __DIR__ . '/config.php') : [];
$localConfig = file_exists(__DIR__ . '/config.local.php') ? (require __DIR__ . '/config.local.php') : [];
$config      = array_replace_recursive($prodConfig, $localConfig);


$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$mensagemsucesso = '';
$erromsg = '';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    if (empty($token)) {
        throw new Exception("Token de gerenciamento inválido ou ausente.");
    }

    $stmt = $pdo->prepare("SELECT * FROM vagas WHERE magic_token = :token LIMIT 1");
    $stmt->execute([':token' => $token]);
    $vaga = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vaga) {
        throw new Exception("Vaga não encontrada com o token fornecido.");
    }

    // Trata edição via POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $acao = $_POST['acao'] ?? 'salvar';

        if ($acao === 'encerrar') {
            $up = $pdo->prepare("UPDATE vagas SET status = 'inativa' WHERE id = :id");
            $up->execute([':id' => $vaga['id']]);
            $vaga['status'] = 'inativa';
            $mensagemsucesso = "A vaga foi encerrada e não aparecerá mais nos resultados públicos.";
        } elseif ($acao === 'reativar') {
            $up = $pdo->prepare("UPDATE vagas SET status = 'ativa' WHERE id = :id");
            $up->execute([':id' => $vaga['id']]);
            $vaga['status'] = 'ativa';
            $mensagemsucesso = "A vaga foi reativada com sucesso!";
        } else {
            $titulo = trim($_POST['titulo'] ?? '');
            $empresa = trim($_POST['empresa'] ?? '');
            $localizacao = trim($_POST['localizacao'] ?? '');
            $modeloTrabalho = trim($_POST['modelo_trabalho'] ?? 'Remoto');
            $urlVaga = trim($_POST['url_vaga'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');

            if ($titulo && $empresa && $descricao) {
                $resumo = mb_substr(strip_tags($descricao), 0, 250) . '...';
                $up = $pdo->prepare("UPDATE vagas SET 
                    titulo = :titulo, 
                    empresa = :empresa, 
                    localizacao = :localizacao, 
                    modelo_trabalho = :modelo, 
                    url_vaga = :url, 
                    descricao = :descricao, 
                    resumo = :resumo 
                    WHERE id = :id");
                $up->execute([
                    ':titulo'      => $titulo,
                    ':empresa'     => $empresa,
                    ':localizacao' => $localizacao,
                    ':modelo'      => $modeloTrabalho,
                    ':url'         => $urlVaga,
                    ':descricao'   => $descricao,
                    ':resumo'      => $resumo,
                    ':id'          => $vaga['id']
                ]);
                $vaga['titulo'] = $titulo;
                $vaga['empresa'] = $empresa;
                $vaga['localizacao'] = $localizacao;
                $vaga['modelo_trabalho'] = $modeloTrabalho;
                $vaga['url_vaga'] = $urlVaga;
                $vaga['descricao'] = $descricao;
                $mensagemsucesso = "As alterações na vaga foram salvas com sucesso!";
            } else {
                $erromsg = "Por favor, preencha os campos obrigatórios.";
            }
        }
    }
} catch (Exception $e) {
    $erromsg = $e->getMessage();
    $vaga = null;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Vaga Premium - Mondy Work</title>
    <link rel="stylesheet" href="/css/style.css?v=2.0">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body style="background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif;">

    <header style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 16px 24px;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
            <a href="/" style="font-size: 22px; font-weight: 800; color: #7e22ce; text-decoration: none;">Mondy Work</a>
            <a href="/" style="color: #64748b; text-decoration: none; font-size: 14px; font-weight: 600;">← Voltar ao site</a>
        </div>
    </header>

    <main class="post-job-container">
        <?php if ($vaga): ?>
            <div class="post-job-header">
                <h1>Gerenciamento da Vaga 🛠️</h1>
                <p>Edite os detalhes da sua vaga ou altere seu status de exibição.</p>
            </div>

            <?php if ($mensagemsucesso): ?>
                <div style="background: #f0fdf4; color: #166534; padding: 14px; border-radius: 8px; font-size: 14px; font-weight: 600; margin-bottom: 20px; border: 1px solid #bbf7d0;">
                    ✓ <?= htmlspecialchars($mensagemsucesso) ?>
                </div>
            <?php endif; ?>

            <?php if ($erromsg): ?>
                <div style="background: #fef2f2; color: #991b1b; padding: 14px; border-radius: 8px; font-size: 14px; font-weight: 600; margin-bottom: 20px; border: 1px solid #fecaca;">
                    ⚠ <?= htmlspecialchars($erromsg) ?>
                </div>
            <?php endif; ?>

            <!-- Status Card -->
            <div style="background: #f3e8ff; border: 1.5px solid #c084fc; border-radius: 12px; padding: 20px; margin-bottom: 28px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div>
                    <span class="badge-destaque" style="margin-bottom: 6px;">Destaque 🚀</span>
                    <h3 style="font-size: 18px; font-weight: 800; color: #4c1d95; margin: 4px 0;"><?= htmlspecialchars($vaga['titulo']) ?></h3>
                    <p style="font-size: 13px; color: #6b21a8; margin: 0;">Empresa: <strong><?= htmlspecialchars($vaga['empresa']) ?></strong> | Status atual: <strong><?= strtoupper($vaga['status']) ?></strong></p>
                    <?php if ($vaga['destaque_ate']): ?>
                        <p style="font-size: 12px; color: #7e22ce; margin-top: 4px;">Destaque ativo até: <?= date('d/m/Y H:i', strtotime($vaga['destaque_ate'])) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <a href="/vaga/<?= urlencode($vaga['vaga_id_externo']) ?>" target="_blank" style="background: #7e22ce; color: #fff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 700; display: inline-block;">
                        Ver Vaga Pública ↗
                    </a>
                </div>
            </div>

            <!-- Form de Edição -->
            <form method="POST">
                <input type="hidden" name="acao" value="salvar">

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="titulo">Título da Vaga *</label>
                        <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($vaga['titulo']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="empresa">Empresa *</label>
                        <input type="text" id="empresa" name="empresa" value="<?= htmlspecialchars($vaga['empresa']) ?>" required>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="modelo_trabalho">Modelo de Trabalho</label>
                        <select id="modelo_trabalho" name="modelo_trabalho">
                            <option value="Remoto" <?= $vaga['modelo_trabalho'] === 'Remoto' ? 'selected' : '' ?>>Remoto 🌐</option>
                            <option value="Híbrido" <?= $vaga['modelo_trabalho'] === 'Híbrido' ? 'selected' : '' ?>>Híbrido 🏢</option>
                            <option value="Presencial" <?= $vaga['modelo_trabalho'] === 'Presencial' ? 'selected' : '' ?>>Presencial 📌</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="localizacao">Localização</label>
                        <input type="text" id="localizacao" name="localizacao" value="<?= htmlspecialchars($vaga['localizacao']) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="url_vaga">Link de Candidatura</label>
                    <input type="url" id="url_vaga" name="url_vaga" value="<?= htmlspecialchars($vaga['url_vaga']) ?>">
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição da Vaga *</label>
                    <textarea id="descricao" name="descricao" rows="8" required><?= htmlspecialchars($vaga['descricao']) ?></textarea>
                </div>

                <button type="submit" class="btn-primary-glow" style="margin-bottom: 20px;">
                    Salvar Alterações
                </button>
            </form>

            <div style="border-top: 1px solid #e2e8f0; padding-top: 20px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 13px; color: #64748b;">Deseja interromper o anúncio antes dos 30 dias?</span>
                <form method="POST" onsubmit="return confirm('Tem certeza que deseja alterar o status desta vaga?');">
                    <?php if ($vaga['status'] === 'ativa'): ?>
                        <input type="hidden" name="acao" value="encerrar">
                        <button type="submit" style="background: #ef4444; color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer;">
                            Encerrar Vaga
                        </button>
                    <?php else: ?>
                        <input type="hidden" name="acao" value="reativar">
                        <button type="submit" style="background: #16a34a; color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer;">
                            Reativar Vaga
                        </button>
                    <?php endif; ?>
                </form>
            </div>

        <?php else: ?>
            <div style="text-align: center; padding: 40px 20px;">
                <div style="font-size: 48px; margin-bottom: 16px;">⚠️</div>
                <h2 style="font-size: 22px; font-weight: 800; color: #1e293b; margin-bottom: 8px;">Acesso Negado ou Token Inválido</h2>
                <p style="color: #64748b; font-size: 15px; margin-bottom: 24px;"><?= htmlspecialchars($erromsg) ?></p>
                <a href="/" style="background: #7e22ce; color: #fff; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 700; display: inline-block;">Voltar à Página Inicial</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
