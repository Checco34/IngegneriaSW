$(document).ready(function() {
    const API_BASE_URL = '/api';
    const router = {
        showView: (viewId) => {
            $('main > section').hide();
            $(`#${viewId}`).show();
        },
        updateNav: () => {
            const token = localStorage.getItem('jwtToken');
            if (token) {
                $('#login-link, #register-link').hide();
                $('#logout-link').show();
            } else {
                $('#login-link, #register-link').show();
                $('#logout-link').hide();
            }
        }
    };
    function handleLogout() {
        localStorage.removeItem('jwtToken');
        router.updateNav();
        router.showView('dinner-list-view');
    }
    $('#home-link').on('click', () => router.showView('dinner-list-view'));
    $('#login-link').on('click', () => router.showView('login-view'));
    $('#register-link').on('click', () => router.showView('register-view'));
    $('#logout-link').on('click', handleLogout);
    $('#login-form').on('submit', function(e) {
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
            },
            error: (err) => {
                $('#login-error').text(err.responseJSON?.message || 'Errore di login.');
            }
        });
    });
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
                alert('Registrazione avvenuta con successo! Effettua il login.');
                router.showView('login-view');
            },
            error: (err) => {
                $('#register-error').text(err.responseJSON?.message || 'Errore di registrazione.');
            }
        });
    });
    router.updateNav();
    router.showView('dinner-list-view');
});