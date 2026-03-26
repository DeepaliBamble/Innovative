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
                                <li><a href="track-order.php" class="link h6">Track order</a></li>
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
