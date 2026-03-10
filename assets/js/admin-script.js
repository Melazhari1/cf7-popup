/**
 * CF7 Popup — Admin Settings Page Script
 */
(function ($) {
    'use strict';

    $(document).ready(function () {

        // Toggle settings panel + badge on checkbox change
        $('.cf7p-toggle input[type="checkbox"]').on('change', function () {
            var $card = $(this).closest('.cf7p-card');
            var $body = $card.find('.cf7p-card-body');
            var $badge = $card.find('.cf7p-badge');

            if ($(this).is(':checked')) {
                $body.removeClass('collapsed');
                $badge.removeClass('cf7p-badge--inactive').addClass('cf7p-badge--active').text('Actif');
            } else {
                $body.addClass('collapsed');
                $badge.removeClass('cf7p-badge--active').addClass('cf7p-badge--inactive').text('Inactif');
            }
        });

    });

})(jQuery);
