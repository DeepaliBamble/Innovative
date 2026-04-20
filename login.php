<?php
require_once __DIR__ . '/includes/init.php';
?>
<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="<?= generateCsrfToken() ?>">
    <title>Customer Login - Innovative Homesi | Access Your Account</title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="Login to your Innovative Homesi account to manage orders, wishlist, and access exclusive furniture deals.">
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
            margin: 20px 0;
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
            animation: shake 0.3s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        @media (max-width: 480px) {
            .otp-digit {
                width: 40px;
                height: 40px;
                font-size: 20px;
            }
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
                    <h1 class="title-page">Login</h1>
                    <ul class="breadcrumbs-page">
                        <li><a href="index.php" class="h6 link">Home</a></li>
                        <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                        <li>
                            <h6 class="current-page fw-normal">Login</h6>
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
                        <h1 class="heading">Login</h1>

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

                        <!-- Email Form (Step 1) -->
                        <form class="form-login" id="emailForm" style="display: block;">
                            <div class="list-ver">
                                <fieldset>
                                    <input type="email" name="email" id="email" placeholder="Enter your email address *" required>
                                </fieldset>
                                <div class="check-bottom">
                                    <div class="checkbox-wrap">
                                        <input id="rememberEmail" name="remember" type="checkbox" class="tf-check" value="1">
                                        <label for="rememberEmail" class="h6">Keep me signed in</label>
                                    </div>
                                </div>
                            </div>

                            <button id="btnSendOTP" type="submit" class="tf-btn animate-btn w-100">
                                <span class="btn-text">Send OTP</span>
                                <span class="btn-loading" style="display:none;">
                                    <i class="fa fa-spinner fa-spin"></i> Sending...
                                </span>
                            </button>

                            <div class="mt-3 text-center">
                                <p class="h6">Or use <a href="#" id="usePasswordLogin" class="link fw-bold">password login</a></p>
                            </div>
                        </form>

                        <!-- OTP Verification Form (Step 2) -->
                        <form class="form-login" id="otpForm" style="display: none;">
                            <div class="alert alert-info mb-3">
                                <i class="fa fa-envelope"></i> We've sent a 6-digit OTP to <strong id="otpEmail"></strong>
                            </div>

                            <div class="otp-input-container mb-3">
                                <div class="otp-inputs">
                                    <input type="text" class="otp-digit" maxlength="1" pattern="\d" inputmode="numeric" autocomplete="off">
                                    <input type="text" class="otp-digit" maxlength="1" pattern="\d" inputmode="numeric" autocomplete="off">
                                    <input type="text" class="otp-digit" maxlength="1" pattern="\d" inputmode="numeric" autocomplete="off">
                                    <input type="text" class="otp-digit" maxlength="1" pattern="\d" inputmode="numeric" autocomplete="off">
                                    <input type="text" class="otp-digit" maxlength="1" pattern="\d" inputmode="numeric" autocomplete="off">
                                    <input type="text" class="otp-digit" maxlength="1" pattern="\d" inputmode="numeric" autocomplete="off">
                                </div>
                            </div>

                            <div class="list-ver">
                                <div class="check-bottom">
                                    <div class="checkbox-wrap">
                                        <input id="rememberOTP" name="remember" type="checkbox" class="tf-check" value="1">
                                        <label for="rememberOTP" class="h6">Keep me signed in</label>
                                    </div>
                                    <h6>
                                        <a href="#" id="resendOTP" class="link">
                                            <i class="fa fa-redo"></i> Resend OTP
                                        </a>
                                        <span id="resendTimer" style="display: none; color: #666; margin-left: 5px;">(Wait <span id="countdown">60</span>s)</span>
                                    </h6>
                                </div>
                            </div>

                            <button id="btnVerifyOTP" type="submit" class="tf-btn animate-btn w-100">
                                <span class="btn-text">Verify & Login</span>
                                <span class="btn-loading" style="display:none;">
                                    <i class="fa fa-spinner fa-spin"></i> Verifying...
                                </span>
                            </button>

                            <div class="mt-3 text-center">
                                <p class="h6"><a href="#" id="backToEmail" class="link"><i class="fa fa-arrow-left"></i> Change Email</a></p>
                            </div>
                        </form>

                        <!-- Password Login Form (Alternative) -->
                        <form class="form-login" method="POST" action="auth/login_handler.php" id="passwordForm" style="display: none;">
                            <?= csrfTokenField() ?>
                            <div class="list-ver">
                                <fieldset>
                                    <input type="email" name="email" id="passwordEmail" placeholder="Enter your email address *" required>
                                </fieldset>
                                <fieldset class="password-wrapper mb-8">
                                    <input class="password-field" type="password" name="password" id="password" placeholder="Password *" required>
                                    <span class="toggle-pass icon-show-password"></span>
                                </fieldset>
                                <div class="check-bottom">
                                    <div class="checkbox-wrap">
                                        <input id="rememberPassword" name="remember" type="checkbox" class="tf-check" value="1">
                                        <label for="rememberPassword" class="h6">Keep me signed in</label>
                                    </div>
                                </div>
                            </div>

                            <button id="btnPasswordLogin" type="submit" class="tf-btn animate-btn w-100">
                                Login with Password
                            </button>

                            <div class="mt-3 text-center">
                                <p class="h6">Or use <a href="#" id="useOTPLogin" class="link fw-bold">OTP login</a></p>
                            </div>
                        </form>

                        <div id="loginMessage" class="mt-3" style="display:none;"></div>
                    </div>
                    <div class="col-right">
                        <h1 class="heading">New Customer</h1>
                        <p class="h6 text-sub mb-32">
                            Welcome to Innovative Homesi! Create an account to manage your orders, save your wishlist, and enjoy a seamless shopping experience for premium furniture.
                        </p>
                        <a href="register.php" class="tf-btn animate-btn w-100 fw-bold">
                            Register Now
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
    <script src="js/bootstrap-select.min.js"></script>
    <script src="js/lazysize.min.js"></script>
    <script src="js/wow.min.js"></script>
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

    <script>
        $(document).ready(function() {
            let currentEmail = '';
            let resendCountdown = 60;
            let countdownInterval;
            const otpDigits = $('.otp-digit');

            // Show message function
            function showMessage(message, type = 'danger') {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                $('#loginMessage').html(`
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `).show();
            }

            // Resend countdown timer
            function startResendCountdown() {
                resendCountdown = 60;
                $('#resendOTP').css('pointer-events', 'none').css('opacity', '0.5');
                $('#resendTimer').show();

                clearInterval(countdownInterval);

                countdownInterval = setInterval(function() {
                    resendCountdown--;
                    $('#countdown').text(resendCountdown);

                    if (resendCountdown <= 0) {
                        clearInterval(countdownInterval);
                        $('#resendOTP').css('pointer-events', 'auto').css('opacity', '1');
                        $('#resendTimer').hide();
                    }
                }, 1000);
            }

            // OTP Input Handling
            otpDigits.each(function(index) {
                $(this).on('input', function(e) {
                    const value = $(this).val();

                    // Only allow digits
                    if (!/^\d*$/.test(value)) {
                        $(this).val('');
                        return;
                    }

                    if (value) {
                        $(this).addClass('filled');
                        // Move to next input
                        if (index < otpDigits.length - 1) {
                            otpDigits.eq(index + 1).focus();
                        }
                    } else {
                        $(this).removeClass('filled');
                    }
                });

                $(this).on('keydown', function(e) {
                    // Backspace handling
                    if (e.key === 'Backspace' && !$(this).val() && index > 0) {
                        otpDigits.eq(index - 1).focus().val('').removeClass('filled');
                    }

                    // Left arrow key
                    if (e.key === 'ArrowLeft' && index > 0) {
                        otpDigits.eq(index - 1).focus();
                    }

                    // Right arrow key
                    if (e.key === 'ArrowRight' && index < otpDigits.length - 1) {
                        otpDigits.eq(index + 1).focus();
                    }
                });

                // Paste handling
                $(this).on('paste', function(e) {
                    e.preventDefault();
                    const pastedData = e.originalEvent.clipboardData.getData('text').trim();

                    if (/^\d{6}$/.test(pastedData)) {
                        pastedData.split('').forEach(function(char, i) {
                            if (otpDigits.eq(i).length) {
                                otpDigits.eq(i).val(char).addClass('filled');
                            }
                        });
                        otpDigits.eq(5).focus();
                    }
                });
            });

            // Toggle between forms
            $('#usePasswordLogin').click(function(e) {
                e.preventDefault();
                $('#emailForm').hide();
                $('#otpForm').hide();
                $('#passwordForm').show();
                $('#loginMessage').hide();
            });

            $('#useOTPLogin').click(function(e) {
                e.preventDefault();
                $('#passwordForm').hide();
                $('#otpForm').hide();
                $('#emailForm').show();
                $('#loginMessage').hide();
            });

            $('#backToEmail').click(function(e) {
                e.preventDefault();
                $('#otpForm').hide();
                $('#emailForm').show();
                $('#loginMessage').hide();
                // Clear OTP inputs
                otpDigits.val('').removeClass('filled error');
                clearInterval(countdownInterval);
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Send OTP
            $('#emailForm').submit(function(e) {
                e.preventDefault();

                const email = $('#email').val();
                const remember = $('#rememberEmail').is(':checked') ? 1 : 0;

                // Show loading state
                $('#btnSendOTP .btn-text').hide();
                $('#btnSendOTP .btn-loading').show();
                $('#btnSendOTP').prop('disabled', true);
                $('#loginMessage').hide();

                $.ajax({
                    url: 'auth/send-login-otp.php',
                    method: 'POST',
                    data: { email: email, csrf_token: csrfToken },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            currentEmail = email;
                            $('#otpEmail').text(email);
                            $('#emailForm').hide();
                            $('#otpForm').show();
                            $('#rememberOTP').prop('checked', remember);
                            // Focus on first OTP input
                            otpDigits.eq(0).focus();
                            // Start countdown
                            startResendCountdown();
                            showMessage(response.message, 'success');
                        } else {
                            showMessage(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showMessage('An error occurred. Please try again.', 'danger');
                    },
                    complete: function() {
                        // Reset button state
                        $('#btnSendOTP .btn-text').show();
                        $('#btnSendOTP .btn-loading').hide();
                        $('#btnSendOTP').prop('disabled', false);
                    }
                });
            });

            // Resend OTP
            $('#resendOTP').click(function(e) {
                e.preventDefault();

                if (!currentEmail) {
                    showMessage('Please enter your email first.', 'danger');
                    return;
                }

                $.ajax({
                    url: 'auth/send-login-otp.php',
                    method: 'POST',
                    data: { email: currentEmail, csrf_token: csrfToken },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showMessage('OTP resent successfully!', 'success');
                            // Clear OTP inputs
                            otpDigits.val('').removeClass('filled error');
                            otpDigits.eq(0).focus();
                            // Restart countdown
                            startResendCountdown();
                        } else {
                            showMessage(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showMessage('Failed to resend OTP. Please try again.', 'danger');
                    }
                });
            });

            // Verify OTP
            $('#otpForm').submit(function(e) {
                e.preventDefault();

                // Collect OTP from individual inputs
                const otp = otpDigits.map(function() {
                    return $(this).val();
                }).get().join('');

                const remember = $('#rememberOTP').is(':checked') ? 1 : 0;

                // Validate OTP format
                if (otp.length !== 6 || !/^\d{6}$/.test(otp)) {
                    showMessage('Please enter all 6 digits of the OTP', 'danger');
                    otpDigits.addClass('error');
                    setTimeout(function() {
                        otpDigits.removeClass('error');
                    }, 500);
                    return;
                }

                // Show loading state
                $('#btnVerifyOTP .btn-text').hide();
                $('#btnVerifyOTP .btn-loading').show();
                $('#btnVerifyOTP').prop('disabled', true);
                $('#loginMessage').hide();

                $.ajax({
                    url: 'auth/verify-login-otp.php',
                    method: 'POST',
                    data: {
                        otp: otp,
                        remember: remember,
                        csrf_token: csrfToken
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showMessage(response.message, 'success');
                            // Redirect after short delay
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1000);
                        } else {
                            showMessage(response.message, 'danger');
                            // Clear OTP inputs on error
                            otpDigits.val('').removeClass('filled').addClass('error');
                            setTimeout(function() {
                                otpDigits.removeClass('error');
                                otpDigits.eq(0).focus();
                            }, 500);
                            // Reset button state on error
                            $('#btnVerifyOTP .btn-text').show();
                            $('#btnVerifyOTP .btn-loading').hide();
                            $('#btnVerifyOTP').prop('disabled', false);
                        }
                    },
                    error: function() {
                        showMessage('An error occurred. Please try again.', 'danger');
                        // Reset button state
                        $('#btnVerifyOTP .btn-text').show();
                        $('#btnVerifyOTP .btn-loading').hide();
                        $('#btnVerifyOTP').prop('disabled', false);
                    }
                });
            });
        });
    </script>
</body>
</html>

