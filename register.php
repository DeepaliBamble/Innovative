<?php
require_once __DIR__ . '/includes/init.php';
?>
<!DOCTYPE html>


<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="<?= generateCsrfToken() ?>">
    <title>Create Account - Innovative Homesi | Sign Up for Exclusive Benefits</title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="Create your Innovative Homesi account to track orders, save favorites, and get exclusive furniture deals and updates.">
    <meta name="robots" content="noindex, nofollow">

    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="fonts/fonts.css">
    <link rel="stylesheet" href="icon/icomoon/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- css -->
    <link rel="stylesheet" href="../sibforms.com/forms/end-form/build/sib-styles.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/swiper-bundle.min.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <link rel="stylesheet" type="text/css" href="css/modern-typography.css">

    <!-- Favicon and Touch Icons  -->
    <link rel="shortcut icon" href="images/logo/favicon.svg">
    <link rel="apple-touch-icon-precomposed" href="images/logo/favicon.svg">

    <style>
        /* OTP Input Styles */
        .otp-inputs {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .otp-digit {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid #ddd;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .otp-digit:focus {
            border-color: #6366f1;
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .otp-digit.filled {
            border-color: #6366f1;
            background: #f0f0ff;
        }

        .otp-digit.error {
            border-color: #dc3545;
            background: #fff5f5;
        }

        #messageContainer .alert {
            margin-bottom: 15px;
            border-radius: 8px;
        }

        .btn-link {
            color: #6366f1;
            text-decoration: none;
            font-weight: 500;
        }

        .btn-link:hover {
            color: #4f46e5;
            text-decoration: underline;
        }

        .btn-outline-secondary {
            border: 1px solid #6c757d;
            color: #6c757d;
            background: transparent;
            padding: 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-outline-secondary:hover {
            background: #6c757d;
            color: white;
        }

        @media (max-width: 480px) {
            .otp-digit {
                width: 40px;
                height: 40px;
                font-size: 20px;
            }
        }

        /* Channel tabs */
        .login-channel-tabs .channel-tab {
            background: #f5f5f5;
            color: #555;
            border: 1px solid #e0e0e0;
            padding: 10px 12px;
            border-radius: 8px;
            font-weight: 500;
        }
        .login-channel-tabs .channel-tab.active {
            background: #d4a574;
            color: #fff;
            border-color: #d4a574;
        }
    </style>
</head>

<body>

    <!-- Scroll Top -->
    <button id="goTop">
        <span class="border-progress"></span>
        <span class="icon icon-caret-up"></span>
    </button>

    <!-- preload -->
    <div class="preload preload-container" id="preload">
        <div class="preload-logo">
            <div class="spinner"></div>
        </div>
    </div>
    <!-- /preload -->

    <div id="wrapper">
        <?php include 'includes/topbar.php'; ?>
        <?php include 'includes/header.php'; ?>
        <!-- Page Title -->
        <section class="s-page-title">
            <div class="container">
                <div class="content">
                    <h1 class="title-page">Register</h1>
                    <ul class="breadcrumbs-page">
                        <li><a href="index.php" class="h6 link">Home</a></li>
                        <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                        <li>
                            <h6 class="current-page fw-normal">Register</h6>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
        <!-- /Page Title -->
        <!-- Login -->
        <section class="flat-spacing">
            <div class="container">
                <div class="s-log">
                    <div class="col-left">
                        <h1 class="heading">Register</h1>

                        <?php
                        // Display flash message if exists
                        $flash = getFlashMessage();
                        if ($flash):
                        ?>
                        <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($flash['message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <!-- Channel toggle: where to send the verification OTP -->
                        <div class="login-channel-tabs d-flex mb-3" id="channelTabs" role="tablist" style="gap:8px;">
                            <button type="button" class="tf-btn channel-tab active w-100" data-channel="mobile">
                                <i class="fa fa-mobile-alt"></i> Verify via Mobile
                            </button>
                            <button type="button" class="tf-btn channel-tab w-100" data-channel="email">
                                <i class="fa fa-envelope"></i> Verify via Email
                            </button>
                        </div>

                        <!-- Registration Form -->
                        <form class="form-login" id="registrationForm">
                            <div id="registrationFields" class="list-ver">
                                <fieldset>
                                    <input type="text" name="name" id="reg_name" placeholder="Enter your full name *" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                                </fieldset>
                                <fieldset>
                                    <input type="tel" name="mobile" id="reg_mobile" placeholder="Enter your mobile number *" maxlength="10" inputmode="numeric" pattern="[6-9][0-9]{9}" required>
                                </fieldset>
                                <fieldset>
                                    <input type="email" name="email" id="reg_email" placeholder="Enter your email address *" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                </fieldset>
                                <fieldset>
                                    <input type="text" name="location" id="reg_location" placeholder="Enter your location (city / area) *" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" required>
                                </fieldset>
                            </div>

                            <!-- OTP Verification Section (Hidden by default) -->
                            <div id="otpSection" style="display: none;">
                                <div class="alert alert-info mb-3">
                                    <i id="otpChannelIcon" class="fas fa-mobile-alt"></i> We've sent a 6-digit OTP to <strong id="otpEmail"></strong>
                                </div>

                                <div class="otp-input-container mb-3">
                                    <div class="otp-inputs d-flex justify-content-center gap-2 mb-3">
                                        <input type="text" class="otp-digit" maxlength="1" pattern="\d" inputmode="numeric" autocomplete="off">
                                        <input type="text" class="otp-digit" maxlength="1" pattern="\d" inputmode="numeric" autocomplete="off">
                                        <input type="text" class="otp-digit" maxlength="1" pattern="\d" inputmode="numeric" autocomplete="off">
                                        <input type="text" class="otp-digit" maxlength="1" pattern="\d" inputmode="numeric" autocomplete="off">
                                        <input type="text" class="otp-digit" maxlength="1" pattern="\d" inputmode="numeric" autocomplete="off">
                                        <input type="text" class="otp-digit" maxlength="1" pattern="\d" inputmode="numeric" autocomplete="off">
                                    </div>
                                </div>

                                <div class="text-center mb-3">
                                    <p class="mb-2">Didn't receive the code?</p>
                                    <button type="button" id="resendOtpBtn" class="btn btn-link p-0">
                                        <i class="fas fa-redo"></i> Resend OTP
                                    </button>
                                    <span id="resendTimer" style="display: none; color: #666;"> (Wait <span id="countdown">60</span>s)</span>
                                </div>

                                <button type="button" id="backToRegBtn" class="btn btn-outline-secondary w-100 mb-2">
                                    <i class="fas fa-arrow-left"></i> Back to Registration
                                </button>
                            </div>

                            <!-- Submit Button -->
                            <div id="messageContainer"></div>
                            <button type="submit" id="submitBtn" class="tf-btn animate-btn w-100">
                                <span id="btnText">Register</span>
                                <span id="btnSpinner" style="display: none;">
                                    <i class="fas fa-spinner fa-spin"></i> Processing...
                                </span>
                            </button>
                        </form>
                    </div>
                    <div class="col-right">
                        <h1 class="heading">Have An Account</h1>
                        <p class="h6 text-sub">
                            Welcome back, log in to your account to enhance your shopping experience, receive coupons, and the best discount codes.
                        </p>
                        <a href="login.php" class="btn_log tf-btn animate-btn">
                            Login
                        </a>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Login -->
        <?php include 'includes/footer.php'; ?>
    <!-- Javascript -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <!-- <script src="js/swiper-bundle.min.js"></script> -->
    <!-- <script src="js/carousel.js"></script> -->
    <script src="js/bootstrap-select.min.js"></script>
    <script src="js/lazysize.min.js"></script>
    <script src="js/wow.min.js"></script>



    <!-- <script src="js/parallaxie.js"></script> -->
    <script src="js/count-down.js"></script>
    <script src="js/main.js"></script>

    <script src="js/sibforms.js" defer></script>
    <script>
        window.REQUIRED_CODE_ERROR_MESSAGE = 'Please choose a country code';
        window.LOCALE = 'en';
        window.EMAIL_INVALID_MESSAGE = window.SMS_INVALID_MESSAGE = "The information provided is invalid. Please review the field format and try again.";

        window.REQUIRED_ERROR_MESSAGE = "This field cannot be left blank. ";

        window.GENERIC_INVALID_MESSAGE = "The information provided is invalid. Please review the field format and try again.";

        window.translation = {
            common: {
                selectedList: '{quantity} list selected',
                selectedLists: '{quantity} lists selected'
            }
        };

        var AUTOHIDE = Boolean(0);
    </script>

    <!-- Registration OTP JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const registrationForm = document.getElementById('registrationForm');
            const registrationFields = document.getElementById('registrationFields');
            const otpSection = document.getElementById('otpSection');
            const messageContainer = document.getElementById('messageContainer');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            const otpDigits = document.querySelectorAll('.otp-digit');
            const resendOtpBtn = document.getElementById('resendOtpBtn');
            const backToRegBtn = document.getElementById('backToRegBtn');
            const resendTimer = document.getElementById('resendTimer');
            const countdown = document.getElementById('countdown');
            const otpEmailDisplay = document.getElementById('otpEmail');

            let isOtpMode = false;
            let resendCountdown = 60;
            let countdownInterval;
            let currentChannel = 'mobile'; // 'mobile' | 'email'

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const channelTabs = document.querySelectorAll('.channel-tab');
            const otpChannelIcon = document.getElementById('otpChannelIcon');

            // Channel tab switching — only meaningful before OTP has been sent.
            channelTabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    if (isOtpMode) return; // Don't allow switching mid-flow
                    const ch = this.dataset.channel;
                    if (ch === currentChannel) return;
                    currentChannel = ch;
                    channelTabs.forEach(function(t) { t.classList.remove('active'); });
                    this.classList.add('active');
                });
            });

            // Handle form submission
            registrationForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!isOtpMode) {
                    // Step 1: Send OTP
                    sendRegistrationOTP();
                } else {
                    // Step 2: Verify OTP
                    verifyRegistrationOTP();
                }
            });

            // Send Registration OTP
            function sendRegistrationOTP() {
                const name     = document.getElementById('reg_name').value.trim();
                const mobile   = document.getElementById('reg_mobile').value.replace(/\D/g, '');
                const email    = document.getElementById('reg_email').value.trim();
                const location = document.getElementById('reg_location').value.trim();

                if (!name || name.length < 2) {
                    showMessage('danger', 'Please enter your full name');
                    return;
                }
                if (!/^[6-9]\d{9}$/.test(mobile)) {
                    showMessage('danger', 'Please enter a valid 10-digit mobile number');
                    return;
                }
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    showMessage('danger', 'Please enter a valid email address');
                    return;
                }
                if (!location || location.length < 2) {
                    showMessage('danger', 'Please enter your location');
                    return;
                }

                const formData = new FormData();
                formData.append('name',       name);
                formData.append('mobile',     mobile);
                formData.append('email',      email);
                formData.append('location',   location);
                formData.append('csrf_token', csrfToken);

                showLoading(true);
                clearMessage();

                const endpoint = currentChannel === 'mobile'
                    ? 'auth/send-register-otp.php'
                    : 'auth/send-register-otp-email.php';

                fetch(endpoint, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    showLoading(false);

                    if (data.success) {
                        isOtpMode = true;
                        registrationFields.style.display = 'none';
                        otpSection.style.display = 'block';

                        if (currentChannel === 'mobile') {
                            otpChannelIcon.className = 'fas fa-mobile-alt';
                            otpEmailDisplay.textContent = data.maskedMobile || mobile.replace(/(\d{2})(\d{4})(\d{4})/, '$1XXXX$3');
                        } else {
                            otpChannelIcon.className = 'fas fa-envelope';
                            otpEmailDisplay.textContent = data.maskedEmail || email;
                        }

                        btnText.textContent = 'Verify & Register';
                        otpDigits[0].focus();
                        startResendCountdown();
                        showMessage('success', data.message);
                    } else {
                        showMessage('danger', data.message);
                    }
                })
                .catch(error => {
                    showLoading(false);
                    showMessage('danger', 'An error occurred. Please try again.');
                    console.error('Error:', error);
                });
            }

            // Verify Registration OTP
            function verifyRegistrationOTP() {
                const otp = Array.from(otpDigits).map(input => input.value).join('');

                if (otp.length !== 6) {
                    showMessage('danger', 'Please enter all 6 digits of the OTP');
                    return;
                }

                showLoading(true);
                clearMessage();

                const formData = new FormData();
                formData.append('otp', otp);
                formData.append('csrf_token', csrfToken);

                const verifyUrl = currentChannel === 'mobile'
                    ? 'auth/verify-register-otp.php'
                    : 'auth/verify-register-otp-email.php';

                fetch(verifyUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    showLoading(false);

                    if (data.success) {
                        showMessage('success', data.message);

                        // Redirect after a short delay
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1500);
                    } else {
                        showMessage('danger', data.message);
                        // Clear OTP inputs on error
                        otpDigits.forEach(input => {
                            input.value = '';
                            input.classList.remove('filled');
                            input.classList.add('error');
                        });
                        setTimeout(() => {
                            otpDigits.forEach(input => input.classList.remove('error'));
                            otpDigits[0].focus();
                        }, 500);
                    }
                })
                .catch(error => {
                    showLoading(false);
                    showMessage('danger', 'An error occurred. Please try again.');
                    console.error('Error:', error);
                });
            }

            // OTP Input Handling
            otpDigits.forEach((input, index) => {
                input.addEventListener('input', function(e) {
                    const value = e.target.value;

                    // Only allow digits
                    if (!/^\d*$/.test(value)) {
                        e.target.value = '';
                        return;
                    }

                    if (value) {
                        e.target.classList.add('filled');
                        // Move to next input
                        if (index < otpDigits.length - 1) {
                            otpDigits[index + 1].focus();
                        }
                    } else {
                        e.target.classList.remove('filled');
                    }
                });

                input.addEventListener('keydown', function(e) {
                    // Backspace handling
                    if (e.key === 'Backspace' && !e.target.value && index > 0) {
                        otpDigits[index - 1].focus();
                        otpDigits[index - 1].value = '';
                        otpDigits[index - 1].classList.remove('filled');
                    }

                    // Left arrow key
                    if (e.key === 'ArrowLeft' && index > 0) {
                        otpDigits[index - 1].focus();
                    }

                    // Right arrow key
                    if (e.key === 'ArrowRight' && index < otpDigits.length - 1) {
                        otpDigits[index + 1].focus();
                    }
                });

                // Paste handling
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pastedData = e.clipboardData.getData('text').trim();

                    if (/^\d{6}$/.test(pastedData)) {
                        pastedData.split('').forEach((char, i) => {
                            if (otpDigits[i]) {
                                otpDigits[i].value = char;
                                otpDigits[i].classList.add('filled');
                            }
                        });
                        otpDigits[5].focus();
                    }
                });
            });

            // Resend OTP
            resendOtpBtn.addEventListener('click', function() {
                if (resendOtpBtn.disabled) return;

                const formData = new FormData();
                formData.append('name',       document.getElementById('reg_name').value.trim());
                formData.append('mobile',     document.getElementById('reg_mobile').value.replace(/\D/g, ''));
                formData.append('email',      document.getElementById('reg_email').value.trim());
                formData.append('location',   document.getElementById('reg_location').value.trim());
                formData.append('csrf_token', csrfToken);

                clearMessage();

                const resendUrl = currentChannel === 'mobile'
                    ? 'auth/send-register-otp.php'
                    : 'auth/send-register-otp-email.php';

                fetch(resendUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('success', 'OTP resent successfully!');
                        otpDigits.forEach(input => {
                            input.value = '';
                            input.classList.remove('filled');
                        });
                        otpDigits[0].focus();
                        startResendCountdown();
                    } else {
                        showMessage('danger', data.message);
                    }
                })
                .catch(error => {
                    showMessage('danger', 'Failed to resend OTP. Please try again.');
                    console.error('Error:', error);
                });
            });

            // Back to Registration
            backToRegBtn.addEventListener('click', function() {
                isOtpMode = false;
                otpSection.style.display = 'none';
                registrationFields.style.display = 'block';
                btnText.textContent = 'Register';

                // Clear OTP inputs
                otpDigits.forEach(input => {
                    input.value = '';
                    input.classList.remove('filled');
                });

                clearMessage();
                clearInterval(countdownInterval);
            });

            // Resend countdown timer
            function startResendCountdown() {
                resendCountdown = 60;
                resendOtpBtn.disabled = true;
                resendTimer.style.display = 'inline';

                clearInterval(countdownInterval);

                countdownInterval = setInterval(() => {
                    resendCountdown--;
                    countdown.textContent = resendCountdown;

                    if (resendCountdown <= 0) {
                        clearInterval(countdownInterval);
                        resendOtpBtn.disabled = false;
                        resendTimer.style.display = 'none';
                    }
                }, 1000);
            }

            // UI Helper Functions
            function showLoading(show) {
                submitBtn.disabled = show;
                if (show) {
                    btnText.style.display = 'none';
                    btnSpinner.style.display = 'inline';
                } else {
                    btnText.style.display = 'inline';
                    btnSpinner.style.display = 'none';
                }
            }

            function showMessage(type, message) {
                messageContainer.innerHTML = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
            }

            function clearMessage() {
                messageContainer.innerHTML = '';
            }
        });
    </script>
</body>
</html>

