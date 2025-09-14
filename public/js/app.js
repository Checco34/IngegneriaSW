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
                $('#logout-link, #home-link').show();
                $('#create-dinner-link').show();
            } else {
                // Se non c'è un token, mostra i link per i non autenticati e nascondi quelli per gli autenticati
                currentUser = null;
                $('#login-link, #register-link').show();
                $('#logout-link, #create-dinner-link, #home-link').hide();
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
                    const canParticipate = currentUser && !isHost && dinner.numPostiDisponibili > 0;

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
        const canParticipate = currentUser && !isHost && dinner.numPostiDisponibili > 0 && dinner.stato === 'APERTA';
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

    // AGGIUNTA GESTIONE NUOVI LINK E FORM
    $('#home-link').on('click', () => {
        router.showView('dinner-list-view');
        loadDinners();
    });
    $('#login-link').on('click', () => router.showView('login-view'));
    $('#register-link').on('click', () => router.showView('register-view'));
    $('#create-dinner-link').on('click', () => router.showView('create-dinner-view'));
    $('#logout-link').on('click', handleLogout);

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

    // NUOVI EVENT LISTENER
    // Aggiungi la vista dei dettagli della cena al DOM se non esiste
    if ($('#dinner-detail-view').length === 0) {
        $('main').append('<section id="dinner-detail-view" style="display:none;"><div id="dinner-details-container"></div></section>');
    }

    // Clic su una card per visualizzare i dettagli
    $(document).on('click', '.dinner-card', function() {
        const dinnerId = $(this).data('id');
        fetchDinnerDetails(dinnerId);
    });

    // Clic sul bottone "Iscriviti" nella lista o nei dettagli
    $(document).on('click', '.participate-btn', function() {
        const dinnerId = $(this).data('id');
        handleParticipationRequest(dinnerId);
    });

    // --- INIZIALIZZAZIONE ---
    router.updateNav();
    const token = localStorage.getItem('jwtToken');
    if (!token) {
        router.showView('login-view');
    } else {
        router.showView('dinner-list-view');
        loadDinners();
    }
});