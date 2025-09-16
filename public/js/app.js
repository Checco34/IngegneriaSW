$(document).ready(function () {
    const API_BASE_URL = '/api';
    let currentUser = null; // Memorizza i dati dell'utente loggato

    const router = {
        showView: (viewId) => {
            $('main > section').hide();
            $(`#${viewId}`).show();
        },
        updateNav: () => {
            const token = localStorage.getItem('jwtToken');
            if (token) {
                try {
                    // Decodifichiamo il payload del token per leggere i dati utente
                    const payload = JSON.parse(atob(token.split('.')[1]));
                    currentUser = payload.data;
                } catch (e) {
                    handleLogout();
                    return; // Se il token non è valido, esegui il logout
                }

                // Se c'è un token, mostra i link per gli utenti autenticati e nascondi quelli per i non autenticati
                $('#login-link, #register-link').hide();
                $('#logout-link, #home-link, #create-dinner-link, #my-dinners-link, #my-participations-link, #notifications-li').show();
            } else {
                // Se non c'è un token, mostra i link per i non autenticati e nascondi quelli per gli autenticati
                currentUser = null;
                $('#login-link, #register-link').show();
                $('#logout-link, #create-dinner-link, #home-link, #my-dinners-link, #my-participations-link, #notifications-li').hide();
            }
        }
    };

    //Funzione che utilizzo per la gestione delle notifiche personalizzate
    function showToast(message, type = 'success') {
        const toast = $('#notification-toast');
        const messageP = $('#notification-message');
        toast.removeClass('success error').addClass(type);
        messageP.text(message);
        toast.addClass('show');
        setTimeout(() => {
            toast.removeClass('show');
        }, 4000);
    }

    //Funzione per il caricamento e la visualizzazione delle cene
    function loadDinners() {
        $.ajax({
            url: `${API_BASE_URL}/cene`,
            method: 'GET',
            beforeSend: (xhr) => { // <-- AGGIUNGI QUESTO BLOCCO
                const token = localStorage.getItem('jwtToken');
                if (token) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                }
            },
            success: (dinners) => {
                const container = $('#dinners-container');
                container.empty();
                if (dinners.length === 0) {
                    container.html('<p>Nessuna cena disponibile al momento.</p>');
                    return;
                }
                dinners.forEach(dinner => {
                    // Logica per mostrare il pulsante "Iscriviti"
                    const isHost = currentUser && currentUser.id == dinner.id_oste;
                    const canParticipate = currentUser && !isHost && dinner.numPostiDisponibili > 0 && dinner.stato === 'APERTA' && !dinner.stato_richiesta_utente;

                    const dinnerCard = `
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body d-flex flex-column">
                                    <div class="flex-grow-1 dinner-card" data-id="${dinner.id}">
                                        <h5 class="card-title">${dinner.titolo}</h5>
                                        <h6 class="card-subtitle mb-2 text-muted">Organizzata da: ${dinner.nome_oste}</h6>
                                        <p><small>${new Date(dinner.dataOra).toLocaleString('it-IT')}</small></p>
                                        <p class="card-text">${dinner.descrizione.substring(0, 100)}...</p>
                                        <p><small><strong>Località:</strong> ${dinner.localita}</small></p>
                                        <p><small><strong>Posti rimasti:</strong> ${dinner.numPostiDisponibili}</small></p>
                                    </div>
                                    ${canParticipate
                            ? `<button class="btn btn-primary btn-sm mt-auto participate-btn" data-id="${dinner.id}">Iscriviti</button>`
                            : ''
                        }
                                </div>
                            </div>
                        </div>`;
                    container.append(dinnerCard);
                });
            },
            error: () => {
                $('#dinners-container').html('<p class="text-danger">Impossibile caricare le cene.</p>');
            }
        });
    }

    // NUOVA FUNZIONE per caricare e visualizzare i dettagli di una singola cena
    function fetchDinnerDetails(dinnerId) {
        $.ajax({
            url: `${API_BASE_URL}/cene/${dinnerId}`,
            method: 'GET',
            beforeSend: (xhr) => {
                const token = localStorage.getItem('jwtToken');
                if (token) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                }
            },
            success: (dinner) => {
                renderDinnerDetails(dinner);
                router.showView('dinner-detail-view');
            },
            error: (err) => {
                showToast('Errore nel caricamento dei dettagli della cena.', 'error');
                router.showView('dinner-list-view');
            }
        });
    }

    // NUOVA FUNZIONE per renderizzare i dettagli della cena
    function renderDinnerDetails(dinner) {
        const container = $('#dinner-details-container');
        container.empty();
        const isHost = currentUser && currentUser.id == dinner.id_oste;
        const canParticipate = currentUser && !isHost && dinner.numPostiDisponibili > 0 && dinner.stato === 'APERTA' && !dinner.stato_richiesta_utente;
        const detailsHtml = `
            <div class="card p-4">
                <h2>${dinner.titolo}</h2>
                <p><strong>Descrizione:</strong> ${dinner.descrizione}</p>
                <p><strong>Menù:</strong> ${dinner.menu}</p>
                <p><strong>Data:</strong> ${new Date(dinner.dataOra).toLocaleString('it-IT')}</p>
                <p><strong>Località:</strong> ${dinner.localita}</p>
                <p><strong>Posti disponibili:</strong> ${dinner.numPostiDisponibili}</p>
                ${canParticipate
                ? `<button class="btn btn-primary mt-3 participate-btn" data-id="${dinner.id}">Invia richiesta di partecipazione</button>`
                : ''}
            </div>`;
        container.append(detailsHtml);
    }

    // NUOVA FUNZIONE per gestire la richiesta di partecipazione
    function handleParticipationRequest(dinnerId) {
        const token = localStorage.getItem('jwtToken');
        if (!token) {
            showToast('Devi essere loggato per iscriverti ad una cena.', 'error');
            router.showView('login-view');
            return;
        }

        $.ajax({
            url: `${API_BASE_URL}/richieste`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ id_cena: dinnerId }),
            beforeSend: (xhr) => {
                xhr.setRequestHeader('Authorization', 'Bearer ' + token);
            },
            success: (response) => {
                showToast(response.message);
                router.showView('dinner-list-view');
                loadDinners();
            },
            error: (err) => {
                const message = err.responseJSON?.message || 'Errore nella richiesta di partecipazione.';
                showToast(message, 'error');
            }
        });
    }

    function handleLogout() {
        localStorage.removeItem('jwtToken');
        currentUser = null;
        router.updateNav();
        router.showView('login-view');
        showToast('Logout effettuato con successo!');
    }

    function loadMyParticipations() {
        const token = localStorage.getItem('jwtToken'); 
        const futureContainer = $('#my-future-participations-container');
        const pastContainer = $('#my-past-participations-container');

        $.ajax({
            url: `${API_BASE_URL}/partecipazioni/mie/future`,
            method: 'GET',
            beforeSend: (xhr) => xhr.setRequestHeader('Authorization', 'Bearer ' + token),
            success: (participations) => {
                futureContainer.empty();
                if (participations.length === 0) {
                    futureContainer.html('<h5>Cene in Programma</h5><p>Nessuna partecipazione futura.</p>');
                    return;
                }
                let html = '<h5>Cene in Programma</h5><ul class="list-group">';
                participations.forEach(p => {
                    html += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${p.titolo}</strong> (organizzata da ${p.nome_oste})
                            <br><small>${new Date(p.dataOra).toLocaleString('it-IT')}</small>
                        </div>
                        <button class="btn btn-warning btn-sm btn-annulla-partecipazione" data-id="${p.id}">Annulla partecipazione</button>
                    </li>`;
                });
                html += '</ul>';
                futureContainer.html(html);
            }
        });

        $.ajax({
            url: `${API_BASE_URL}/partecipazioni/mie/passate`,
            method: 'GET',
            beforeSend: (xhr) => xhr.setRequestHeader('Authorization', 'Bearer ' + token),
            success: (participations) => {
                pastContainer.empty();
                if (participations.length === 0) {
                    pastContainer.html('<h5>Cene Passate</h5><p>Non hai ancora partecipato a nessuna cena.</p>');
                    return;
                }
                let html = '<h5>Cene Passate</h5><ul class="list-group">';
                participations.forEach(p => {
                    html += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${p.titolo}</strong> (organizzata da ${p.nome_oste})
                            <br><small>${new Date(p.dataOra).toLocaleDateString('it-IT')}</small>
                        </div>
                        ${!p.ha_recensito_oste ?
                            `<button class="btn btn-primary btn-sm review-btn" data-bs-toggle="modal" data-bs-target="#review-modal" data-id-cena="${p.id_cena}" data-id-valutato="${p.id_oste}" data-nome-valutato="${p.nome_oste}">Lascia una recensione</button>` :
                            '<span class="badge bg-success">Recensito</span>'
                        }
                    </li>`;
                });
                html += '</ul>';
                pastContainer.html(html);
            }
        });
    }

    function loadNotifications() {
        const token = localStorage.getItem('jwtToken');
        if (!token) return;

        $.ajax({
            url: `${API_BASE_URL}/notifiche`,
            method: 'GET',
            beforeSend: (xhr) => xhr.setRequestHeader('Authorization', 'Bearer ' + token),
            success: (response) => {
                const container = $('#notifications-container');
                const countBadge = $('#notifications-count');
                container.empty();

                if (response.notifications.length === 0) {
                    container.html('<li><span class="dropdown-item-text">Nessuna notifica</span></li>');
                } else {
                    response.notifications.forEach(n => {
                        const isUnread = !n.letta ? 'fw-bold' : '';
                        const notificationHtml = `
                            <li>
                                <a href="#" class="dropdown-item notification-item ${isUnread}" data-id="${n.id}">
                                    ${n.messaggio}
                                    <br><small class="text-muted">${new Date(n.data_creazione).toLocaleString('it-IT')}</small>
                                </a>
                            </li>`;
                        container.append(notificationHtml);
                    });
                }

                if (response.unreadCount > 0) {
                    countBadge.text(response.unreadCount).show();
                } else {
                    countBadge.hide();
                }
            }
        });
    }

    // Gestione apertura modale per la recensione
    $(document).on('click', '.review-btn', function() {
        const id_cena = $(this).data('id-cena');
        const id_valutato = $(this).data('id-valutato');
        const nome_valutato = $(this).data('nome-valutato');

        $('#review-modal-title').text(`Recensione per ${nome_valutato}`);
        $('#review-id-cena').val(id_cena);
        $('#review-id-valutato').val(id_valutato);
    });

    // Gestione invio del form della recensione
    $('#review-form').on('submit', function(e) {
        e.preventDefault();
        const token = localStorage.getItem('jwtToken');
        const data = {
            id_cena: $('#review-id-cena').val(),
            id_valutato: $('#review-id-valutato').val(),
            voto: parseInt($('#review-voto').val()),
            commento: $('#review-commento').val()
        };

        $.ajax({
            url: `${API_BASE_URL}/recensioni`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            beforeSend: (xhr) => xhr.setRequestHeader('Authorization', 'Bearer ' + token),
            success: (response) => {
                showToast(response.message);
                $('#review-modal').modal('hide');
                loadMyParticipations();
                loadMyDinners();
            },
            error: (err) => {
                showToast(err.responseJSON?.message || 'Errore', 'error');
            }
        });
    });

    // Funzione per caricare le cene organizzate dall'utente loggato
    function loadMyDinners() {
        const token = localStorage.getItem('jwtToken');
        $.ajax({
            url: `${API_BASE_URL}/cene/mie`,
            method: 'GET',
            beforeSend: (xhr) => {
                xhr.setRequestHeader('Authorization', 'Bearer ' + token);
            },
            success: (dinners) => {
                const container = $('#my-dinners-container');
                container.empty();
                if (dinners.length === 0) {
                    container.html('<p>Non hai ancora organizzato nessuna cena.</p>');
                    return;
                }
                dinners.forEach((dinner, index) => {
                    const canManage = dinner.stato === 'APERTA';
                    const isAnnullata = dinner.stato === 'ANNULLATA';
                    const dinnerAccordion = `
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-${index}">
                            <button ${isAnnullata ? `style="cursor: default;"` : ''} class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${index}">
                                ${dinner.titolo} - ${new Date(dinner.dataOra).toLocaleDateString('it-IT')}
                                <span class="badge bg-${dinner.stato === 'APERTA' ? 'success' : 'secondary'} ms-2">${dinner.stato}</span>
                            </button>
                        </h2>
                        ${!isAnnullata ? `
                            <div id="collapse-${index}" class="accordion-collapse collapse" data-bs-parent="#my-dinners-container">
                                ${canManage ? `<button class="btn btn-danger btn-sm btn-annulla-cena" data-id="${dinner.id}">Annulla Cena</button> ` : ''}
                                <div class="accordion-body" id="requests-for-dinner-${dinner.id}">
                                    Caricamento...
                                </div>
                            </div> ` 
                        : ''}
                    </div>`;
                    container.append(dinnerAccordion);
                    
                    const isPastDinner = new Date(dinner.dataOra) < new Date();
                    if (isPastDinner) {
                        loadParticipantsForDinner(dinner.id);
                    } else {
                        loadRequestsForDinner(dinner.id);
                    }
                });
            }
        });
    }

    // Funzione che carica le richieste per una cena e mostra i pulsanti
    function loadRequestsForDinner(dinnerId) {
        const token = localStorage.getItem('jwtToken');
        $.ajax({
            url: `${API_BASE_URL}/cene/${dinnerId}/richieste`,
            method: 'GET',
            beforeSend: (xhr) => xhr.setRequestHeader('Authorization', 'Bearer ' + token),
            success: (requests) => {
                const container = $(`#requests-for-dinner-${dinnerId}`);
                if (requests.length === 0) {
                    container.html('<p>Nessuna richiesta di partecipazione per questa cena.</p>');
                    return;
                }
                let requestsHtml = '<h6>Richieste Ricevute:</h6><ul class="list-group">';
                requests.forEach(req => {
                    requestsHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        ${req.nome} ${req.cognome} - Stato: <strong>${req.stato}</strong>
                        ${req.stato === 'IN ATTESA' ? 
                        `<div>
                            <button class="btn btn-success btn-sm me-2 manage-request-btn" data-id="${req.id}" data-action="ACCETTATA">Accetta</button>
                            <button class="btn btn-danger btn-sm manage-request-btn" data-id="${req.id}" data-action="RIFIUTATA">Rifiuta</button>
                        </div>` : ''
                        }
                    </li>`;
                });
                requestsHtml += '</ul>';
                container.html(requestsHtml);
            }
        });
    }

    function loadParticipantsForDinner(dinnerId) {
        const token = localStorage.getItem('jwtToken');
        $.ajax({
            url: `${API_BASE_URL}/cene/${dinnerId}/partecipanti`,
            method: 'GET',
            beforeSend: (xhr) => xhr.setRequestHeader('Authorization', 'Bearer ' + token),
            success: (participants) => {
                const container = $(`#requests-for-dinner-${dinnerId}`);
                let participantsHtml = '<h6>Partecipanti Confermati:</h6><ul class="list-group">';
                if (participants.length === 0) {
                    container.html('<p>Nessun partecipante confermato per questa cena.</p>');
                    return;
                }
                participants.forEach(p => {
                    participantsHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        ${p.nome} ${p.cognome}
                        ${!p.oste_ha_recensito ? 
                            `<button class="btn btn-outline-primary btn-sm review-btn" data-bs-toggle="modal" data-bs-target="#review-modal" data-id-cena="${dinnerId}" data-id-valutato="${p.id_commensale}" data-nome-valutato="${p.nome}">Recensisci</button>` : 
                            '<span class="badge bg-success">Già Recensito</span>'
                        }
                    </li>`;
                });
                participantsHtml += '</ul>';
                container.html(participantsHtml);
            },
            error: (err) => {
                const container = $(`#requests-for-dinner-${dinnerId}`);
                container.html('<p class="text-danger">Errore nel caricamento dei partecipanti.</p>')
            }
        });
    }

    // Funzione per gestire il click su "Accetta" o "Rifiuta"
    function handleManageRequest(requestId, action) {
        const token = localStorage.getItem('jwtToken');
        $.ajax({
            url: `${API_BASE_URL}/richieste/${requestId}`,
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify({ stato: action }),
            beforeSend: (xhr) => xhr.setRequestHeader('Authorization', 'Bearer ' + token),
            success: (response) => {
                showToast(response.message);
                loadMyDinners(); // Ricarica la vista per mostrare lo stato aggiornato
            },
            error: (err) => {
                showToast(err.responseJSON?.message || 'Errore', 'error');
            }
        });
    }

    // AGGIUNTA GESTIONE NUOVI LINK E FORM
    $('#home-link').on('click', () => {
        router.showView('dinner-list-view');
        loadDinners();
    });
    $('#login-link').on('click', () => router.showView('login-view'));
    $('#register-link').on('click', () => router.showView('register-view'));
    $('#create-dinner-link').on('click', () => router.showView('create-dinner-view'));
    $('#my-dinners-link').on('click', () => {
        router.showView('my-dinners-view');
        loadMyDinners();
    });
    $('#logout-link').on('click', handleLogout);
    // Clic sui pulsanti "Accetta" o "Rifiuta"
    $(document).on('click', '.manage-request-btn', function() {
        handleManageRequest($(this).data('id'), $(this).data('action'));
    });

    // Form di Login (AGGIORNATO per ricaricare le cene dopo il login)
    $('#login-form').on('submit', function (e) {
        e.preventDefault();
        const data = {
            email: $('#login-email').val(),
            password: $('#login-password').val()
        };
        $.ajax({
            url: `${API_BASE_URL}/login`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: (response) => {
                localStorage.setItem('jwtToken', response.token);
                router.updateNav();
                router.showView('dinner-list-view');
                loadDinners(); // Ricarica le cene per mostrare eventuali nuove opzioni
                loadNotifications();
                showToast('Login avvenuto con successo!');
            },
            error: (err) => {
                $('#login-error').text(err.responseJSON?.message || 'Credenziali non valide.');
                showToast('Credenziali non valide.', 'error');
            }
        });
    });

    // La versione finale e pulita del gestore del form di registrazione
    $('#register-form').on('submit', function (e) {
        e.preventDefault();
        const data = {
            nome: $('#register-nome').val(),
            cognome: $('#register-cognome').val(),
            email: $('#register-email').val(),
            password: $('#register-password').val(),
        };

        $.ajax({
            url: `${API_BASE_URL}/registrati`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: (response) => {
                showToast('Registrazione avvenuta con successo!');
                router.showView('login-view');
            },
            error: (err) => {
                $('#register-error').text(err.responseJSON?.message || 'Errore di registrazione.');
                showToast(err.responseJSON?.message || 'Errore di registrazione.', 'error');
            }
        });
    });

    // NUOVA GESTIONE FORM CREA CENA
    $('#create-dinner-form').on('submit', function (e) {
        e.preventDefault();
        const data = {
            titolo: $('#dinner-titolo').val(),
            descrizione: $('#dinner-descrizione').val(),
            dataOra: $('#dinner-data').val(),
            localita: $('#dinner-localita').val(),
            numPostiDisponibili: parseInt($('#dinner-posti').val()),
            menu: $('#dinner-menu').val()
        };

        // Frontend validation for numPostiDisponibili
        if (data.numPostiDisponibili <= 0 || isNaN(data.numPostiDisponibili)) {
            $('#create-dinner-error').text('Il numero di posti deve essere maggiore di zero.');
            showToast('Il numero di posti deve essere maggiore di zero.', 'error');
            return;
        }

        const token = localStorage.getItem('jwtToken');
        if (!token) {
            showToast('Devi essere loggato per creare una cena.', 'error');
            return;
        }

        $.ajax({
            url: `${API_BASE_URL}/cene`, // Adjusted URL to match backend router
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            beforeSend: (xhr) => {
                xhr.setRequestHeader('Authorization', 'Bearer ' + token);
            },
            success: (response) => {
                showToast('Cena creata con successo!');
                $('#create-dinner-form')[0].reset(); // Reset form
                router.showView('dinner-list-view');
                loadDinners(); // Ricarica la lista per mostrare la nuova cena
            },
            error: (err) => {
                $('#create-dinner-error').text(err.responseJSON?.message || 'Errore nella creazione della cena.');
                showToast(err.responseJSON?.message || 'Errore nella creazione della cena.', 'error');
            }
        });
    });

    if ($('#dinner-detail-view').length === 0) {
        $('main').append('<section id="dinner-detail-view" style="display:none;"><div id="dinner-details-container"></div></section>');
    }

    $(document).on('click', '.dinner-card', function () {
        const dinnerId = $(this).data('id');
        fetchDinnerDetails(dinnerId);
    });

    $(document).on('click', '.participate-btn', function () {
        const dinnerId = $(this).data('id');
        handleParticipationRequest(dinnerId);
    });

    $(document).on('click', '.btn-annulla-cena', function() {
        const cenaId = $(this).data('id');
        const token = localStorage.getItem('jwtToken');

        if ($(this).hasClass('btn-annulla-cena')) {
            if (confirm('Sei sicuro di voler annullare questa cena? L\'azione è irreversibile.')) {
                $.ajax({
                    url: `${API_BASE_URL}/cene/annulla/${cenaId}`,
                    method: 'POST',
                    beforeSend: (xhr) => xhr.setRequestHeader('Authorization', 'Bearer ' + token),
                    success: (response) => {
                        showToast(response.message);
                        loadMyDinners(); // Ricarica la lista per vedere lo stato aggiornato
                    },
                    error: (err) => showToast(err.responseJSON?.message || 'Errore', 'error')
                });
            }
        }
    });

    $('#notifications-container').on('click', '.notification-item', function(e) {
        e.preventDefault();
        const notificationId = $(this).data('id');

        if (!notificationId) {
            console.error("ERRORE: ID della notifica non trovato. Controlla l'attributo data-id nell'HTML.");
            return;
        }

        const token = localStorage.getItem('jwtToken');

        $.ajax({
            url: `${API_BASE_URL}/notifiche/${notificationId}`,
            method: 'GET',
            beforeSend: (xhr) => xhr.setRequestHeader('Authorization', 'Bearer ' + token),
            success: (details) => {

                const modalElement = $('#notification-modal');
                const modalTitle = $('#notification-modal-title');
                const modalBody = $('#notification-modal-body');
                
                
                if (details.id_recensione) {
                    modalTitle.text(`Recensione da ${details.nome_valutatore}`);
                    const vote = details.voto ? parseInt(details.voto) : 0;
                    modalBody.html(`
                        <p><strong>Cena:</strong> ${details.titolo_cena}</p>
                        <p><strong>Voto:</strong> ${'⭐'.repeat(vote)}</p>
                        <hr>
                        <p><strong>Commento:</strong></p>
                        <p class="fst-italic word-break-custom">"${details.commento}"</p>
                    `);
                } else {
                    modalTitle.text('Notifica');
                    modalBody.text(details.messaggio);
                }
                
                try {
                    modalElement.modal('show');
                } catch (error) {
                    console.error("ERRORE CRITICO: Il comando .modal('show') ha fallito. Questo significa che la funzione modal di Bootstrap non è disponibile o non funziona correttamente.", error);
                }
            },
            error: (err) => {
                console.error("ERRORE: La chiamata AJAX per ottenere i dettagli della notifica è fallita.", err);
                showToast('Impossibile caricare i dettagli della notifica.', 'error');
            }
        });
    });

    $('#notifications-link').on('click', function() {
        const token = localStorage.getItem('jwtToken');
        const countBadge = $('#notifications-count');
        
        // Se il contatore è visibile, significa che ci sono notifiche non lette da segnare
        if (countBadge.is(':visible')) {
            $.ajax({
                url: `${API_BASE_URL}/notifiche/leggi`,
                method: 'POST',
                beforeSend: (xhr) => xhr.setRequestHeader('Authorization', 'Bearer ' + token),
                success: () => {
                    // Nascondiamo subito il contatore per un feedback immediato
                    countBadge.hide();
                    // Dopo un breve ritardo, ricarichiamo le notifiche per togliere il grassetto
                    setTimeout(() => {
                        loadNotifications(); 
                    }, 2000);
                }
            });
        }
    });

    $('#my-participations-link').on('click', () => {
        router.showView('my-participations-view');
        loadMyParticipations();
    });

    $(document).on('click', '.btn-annulla-partecipazione', function() {
        const participationId = $(this).data('id');
        const token = localStorage.getItem('jwtToken');

        if (confirm('Sei sicuro di voler annullare la tua partecipazione a questa cena?')) {
            $.ajax({
                url: `${API_BASE_URL}/partecipazioni/annulla/${participationId}`,
                method: 'POST',
                beforeSend: (xhr) => xhr.setRequestHeader('Authorization', 'Bearer ' + token),
                success: (response) => {
                    showToast(response.message);
                    loadMyParticipations();
                },
                error: (err) => {
                    showToast(err.responseJSON?.message || 'Errore durante l\'annullamento.', 'error');
                }
            });
        }
    });

    router.updateNav();
    const token = localStorage.getItem('jwtToken');
    if (!token) {
        router.showView('login-view');
    } else {
        router.showView('dinner-list-view');
        loadDinners();
        loadNotifications();
    }
});