(function() {
    const container    = document.getElementById('vagas-container');
    const loadingEl    = document.getElementById('loading');
    const sentinel     = document.getElementById('sentinel');
    const searchInput   = document.getElementById('search');
    const searchLoading = document.getElementById('search-loading');
    const searchCorrection = document.getElementById('search-correction');
    const searchModes   = document.querySelectorAll('input[name="modo"]');
    const resultsInfo   = document.getElementById('results-info');
    const vagasTotal  = document.getElementById('vagas-total');
    const backToTop   = document.getElementById('back-to-top');
    const newsletterForm = document.getElementById('newsletter-form');

    const modalOverlay = document.getElementById('modal-overlay');
    const modalTitle = document.getElementById('modal-title');
    const modalSubtitle = document.getElementById('modal-subtitle');
    const modalBody = document.getElementById('modal-body');
    const modalApply = document.getElementById('modal-apply');
    const modalClose = document.getElementById('modal-close');
    const modalFooter = document.getElementById('modal-footer');

    const LIMIT = 10;
    const DEBOUNCE_MS = 1000;

    const spinnerSvg = '<svg class="loading-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';

    let page = 0;
    let hasMore = true;
    let loading = false;
    let currentQuery = '';
    let debounceTimer = null;

    function resetAndFetch() {
        container.innerHTML = '';
        resultsInfo.textContent = '';
        vagasTotal.textContent = '';
        searchCorrection.classList.add('hidden');
        loadingEl.classList.add('hidden');
        page = 0;
        hasMore = true;
        loading = false;
        fetchVagas();
    }

    function showSearchLoading() { searchLoading.classList.remove('hidden'); }
    function hideSearchLoading() { searchLoading.classList.add('hidden'); }

    function fetchVagaById(id) {
        return fetch('/api.php?vaga_id=' + encodeURIComponent(id)).then(function(r) { return r.json(); });
    }

    function openModal(v) {
        modalTitle.textContent = v.titulo;
        modalSubtitle.textContent = v.empresa + ' • ' + (v.localizacao || 'Remote');
        modalBody.innerHTML = v.descricao || '<p>Description not available.</p>';
        modalApply.href = v.url_vaga;
        modalFooter.classList.remove('hidden');
        modalOverlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        location.hash = v.vaga_id_externo;
    }

    function closeModal() {
        modalOverlay.classList.add('hidden');
        document.body.style.overflow = '';
        history.replaceState(null, '', window.location.pathname + window.location.search);
    }

    modalClose.addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) closeModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modalOverlay.classList.contains('hidden')) closeModal();
    });

    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var nomeInput = newsletterForm.querySelector('input[type="text"]');
            var emailInput = newsletterForm.querySelector('input[type="email"]');
            var areaSelect = document.getElementById('newsletter-area');
            var btn = newsletterForm.querySelector('button');
            var nome = nomeInput.value.trim();
            var email = emailInput.value.trim();
            var area = areaSelect ? areaSelect.value : '';

            if (!nome || !email || !area) return;

            btn.disabled = true;
            btn.textContent = 'Sending...';

            var params = 'nome=' + encodeURIComponent(nome) + '&email=' + encodeURIComponent(email) + '&area=' + encodeURIComponent(area) + '&origem=usa';

            var msgs = {
                success: 'Successfully subscribed!',
                duplicate_email: 'This email is already registered',
                missing_fields: 'Name and email are required',
                invalid_email: 'Invalid email',
                invalid_area: 'Select an area of interest',
                _default: 'Subscription failed',
                _network: 'Connection error. Try again.'
            };

            function showFormError(msg) {
                var oldErr = newsletterForm.querySelector('.form-error');
                if (oldErr) oldErr.remove();
                var errEl = document.createElement('p');
                errEl.className = 'form-error';
                errEl.style.cssText = 'color:#ba1a1a;font-weight:500;text-align:center;padding:12px 0;margin:0';
                errEl.textContent = msg;
                newsletterForm.insertBefore(errEl, newsletterForm.firstChild);
                btn.disabled = false;
                btn.textContent = 'Subscribe Now';
            }

            fetch('/subscribe.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params
            })
            .then(function(r) { return r.text(); })
            .then(function(text) {
                var json;
                try { json = JSON.parse(text); } catch(e) { json = null; }
                if (json && json.success) {
                    newsletterForm.innerHTML = '<p style="color:#4b41e1;font-weight:600;text-align:center;padding:12px 0;">' + msgs.success + '</p>';
                } else {
                    showFormError(json && msgs[json.code] ? msgs[json.code] : msgs._default);
                }
            })
            .catch(function() {
                showFormError(msgs._network);
            });
        });
    }

    function normalizarModelo(raw) {
        if (!raw) return null;
        var map = { 'Remote': 'Remote', 'Hybrid': 'Hybrid', 'OnSite': 'On-site', 'On-site': 'On-site' };
        return map[raw] || raw;
    }

    function getBadgeClass(modelo) {
        if (!modelo) return '';
        var m = modelo.toLowerCase();
        if (m === 'remote') return 'badge badge-remote';
        if (m === 'hybrid') return 'badge badge-hybrid';
        return 'badge badge-onsite';
    }

    function renderCard(v) {
        const rawModelo = normalizarModelo(v.modelo_trabalho);
        const modelo = rawModelo ? formatModelo(rawModelo) : null;
        const local = v.localizacao || 'Remote';
        const resumo = v.resumo || 'This ' + v.titulo + ' position' + (modelo ? ' is ' + modelo.toLowerCase() : '') + '.';

        var card = document.createElement('article');
        card.className = 'job-card';

        var modeloHtml = modelo
            ? '<span class="' + getBadgeClass(modelo) + '">' + escapeHtml(modelo) + '</span>'
            : '';

        var locSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0116 0z"/><circle cx="12" cy="10" r="3"/></svg>';
        var calSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';

        var dateHtml = v.publicado_em
            ? '<span class="job-card-info-text job-card-date">' + calSvg + escapeHtml(v.publicado_em) + '</span>'
            : '';

        card.innerHTML =
            '<div>' +
                '<h3 class="job-card-title">' + escapeHtml(v.titulo) + '</h3>' +
                '<p class="job-card-company">' + escapeHtml(v.empresa) + '</p>' +
            '</div>' +
            '<div class="job-card-info">' +
                modeloHtml +
                '<span class="job-card-info-text">' + locSvg + escapeHtml(local) + '</span>' +
                dateHtml +
            '</div>' +
            '<p class="job-card-resumo line-clamp-2">' + escapeHtml(resumo) + '</p>' +
            '<div class="job-card-footer"><button class="job-card-btn btn-open-desc">View Details</button></div>';

        var openBtn = card.querySelector('.btn-open-desc');
        openBtn.addEventListener('click', function() { openModal(v); });

        container.appendChild(card);
    }

    function formatModelo(modelo) {
        return modelo;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function getSearchMode() {
        for (var i = 0; i < searchModes.length; i++) {
            if (searchModes[i].checked) return searchModes[i].value;
        }
        return 'titulo';
    }

    function fetchVagas() {
        if (loading || !hasMore) return;
        loading = true;
        loadingEl.innerHTML = spinnerSvg + ' Loading...';
        loadingEl.classList.remove('hidden');
        page++;

        var modo = getSearchMode();
        var url = '/api.php?page=' + page + '&limit=' + LIMIT + '&origem=exterior' + (currentQuery ? '&q=' + encodeURIComponent(currentQuery) + '&modo=' + modo : '');
        let hasError = false;

        fetch(url)
            .then(function(res) { return res.json(); })
            .then(function(json) {
                if (json.error) {
                    loadingEl.textContent = 'Erro: ' + json.error;
                    hasError = true;
                    return;
                }
                json.data.forEach(function(v) { renderCard(v); });
                hasMore = json.has_more;

                if (page === 1) {
                    vagasTotal.textContent = json.total + (currentQuery ? ' result(s) found' : ' international jobs');
                }

                if (currentQuery && page === 1) {
                    resultsInfo.textContent = json.total > 0
                        ? json.total + ' result(s) for "' + currentQuery + '"'
                        : 'No results for "' + currentQuery + '"';
                }

                if (page === 1 && json.query_corrigida) {
                    searchCorrection.textContent = 'Did you mean: "' + json.query_corrigida + '"';
                    searchCorrection.classList.remove('hidden');
                } else if (page === 1) {
                    searchCorrection.classList.add('hidden');
                }

                if (!hasMore) {
                    loadingEl.textContent = json.data.length === 0 ? 'No jobs found.' : 'All jobs have been loaded.';
                }
            })
            .catch(function() {
                loadingEl.textContent = 'Error loading.';
                hasError = true;
            })
            .finally(function() {
                loading = false;
                if (!hasError && hasMore) {
                    loadingEl.classList.add('hidden');
                }
            });
    }

    const observer = new IntersectionObserver(function(entries) {
        if (entries[0].isIntersecting) {
            fetchVagas();
        }
    }, { rootMargin: '100px' });

    observer.observe(sentinel);

    function onSearchChange() {
        clearTimeout(debounceTimer);
        hideSearchLoading();
        debounceTimer = setTimeout(function() {
            hideSearchLoading();
            const val = searchInput.value.trim();
            if (val !== currentQuery) {
                currentQuery = val;
                resetAndFetch();
            }
        }, DEBOUNCE_MS);
        showSearchLoading();
    }

    searchInput.addEventListener('input', onSearchChange);

    for (var i = 0; i < searchModes.length; i++) {
        searchModes[i].addEventListener('change', function() {
            if (currentQuery) {
                resetAndFetch();
            }
        });
    }

    function handleHash() {
        if (location.hash) {
            var id = decodeURIComponent(location.hash.substring(1));
            fetchVagaById(id).then(function(vaga) {
                if (vaga && vaga.vaga_id_externo) openModal(vaga);
            });
        }
    }

    window.addEventListener('hashchange', function() {
        if (location.hash) {
            var id = decodeURIComponent(location.hash.substring(1));
            fetchVagaById(id).then(function(vaga) {
                if (vaga && vaga.vaga_id_externo) openModal(vaga);
            });
        } else {
            if (!modalOverlay.classList.contains('hidden')) closeModal();
        }
    });

    handleHash();
    fetchVagas();

    window.addEventListener('scroll', function() {
        if (window.scrollY > 400) {
            backToTop.classList.remove('hidden');
        } else {
            backToTop.classList.add('hidden');
        }
    });

    backToTop.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    /* ==== ABOUT MODAL ==== */
    function openAboutModal() {
        modalTitle.textContent = 'About Mondywork';
        modalSubtitle.textContent = '';
        modalBody.innerHTML =
            '<p>Mondywork was born from a volunteer effort to connect those seeking an opportunity to their next professional step. We believe a new job transforms not just a career, but the life of an entire family.</p>' +
            '<p>If you believe in a society with better income distribution through work, consider supporting us by following this page and sharing our link so we can reach more people who need a job.</p>' +
            '<p>Together we can make a difference. Thank you!</p>';
        modalFooter.classList.add('hidden');
        modalOverlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    var linkAboutFooter = document.getElementById('link-sobre-footer');
    if (linkAboutFooter) {
        linkAboutFooter.addEventListener('click', function(e) {
            e.preventDefault();
            openAboutModal();
        });
    }

    document.querySelectorAll('.mobile-about-link').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            closeMenu();
            openAboutModal();
        });
    });

    /* ==== MOBILE MENU ==== */
    var navToggle = document.getElementById('nav-toggle');
    var mobileMenu = document.getElementById('mobile-menu');

    function closeMenu() {
        navToggle.classList.remove('active');
        mobileMenu.classList.remove('open');
    }

    if (navToggle && mobileMenu) {
        navToggle.addEventListener('click', function() {
            navToggle.classList.toggle('active');
            mobileMenu.classList.toggle('open');
        });

        mobileMenu.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', closeMenu);
        });
    }
})();
