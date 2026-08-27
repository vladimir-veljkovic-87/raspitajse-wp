(function ($, window, document) {
    'use strict';

    var namespace = window.RaspitajseCommerce || {};
    var settings = namespace.packagePurchase || {};
    var buttonSelector = '.wjbpwpl-packages-form button[name="wjbpwpl_job_package"]';
    var errorSelector = '.raspitajse-package-purchase-error';

    function sameOriginUrl(value) {
        var parsed;

        if (typeof value !== 'string' || value === '') {
            return '';
        }

        try {
            parsed = new window.URL(value, window.location.href);
        } catch (error) {
            return '';
        }

        return parsed.origin === window.location.origin ? parsed.href : '';
    }

    function clearError($form) {
        $form.children(errorSelector).remove();
    }

    function showError($form) {
        var message = typeof settings.failureMessage === 'string'
            ? settings.failureMessage
            : '';

        if (!message) {
            return;
        }

        clearError($form);
        $('<div>', {
            'class': errorSelector.substring(1) + ' woocommerce-error',
            'role': 'alert',
            'text': message
        }).prependTo($form);
    }

    function restoreButton($form, $button) {
        var originalText = $button.data('raspitajse-package-purchase-text');

        $form.removeData('raspitajse-package-purchase-pending');
        $button.prop('disabled', false).removeClass('loading');

        if (typeof originalText === 'string') {
            $button.text(originalText);
        }
    }

    function failClosed($form, $button) {
        restoreButton($form, $button);
        showError($form);
    }

    $(document).on(
        'click.raspitajsePackagePurchase',
        buttonSelector,
        function (event) {
            var $button = $(this);
            var $form = $button.closest('.wjbpwpl-packages-form');
            var productId = parseInt($button.val() || $button.attr('value'), 10);
            var addToCartUrl = sameOriginUrl(settings.addToCartUrl);
            var checkoutUrl = sameOriginUrl(settings.checkoutUrl);

            event.preventDefault();
            event.stopPropagation();

            if (
                !$form.length
                || $form.data('raspitajse-package-purchase-pending')
                || !productId
                || productId < 1
                || !addToCartUrl
                || !checkoutUrl
            ) {
                if ($form.length) {
                    failClosed($form, $button);
                }
                return;
            }

            clearError($form);
            $form.data('raspitajse-package-purchase-pending', true);
            $button.data('raspitajse-package-purchase-text', $button.text());
            $button.prop('disabled', true).addClass('loading');

            if (typeof settings.pendingMessage === 'string' && settings.pendingMessage) {
                $button.text(settings.pendingMessage);
            }

            $.ajax({
                url: addToCartUrl,
                method: 'POST',
                dataType: 'json',
                data: {
                    product_id: productId,
                    quantity: 1
                }
            }).done(function (response) {
                var productUrl;

                if (!response || response.error) {
                    productUrl = response && response.product_url
                        ? sameOriginUrl(response.product_url)
                        : '';

                    if (productUrl) {
                        window.location.assign(productUrl);
                        return;
                    }

                    failClosed($form, $button);
                    return;
                }

                if (response.fragments && typeof response.fragments === 'object') {
                    $.each(response.fragments, function (selector, html) {
                        $(selector).replaceWith(html);
                    });
                }

                $(document.body).trigger('added_to_cart', [
                    response.fragments || {},
                    response.cart_hash || '',
                    $button
                ]);
                window.location.assign(checkoutUrl);
            }).fail(function () {
                failClosed($form, $button);
            });
        }
    );
}(jQuery, window, document));
