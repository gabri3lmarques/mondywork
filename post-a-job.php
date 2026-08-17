<?php
require_once __DIR__ . '/App/Autoloader.php';
$prodConfig  = file_exists(__DIR__ . '/config.php') ? (require __DIR__ . '/config.php') : [];
$localConfig = file_exists(__DIR__ . '/config.local.php') ? (require __DIR__ . '/config.local.php') : [];
$config      = array_replace_recursive($prodConfig, $localConfig);


$precoVaga = $config['efibank']['preco_vaga_premium'] ?? ($config['pagbank']['preco_vaga_premium'] ?? 49.90);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anunciar Vaga Premium - Mondy Work</title>
    <meta name="description" content="Anuncie sua vaga em destaque no Mondy Work. Destaque por 30 dias com cobrança Pix instantânea.">
    <link rel="stylesheet" href="/css/style.css?v=2.4.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body style="background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif;">

    <!-- HEADER / NAV -->
    <header style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 16px 24px;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
            <a href="/" style="font-size: 22px; font-weight: 800; color: #7e22ce; text-decoration: none;">Mondy Work</a>
            <a href="/" style="color: #64748b; text-decoration: none; font-size: 14px; font-weight: 600;">← Voltar ao site</a>
        </div>
    </header>

    <main class="post-job-container">
        <div class="post-job-header">
            <h1>Anuncie sua Vaga em Destaque 🚀</h1>
            <p>Conecte-se aos melhores talentos da comunidade tech com visibilidade prioritária no topo.</p>
        </div>

        <div class="plan-card-selected">
            <div>
                <div class="plan-title">Plano Vaga Premium (30 Dias)</div>
                <div style="font-size: 13px; color: #6b21a8; margin-top: 4px;">
                    ✓ Destaque com Fundo Roxo e Selo "Destaque 🚀"<br>
                    ✓ Posicionamento prioritário nas buscas<br>
                    ✓ Magic Link instantâneo para edição e gerenciamento
                </div>
            </div>
            <div class="plan-price">
                R$ <?= number_format($precoVaga, 2, ',', '.') ?>
            </div>
        </div>

        <form id="form-post-job">
            <div class="form-grid-2">
                <div class="form-group">
                    <label for="titulo">Título da Vaga *</label>
                    <input type="text" id="titulo" name="titulo" placeholder="Ex: Desenvolvedor Full Stack Senior (React + Node)" required>
                </div>
                <div class="form-group">
                    <label for="empresa">Nome da Empresa *</label>
                    <input type="text" id="empresa" name="empresa" placeholder="Ex: Tech Solutions" required>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="email_recrutador">E-mail do Recrutador / Empresa *</label>
                    <input type="email" id="email_recrutador" name="email_recrutador" placeholder="recrutador@empresa.com" required>
                    <span style="font-size: 12px; color: #64748b; margin-top: 4px; display: block;">Enviaremos o Magic Link de edição para este e-mail.</span>
                </div>
                <div class="form-group">
                    <label for="cpf_cnpj">CPF ou CNPJ (para o Pix) *</label>
                    <input type="text" id="cpf_cnpj" name="cpf_cnpj" placeholder="000.000.000-00 ou 00.000.000/0001-00" required>
                    <span style="font-size: 12px; color: #64748b; margin-top: 4px; display: block;">Exigência do PagBank / Banco Central para gerar o Pix.</span>
                </div>
            </div>

            <div class="form-group">
                <label for="area">Área da Vaga</label>
                <select id="area" name="area">
                    <option value="desenvolvimento">Desenvolvimento / Engenharia</option>
                    <option value="dados">Dados & IA</option>
                    <option value="design">Design & UX</option>
                    <option value="produto">Produto & Projeto</option>
                    <option value="marketing">Marketing & Conteúdo</option>
                    <option value="financas">Finanças & Administração</option>
                    <option value="geral">Outra Área</option>
                </select>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="modelo_trabalho">Modelo de Trabalho</label>
                    <select id="modelo_trabalho" name="modelo_trabalho">
                        <option value="Remoto">Remoto 🌐</option>
                        <option value="Híbrido">Híbrido 🏢</option>
                        <option value="Presencial">Presencial 📌</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="localizacao">Localização</label>
                    <input type="text" id="localizacao" name="localizacao" placeholder="Ex: Brasil / São Paulo, SP">
                </div>
            </div>

            <div class="form-group">
                <label for="url_vaga">Link de Candidatura ou Instruções *</label>
                <input type="url" id="url_vaga" name="url_vaga" placeholder="https://suaempresa.workable.com/j/12345" required>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição Completa da Vaga *</label>
                <textarea id="descricao" name="descricao" rows="7" placeholder="Descreva os requisitos, responsabilidades e benefícios da vaga..." required></textarea>
            </div>

            <div id="form-error" style="display: none; background: #fef2f2; color: #991b1b; padding: 12px; border-radius: 8px; font-size: 14px; margin-bottom: 16px;"></div>

            <button type="submit" id="btn-submit-vaga" class="btn-primary-glow">
                Gerar Cobrança Pix 🚀 (R$ <?= number_format($precoVaga, 2, ',', '.') ?>)
            </button>
        </form>
    </main>

    <!-- MODAL CHECKOUT PIX -->
    <div id="pix-modal-overlay" class="modal-overlay hidden" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 9999; justify-content: center; align-items: center;">
        <div class="pix-modal-content">
            <button id="close-pix-modal" style="position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8;">✕</button>

            <!-- TELA DE PAGAMENTO PIX -->
            <div id="step-pix-payment">
                <h3 style="font-size: 20px; font-weight: 800; color: #1e293b; margin-bottom: 4px;">Pagamento via Pix</h3>
                <p style="font-size: 13px; color: #64748b;">Abra o app do seu banco e escaneie o código abaixo:</p>

                <img id="pix-qrcode-img" class="pix-qrcode-img" src="" alt="QR Code Pix">

                <div class="pix-copia-cola-box" id="pix-code-text">Copiando código...</div>

                <button type="button" id="btn-copy-pix" class="btn-copy-pix">
                    📋 Copiar Código Pix Copia e Cola
                </button>

                <div class="timer-box">
                    ⏳ Aguardando pagamento em tempo real... <span id="pix-countdown" style="font-weight: 800; color: #7e22ce;">30:00</span>
                </div>

                <?php if (($config['efibank']['env'] ?? ($config['pagbank']['env'] ?? 'sandbox')) !== 'production'): ?>
                <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid #f1f5f9; display: flex; justify-content: center; gap: 10px;">
                    <!-- Botão de simular teste em ambiente sandbox/dev -->
                    <button type="button" id="btn-simular-pago" style="background: #f3e8ff; color: #6b21a8; border: 1px dashed #c084fc; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer;">
                        ⚡ Teste Sandbox: Simular Pagamento Confirmado
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <!-- TELA DE SUCESSO / MAGIC LINK -->
            <div id="step-pix-success" style="display: none;">
                <div style="font-size: 48px; margin-bottom: 8px;">🎉</div>
                <h3 style="font-size: 22px; font-weight: 800; color: #15803d; margin-bottom: 8px;">Pagamento Confirmado!</h3>
                <p style="font-size: 14px; color: #334155; margin-bottom: 20px;">Sua vaga já está <strong>AO VIVO</strong> no Mondy Work com selo <strong>Destaque 🚀</strong> pelo período de 30 dias!</p>

                <div style="background: #f3e8ff; border: 1.5px solid #d8b4fe; border-radius: 12px; padding: 16px; text-align: left; margin-bottom: 20px;">
                    <div style="font-size: 13px; font-weight: 700; color: #581c87; margin-bottom: 4px;">🔗 Seu Magic Link de Gerenciamento:</div>
                    <div style="font-size: 12px; color: #6b21a8; margin-bottom: 12px;">Com este link você poderá editar as informações ou encerrar a vaga quando quiser.</div>
                    <input type="text" id="magic-link-input" readonly style="width: 100%; padding: 8px; border: 1px solid #c084fc; border-radius: 6px; font-size: 12px; background: #fff; color: #4c1d95;" value="">
                    <button type="button" id="btn-copy-magic-link" style="margin-top: 8px; width: 100%; background: #7e22ce; color: #fff; border: none; padding: 8px; border-radius: 6px; font-weight: 700; font-size: 13px; cursor: pointer;">
                        Copiar Magic Link
                    </button>
                </div>

                <a id="btn-ver-vaga-live" href="/" style="display: block; width: 100%; padding: 14px; background: #16a34a; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 15px;">
                    Ver Vaga no Site Agora 🚀
                </a>
            </div>
        </div>
    </div>

    <script>
    (function() {
        const form = document.getElementById('form-post-job');
        const btnSubmit = document.getElementById('btn-submit-vaga');
        const formError = document.getElementById('form-error');

        const pixModal = document.getElementById('pix-modal-overlay');
        const closePixModal = document.getElementById('close-pix-modal');
        const qrImg = document.getElementById('pix-qrcode-img');
        const pixCodeBox = document.getElementById('pix-code-text');
        const btnCopyPix = document.getElementById('btn-copy-pix');
        const countdownEl = document.getElementById('pix-countdown');
        const btnSimular = document.getElementById('btn-simular-pago');

        const stepPayment = document.getElementById('step-pix-payment');
        const stepSuccess = document.getElementById('step-pix-success');
        const magicLinkInput = document.getElementById('magic-link-input');
        const btnCopyMagic = document.getElementById('btn-copy-magic-link');

        let currentVagaId = null;
        let currentOrderId = null;
        let pollInterval = null;
        let timerInterval = null;

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            formError.style.display = 'none';
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = 'Gerando código Pix... ⏳';

            const payload = {
                titulo: document.getElementById('titulo').value.trim(),
                empresa: document.getElementById('empresa').value.trim(),
                email_recrutador: document.getElementById('email_recrutador').value.trim(),
                cpf_cnpj: document.getElementById('cpf_cnpj').value.trim(),
                area: document.getElementById('area').value,
                modelo_trabalho: document.getElementById('modelo_trabalho').value,
                localizacao: document.getElementById('localizacao').value.trim(),
                url_vaga: document.getElementById('url_vaga').value.trim(),
                descricao: document.getElementById('descricao').value.trim(),
            };


            try {
                const res = await fetch('/api.php?action=criar_vaga_premium', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('Resposta do servidor: ' + text.substring(0, 150));
                }

                if (!data.success) {
                    throw new Error(data.error || 'Erro ao gerar Pix');
                }


                currentVagaId = data.vaga_id;
                currentOrderId = data.order_id;

                // Preenche dados no modal
                qrImg.src = data.qr_code_image || ('https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' + encodeURIComponent(data.qr_code_text));
                pixCodeBox.textContent = data.qr_code_text;
                magicLinkInput.value = data.magic_url;
                if (data.vaga_url) {
                    const btnVerVaga = document.getElementById('btn-ver-vaga-live');
                    if (btnVerVaga) btnVerVaga.href = data.vaga_url;
                }

                // Exibe modal de Pix
                stepPayment.style.display = 'block';
                stepSuccess.style.display = 'none';
                pixModal.classList.remove('hidden');
                pixModal.style.display = 'flex';

                startTimer(30 * 60);
                startPolling(data.vaga_id);

            } catch (err) {
                formError.textContent = err.message;
                formError.style.display = 'block';
            } finally {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = 'Gerar Cobrança Pix 🚀 (R$ <?= number_format($precoVaga, 2, ',', '.') ?>)';
            }
        });

        // Botão copiar Pix
        btnCopyPix.addEventListener('click', function() {
            navigator.clipboard.writeText(pixCodeBox.textContent).then(() => {
                btnCopyPix.innerHTML = '✓ Código Copiado!';
                setTimeout(() => { btnCopyPix.innerHTML = '📋 Copiar Código Pix Copia e Cola'; }, 3000);
            });
        });

        // Botão copiar Magic Link
        btnCopyMagic.addEventListener('click', function() {
            navigator.clipboard.writeText(magicLinkInput.value).then(() => {
                btnCopyMagic.innerHTML = '✓ Link Copiado!';
                setTimeout(() => { btnCopyMagic.innerHTML = 'Copiar Magic Link'; }, 3000);
            });
        });

        // Fechar modal
        closePixModal.addEventListener('click', function() {
            pixModal.classList.add('hidden');
            pixModal.style.display = 'none';
            stopPolling();
        });

        // Simular pagamento no Sandbox (caso o botão esteja no DOM)
        if (btnSimular) {
            btnSimular.addEventListener('click', async function() {
                if (!currentVagaId) return;
                btnSimular.textContent = 'Simulando...';
                try {
                    const res = await fetch('/api.php?action=simular_pagamento_pix&vaga_id=' + currentVagaId);
                    const data = await res.json();
                    if (data.success) {
                        showSuccess();
                    } else {
                        alert(data.error || 'Erro ao simular');
                    }
                } catch(e) {
                    alert('Erro ao simular');
                }
            });
        }

        function startTimer(durationSeconds) {
            let timer = durationSeconds;
            clearInterval(timerInterval);
            timerInterval = setInterval(function() {
                const minutes = Math.floor(timer / 60);
                const seconds = timer % 60;
                countdownEl.textContent = (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
                if (--timer < 0) {
                    clearInterval(timerInterval);
                    countdownEl.textContent = 'EXPIRADO';
                    stopPolling();
                }
            }, 1000);
        }

        function startPolling(vagaId) {
            stopPolling();
            pollInterval = setInterval(async function() {
                try {
                    const res = await fetch('/api.php?action=verificar_pagamento&vaga_id=' + vagaId + '&_t=' + Date.now(), {
                        cache: 'no-store',
                        headers: { 'Cache-Control': 'no-cache' }
                    });
                    const data = await res.json();
                    if (data.status === 'PAID') {
                        if (data.magic_url) {
                            magicLinkInput.value = data.magic_url;
                        }
                        if (data.vaga_url) {
                            const btnVerVaga = document.getElementById('btn-ver-vaga-live');
                            if (btnVerVaga) btnVerVaga.href = data.vaga_url;
                        }
                        showSuccess();
                    }
                } catch(e) {}
            }, 3000);
        }

        function stopPolling() {
            if (pollInterval) clearInterval(pollInterval);
            if (timerInterval) clearInterval(timerInterval);
        }

        function showSuccess() {
            stopPolling();
            stepPayment.style.display = 'none';
            stepSuccess.style.display = 'block';
        }
    })();
    </script>
</body>
</html>
