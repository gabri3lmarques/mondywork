/**
 * Mondywork - Sistema de Autenticação, Reações e Comentários (Suporte Completo Bilíngue PT/EN)
 */
(function() {
  'use strict';

  // Detecta se a página atual está no idioma inglês
  const isEn = (document.documentElement.lang === 'en') 
    || window.location.pathname.startsWith('/usa/') 
    || window.location.pathname.startsWith('/job/');
  const currentLang = isEn ? 'en' : 'pt';

  // Dicionário de Traduções
  const i18n = {
    pt: {
      reaction_singular: 'reação',
      reaction_plural: 'reações',
      reactions_tooltips: {
        like: 'Gostei',
        dislike: 'Não gostei',
        love: 'Amei',
        angry: 'Bravo'
      },
      comments_title: 'Comentários',
      loading_comments: 'Carregando comentários...',
      no_comments_vaga: 'Seja o primeiro a deixar um comentário sobre esta vaga!',
      no_comments_card: 'Nenhum comentário ainda. Deixe o primeiro!',
      login_to_react: 'Faça login ou cadastre-se para reagir a esta vaga.',
      login_to_comment_card: 'Faça login para comentar nesta vaga',
      login_btn_short: 'Entrar',
      comment_placeholder_card: 'Deixe seu comentário sobre a vaga...',
      post_comment_btn: 'Publicar comentário',
      comment_btn_short: 'Comentar',
      sending_btn: 'Enviando...',
      delete_btn: 'Excluir',
      delete_confirm: 'Tem certeza que deseja excluir seu comentário?',
      error_reaction: 'Erro ao reagir à vaga.',
      error_comment: 'Erro ao publicar comentário.',
      error_delete: 'Erro ao excluir comentário.',
      error_connection: 'Erro de conexão. Tente novamente.',
      fill_all_fields: 'Preencha todos os campos.',
      fill_required_fields: 'Preencha os campos obrigatórios.',
      login_success: 'Login realizado com sucesso!',
      register_success: 'Conta criada com sucesso!',
      login_submitting: 'Entrando...',
      register_submitting: 'Criando conta...',
      login_submit_btn: 'Entrar na conta',
      register_submit_btn: 'Criar minha conta',
      valid_image_alert: 'Por favor, selecione um arquivo de imagem válido (JPG, PNG, WebP).',
      sign_out: 'Sair da conta',
      auth_modal_title: 'Mondywork',
      auth_modal_subtitle: 'Acesse sua conta para reagir e comentar nas vagas',
      tab_login: 'Entrar',
      tab_register: 'Criar Conta',
      label_email: 'E-mail',
      label_senha: 'Senha',
      label_nome: 'Nome Completo *',
      label_reg_email: 'E-mail *',
      label_reg_senha: 'Senha * (mínimo 6 caracteres)',
      label_avatar: 'Foto de Perfil',
      label_avatar_opt: '(opcional)',
      avatar_hint: 'Recorte quadrado e compressão automática (máx. 128KB). Sem foto, usamos a inicial.',
      avatar_choose_btn: 'Escolher foto...'
    },
    en: {
      reaction_singular: 'reaction',
      reaction_plural: 'reactions',
      reactions_tooltips: {
        like: 'Like',
        dislike: 'Dislike',
        love: 'Loved it',
        angry: 'Angry'
      },
      comments_title: 'Comments',
      loading_comments: 'Loading comments...',
      no_comments_vaga: 'Be the first to leave a comment about this job!',
      no_comments_card: 'No comments yet. Be the first to comment!',
      login_to_react: 'Sign in or create an account to react to this job.',
      login_to_comment_card: 'Sign in to comment on this job',
      login_btn_short: 'Sign In',
      comment_placeholder_card: 'Leave your comment about this job...',
      post_comment_btn: 'Post Comment',
      comment_btn_short: 'Comment',
      sending_btn: 'Sending...',
      delete_btn: 'Delete',
      delete_confirm: 'Are you sure you want to delete your comment?',
      error_reaction: 'Error reacting to job.',
      error_comment: 'Error posting comment.',
      error_delete: 'Error deleting comment.',
      error_connection: 'Connection error. Please try again.',
      fill_all_fields: 'Please fill in all fields.',
      fill_required_fields: 'Please fill in the required fields.',
      login_success: 'Logged in successfully!',
      register_success: 'Account created successfully!',
      login_submitting: 'Signing in...',
      register_submitting: 'Creating account...',
      login_submit_btn: 'Sign In',
      register_submit_btn: 'Create Account',
      valid_image_alert: 'Please select a valid image file (JPG, PNG, WebP).',
      sign_out: 'Sign Out',
      auth_modal_title: 'Mondywork',
      auth_modal_subtitle: 'Access your account to react and comment on jobs',
      tab_login: 'Sign In',
      tab_register: 'Create Account',
      label_email: 'Email',
      label_senha: 'Password',
      label_nome: 'Full Name *',
      label_reg_email: 'Email *',
      label_reg_senha: 'Password * (min. 6 characters)',
      label_avatar: 'Profile Photo',
      label_avatar_opt: '(optional)',
      avatar_hint: 'Square crop & auto compression (max. 128KB). Fallback uses name initial.',
      avatar_choose_btn: 'Choose photo...'
    }
  };

  const t = i18n[currentLang];

  let currentUser = null;
  let currentVagaId = null;
  const loadedBatchIds = new Set();

  // Elementos do Modal de Auth
  const modalOverlay = document.getElementById('auth-modal');
  const modalClose = document.getElementById('auth-modal-close');
  const tabBtns = document.querySelectorAll('.auth-tab-btn');
  const formLogin = document.getElementById('auth-form-login');
  const formRegister = document.getElementById('auth-form-register');
  const alertBox = document.getElementById('auth-alert-box');
  const avatarInput = document.getElementById('reg-avatar-input');
  const avatarPreview = document.getElementById('reg-avatar-img-preview');
  const avatarPreviewText = document.getElementById('reg-avatar-text-preview');

  document.addEventListener('DOMContentLoaded', function() {
    localizeStaticElements();
    initAuthModal();
    initNavbarUser();
    initVagaInteractions();
    initCardsInteractions();
  });

  // Localiza os textos estáticos do Modal se necessário
  function localizeStaticElements() {
    if (!modalOverlay) return;

    const subtitleEl = modalOverlay.querySelector('.auth-modal-subtitle');
    if (subtitleEl) subtitleEl.textContent = t.auth_modal_subtitle;

    const tabLoginBtn = document.getElementById('tab-login-btn');
    if (tabLoginBtn) tabLoginBtn.textContent = t.tab_login;

    const tabRegBtn = document.getElementById('tab-register-btn');
    if (tabRegBtn) tabRegBtn.textContent = t.tab_register;

    const loginSubmitBtn = formLogin ? formLogin.querySelector('.auth-submit-btn') : null;
    if (loginSubmitBtn) loginSubmitBtn.textContent = t.login_submit_btn;

    const regSubmitBtn = formRegister ? formRegister.querySelector('.auth-submit-btn') : null;
    if (regSubmitBtn) regSubmitBtn.textContent = t.register_submit_btn;

    const avatarHint = modalOverlay.querySelector('.auth-avatar-hint');
    if (avatarHint) avatarHint.textContent = t.avatar_hint;

    const avatarBtn = modalOverlay.querySelector('.auth-avatar-upload-btn');
    if (avatarBtn) avatarBtn.textContent = t.avatar_choose_btn;
  }

  // ==========================================
  // MODAL DE AUTENTICAÇÃO
  // ==========================================
  function initAuthModal() {
    if (!modalOverlay) return;

    if (modalClose) {
      modalClose.addEventListener('click', closeAuthModal);
    }
    modalOverlay.addEventListener('click', function(e) {
      if (e.target === modalOverlay) closeAuthModal();
    });

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && modalOverlay.classList.contains('active')) {
        closeAuthModal();
      }
    });

    if (tabBtns) {
      tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
          switchAuthTab(this.dataset.tab);
        });
      });
    }

    if (avatarInput) {
      avatarInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
          showAlert(t.valid_image_alert, 'error');
          avatarInput.value = '';
          return;
        }

        const reader = new FileReader();
        reader.onload = function(evt) {
          if (avatarPreview) {
            avatarPreview.src = evt.target.result;
            avatarPreview.style.display = 'block';
          }
          if (avatarPreviewText) {
            avatarPreviewText.style.display = 'none';
          }
        };
        reader.readAsDataURL(file);
      });
    }

    // Submit Login
    if (formLogin) {
      formLogin.addEventListener('submit', function(e) {
        e.preventDefault();
        const submitBtn = formLogin.querySelector('.auth-submit-btn');
        const email = document.getElementById('login-email').value.trim();
        const senha = document.getElementById('login-senha').value;

        if (!email || !senha) {
          showAlert(t.fill_all_fields, 'error');
          return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = t.login_submitting;
        clearAlert();

        const formData = new FormData();
        formData.append('action', 'login');
        formData.append('email', email);
        formData.append('senha', senha);
        formData.append('lang', currentLang);

        fetch('/auth.php', {
          method: 'POST',
          body: formData
        })
        .then(r => r.json())
        .then(data => {
          submitBtn.disabled = false;
          submitBtn.textContent = t.login_submit_btn;
          if (data.success) {
            currentUser = data.user;
            showAlert(t.login_success, 'success');
            setTimeout(() => {
              closeAuthModal();
              updateUserInterface(data.user, data.avatar_html);
              if (currentVagaId) refreshVagaInteractions(currentVagaId);
              refreshCardsBatch(true);
            }, 500);
          } else {
            showAlert(data.error || t.error_connection, 'error');
          }
        })
        .catch(() => {
          submitBtn.disabled = false;
          submitBtn.textContent = t.login_submit_btn;
          showAlert(t.error_connection, 'error');
        });
      });
    }

    // Submit Cadastro
    if (formRegister) {
      formRegister.addEventListener('submit', function(e) {
        e.preventDefault();
        const submitBtn = formRegister.querySelector('.auth-submit-btn');
        const nome = document.getElementById('reg-nome').value.trim();
        const email = document.getElementById('reg-email').value.trim();
        const senha = document.getElementById('reg-senha').value;

        if (!nome || !email || !senha) {
          showAlert(t.fill_required_fields, 'error');
          return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = t.register_submitting;
        clearAlert();

        const formData = new FormData();
        formData.append('action', 'register');
        formData.append('nome', nome);
        formData.append('email', email);
        formData.append('senha', senha);
        formData.append('lang', currentLang);

        if (avatarInput && avatarInput.files[0]) {
          formData.append('foto', avatarInput.files[0]);
        }

        fetch('/auth.php', {
          method: 'POST',
          body: formData
        })
        .then(r => r.json())
        .then(data => {
          submitBtn.disabled = false;
          submitBtn.textContent = t.register_submit_btn;
          if (data.success) {
            currentUser = data.user;
            showAlert(t.register_success, 'success');
            setTimeout(() => {
              closeAuthModal();
              updateUserInterface(data.user, data.avatar_html);
              if (currentVagaId) refreshVagaInteractions(currentVagaId);
              refreshCardsBatch(true);
            }, 500);
          } else {
            showAlert(data.error || t.error_connection, 'error');
          }
        })
        .catch(() => {
          submitBtn.disabled = false;
          submitBtn.textContent = t.register_submit_btn;
          showAlert(t.error_connection, 'error');
        });
      });
    }

    document.querySelectorAll('.open-auth-modal').forEach(el => {
      el.addEventListener('click', function(e) {
        e.preventDefault();
        openAuthModal(this.dataset.tab || 'login');
      });
    });
  }

  function openAuthModal(defaultTab = 'login', message = null) {
    if (!modalOverlay) return;
    clearAlert();
    switchAuthTab(defaultTab);
    if (message) {
      showAlert(message, 'error');
    }
    modalOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeAuthModal() {
    if (!modalOverlay) return;
    modalOverlay.classList.remove('active');
    document.body.style.overflow = '';
    clearAlert();
  }

  function switchAuthTab(tab) {
    clearAlert();
    tabBtns.forEach(btn => {
      btn.classList.toggle('active', btn.dataset.tab === tab);
    });
    if (formLogin && formRegister) {
      if (tab === 'login') {
        formLogin.style.display = 'block';
        formRegister.style.display = 'none';
      } else {
        formLogin.style.display = 'none';
        formRegister.style.display = 'block';
      }
    }
  }

  function showAlert(msg, type = 'error') {
    if (!alertBox) return;
    alertBox.textContent = msg;
    alertBox.className = 'auth-alert-box ' + type;
  }

  function clearAlert() {
    if (!alertBox) return;
    alertBox.className = 'auth-alert-box';
    alertBox.textContent = '';
  }

  // ==========================================
  // NAVBAR & SESSÃO DO USUÁRIO
  // ==========================================
  function initNavbarUser() {
    document.addEventListener('click', function(e) {
      const trigger = e.target.closest('#nav-user-trigger, .nav-user-trigger');
      const userContainer = document.getElementById('nav-user-dropdown');

      if (trigger && userContainer) {
        e.preventDefault();
        e.stopPropagation();
        userContainer.classList.toggle('open');
        return;
      }

      const logoutBtn = e.target.closest('#nav-user-logout, .nav-user-item.logout');
      if (logoutBtn) {
        e.preventDefault();
        fetch('/auth.php?action=logout', { method: 'POST' })
          .then(() => {
            window.location.reload();
          })
          .catch(() => {
            window.location.reload();
          });
        return;
      }

      if (userContainer && !userContainer.contains(e.target)) {
        userContainer.classList.remove('open');
      }
    });
  }

  function updateUserInterface(user, avatarHtml) {
    const authLoggedOut = document.getElementById('nav-auth-logged-out');
    const authLoggedIn = document.getElementById('nav-user-dropdown');
    const navUserName = document.getElementById('nav-user-name');
    const navUserAvatar = document.getElementById('nav-user-avatar-slot');
    const navMenuName = document.getElementById('nav-menu-user-name');
    const navMenuEmail = document.getElementById('nav-menu-user-email');

    if (authLoggedOut) authLoggedOut.style.display = 'none';
    if (authLoggedIn) authLoggedIn.style.display = 'inline-block';
    if (navUserName) navUserName.textContent = user.nome.split(' ')[0];
    if (navUserAvatar && avatarHtml) navUserAvatar.innerHTML = avatarHtml;
    if (navMenuName) navMenuName.textContent = user.nome;
    if (navMenuEmail) navMenuEmail.textContent = user.email;

    const commentAuthBanner = document.getElementById('comment-auth-banner');
    const commentFormWrapper = document.getElementById('comment-form-wrapper');
    const commentUserAvatar = document.getElementById('comment-user-avatar');

    if (commentAuthBanner) commentAuthBanner.style.display = 'none';
    if (commentFormWrapper) commentFormWrapper.style.display = 'flex';
    if (commentUserAvatar && avatarHtml) commentUserAvatar.innerHTML = avatarHtml;

    // Atualiza accordions abertos nos cards
    document.querySelectorAll('.job-card-comments-accordion').forEach(acc => {
      if (acc.style.display !== 'none') {
        const vid = acc.dataset.vagaId;
        renderCardCommentFormSlot(vid);
      }
    });
  }

  // ==========================================
  // PÁGINA INDIVIDUAL DA VAGA (vaga.php)
  // ==========================================
  function initVagaInteractions() {
    const vagaContainer = document.querySelector('article.vaga-page[data-vaga-id]');
    if (!vagaContainer) return;

    currentVagaId = parseInt(vagaContainer.dataset.vagaId, 10);
    if (!currentVagaId) return;

    const reactionBtns = vagaContainer.querySelectorAll('.reaction-btn');
    reactionBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const tipo = this.dataset.tipo;
        handleReactionClick(currentVagaId, tipo, 'detail');
      });
    });

    const commentForm = document.getElementById('vaga-comment-form');
    if (commentForm) {
      commentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const textarea = document.getElementById('comment-text-input');
        const submitBtn = commentForm.querySelector('.comment-submit-btn');
        const text = textarea ? textarea.value.trim() : '';

        if (!text) return;

        submitBtn.disabled = true;
        submitBtn.textContent = t.sending_btn;

        const formData = new FormData();
        formData.append('action', 'comment');
        formData.append('vaga_id', currentVagaId);
        formData.append('comentario', text);
        formData.append('lang', currentLang);

        fetch('/interacoes.php', {
          method: 'POST',
          body: formData
        })
        .then(r => r.json())
        .then(data => {
          submitBtn.disabled = false;
          submitBtn.textContent = t.post_comment_btn;
          if (data.success && data.comment) {
            textarea.value = '';
            appendCommentToDom(data.comment);
            incrementCommentsCount();
          } else {
            alert(data.error || t.error_comment);
          }
        })
        .catch(() => {
          submitBtn.disabled = false;
          submitBtn.textContent = t.post_comment_btn;
          alert(t.error_connection);
        });
      });
    }

    const commentsList = document.getElementById('vaga-comments-list');
    if (commentsList) {
      commentsList.addEventListener('click', function(e) {
        const delBtn = e.target.closest('.comment-delete-btn');
        if (!delBtn) return;

        const commentId = delBtn.dataset.commentId;
        if (!commentId || !confirm(t.delete_confirm)) return;

        const formData = new FormData();
        formData.append('action', 'delete_comment');
        formData.append('id', commentId);
        formData.append('lang', currentLang);

        fetch('/interacoes.php', {
          method: 'POST',
          body: formData
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            const item = document.getElementById('comment-item-' + commentId);
            if (item) {
              item.style.opacity = '0';
              setTimeout(() => {
                item.remove();
                decrementCommentsCount();
              }, 200);
            }
          } else {
            alert(data.error || t.error_delete);
          }
        })
        .catch(() => {
          alert(t.error_connection);
        });
      });
    }

    refreshVagaInteractions(currentVagaId);
  }

  function handleReactionClick(vagaId, tipo, source = 'detail') {
    const formData = new FormData();
    formData.append('action', 'react');
    formData.append('vaga_id', vagaId);
    formData.append('tipo', tipo);
    formData.append('lang', currentLang);

    fetch('/interacoes.php', {
      method: 'POST',
      body: formData
    })
    .then(r => {
      if (r.status === 401) {
        openAuthModal('login', t.login_to_react);
        throw new Error('Unauthenticated');
      }
      return r.json();
    })
    .then(data => {
      if (data.success) {
        if (source === 'detail' || currentVagaId === vagaId) {
          updateReactionsDom(data.reactions, data.user_reaction);
        }
        updateCardReactionsDom(vagaId, data.reactions, data.user_reaction);
      } else {
        alert(data.error || t.error_reaction);
      }
    })
    .catch(err => {
      if (err.message !== 'Unauthenticated') {
        console.error('Reaction error:', err);
      }
    });
  }

  function refreshVagaInteractions(vagaId) {
    fetch('/interacoes.php?action=get&vaga_id=' + vagaId + '&lang=' + currentLang)
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          currentUser = data.current_user;
          updateReactionsDom(data.reactions, data.user_reaction);
          renderCommentsDom(data.comments, data.comments_count);
        }
      })
      .catch(err => console.error('Interactions load error:', err));
  }

  function updateReactionsDom(counts, userReaction) {
    const reactionBtns = document.querySelectorAll('.vaga-reactions-section .reaction-btn');
    let total = 0;

    reactionBtns.forEach(btn => {
      const tipo = btn.dataset.tipo;
      const countEl = btn.querySelector('.reaction-count');
      const count = (counts && counts[tipo]) ? counts[tipo] : 0;
      total += count;

      if (countEl) {
        countEl.textContent = count > 0 ? count : '';
      }

      btn.classList.toggle('active', userReaction === tipo);
    });

    const totalEl = document.getElementById('reactions-total-count');
    if (totalEl) {
      const word = total === 1 ? t.reaction_singular : t.reaction_plural;
      totalEl.textContent = total > 0 ? `${total} ${word}` : '';
    }
  }

  function renderCommentsDom(comments, count) {
    const list = document.getElementById('vaga-comments-list');
    const badge = document.getElementById('comments-count-badge');
    if (badge) badge.textContent = count || 0;

    if (!list) return;

    if (!comments || comments.length === 0) {
      list.innerHTML = `<div class="comments-empty-state" id="comments-empty-msg">${t.no_comments_vaga}</div>`;
      return;
    }

    let html = '';
    comments.forEach(c => {
      html += createCommentHtml(c);
    });
    list.innerHTML = html;
  }

  function appendCommentToDom(comment) {
    const list = document.getElementById('vaga-comments-list');
    if (!list) return;

    const emptyMsg = document.getElementById('comments-empty-msg');
    if (emptyMsg) emptyMsg.remove();

    const div = document.createElement('div');
    div.innerHTML = createCommentHtml(comment);
    list.insertBefore(div.firstElementChild, list.firstChild);
  }

  function createCommentHtml(c) {
    return `
      <div class="comment-item" id="comment-item-${c.id}">
        <div class="comment-avatar-slot">
          ${c.avatar_html}
        </div>
        <div class="comment-content">
          <div class="comment-meta">
            <span class="comment-author-name">${c.autor_nome}</span>
            <span class="comment-time">${c.tempo}</span>
          </div>
          <div class="comment-text">${c.comentario}</div>
          ${c.pode_excluir ? `<button type="button" class="comment-delete-btn" data-comment-id="${c.id}">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
            ${t.delete_btn}
          </button>` : ''}
        </div>
      </div>
    `;
  }

  function incrementCommentsCount() {
    const badge = document.getElementById('comments-count-badge');
    if (badge) {
      let c = parseInt(badge.textContent, 10) || 0;
      badge.textContent = c + 1;
    }
  }

  function decrementCommentsCount() {
    const badge = document.getElementById('comments-count-badge');
    if (badge) {
      let c = Math.max(0, (parseInt(badge.textContent, 10) || 0) - 1);
      badge.textContent = c;
      if (c === 0) {
        const list = document.getElementById('vaga-comments-list');
        if (list && list.children.length === 0) {
          list.innerHTML = `<div class="comments-empty-state" id="comments-empty-msg">${t.no_comments_vaga}</div>`;
        }
      }
    }
  }

  // ==========================================
  // LISTAGEM DE VAGAS: CARDS, REAÇÕES E ACCORDION
  // ==========================================
  function initCardsInteractions() {
    document.addEventListener('click', function(e) {
      // Reação no card
      const reactBtn = e.target.closest('.card-reaction-btn');
      if (reactBtn) {
        e.preventDefault();
        e.stopPropagation();
        const vagaId = parseInt(reactBtn.dataset.vagaId, 10);
        const tipo = reactBtn.dataset.tipo;
        if (vagaId && tipo) {
          handleReactionClick(vagaId, tipo, 'card');
        }
        return;
      }

      // Toggle do Accordion de comentários
      const toggleBtn = e.target.closest('.card-comments-toggle-btn');
      if (toggleBtn) {
        e.preventDefault();
        e.stopPropagation();
        const vagaId = parseInt(toggleBtn.dataset.vagaId, 10);
        if (vagaId) {
          toggleCardAccordion(vagaId, toggleBtn);
        }
        return;
      }

      // Exclusão de comentário dentro do card
      const delBtn = e.target.closest('.card-comment-del-btn');
      if (delBtn) {
        e.preventDefault();
        e.stopPropagation();
        const commentId = delBtn.dataset.commentId;
        const vagaId = delBtn.dataset.vagaId;
        if (commentId && confirm(t.delete_confirm)) {
          deleteCardComment(commentId, vagaId);
        }
        return;
      }
    });

    // Delegação para submit de comentário dentro de card
    document.addEventListener('submit', function(e) {
      const cardForm = e.target.closest('.card-comment-form');
      if (cardForm) {
        e.preventDefault();
        e.stopPropagation();
        const vagaId = parseInt(cardForm.dataset.vagaId, 10);
        const textarea = cardForm.querySelector('.card-comment-textarea');
        const submitBtn = cardForm.querySelector('.card-comment-submit-btn');
        const text = textarea ? textarea.value.trim() : '';

        if (!text || !vagaId) return;

        submitBtn.disabled = true;
        submitBtn.textContent = t.sending_btn;

        const formData = new FormData();
        formData.append('action', 'comment');
        formData.append('vaga_id', vagaId);
        formData.append('comentario', text);
        formData.append('lang', currentLang);

        fetch('/interacoes.php', {
          method: 'POST',
          body: formData
        })
        .then(r => r.json())
        .then(data => {
          submitBtn.disabled = false;
          submitBtn.textContent = t.comment_btn_short;
          if (data.success && data.comment) {
            textarea.value = '';
            appendCommentToCard(vagaId, data.comment);
            incrementCardCommentsCount(vagaId);
          } else {
            alert(data.error || t.error_comment);
          }
        })
        .catch(() => {
          submitBtn.disabled = false;
          submitBtn.textContent = t.comment_btn_short;
          alert(t.error_connection);
        });
      }
    });

    refreshCardsBatch();
  }

  function refreshCardsBatch(forceReload = false) {
    const cardInteractions = document.querySelectorAll('.job-card-interactions[data-vaga-id]');
    const idsToFetch = [];

    cardInteractions.forEach(el => {
      const vid = parseInt(el.dataset.vagaId, 10);
      if (vid && (forceReload || !loadedBatchIds.has(vid))) {
        idsToFetch.push(vid);
        loadedBatchIds.add(vid);
      }
    });

    if (idsToFetch.length === 0) return;

    fetch('/interacoes.php?action=get_batch&vaga_ids=' + idsToFetch.join(',') + '&lang=' + currentLang)
      .then(r => r.json())
      .then(res => {
        if (res.success && res.data) {
          currentUser = res.current_user;
          Object.keys(res.data).forEach(vidStr => {
            const vid = parseInt(vidStr, 10);
            const item = res.data[vidStr];
            updateCardReactionsDom(vid, item.reactions, item.user_reaction);
            updateCardCommentsCountPill(vid, item.comments_count);
          });
        }
      })
      .catch(err => console.error('Batch load error:', err));
  }

  function updateCardReactionsDom(vagaId, counts, userReaction) {
    const container = document.querySelector(`.job-card-interactions[data-vaga-id="${vagaId}"]`);
    if (!container) return;

    const btns = container.querySelectorAll('.card-reaction-btn');
    btns.forEach(btn => {
      const tipo = btn.dataset.tipo;
      const countEl = btn.querySelector('.card-reaction-count');
      const count = (counts && counts[tipo]) ? counts[tipo] : 0;

      if (countEl) {
        countEl.textContent = count > 0 ? count : '';
      }
      btn.classList.toggle('active', userReaction === tipo);
    });
  }

  function updateCardCommentsCountPill(vagaId, count) {
    const toggleBtn = document.querySelector(`.card-comments-toggle-btn[data-vaga-id="${vagaId}"]`);
    if (toggleBtn) {
      const pill = toggleBtn.querySelector('.card-comments-count-pill');
      if (pill) pill.textContent = count || 0;
    }
  }

  function incrementCardCommentsCount(vagaId) {
    const pill = document.querySelector(`.card-comments-toggle-btn[data-vaga-id="${vagaId}"] .card-comments-count-pill`);
    if (pill) {
      let c = parseInt(pill.textContent, 10) || 0;
      pill.textContent = c + 1;
    }
  }

  function decrementCardCommentsCount(vagaId) {
    const pill = document.querySelector(`.card-comments-toggle-btn[data-vaga-id="${vagaId}"] .card-comments-count-pill`);
    if (pill) {
      let c = Math.max(0, (parseInt(pill.textContent, 10) || 0) - 1);
      pill.textContent = c;
    }
  }

  function toggleCardAccordion(vagaId, toggleBtn) {
    const accordion = document.getElementById('card-comments-' + vagaId);
    if (!accordion) return;

    const isOpen = accordion.style.display !== 'none';
    if (isOpen) {
      accordion.style.display = 'none';
      toggleBtn.classList.remove('open');
      toggleBtn.setAttribute('aria-expanded', 'false');
    } else {
      accordion.style.display = 'block';
      toggleBtn.classList.add('open');
      toggleBtn.setAttribute('aria-expanded', 'true');
      renderCardCommentFormSlot(vagaId);
      loadCardComments(vagaId);
    }
  }

  function renderCardCommentFormSlot(vagaId) {
    const slot = document.querySelector(`#card-comments-${vagaId} .card-comment-form-slot`);
    if (!slot) return;

    if (currentUser) {
      const userInitial = (currentUser.nome || 'U').charAt(0).toUpperCase();
      const avatarMarkup = currentUser.foto
        ? `<img src="${currentUser.foto}" alt="${currentUser.nome}" class="user-avatar" style="width:30px;height:30px;border-radius:50%;object-fit:cover;">`
        : `<div class="user-avatar user-avatar-fallback" style="width:30px;height:30px;border-radius:50%;background:#4b41e1;color:#fff;font-size:13px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;">${userInitial}</div>`;

      slot.innerHTML = `
        <form class="card-comment-form" data-vaga-id="${vagaId}">
          ${avatarMarkup}
          <div class="card-comment-input-wrap">
            <textarea class="card-comment-textarea" placeholder="${t.comment_placeholder_card}" required maxlength="1000"></textarea>
            <button type="submit" class="card-comment-submit-btn">${t.comment_btn_short}</button>
          </div>
        </form>
      `;
    } else {
      slot.innerHTML = `
        <div class="card-comments-auth-prompt">
          <span>${t.login_to_comment_card}</span>
          <button type="button" class="card-comments-auth-prompt-btn open-auth-modal" data-tab="login">${t.login_btn_short}</button>
        </div>
      `;
    }
  }

  function loadCardComments(vagaId) {
    const list = document.getElementById('card-comments-list-' + vagaId);
    if (!list) return;

    list.innerHTML = `<div class="card-comments-empty-notice">${t.loading_comments}</div>`;

    fetch('/interacoes.php?action=get&vaga_id=' + vagaId + '&lang=' + currentLang)
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          currentUser = data.current_user;
          renderCardCommentFormSlot(vagaId);
          renderCardCommentsList(vagaId, data.comments);
          updateCardCommentsCountPill(vagaId, data.comments_count);
        }
      })
      .catch(() => {
        list.innerHTML = `<div class="card-comments-empty-notice" style="color:#e11d48">${t.error_connection}</div>`;
      });
  }

  function renderCardCommentsList(vagaId, comments) {
    const list = document.getElementById('card-comments-list-' + vagaId);
    if (!list) return;

    if (!comments || comments.length === 0) {
      list.innerHTML = `<div class="card-comments-empty-notice">${t.no_comments_card}</div>`;
      return;
    }

    let html = '';
    comments.forEach(c => {
      html += createCardCommentHtml(vagaId, c);
    });
    list.innerHTML = html;
  }

  function appendCommentToCard(vagaId, comment) {
    const list = document.getElementById('card-comments-list-' + vagaId);
    if (!list) return;

    const notice = list.querySelector('.card-comments-empty-notice');
    if (notice) notice.remove();

    const div = document.createElement('div');
    div.innerHTML = createCardCommentHtml(vagaId, comment);
    list.insertBefore(div.firstElementChild, list.firstChild);
  }

  function createCardCommentHtml(vagaId, c) {
    return `
      <div class="card-comment-item" id="card-comment-${c.id}">
        <div style="flex-shrink:0;">${c.avatar_html}</div>
        <div class="card-comment-body">
          <div class="card-comment-header">
            <span class="card-comment-author">${c.autor_nome}</span>
            <span class="card-comment-date">${c.tempo}</span>
          </div>
          <div class="card-comment-msg">${c.comentario}</div>
          ${c.pode_excluir ? `<button type="button" class="card-comment-del-btn" data-comment-id="${c.id}" data-vaga-id="${vagaId}">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
            ${t.delete_btn}
          </button>` : ''}
        </div>
      </div>
    `;
  }

  function deleteCardComment(commentId, vagaId) {
    const formData = new FormData();
    formData.append('action', 'delete_comment');
    formData.append('id', commentId);
    formData.append('lang', currentLang);

    fetch('/interacoes.php', {
      method: 'POST',
      body: formData
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const item = document.getElementById('card-comment-' + commentId);
        if (item) {
          item.remove();
          decrementCardCommentsCount(vagaId);
          const list = document.getElementById('card-comments-list-' + vagaId);
          if (list && list.children.length === 0) {
            list.innerHTML = `<div class="card-comments-empty-notice">${t.no_comments_card}</div>`;
          }
        }
      } else {
        alert(data.error || t.error_delete);
      }
    })
    .catch(() => alert(t.error_connection));
  }

  // Exporta globalmente
  window.MondyAuth = {
    openModal: openAuthModal,
    closeModal: closeAuthModal,
    refreshCards: refreshCardsBatch,
    lang: currentLang
  };

})();
