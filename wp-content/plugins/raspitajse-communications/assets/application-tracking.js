(function () {
    'use strict';

    function hideLegacyControls() {
        document.querySelectorAll(
            '.btn-reject-job-applied, .btn-undo-reject-job-applied, .btn-approve-job-applied, .btn-undo-approve-job-applied'
        ).forEach(function (element) {
            element.hidden = true;
        });
    }

    document.addEventListener('submit', function (event) {
        var form = event.target.closest('.raspitajse-application-status-form');
        if (!form || !window.raspitajseApplicationTracking) {
            return;
        }
        event.preventDefault();
        var data = new FormData(form);
        var message = form.querySelector('.raspitajse-application-status-form__message');
        data.append('action', window.raspitajseApplicationTracking.action);
        fetch(window.raspitajseApplicationTracking.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: data
        }).then(function (response) {
            return response.json();
        }).then(function (result) {
            if (!result.success) {
                throw new Error(result.data && result.data.message ? result.data.message : window.raspitajseApplicationTracking.failure);
            }
            window.location.reload();
        }).catch(function (error) {
            message.textContent = error.message || window.raspitajseApplicationTracking.failure;
        });
    });

    if ('loading' === document.readyState) {
        document.addEventListener('DOMContentLoaded', hideLegacyControls);
    } else {
        hideLegacyControls();
    }
}());
