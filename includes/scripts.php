<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/swiper-bundle.min.js"></script>
<script src="js/carousel.js"></script>
<script src="js/bootstrap-select.min.js"></script>
<script src="js/lazysize.min.js"></script>
<script src="js/wow.min.js"></script>
<script src="js/parallaxie.js"></script>
<script src="js/main.js"></script>

<script>
// Global functions for updating header counts
window.updateWishlistCount = function(count) {
    document.querySelectorAll('.wishlist-count').forEach(function(el) {
        el.textContent = count;
    });
};

window.updateCartCount = function(count) {
    document.querySelectorAll('.count-box:not(.wishlist-count)').forEach(function(el) {
        el.textContent = count;
    });
};

// Global notification function
window.showNotification = function(title, message, type) {
    // Remove existing notifications
    document.querySelectorAll('.notification-toast').forEach(function(el) {
        el.remove();
    });

    var bgColor = type === 'success' ? '#28a745' : (type === 'error' ? '#dc3545' : '#ffc107');
    var notification = document.createElement('div');
    notification.className = 'notification-toast';
    notification.style.cssText = 'position:fixed;top:20px;right:20px;background:' + bgColor + ';color:#fff;padding:15px 25px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:99999;animation:slideIn 0.3s ease;';
    notification.innerHTML = '<strong>' + title + '</strong><br>' + message;
    document.body.appendChild(notification);

    setTimeout(function() {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(function() {
            notification.remove();
        }, 300);
    }, 3000);
};
</script>