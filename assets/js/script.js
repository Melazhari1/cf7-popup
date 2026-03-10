document.addEventListener('DOMContentLoaded', function () {
    var overlay = document.getElementById('cf7-popup-overlay');
    var contentDiv = document.getElementById('cf7-popup-content');
    var messageDiv = document.getElementById('cf7-popup-message');
    var titleDiv = document.getElementById('cf7-popup-title');
    var closeBtn = document.getElementById('cf7-popup-close');
    var actionBtn = document.getElementById('cf7-popup-btn');

    var successIcon = document.querySelector('.success-icon');
    var errorIcon = document.querySelector('.error-icon');

    if (!overlay || !messageDiv || !closeBtn) return;

    function closePopup() {
        overlay.classList.remove('show');
        // Wait for transition to finish before hiding display
        setTimeout(function () {
            overlay.style.display = 'none';
            contentDiv.classList.remove('success', 'error');
        }, 300);
    }

    // Close popup on click
    closeBtn.addEventListener('click', closePopup);
    if (actionBtn) {
        actionBtn.addEventListener('click', closePopup);
    }

    // Close popup on overlay click
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) {
            closePopup();
        }
    });

    // Helper to show popup
    // Note: message is already sanitized on the server side with wp_kses_post()
    function showPopup(message, title = '', btnText = 'OK', formId = null, type = 'success') {\n        if (message) {
            // Remove previous classes
            contentDiv.classList.remove('success', 'error');
            // Add new class
            contentDiv.classList.add(type);

            // Toggle Icons
            if (type === 'success') {
                if (successIcon) successIcon.style.display = 'block';
                if (errorIcon) errorIcon.style.display = 'none';
            } else {
                if (successIcon) successIcon.style.display = 'none';
                if (errorIcon) errorIcon.style.display = 'block';
            }

            // Set message (sanitized by server)
            messageDiv.innerHTML = message;

            // Set title (plain text)
            if (titleDiv) {
                titleDiv.textContent = title;
                titleDiv.style.display = title ? 'block' : 'none';
            }

            // Set button text (plain text)
            if (actionBtn) {
                actionBtn.textContent = btnText || 'OK';
            }

            // formId is passed for context but hiding the response output is handled in handleCF7Event
            // nothing else to do here

            // Show popup with animation
            overlay.style.display = 'flex';
            // Trigger reflow to enable transition
            overlay.offsetHeight;
            overlay.classList.add('show');
        }
    }

    function handleCF7Event(event, type) {
        var formId = event.detail.contactFormId;
        var settings = cf7PopupSettings[formId];

        if (settings && settings.enabled == '1') {
            var message = '';
            if (type === 'success') message = settings.success;
            else message = settings.error;

            if (message) {
                // Hide the default response output for this specific form instance
                var formElement = document.querySelector('#' + event.detail.unitTag);
                if (formElement) {
                    var responseOutput = formElement.querySelector('.wpcf7-response-output');
                    if (responseOutput) {
                        responseOutput.style.display = 'none';
                        // Also add a listener to reset it if needed, or just leave it hidden until next submit? 
                        // CF7 resets display:block on submission usually via JS, so we might need to strictly hide it via CSS class or persistent inline style.
                        // Better approach: Add a class to the form and style it in CSS, but inline style is stronger against CF7's JS.
                        responseOutput.setAttribute('style', 'display: none !important;');
                    }
                }

                showPopup(message, settings.title, settings.btn_text, formId, type);
            }
        }
    }

    // Listen for CF7 submission
    document.addEventListener('wpcf7mailsent', function (event) {
        handleCF7Event(event, 'success');
    });

    // Listen for CF7 error
    document.addEventListener('wpcf7invalid', function (event) {
        handleCF7Event(event, 'error');
    });

    document.addEventListener('wpcf7spam', function (event) {
        handleCF7Event(event, 'error');
    });

    document.addEventListener('wpcf7mailfailed', function (event) {
        handleCF7Event(event, 'error');
    });
});
