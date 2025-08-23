$(document).ready(function() {
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
                    handleLogout(); return; // Se il token non è valido, esegui il logout
                }

                $('#login-link, #register-link').hide();
                $('#logout-link').show();
                
                // Mostra "Crea Cena" solo se l'utente è un OSTE
                if (currentUser && currentUser.ruolo === 'OSTE') {
                    $('#create-dinner-link').show();
                }
            } else {
                currentUser = null;
                $('#login-link, #register-link').show();
                $('#logout-link, #create-dinner-link').hide();
            }
        }
    };

    //Funzione che utilizzo per la gestione delle notifiche personalizzate
    function showToast(message, type = 'success') {
        const toast = $('#notification-toast');
        const messageP = $('#notification-message');

        // Imposta il messaggio e il tipo di notifica (success o error)
        messageP.text(message);
        toast.removeClass('success error').addClass(type);

        // Mostra la notifica aggiungendo la classe 'show'
        toast.addClass('show');

        // Nasconde la notifica dopo 4 secondi
        setTimeout(() => {
            toast.removeClass('show');
        }, 4000);
    }

    //Funzione per il caricamento e la visualizzazione delle cene
    function loadDinners() {
        $.ajax({
            url: `${API_BASE_URL}/dinners`,
            method: 'GET',
            success: (dinners) => {
                const container = $('#dinners-container');
                container.empty();
                if (dinners.length === 0) {
                    container.html('<p>Nessuna cena disponibile al momento.</p>');
                    return;
                }
                dinners.forEach(dinner => {
                    const dinnerCard = `
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">${dinner.titolo}</h5>
                                    <h6 class="card-subtitle mb-2 text-muted">${new Date(dinner.dataOra).toLocaleString('it-IT')}</h6>
                                    <p class="card-text">${dinner.descrizione.substring(0, 100)}...</p>
                                    <p><small><strong>Località:</strong> ${dinner.localita}</small></p>
                                    <p><small><strong>Posti rimasti:</strong> ${dinner.numPostiDisponibili}</small></p>
                                </div>
                            </div>
                        </div>`;
                    container.append(dinnerCard);
                });
            },
            error: () => { $('#dinners-container').html('<p class="text-danger">Impossibile caricare le cene.</p>');}
        });
    }

    function handleLogout() {
        localStorage.removeItem('jwtToken');
        router.updateNav();
        router.showView('dinner-list-view');
    }

    // AGGIUNTA GESTIONE NUOVI LINK E FORM
    $('#home-link').on('click', () => router.showView('dinner-list-view'));
    $('#login-link').on('click', () => router.showView('login-view'));
    $('#register-link').on('click', () => router.showView('register-view'));
    $('#create-dinner-link').on('click', () => router.showView('create-dinner-view'));
    $('#logout-link').on('click', handleLogout);

    // Form di Login (AGGIORNATO per ricaricare le cene dopo il login)
    $('#login-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: `${API_BASE_URL}/login`, method: 'POST', contentType: 'application/json',
            data: JSON.stringify({ email: $('#login-email').val(), password: $('#login-password').val() }),
            success: (response) => {
                localStorage.setItem('jwtToken', response.token);
                router.updateNav();
                router.showView('dinner-list-view');
                loadDinners(); // Ricarica le cene per mostrare eventuali nuove opzioni
            },
            error: (err) => { $('#login-error').text(err.responseJSON?.message || 'Errore.'); }
        });
    });

    // La versione finale e pulita del gestore del form di registrazione
    $('#register-form').on('submit', function(e) {
        e.preventDefault();
        const data = {
            nome: $('#register-nome').val(),
            cognome: $('#register-cognome').val(),
            email: $('#register-email').val(),
            password: $('#register-password').val(),
            ruolo: $('#register-ruolo').val(),
        };
        
        $.ajax({
            url: `${API_BASE_URL}/register`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: (response) => {
                showToast('Registrazione avvenuta con successo!');
                router.showView('login-view');
            },
            error: (err) => {
                $('#register-error').text(err.responseJSON?.message || 'Errore di registrazione.');
            }
        });
    });
    
    // NUOVA GESTIONE FORM CREA CENA
    $('#create-dinner-form').on('submit', function(e) {
        e.preventDefault();
        const data = {
            titolo: $('#dinner-titolo').val(), descrizione: $('#dinner-descrizione').val(),
            dataOra: $('#dinner-data').val(), localita: $('#dinner-localita').val(),
            numPostiDisponibili: parseInt($('#dinner-posti').val()), menu: $('#dinner-menu').val()
        };
        $.ajax({
            url: `${API_BASE_URL}/dinners`, method: 'POST', contentType: 'application/json',
            data: JSON.stringify(data),
            beforeSend: (xhr) => {
                // Allega il token JWT a questa richiesta protetta
                const token = localStorage.getItem('jwtToken');
                if (token) { xhr.setRequestHeader('Authorization', 'Bearer ' + token); }
            },
            success: () => {
                alert('Cena creata con successo!');
                router.showView('dinner-list-view');
                loadDinners(); // Ricarica la lista per mostrare la nuova cena
            },
            error: (err) => { $('#create-dinner-error').text(err.responseJSON?.message || 'Errore nella creazione della cena.'); }
        });
    });

    // --- INIZIALIZZAZIONE ---
    router.updateNav();
    router.showView('dinner-list-view');
    loadDinners(); // Carica le cene all'avvio dell'applicazione
});