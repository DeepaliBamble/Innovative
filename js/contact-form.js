/**
 * Contact Form Handler
 * Handles AJAX submission of contact form
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        const contactForm = document.querySelector('.form-contact');

        if (!contactForm) {
            return;
        }

        const formMessage = contactForm.querySelector('.form_message');
        const submitBtn = contactForm.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn ? submitBtn.innerHTML : '';

        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Get form data
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const message = document.getElementById('desc').value.trim();

            // Client-side validation
            if (!name || !email || !message) {
                showMessage('Please fill in all required fields.', 'error');
                return;
            }

            if (!isValidEmail(email)) {
                showMessage('Please enter a valid email address.', 'error');
                return;
            }

            // Disable submit button and show loading
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending...';
            }

            // Prepare form data
            const formData = new FormData();
            formData.append('name', name);
            formData.append('email', email);
            formData.append('message', message);

            // Send AJAX request
            fetch('contactform.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    contactForm.reset();
                } else {
                    showMessage(data.message || 'An error occurred. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Failed to send message. Please try again later.', 'error');
            })
            .finally(() => {
                // Re-enable submit button
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });
        });

        /**
         * Display message to user
         */
        function showMessage(message, type) {
            if (!formMessage) {
                alert(message);
                return;
            }

            formMessage.innerHTML = message;
            formMessage.className = 'form_message text-center';

            if (type === 'success') {
                formMessage.style.color = '#28a745';
                formMessage.style.backgroundColor = '#d4edda';
                formMessage.style.border = '1px solid #c3e6cb';
            } else {
                formMessage.style.color = '#dc3545';
                formMessage.style.backgroundColor = '#f8d7da';
                formMessage.style.border = '1px solid #f5c6cb';
            }

            formMessage.style.padding = '12px';
            formMessage.style.borderRadius = '4px';
            formMessage.style.marginBottom = '15px';
            formMessage.style.display = 'block';

            // Scroll to message
            formMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });

            // Auto-hide after 5 seconds
            setTimeout(() => {
                formMessage.style.display = 'none';
            }, 5000);
        }

        /**
         * Validate email format
         */
        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    });
})();
