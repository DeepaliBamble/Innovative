/**
 * Shop This Look / Lookbook Functionality
 * Handles interactive hover pins, add to cart, and wishlist for lookbook products
 */

(function ($) {
    'use strict';

    /**
     * Handle Add to Cart for Lookbook Products
     */
    function handleLookbookAddToCart() {
        $(document).on('click', '.btn-add-to-cart-lookbook', function (e) {
            e.preventDefault();
            const $btn = $(this);
            const productId = $btn.data('product-id');
            const productName = $btn.data('product-name');

            // Disable button during request
            $btn.prop('disabled', true).addClass('loading');
            const originalHtml = $btn.find('.tooltip').text();
            $btn.find('.tooltip').text('Adding...');

            $.ajax({
                url: 'ajax/add-to-cart.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    product_id: productId,
                    quantity: 1
                },
                success: function (response) {
                    if (response.success) {
                        // Show success message
                        showNotification('success', response.message || 'Product added to cart!');

                        // Update cart count in header
                        if (response.cart_count !== undefined) {
                            updateCartCount(response.cart_count);
                        }

                        // Optional: Update mini cart if exists
                        if (typeof updateMiniCart === 'function') {
                            updateMiniCart();
                        }

                        // Optional: Trigger cart update event
                        $(document).trigger('cart:updated', [response]);

                        // Change button text temporarily
                        $btn.find('.tooltip').text('Added!');
                        setTimeout(function() {
                            $btn.find('.tooltip').text(originalHtml);
                        }, 2000);
                    } else {
                        showNotification('error', response.message || 'Failed to add product to cart.');
                        $btn.find('.tooltip').text(originalHtml);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Add to cart error:', error);
                    showNotification('error', 'An error occurred. Please try again.');
                    $btn.find('.tooltip').text(originalHtml);
                },
                complete: function () {
                    $btn.prop('disabled', false).removeClass('loading');
                }
            });
        });
    }

    /**
     * Handle Add to Wishlist for Lookbook Products
     */
    function handleLookbookWishlist() {
        $(document).on('click', '.btn-add-to-wishlist-lookbook', function (e) {
            e.preventDefault();
            const $btn = $(this);
            const productId = $btn.data('product-id');
            const $icon = $btn.find('.icon');
            const $tooltip = $btn.find('.tooltip');

            // Check if already in wishlist
            const isInWishlist = $btn.hasClass('in-wishlist');

            // Disable button during request
            $btn.prop('disabled', true).addClass('loading');
            const originalTooltip = $tooltip.text();
            $tooltip.text(isInWishlist ? 'Removing...' : 'Adding...');

            $.ajax({
                url: 'ajax/wishlist.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'toggle',
                    product_id: productId
                },
                success: function (response) {
                    if (response.success) {
                        if (response.action === 'added') {
                            // Added to wishlist
                            $btn.addClass('in-wishlist');
                            $icon.removeClass('icon-heart').addClass('icon-heart-filled');
                            $tooltip.text('Remove from Wishlist');
                            showNotification('success', response.message || 'Added to wishlist!');
                        } else {
                            // Removed from wishlist
                            $btn.removeClass('in-wishlist');
                            $icon.removeClass('icon-heart-filled').addClass('icon-heart');
                            $tooltip.text('Add to Wishlist');
                            showNotification('success', response.message || 'Removed from wishlist!');
                        }

                        // Update wishlist count in header
                        if (response.wishlist_count !== undefined) {
                            updateWishlistCount(response.wishlist_count);
                        }

                        // Trigger wishlist update event
                        $(document).trigger('wishlist:updated', [response]);
                    } else {
                        showNotification('error', response.error || 'Failed to update wishlist.');
                        $tooltip.text(originalTooltip);

                        // Check if login is required
                        if (response.redirect) {
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1500);
                        }
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Wishlist error:', error);
                    showNotification('error', 'An error occurred. Please try again.');
                    $tooltip.text(originalTooltip);
                },
                complete: function () {
                    $btn.prop('disabled', false).removeClass('loading');
                }
            });
        });
    }

    /**
     * Enhanced Hover Pin Interaction
     * Syncs the hover state between pins on image and product cards
     */
    function enhancedHoverPin() {
        $('.tf-lookbook-hover').each(function () {
            const $container = $(this);
            let hoverTimeout;

            // When hovering over pin buttons
            $container.find('.bundle-pin-item').on('mouseenter', function () {
                clearTimeout(hoverTimeout);
                const pinId = this.id; // e.g., "pin1"
                const $hoverWrap = $container.find('.bundle-hover-wrap');
                const $targetCard = $container.find('.' + pinId);

                if ($targetCard.length > 0) {
                    $hoverWrap.addClass('has-hover');
                    $targetCard.addClass('active-hover').show();
                    $hoverWrap.find('.bundle-hover-item').not($targetCard).addClass('no-hover');
                }
            });

            $container.find('.bundle-pin-item').on('mouseleave', function () {
                const $hoverWrap = $container.find('.bundle-hover-wrap');

                hoverTimeout = setTimeout(function() {
                    $hoverWrap.removeClass('has-hover');
                    $hoverWrap.find('.bundle-hover-item').removeClass('no-hover active-hover');
                }, 200);
            });

            // When hovering over product cards
            $container.find('.bundle-hover-item').on('mouseenter', function () {
                clearTimeout(hoverTimeout);
                const $card = $(this);
                const pinClass = $card.attr('class').match(/pin\d+/);

                if (pinClass) {
                    const pinId = pinClass[0];
                    const $hoverWrap = $container.find('.bundle-hover-wrap');
                    const $pin = $container.find('#' + pinId);

                    $hoverWrap.addClass('has-hover');
                    $card.addClass('active-hover');
                    $hoverWrap.find('.bundle-hover-item').not($card).addClass('no-hover');
                    $pin.addClass('active');
                }
            });

            $container.find('.bundle-hover-item').on('mouseleave', function () {
                const $card = $(this);
                const pinClass = $card.attr('class').match(/pin\d+/);
                const $hoverWrap = $container.find('.bundle-hover-wrap');

                hoverTimeout = setTimeout(function() {
                    $hoverWrap.removeClass('has-hover');
                    $hoverWrap.find('.bundle-hover-item').removeClass('no-hover active-hover');

                    if (pinClass) {
                        const pinId = pinClass[0];
                        $container.find('#' + pinId).removeClass('active');
                    }
                }, 200);
            });
        });
    }

    /**
     * Update cart count in header
     */
    function updateCartCount(count) {
        $('.count.count-box').not('.wishlist-count').text(count);

        // Also use the global update function if available
        if (typeof window.updateMenuCartCount === 'function') {
            window.updateMenuCartCount();
        }
    }

    /**
     * Update wishlist count in header
     */
    function updateWishlistCount(count) {
        $('.wishlist-count').text(count);

        // Also use the global update function if available
        if (typeof window.updateMenuWishlistCount === 'function') {
            window.updateMenuWishlistCount();
        }
    }

    /**
     * Show notification message
     */
    function showNotification(type, message) {
        // Try to use existing notification system if available
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
            return;
        }

        // Fallback to custom notification
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const $alert = $('<div class="alert ' + alertClass + ' alert-dismissible fade show lookbook-notification" role="alert">' +
            message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
            '</div>');

        // Add to page
        if ($('.lookbook-notification-container').length === 0) {
            $('body').append('<div class="lookbook-notification-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 350px;"></div>');
        }

        $('.lookbook-notification-container').append($alert);

        // Auto remove after 3 seconds
        setTimeout(function () {
            $alert.fadeOut(function () {
                $(this).remove();
            });
        }, 3000);
    }

    /**
     * Initialize all lookbook functionality
     */
    function initLookbook() {
        handleLookbookAddToCart();
        handleLookbookWishlist();
        enhancedHoverPin();

        console.log('Lookbook functionality initialized');
    }

    // Initialize on document ready
    $(function () {
        initLookbook();
    });

})(jQuery);
