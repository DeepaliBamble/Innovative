<!-- Footer -->
<footer class="tf-footer"  style="background-color: #faf1e5;">
    <div class="footer-body">
        <div class="container">
            <div class="row">
                <div class="col-xl-4 col-sm-6">
                    <div class="footer-col-block text-center text-sm-start">
                       <img src="images/logo/logo.png" style="width: 100px; height: 100px;" alt="Innovative Homes Logo">
                        <div class="tf-collapse-content">
                            <div class="footer-newsletter">
                                <img src="" alt="">
                                <p class="h6 caption">
                                    Welcome to Innovative Homesi- where comfort meets craftsmanship, and every piece tells your story.
                                </p>
                                   <div class="social-wrap">
                                <ul class="tf-social-icon">
                                    <li>
                                        <a href="https://www.facebook.com/innovativehomesi/" target="_blank" class="social-facebook">
                                            <span class="icon"><i class="fa-brands fa-facebook-f"></i></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.instagram.com/innovative_homesi/" target="_blank" class="social-instagram">
                                            <span class="icon"><i class="fa-brands fa-instagram"></i></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.youtube.com/@innovativehomesi" target="_blank" class="social-youtube">
                                            <span class="icon"><i class="fa-brands fa-youtube"></i></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://in.pinterest.com/innovativehomes3/" target="_blank" class="social-pinterest">
                                            <span class="icon"><i class="fa-brands fa-pinterest-p"></i></span>
                                        </a>
                                    </li>

                                </ul>
                            </div>
                              
                            </div>
                        </div>
                    </div>
                </div>
              
                <div class="col-xl-2 col-sm-6 mb_30 mb-xl-0">
                    <div class="footer-col-block footer-wrap-1 ms-xl-auto text-center text-xl-start">
                        <p class="footer-heading footer-heading-mobile">Information</p>
                        <div class="tf-collapse-content">
                            <ul class="footer-menu-list" style="gap: 2px;">
                                <li><a href="about-us.php" class="link h6">About us</a></li>
                                <li><a href="blogs.php" class="link h6">Blogs</a></li>
                                <li><a href="customise-service.php" class="link h6">Customise Service</a></li>
                                <li><a href="gallery.php" class="link h6">Gallery</a></li>
                                <li><a href="wishlist.php" class="link h6">My wishlist</a></li>

                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb_30 mb-sm-0">
                    <div class="footer-col-block footer-wrap-2 mx-xl-auto text-center text-xl-start">
                        <p class="footer-heading footer-heading-mobile">Guide</p>
                        <div class="tf-collapse-content">
                            <ul class="footer-menu-list" style="gap: 2px;">
                                <li><a href="privacypolicy.php" class="link h6">Privacy policy</a></li>
                                <li><a href="term&conditions.php" class="link h6">Terms & conditions</a></li>
                                <li><a href="shippingpolicy.php" class="link h6">Shipping policy</a></li>
                                <li><a href="refund&returnspolicy.php" class="link h6">Refund and returns policy</a></li>
                                <li><a href="contact-us.php" class="link h6">Contact us</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                 <div class="col-xl-3 col-sm-6 mb_30 mb-xl-0">
                    <div class="footer-col-block text-center text-xl-start">
                        <p class="footer-heading footer-heading-mobile">Contact us</p>
                        <div class="tf-collapse-content">
                            <ul class="footer-contact" style="gap: 2px;">
                                <li>
                                    <i class="icon icon-map-pin"></i>
                                    <span class="br-line"></span>
                                    <a href="#" target="_blank"
                                        class="h6 link">
                                        Innovative Homesi, <br> Mumbai Pune Road, <br class="d-none d-lg-block">Shilphata, Thane, 400612 
                                    </a>
                                </li>
                                <li>
                                    <i class="icon icon-phone"></i>
                                    <span class="br-line"></span>
                                    <a href="tel:+919892827404" class="h6 link">+91 9892827404</a>
                                </li>
                                <li>
                                    <i class="icon icon-envelope-simple"></i>
                                    <span class="br-line"></span>
                                    <a href="mailto:contactus@innovativehomesi.com" class="h6 link">contactus@innovativehomesi.com</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
      <div class="footer-bottom">
        <div class="container">
            <div class="inner-bottom">    
                 <div class="w-100 text-center text-md-start">
                    <p class="h6">Copyright © 2026 <a href="https://innovativehomesi.com/">Innovative Homes.</a> All rights reserved.</p>
                </div>
                <div class="w-100 text-center text-md-end" >
                    <p class="h6">Developed with ❤️ By <a href="https://www.webcraftersitsolutions.com/">Web Crafters IT Solutions.</a></p>
                </div>
            </div>
        </div>
    </div> 
</footer>
<!-- /Footer -->

<!-- Mobile Menu -->
<div class="offcanvas offcanvas-start canvas-mb" id="mobileMenu">
    <span class="icon-close-popup" data-bs-dismiss="offcanvas" aria-label="Close menu">
        <i class="icon-close"></i>
    </span>
    <div class="canvas-header">
        <img src="images/logo/logo.png" alt="Innovative Homesi" class="mobile-logo">
        <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
        <a href="account-page.php" class="tf-btn type-small style-2">
            My Account
            <i class="icon icon-user"></i>
        </a>
        <?php else: ?>
        <a href="login.php" class="tf-btn type-small style-2">
            Login
            <i class="icon icon-user"></i>
        </a>
        <?php endif; ?>
        <span class="br-line"></span>
    </div>
    <div class="canvas-body">
        <div class="mb-content-top">
            <ul class="nav-ul-mb" id="wrapper-menu-navigation"></ul>
        </div>
        <!-- Quick Action Buttons -->
        <div class="mobile-quick-actions">
            <a href="customise-service.php" class="mobile-quick-link">
                <i class="fa-solid fa-palette"></i>
                <span>Customise</span>
            </a>
            <a href="reviews.php" class="mobile-quick-link">
                <i class="fa-solid fa-star"></i>
                <span>Reviews</span>
            </a>
            <a href="refer-a-friend.php" class="mobile-quick-link">
                <i class="fa-solid fa-gift"></i>
                <span>Refer</span>
            </a>
        </div>
        <div class="group-btn">
            <a href="wishlist.php" class="tf-btn type-small style-2">
                Wishlist
                <i class="icon icon-heart"></i>
            </a>
            <div data-bs-dismiss="offcanvas">
                <a href="#search" data-bs-toggle="modal" class="tf-btn type-small style-2">
                    Search
                    <i class="icon icon-magnifying-glass"></i>
                </a>
            </div>
        </div>
        <div class="flow-us-wrap">
            <h5 class="title">Follow us on</h5>
            <ul class="tf-social-icon">
                <li>
                    <a href="https://www.facebook.com/innovativehomesi/" target="_blank" class="social-facebook" aria-label="Facebook">
                        <span class="icon"><i class="fa-brands fa-facebook-f"></i></span>
                    </a>
                </li>
                <li>
                    <a href="https://www.instagram.com/innovative_homesi/" target="_blank" class="social-instagram" aria-label="Instagram">
                        <span class="icon"><i class="fa-brands fa-instagram"></i></span>
                    </a>
                </li>
                <li>
                    <a href="https://www.youtube.com/@innovativehomesi" target="_blank" class="social-youtube" aria-label="YouTube">
                        <span class="icon"><i class="fa-brands fa-youtube"></i></span>
                    </a>
                </li>
                <li>
                    <a href="https://in.pinterest.com/innovativehomes3/" target="_blank" class="social-pinterest" aria-label="Pinterest">
                        <span class="icon"><i class="fa-brands fa-pinterest-p"></i></span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php include __DIR__ . '/search-modal.php'; ?>

<!-- Customise Service Popup -->
<div id="customisePopupOverlay" class="customise-popup-overlay" aria-hidden="true">
    <div class="customise-popup" role="dialog" aria-modal="true" aria-labelledby="customisePopupTitle">
        <button type="button" class="customise-popup-close" aria-label="Close popup">
            <i class="icon icon-close"></i>
        </button>
        <div class="customise-popup-image">
            <img src="images/logo/logo.png" alt="Innovative Homesi" loading="lazy">
        </div>
        <div class="customise-popup-content">
            <span class="customise-popup-badge">Bespoke Service</span>
            <h3 id="customisePopupTitle" class="customise-popup-title">Design Furniture That's Truly Yours</h3>
            <p class="customise-popup-text">
                Choose your fabric, colour, size and style. Our craftsmen will build a one-of-a-kind piece, made just for your home.
            </p>
            <ul class="customise-popup-features">
                <li><i class="fa-solid fa-check"></i> Custom fabrics &amp; finishes</li>
                <li><i class="fa-solid fa-check"></i> Made-to-measure dimensions</li>
                <li><i class="fa-solid fa-check"></i> Personal design consultation</li>
            </ul>
            <a href="customise-service.php" class="tf-btn customise-popup-cta">
                Explore Customise Service
                <i class="icon icon-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<style>
.customise-popup-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.55);
    z-index: 99998;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.35s ease, visibility 0.35s ease;
}
.customise-popup-overlay.is-visible {
    opacity: 1;
    visibility: visible;
}
.customise-popup {
    position: relative;
    background: #faf1e5;
    border-radius: 14px;
    max-width: 880px;
    width: 100%;
    max-height: 92vh;
    overflow: hidden;
    display: flex;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    transform: translateY(20px) scale(0.96);
    transition: transform 0.35s ease;
}
.customise-popup-overlay.is-visible .customise-popup {
    transform: translateY(0) scale(1);
}
.customise-popup-close {
    position: absolute;
    top: 14px;
    right: 14px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.9);
    color: #333;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    z-index: 2;
    transition: background 0.2s ease, transform 0.2s ease;
}
.customise-popup-close:hover {
    background: #fff;
    transform: rotate(90deg);
}
.customise-popup-image {
    flex: 0 0 42%;
    background: #efe1c8;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px;
}
.customise-popup-image img {
    max-width: 100%;
    height: auto;
    object-fit: contain;
}
.customise-popup-content {
    flex: 1;
    padding: 40px 36px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.customise-popup-badge {
    display: inline-block;
    background: #6b4226;
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    padding: 5px 12px;
    border-radius: 20px;
    margin-bottom: 14px;
    width: fit-content;
}
.customise-popup-title {
    font-size: 26px;
    line-height: 1.25;
    color: #2a1a0a;
    margin: 0 0 12px;
}
.customise-popup-text {
    color: #5a4a3a;
    font-size: 15px;
    line-height: 1.6;
    margin: 0 0 18px;
}
.customise-popup-features {
    list-style: none;
    padding: 0;
    margin: 0 0 24px;
}
.customise-popup-features li {
    color: #3a2a1a;
    font-size: 14px;
    padding: 4px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.customise-popup-features i {
    color: #6b4226;
    font-size: 12px;
}
.customise-popup-cta {
    align-self: flex-start;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
@media (max-width: 768px) {
    .customise-popup {
        flex-direction: column;
        max-width: 420px;
    }
    .customise-popup-image {
        flex: 0 0 auto;
        padding: 24px;
    }
    .customise-popup-image img {
        max-height: 110px;
    }
    .customise-popup-content {
        padding: 26px 24px 30px;
    }
    .customise-popup-title {
        font-size: 22px;
    }
}
</style>

<script>
(function () {
    if (sessionStorage.getItem('customisePopupShown') === '1') return;

    var DELAY_MS = 30000;
    var overlay = document.getElementById('customisePopupOverlay');
    if (!overlay) return;

    var closeBtn = overlay.querySelector('.customise-popup-close');
    var timer = setTimeout(showPopup, DELAY_MS);

    function showPopup() {
        overlay.classList.add('is-visible');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        sessionStorage.setItem('customisePopupShown', '1');
    }

    function hidePopup() {
        overlay.classList.remove('is-visible');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    closeBtn.addEventListener('click', hidePopup);
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) hidePopup();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.classList.contains('is-visible')) hidePopup();
    });
    overlay.querySelector('.customise-popup-cta').addEventListener('click', function () {
        sessionStorage.setItem('customisePopupShown', '1');
    });
})();
</script>
<!-- /Customise Service Popup -->
