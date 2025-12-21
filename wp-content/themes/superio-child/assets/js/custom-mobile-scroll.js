jQuery(document).ready(function($) {

    // Run only on mobile
    if (window.innerWidth <= 768) {

        $('.btn-submit').on('click', function(e) {

            setTimeout(function() {
                let target = $('.top-icon-wr.scrollto');

                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 10
                    }, 600);
                }
            }, 400);
        });

    }

	// Dodavanje poruke iznad Captcha provere
	// Proveravamo periodično jer se neke forme / modali mogu učitati malo kasnije
    var tries = 0;
    var checkUniversalRecaptcha = setInterval(function () {
        tries++;

        // Svi wrapperi za reCAPTCHA na stranici
        var $recaptchas = $('.ga-recaptcha');

        if ($recaptchas.length) {
            $recaptchas.each(function () {
                var $this = $(this);

                // Da ne dupliramo poruku ako već postoji
                if (!$this.prev('.recaptcha-info-universal').length) {
                    $('<p class="recaptcha-info-universal" style="margin-bottom:10px;color: #79838f;">Molimo vas da potvrdite da niste mašina.</p>')
                        .insertBefore($this);
                }
            });
        }

        // Posle ~12 sekundi prekidamo interval (40 * 300ms)
        if (tries > 40) {
            clearInterval(checkUniversalRecaptcha);
        }

    }, 300);

    function applyTranslations() {

    const translations = {
        'Create Meeting': 'Zakaži sastanak',
        'Approve Application': 'Odobri prijavu',
        'Reject Application': 'Odbij prijavu',
        'Download CV': 'Preuzmi CV',
        'Remove': 'Ukloni',
        'Date': 'Datum',
        'Time': 'Vreme',
        'Time Duration': 'Trajanje',
        'Message': 'Poruka',
        'Delete candidate': 'Obriši kandidata',
        'Send message': 'Pošalji poruku',
        'View Profile': 'Pogledaj profil',
        'Testimonial': 'Iskustvo',
        'Total(s):' : 'Ukupno:',
        'Approved' : 'Odobreno',
        'Rejected(s):' : 'Odbijeno:',

    };

    document.querySelectorAll('[data-original-title]').forEach(el => {
        const key = el.getAttribute('data-original-title');
        if (translations[key]) {
        el.setAttribute('data-original-title', translations[key]);
        }
    });

    document.querySelectorAll('label, button, h1, h2 span').forEach(el => {
        const key = el.textContent.trim();
        if (translations[key]) {
        el.textContent = translations[key];
        }
    });

    document.querySelectorAll('[placeholder]').forEach(el => {
        const key = el.getAttribute('placeholder');
        if (translations[key]) {
        el.setAttribute('placeholder', translations[key]);
        }
    });
    }

    // initial load
    document.addEventListener('DOMContentLoaded', applyTranslations);

    // after AJAX (safe polling)
    setInterval(applyTranslations, 800);


});
