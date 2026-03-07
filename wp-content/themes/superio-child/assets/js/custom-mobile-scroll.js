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
            'My Packages' : 'Moji paketi',
            'Active'    : 'Aktivan',
            'Expired'   : 'Istekao',
            'Published'  : 'Objavljen',   
            'Professional Skills' : 'Profesionalne veštine',
            'Contact' : 'Kontakt',
            'Subject' : 'Naslov',
            'Comment' : 'Komentar',
            'Social Profiles:' : 'Društveni profili:',
            'Work & Experience' : 'Radno iskustvo',
            'I have read and agree to the website' : 'Pročitao sam i slažem se sa uslovima korisćenja sajta',
             
        };

        document.querySelectorAll('[data-original-title]').forEach(el => {
            const key = el.getAttribute('data-original-title');
            if (translations[key]) {
            el.setAttribute('data-original-title', translations[key]);
            }
        });

        document.querySelectorAll('label, button, h1, h2, h2 span, h3, h4').forEach(el => {
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

        document.querySelectorAll('.social-title').forEach(el => {
            const key = el.textContent.trim();
            if (translations[key]) {
            el.textContent = translations[key];
            }
        });
        document.querySelectorAll('h2.widget-title span').forEach(el => {
        const txt = el.textContent.trim();

        // Ako počinje sa "Contact "
        if (txt.startsWith('Contact ')) {
            el.textContent = txt.replace(/^Contact\s+/, 'Kontaktirajte ');
        }
        });

        document.querySelectorAll('.must-log-in').forEach(el => {
            el.innerHTML = 'Morate biti <a href="">prijavljeni</a> da biste ostavili recenziju.';
        });


        document.querySelectorAll('.total-applicants, .approved-applicants, .rejected-applicants').forEach(el => {
                const textNode = Array.from(el.childNodes)
                    .find(n => n.nodeType === 3 && n.textContent.trim().length);

                if (!textNode) return;

                const raw = textNode.textContent.trim();

                Object.keys(translations).forEach(key => {
                    if (raw.startsWith(key)) {
                        textNode.textContent = raw.replace(key, translations[key]);
                    }
                });
        });
        // 🔹 Translate "Candidates found X"
        document.querySelectorAll('.job-found').forEach(el => {
            const text = el.textContent.trim();
            const match = text.match(/^Candidates found\s+(\d+)/);
            if (match) {
                el.textContent = `Pronađeno kandidata ${match[1]}`;
            }
        });
        // 🔹 Translate status labels like "Active"
        document.querySelectorAll('.action').forEach(el => {
            const text = el.textContent.trim();
            if (text === 'Active') {
                el.textContent = 'Aktivan';
            }

            if (text === 'Expired') {
                el.textContent = 'Istekao';
            }
        });
        // 🔹 Translate publish status labels
        document.querySelectorAll('.job-table-actions-inner').forEach(el => {
        const text = el.textContent.trim();

        if (text === 'Published') {
            el.textContent = 'Objavljeno';
        }

        if (text === 'Draft') {
            el.textContent = 'Nacrt';
        }

        if (text === 'Pending') {
            el.textContent = 'Na čekanju';
        }
        });

        // ✅ Woo checkout: Privacy policy text (pre linka) – fix
        const $p = $('.woocommerce-privacy-policy-text p');
        if ($p.length) {
            const en = 'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our';
            const sr = 'Vaši lični podaci biće korišćeni za obradu porudžbine, podršku vašem iskustvu na ovom sajtu i u druge svrhe opisane u našoj';

            // menjamo samo TEXT node (da link ostane netaknut)
            $p.contents().filter(function () {
                return this.nodeType === 3 && this.nodeValue && this.nodeValue.trim().length;
            }).each(function () {
                const current = this.nodeValue.trim();
                if (current.startsWith(en)) {
                    this.nodeValue = this.nodeValue.replace(en, sr);
                }
            });
        }
        $('.product_meta .sub_title').each(function () {
            const map = {
                'Category:': 'Kategorija:',
                'Tags:': 'Oznake:',
                'Tag:': 'Oznaka:'
            };

            const text = $(this).text().trim();
            if (map[text]) {
                $(this).text(map[text]);
            }
        });        
        $('#reply-title').each(function () {
            let text = $(this).text().trim();

            if (text.startsWith('Be the first to review')) {
                text = text
                    .replace('Be the first to review', 'Budite prvi koji će oceniti')
                    .replace(/["“”]/g, '„')
                    .replace(/„(.+)„/, '„$1“');

                $(this).text(text);
            }
        });
        $('.comments-title').each(function () {
            const text = $(this).text().trim();

            // hvata: "0 Reviews", "1 Reviews", "12 Reviews", itd.
            const match = text.match(/^(\d+)\s+Reviews$/);

            if (match) {
                const count = match[1];
                $(this).text(`${count} recenzija`);
            }
        });

        // Translate only the text node before the <a> inside Woo terms checkbox and change the structure to move the link above the checkbox for better mobile display
        document.querySelectorAll('.woocommerce-form__label-for-checkbox').forEach(label => {

            const container = label.querySelector('.woocommerce-terms-and-conditions-checkbox-text');
            const required = label.querySelector('.required');
            if (!container) return;

            const link = container.querySelector('a');
            if (!link) return;

            // 1️⃣ Promeni checkbox tekst
            container.childNodes.forEach(node => {
                if (node.nodeType === Node.TEXT_NODE) {
                    node.textContent = 'Pročitao sam i slažem se sa uslovima sajta ';
                }
            });

            // 2️⃣ Stilizuj link
            link.style.color = '#1967D2';
            link.style.fontWeight = '700';
            link.style.textDecoration = 'none';

            // 3️⃣ Kreiraj wrapper za link + zvezdicu
            if (!label.previousElementSibling || !label.previousElementSibling.classList.contains('custom-terms-link')) {

                const linkWrapper = document.createElement('div');
                linkWrapper.className = 'custom-terms-link';
                linkWrapper.style.marginBottom = '6px';

                linkWrapper.appendChild(link);

                if (required) {
                    required.style.color = 'red';
                    required.style.marginLeft = '4px';
                    linkWrapper.appendChild(required);
                }

                label.parentNode.insertBefore(linkWrapper, label);
            }
        });


    }
    // initial load
    document.addEventListener('DOMContentLoaded', applyTranslations);
    // after AJAX (safe polling)
    setInterval(applyTranslations, 800);
    
    document.querySelectorAll('.package-more-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const target = document.getElementById(this.dataset.target);
            if (!target) return;

            const isOpen = target.style.display === 'block';
            target.style.display = isOpen ? 'none' : 'block';

            this.innerText = isOpen ? 'Saznaj više' : 'Sakrij detalje';
        });
    });

    // Auto-preselect first ACTIVE package on submit-job page
    function preselectActivePackage() {
        const $radios = $('.user-job-packaged input[type="radio"][name="wjbpwpl_listing_user_package"]');
        if (!$radios.length) return;

        // Ako korisnik već ima selektovan AKTIVAN paket, ne diraj
        const $checked = $radios.filter(':checked');
        if ($checked.length && !$checked.prop('disabled')) return;

        // 1) Ukloni checked sa svih disabled (property + attribute)
        $radios.each(function () {
            if (this.disabled) {
                this.checked = false;
                this.removeAttribute('checked');     // ✅ uklanja checked="checked" iz DOM-a
            }
        });

        // 2) Selektuj prvi aktivan (enabled)
        const $firstActive = $radios.filter(function () {
            return !this.disabled;
        }).first();

        if ($firstActive.length) {
            $firstActive.prop('checked', true).attr('checked', 'checked').trigger('change');
        }
    // Sakrij istekle pakete (ceo <li>)
        $('.user-job-packaged li.package-expired').hide();
    }
    preselectActivePackage();
    setTimeout(preselectActivePackage, 800);

    

    // Woo AJAX add to cart + update checkout (packages cards) na strani Paketi
    $(document).on('click', 'button[name="wjbpwpl_job_package"]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $btn = $(this);
        const productId = $btn.val() || $btn.attr('value');

        if (!productId) return;

        // UI feedback
        $btn.prop('disabled', true).addClass('loading');

        // Woo endpoints
        const addToCartUrl = window.location.origin + '/?wc-ajax=add_to_cart';
        const updateReviewUrl = window.location.origin + '/?wc-ajax=update_order_review';

        // 1) ADD TO CART
        $.ajax({
            url: addToCartUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                product_id: productId,
                quantity: 1
            }
        })
        .done(function (res) {

            // Woo često vrati fragments; ako postoji, možemo da ih “apdejtujemo”
            if (res && res.fragments) {
                $.each(res.fragments, function (key, value) {
                    $(key).replaceWith(value);
                });
            }

            // 2) UPDATE ORDER REVIEW (checkout)
            // Woo očekuje serialized checkout form, ali ako nisi na checkout-u,
            // dovoljno je pingovati endpoint da refreshuje session/fragments.
            return $.ajax({
                url: updateReviewUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    security: (typeof wc_checkout_params !== 'undefined') ? wc_checkout_params.update_order_review_nonce : ''
                }
            });
        })
        .done(function () {
            // ✅ Redirect na checkout (najpraktičnije posle add_to_cart)
            window.location.href = window.location.origin + '/checkout/';
        })
        .fail(function (xhr) {
            console.error('Woo AJAX error:', xhr);
            // fallback: normal add-to-cart redirect
            window.location.href = window.location.origin + '/?add-to-cart=' + encodeURIComponent(productId);
        })
        .always(function () {
            $btn.prop('disabled', false).removeClass('loading');
        });
    });

    // Sakrij parent termine u select2 dropdownu (ako nisu rezultati pretrage i nisu prazni)
    var targetSelectIds = ['_candidate_category', '_employer_category', '_job_category'];
    var activeCategorySelectId = null;

    function isTargetSelect(selectId) {
        return targetSelectIds.indexOf(selectId) !== -1;
    }

    function hideParentTerms() {

        if (!activeCategorySelectId || !isTargetSelect(activeCategorySelectId)) {
            return;
        }

        $('.select2-container--open .select2-results__option').each(function() {

            var text = $(this).text().trim();

            if (
                text !== '' &&
                text !== 'No results found' &&
                !text.startsWith('-')
            ) {
                $(this).hide();
            } else {
                $(this).show();
            }

        });
    }

    $('#_candidate_category, #_employer_category, #_job_category').on('select2:open', function() {

        activeCategorySelectId = this.id;

        setTimeout(hideParentTerms, 50);

    });

    $('#_candidate_category, #_employer_category, #_job_category').on('select2:close', function() {

        activeCategorySelectId = null;

    });

    $(document).on('keyup', '.select2-search__field', function() {

        setTimeout(hideParentTerms, 50);

    });

    // LIMIT SELECTION

    $('#_candidate_category').on('change', function() {

        var selected = $(this).val();

        if (selected && selected.length > 5) {

            selected.pop();
            $(this).val(selected).trigger('change.select2');

            alert('Možete izabrati maksimalno 5 pozicija.');

        }

    });

    $('#_employer_category').on('change', function() {

        var selected = $(this).val();

        if (selected && selected.length > 10) {

            selected.pop();
            $(this).val(selected).trigger('change.select2');

            alert('Možete izabrati maksimalno 10 pozicija.');

        }

    });


});


