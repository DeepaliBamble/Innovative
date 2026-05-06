<?php
require_once __DIR__ . '/includes/init.php';
?>
<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">

<meta http-equiv="content-type" content="text/html;charset=utf-8" />

<head>
    <meta charset="utf-8">
    <title>Customise Service - Innovative Homesi | Bespoke Furniture Design</title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description"
        content="Create your perfect piece with our customisation service. Personalize sofas, chairs, and furniture with your choice of fabrics, colors, sizes, and designs. Bespoke furniture crafted to your exact specifications.">
    <meta name="keywords"
        content="custom furniture, bespoke sofa, customise furniture, personalized design, made-to-order sofa, custom sofa design, furniture customization, tailored furniture">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://innovativehomesi.com/customise-service">

    <!-- Open Graph Meta Tags -->
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Customise Your Furniture - Innovative Homesi">
    <meta property="og:description"
        content="Design your perfect piece with our custom furniture service. Choose fabrics, colors, sizes, and styles to create uniquely tailored furniture.">
    <meta property="og:url" content="https://innovativehomesi.com/customise-service">
    <meta property="og:site_name" content="Innovative Homesi">
    <meta property="og:image" content="https://innovativehomesi.com/images/logo/logo.png">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Customise Your Furniture - Innovative Homesi">
    <meta name="twitter:description" content="Design your perfect piece with our custom furniture service.">
    <meta name="twitter:image" content="https://innovativehomesi.com/images/logo/logo.png">

    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=GFS+Neohellenic:ital,wght@0,400;0,700;1,400;1,700&family=Luxurious+Roman&family=Maven+Pro:wght@400..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="fonts/fonts.css">
    <link rel="stylesheet" href="icon/icomoon/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
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
        .customise-feature {
            padding: 40px 30px;
            border-radius: 12px;
            background: var(--white);
            border: 1px solid var(--line);
            transition: all 0.3s ease;
            height: 100%;
            text-align: center;
        }

        .customise-feature:hover {
            box-shadow: 0 10px 30px var(--shadow-1);
            border-color: var(--primary);
            transform: translateY(-5px);
        }

        .customise-feature-icon {
            width: 70px;
            height: 70px;
            background: var(--linear-5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
            color: var(--primary);
        }

        .customise-feature h3 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--black);
        }

        .customise-feature p {
            color: var(--text);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .process-step {
            position: relative;
            padding: 40px 30px;
            background: var(--white);
            border-radius: 12px;
            text-align: center;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .process-step:hover {
            border-color: var(--primary);
        }

        .step-number {
            display: inline-block;
            width: 60px;
            height: 60px;
            background: var(--linear-5);
            border-radius: 50%;
            line-height: 60px;
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .process-step h4 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--black);
        }

        .process-step p {
            color: var(--text);
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 0;
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            background: var(--bg-2);
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.05);
        }

        .gallery-placeholder {
            background: var(--linear-5);
            color: var(--primary);
            font-size: 2rem;
            font-weight: 700;
        }

        .fabric-card {
            background: var(--white);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            text-align: center;
            border: 2px solid var(--line);
        }

        .fabric-card:hover {
            border-color: var(--primary);
            box-shadow: 0 8px 24px var(--shadow-1);
        }

        .fabric-swatch {
            width: 100%;
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--white);
            font-size: 1.1rem;
        }

        .fabric-name {
            padding: 20px;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--black);
        }

        @media (max-width: 768px) {
            .customise-feature h3 {
                font-size: 1.2rem;
            }

            .process-step {
                margin-bottom: 30px;
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

        <!-- Page Title Banner -->
        <section class="s-page-title" style="background: var(--linear-5);">
            <div class="container text-center">
                <div class="content">
                    <h1 class="title-page w-bold">CREATE YOUR PERFECT PIECE</h1>
                    <ul class="breadcrumbs-page justify-content-center">
                        <li><a href="index.php" class="h6 link">Home</a></li>
                        <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                        <li>
                            <h6 class="current-page fw-normal">Customise Service</h6>
                        </li>
                    </ul>
                    <p class="h6 mt-3">Bespoke furniture tailored to your unique style and space</p>
                </div>
            </div>
        </section>
        <!-- /Page Title Banner -->

        <!-- Hero Section -->
        <section style="padding: 60px 0; background: var(--white);">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <span class="text-uppercase text-primary fw-semibold mb-3 d-block"
                            style="letter-spacing: 2px; font-size: 14px;">CUSTOMISATION SERVICE</span>
                        <h2 class="fw-bold mb-4" style="font-size: 2.5rem; line-height: 1.2;">Your Vision, <br>Our
                            Craftsmanship</h2>
                        <p class="mb-4" style="font-size: 1.1rem; line-height: 1.8; color: var(--text);">
                            At Innovative Homesi, we understand that your home is unique, and so are your preferences.
                            Our customisation service allows you to design furniture that perfectly matches your style,
                            space, and needs. From selecting premium fabrics to choosing dimensions and configurations,
                            you have complete control over your piece.
                        </p>
                        <div class="d-flex gap-3 flex-wrap">
                            <a href="#customise-features" class="tf-btn animate-btn">
                                Explore Features
                                <i class="icon icon-caret-right"></i>
                            </a>
                            <a href="contact-us.php" class="tf-btn btn-outline-primary animate-btn">
                                Contact Us
                                <i class="icon icon-caret-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div
                            style="background: var(--linear-5); border-radius: 12px; padding: 40px; text-align: center;">
                            <div style="font-size: 6rem; color: var(--primary);">
                                <i class="fa-solid fa-sliders"></i>
                            </div>
                            <h3 style="color: var(--black); margin-top: 20px;">Unlimited Possibilities</h3>
                            <p style="color: var(--text);">Design exactly what you envision with our comprehensive
                                customisation options.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Hero Section -->

        <!-- Why Choose Custom Furniture -->
        <section style="padding: 80px 0; background: var(--white);" id="customise-features">
            <div class="container">
                <div class="text-center mb-5 pb-3">
                    <span class="text-uppercase text-primary fw-semibold"
                        style="letter-spacing: 2px; font-size: 14px;">WHY CUSTOMISE</span>
                    <h2 class="fw-bold mt-2 mb-3" style="font-size: 2.5rem;">Why Choose Custom Furniture?</h2>
                    <p
                        style="color: var(--text); font-size: 1.05rem; max-width: 700px; margin: 0 auto; line-height: 1.7;">
                        Transform your living space with furniture that's uniquely yours. Discover the unmatched
                        benefits of bespoke design tailored to your exact needs and preferences.
                    </p>
                </div>
                <div class="row g-4">
                    <div class="col-md-6 col-lg-4">
                        <div class="customise-feature">
                            <div class="customise-feature-icon">
                                <i class="fa-solid fa-ruler-combined"></i>
                            </div>
                            <h3>Perfect Space Fit</h3>
                            <p>No more compromising with off-the-shelf sizes. We design furniture to your exact
                                dimensions, ensuring it fits perfectly in your space—whether it's a compact apartment or
                                a spacious villa. Every inch counts!</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="customise-feature">
                            <div class="customise-feature-icon">
                                <i class="fa-solid fa-palette"></i>
                            </div>
                            <h3>Unlimited Design Freedom</h3>
                            <p>Express your personality through design. Choose from hundreds of premium fabrics, colors,
                                patterns, and finishes. Create furniture that truly reflects your style and complements
                                your existing décor.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="customise-feature">
                            <div class="customise-feature-icon">
                                <i class="fa-solid fa-sparkles"></i>
                            </div>
                            <h3>One-of-a-Kind Pieces</h3>
                            <p>Own furniture that's exclusively yours. Unlike mass-produced items, your custom piece is
                                unique—designed to your specifications and crafted individually just for you.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="customise-feature">
                            <div class="customise-feature-icon">
                                <i class="fa-solid fa-hand-holding-heart"></i>
                            </div>
                            <h3>Lifestyle Adaptation</h3>
                            <p>Your furniture should work for you. Need extra storage? Softer cushions? Pet-friendly
                                fabrics? We customize features to match your lifestyle, habits, and daily needs
                                perfectly.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="customise-feature">
                            <div class="customise-feature-icon">
                                <i class="fa-solid fa-award"></i>
                            </div>
                            <h3>Superior Craftsmanship</h3>
                            <p>Experience the difference of handcrafted quality. Our master artisans use traditional
                                techniques combined with modern precision to create furniture with exceptional attention
                                to detail and finish.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="customise-feature">
                            <div class="customise-feature-icon">
                                <i class="fa-solid fa-leaf"></i>
                            </div>
                            <h3>Premium Materials</h3>
                            <p>We source only the finest materials—solid hardwood frames, high-density foam, premium
                                upholstery fabrics, and durable hardware. Quality materials ensure your furniture stands
                                the test of time.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="customise-feature">
                            <div class="customise-feature-icon">
                                <i class="fa-solid fa-indian-rupee-sign"></i>
                            </div>
                            <h3>True Value Investment</h3>
                            <p>Custom doesn't mean expensive. Get exactly what you want without paying for unnecessary
                                features. Invest in quality pieces that last decades, not disposable furniture you'll
                                replace in years.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="customise-feature">
                            <div class="customise-feature-icon">
                                <i class="fa-solid fa-users-gear"></i>
                            </div>
                            <h3>Expert Guidance</h3>
                            <p>Our experienced design consultants guide you through every decision—from fabric selection
                                to proportions. Benefit from professional expertise to make informed choices and avoid
                                costly mistakes.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="customise-feature">
                            <div class="customise-feature-icon">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <h3>Quality Guarantee</h3>
                            <p>We stand behind our craftsmanship with a comprehensive 2-year structural warranty. Your
                                satisfaction is our priority, backed by quality assurance at every production stage.</p>
                        </div>
                    </div>
                </div>

                <!-- CTA Section -->
                <div class="row mt-5 pt-4">
                    <div class="col-12">
                        <div class="p-5 rounded-3 text-center" style="background: var(--linear-5);">
                            <h3 class="fw-bold mb-3" style="color: var(--black); font-size: 1.8rem;">Ready to Create
                                Your Dream Furniture?</h3>
                            <p class="mb-4"
                                style="color: var(--text); font-size: 1.05rem; max-width: 600px; margin: 0 auto;">
                                Start your customisation journey today. Our design experts are here to help you every
                                step of the way.
                            </p>
                            <div class="d-flex gap-3 justify-content-center flex-wrap">
                                <a href="#customise-form" class="tf-btn animate-btn"
                                    onclick="document.getElementById('customiseEnquiryForm').scrollIntoView({ behavior: 'smooth', block: 'center' }); return false;">
                                    Get Started Now
                                    <i class="icon icon-arrow-right ms-2"></i>
                                </a>
                                <a href="contact-us.php" class="tf-btn btn-outline-primary animate-btn">
                                    Talk to Designer
                                    <i class="icon icon-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Why Choose Custom Furniture -->


        <!-- Customisation Options -->
        <section style="padding: 60px 0; background: var(--bg-9);">
            <div class="container">
                <div class="text-center mb-5 pb-2">
                    <span class="text-uppercase text-primary fw-semibold"
                        style="letter-spacing: 2px; font-size: 14px;">PERSONALISATION</span>
                    <h2 class="fw-bold mt-2 mb-3" style="font-size: 2rem;">What You Can Customise</h2>
                    <p style="color: var(--text); font-size: 1rem; max-width: 700px; margin: 0 auto;">
                        From sofas to dining tables, every piece of furniture can be tailored to your exact
                        specifications
                    </p>
                </div>

                <!-- Sofas Customisation -->
                <div class="row g-4 mb-5">
                    <div class="col-12">
                        <div class="p-4 bg-white rounded-3" style="border: 2px solid var(--primary);">
                            <h3 class="fw-bold mb-4 d-flex align-items-center" style="color: var(--primary);">
                                <i class="fa-solid fa-couch me-3" style="font-size: 1.8rem;"></i>
                                Sofas & Seating
                            </h3>
                            <p class="mb-4" style="color: var(--text); font-size: 0.95rem;">
                                3 Seater Sofa • L Shape Sofa • U Shape Sofa • Sofa Cum Bed • Modern Sofa • Recliner Sofa
                            </p>
                            <div class="row g-3">
                                <div class="col-md-3 col-sm-6">
                                    <div class="d-flex align-items-start">
                                        <i class="fa-solid fa-ruler-combined text-primary me-2 mt-1"></i>
                                        <div>
                                            <strong style="color: var(--black); font-size: 0.95rem;">Dimensions</strong>
                                            <p style="color: var(--text); font-size: 0.85rem; margin: 5px 0 0;">Width,
                                                length, seat height, back height, armrest height</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="d-flex align-items-start">
                                        <i class="fa-solid fa-palette text-primary me-2 mt-1"></i>
                                        <div>
                                            <strong style="color: var(--black); font-size: 0.95rem;">Upholstery</strong>
                                            <p style="color: var(--text); font-size: 0.85rem; margin: 5px 0 0;">Fabric
                                                type, color, texture, pattern options</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="d-flex align-items-start">
                                        <i class="fa-solid fa-layer-group text-primary me-2 mt-1"></i>
                                        <div>
                                            <strong style="color: var(--black); font-size: 0.95rem;">Cushions</strong>
                                            <p style="color: var(--text); font-size: 0.85rem; margin: 5px 0 0;">Firmness
                                                level, back cushion style, seat depth</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="d-flex align-items-start">
                                        <i class="fa-solid fa-gears text-primary me-2 mt-1"></i>
                                        <div>
                                            <strong style="color: var(--black); font-size: 0.95rem;">Features</strong>
                                            <p style="color: var(--text); font-size: 0.85rem; margin: 5px 0 0;">Armrest
                                                style, leg finish, recliner mechanism, storage</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="d-flex align-items-start">
                                        <i class="fa-solid fa-shapes text-primary me-2 mt-1"></i>
                                        <div>
                                            <strong
                                                style="color: var(--black); font-size: 0.95rem;">Configuration</strong>
                                            <p style="color: var(--text); font-size: 0.85rem; margin: 5px 0 0;">L-shape
                                                orientation, chaise lounge position, sectional layout</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="d-flex align-items-start">
                                        <i class="fa-solid fa-bed text-primary me-2 mt-1"></i>
                                        <div>
                                            <strong
                                                style="color: var(--black); font-size: 0.95rem;">Convertible</strong>
                                            <p style="color: var(--text); font-size: 0.85rem; margin: 5px 0 0;">Sofa cum
                                                bed mechanism, mattress type, storage box</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chairs Customisation -->
                <div class="row g-4 mb-5">
                    <div class="col-lg-6">
                        <div class="p-4 bg-white rounded-3 h-100"
                            style="border: 1px solid var(--line); transition: all 0.3s ease;">
                            <h3 class="fw-bold mb-3 d-flex align-items-center" style="color: var(--black);">
                                <i class="fa-solid fa-chair me-3 text-primary" style="font-size: 1.5rem;"></i>
                                Chairs & Seating
                            </h3>
                            <p class="mb-3" style="color: var(--text); font-size: 0.9rem;">
                                Dining Chairs • Accent Chairs • Bench & Ottoman
                            </p>
                            <ul style="list-style: none; padding: 0; color: var(--text);">
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Seat dimensions &
                                    height</li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Backrest style &
                                    height</li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Upholstery fabric &
                                    color</li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Armrest options
                                </li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Leg design & finish
                                </li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Swivel or fixed
                                    base</li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Cushion padding
                                    firmness</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Tables & Storage Customisation -->
                    <div class="col-lg-6">
                        <div class="p-4 bg-white rounded-3 h-100"
                            style="border: 1px solid var(--line); transition: all 0.3s ease;">
                            <h3 class="fw-bold mb-3 d-flex align-items-center" style="color: var(--black);">
                                <i class="fa-solid fa-table me-3 text-primary" style="font-size: 1.5rem;"></i>
                                Tables & Storage
                            </h3>
                            <p class="mb-3" style="color: var(--text); font-size: 0.9rem;">
                                Center Table • Dining Table • Console • Side Table • Cabinet • TV Unit
                            </p>
                            <ul style="list-style: none; padding: 0; color: var(--text);">
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Table dimensions
                                    (length, width, height)</li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Tabletop material &
                                    finish</li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Shape (rectangular,
                                    round, oval, square)</li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Leg style &
                                    material</li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Storage
                                    compartments & drawers</li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Shelf configuration
                                </li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Hardware & handle
                                    design</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Beds & Bedroom Furniture -->
                <div class="row g-4 mb-5">
                    <div class="col-lg-6">
                        <div class="p-4 bg-white rounded-3 h-100"
                            style="border: 1px solid var(--line); transition: all 0.3s ease;">
                            <h3 class="fw-bold mb-3 d-flex align-items-center" style="color: var(--black);">
                                <i class="fa-solid fa-bed me-3 text-primary" style="font-size: 1.5rem;"></i>
                                Beds & Bedroom Furniture
                            </h3>
                            <p class="mb-3" style="color: var(--text); font-size: 0.9rem;">
                                Beds & Frames • Nightstands • Bedroom Benches
                            </p>
                            <ul style="list-style: none; padding: 0; color: var(--text);">
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Bed size (single,
                                    double, queen, king)</li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Headboard design &
                                    height</li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Upholstered or
                                    wooden frame</li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Footboard style
                                </li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Under-bed storage
                                    options</li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Nightstand
                                    dimensions & drawers</li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Bench length &
                                    upholstery</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Furnishing & Decor -->
                    <div class="col-lg-6">
                        <div class="p-4 bg-white rounded-3 h-100"
                            style="border: 1px solid var(--line); transition: all 0.3s ease;">
                            <h3 class="fw-bold mb-3 d-flex align-items-center" style="color: var(--black);">
                                <i class="fa-solid fa-gift me-3 text-primary" style="font-size: 1.5rem;"></i>
                                Furnishing & Decor
                            </h3>
                            <p class="mb-3" style="color: var(--text); font-size: 0.9rem;">
                                Cushions • Table Runners • Table Mats • Rugs • Vases • Lamps • Wall Art
                            </p>
                            <ul style="list-style: none; padding: 0; color: var(--text);">
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Cushion size, shape
                                    & filling</li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Fabric selection &
                                    patterns</li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Color coordination
                                </li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Rug dimensions &
                                    material</li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Table runner length
                                    & design</li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Lamp style & finish
                                </li>
                                <li class="py-2"><i class="fa-solid fa-check text-primary me-2"></i> Custom embroidery
                                    or prints</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Universal Customisation Options -->
                <div class="row g-4">
                    <div class="col-12">
                        <div class="p-5 rounded-3" style="background: var(--white); border: 2px solid var(--primary);">
                            <div class="text-center mb-5">
                                <span class="text-uppercase text-primary fw-semibold mb-2 d-block"
                                    style="letter-spacing: 2px; font-size: 13px;">FOR ALL FURNITURE</span>
                                <h3 class="fw-bold" style="color: var(--black); font-size: 1.8rem;">Universal
                                    Customisation Options</h3>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-3 col-sm-6">
                                    <div class="text-center p-3 h-100"
                                        style="background: var(--linear-5); border-radius: 10px;">
                                        <div
                                            style="width: 70px; height: 70px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                            <i class="fa-solid fa-paint-roller text-primary"
                                                style="font-size: 1.8rem;"></i>
                                        </div>
                                        <h5 class="fw-bold mb-2" style="color: var(--black);">Finish & Color</h5>
                                        <p style="font-size: 0.9rem; color: var(--text); line-height: 1.6;">Wood stains,
                                            paint colors, polish types, matte or glossy finish</p>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="text-center p-3 h-100"
                                        style="background: var(--linear-5); border-radius: 10px;">
                                        <div
                                            style="width: 70px; height: 70px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                            <i class="fa-solid fa-tree text-primary" style="font-size: 1.8rem;"></i>
                                        </div>
                                        <h5 class="fw-bold mb-2" style="color: var(--black);">Materials</h5>
                                        <p style="font-size: 0.9rem; color: var(--text); line-height: 1.6;">Solid wood,
                                            engineered wood, metal, glass, marble, upholstery fabrics</p>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="text-center p-3 h-100"
                                        style="background: var(--linear-5); border-radius: 10px;">
                                        <div
                                            style="width: 70px; height: 70px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                            <i class="fa-solid fa-screwdriver-wrench text-primary"
                                                style="font-size: 1.8rem;"></i>
                                        </div>
                                        <h5 class="fw-bold mb-2" style="color: var(--black);">Hardware</h5>
                                        <p style="font-size: 0.9rem; color: var(--text); line-height: 1.6;">Handle
                                            styles, knob designs, hinges, drawer slides, casters</p>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="text-center p-3 h-100"
                                        style="background: var(--linear-5); border-radius: 10px;">
                                        <div
                                            style="width: 70px; height: 70px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                            <i class="fa-solid fa-puzzle-piece text-primary"
                                                style="font-size: 1.8rem;"></i>
                                        </div>
                                        <h5 class="fw-bold mb-2" style="color: var(--black);">Details</h5>
                                        <p style="font-size: 0.9rem; color: var(--text); line-height: 1.6;">Piping,
                                            tufting, nailhead trim, carved details, inlay work</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Customisation Options -->

        <!-- FAQ Section -->
        <section style="padding: 60px 0; background-color: var(--white);">
            <div class="container">
                <div class="text-center mb-5 pb-2">
                    <span class="text-uppercase text-primary fw-semibold"
                        style="letter-spacing: 2px; font-size: 14px;">COMMON QUESTIONS</span>
                    <h2 class="fw-bold mt-2 mb-3" style="font-size: 2rem;">Frequently Asked Questions</h2>
                    <p style="color: var(--text); font-size: 1rem; max-width: 700px; margin: 0 auto;">
                        Everything you need to know about customising your furniture with Innovative Homesi
                    </p>
                </div>
                <div class="row">
                    <div class="col-lg-10 offset-lg-1">
                        <div class="accordion" id="customiseFAQ">
                            <div class="accordion-item mb-3"
                                style="border: 1px solid var(--line); border-radius: 8px; overflow: hidden;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq1" aria-expanded="false" aria-controls="faq1"
                                        style="font-weight: 600; color: var(--black); background: var(--white); padding: 20px 25px;">
                                        <i class="fa-solid fa-circle-question text-primary me-3"></i>
                                        Which furniture items can be customised?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#customiseFAQ">
                                    <div class="accordion-body"
                                        style="color: var(--text); padding: 20px 25px; background: var(--bg-9);">
                                        We offer customisation for all our furniture categories including Sofas (3
                                        Seater, L Shape, U Shape, Sofa Cum Bed, Recliner), Chairs (Dining, Accent, Bench
                                        & Ottoman), Tables & Storage (Center Table, Dining Table, Console, Side Table,
                                        Cabinet, TV Unit), Beds & Bedroom Furniture, and Furnishing items like cushions,
                                        table runners, and rugs. Every piece can be tailored to your specifications.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item mb-3"
                                style="border: 1px solid var(--line); border-radius: 8px; overflow: hidden;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2"
                                        style="font-weight: 600; color: var(--black); background: var(--white); padding: 20px 25px;">
                                        <i class="fa-solid fa-circle-question text-primary me-3"></i>
                                        How long does it take to create a custom piece?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#customiseFAQ">
                                    <div class="accordion-body"
                                        style="color: var(--text); padding: 20px 25px; background: var(--bg-9);">
                                        The production timeline typically ranges from 6-12 weeks depending on the
                                        complexity of your design, materials selected, and current order volume. Simple
                                        customisations like fabric changes may be completed faster, while complex
                                        designs with multiple custom features may take the full timeline. We'll provide
                                        you with a specific timeline when you place your order.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item mb-3"
                                style="border: 1px solid var(--line); border-radius: 8px; overflow: hidden;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3"
                                        style="font-weight: 600; color: var(--black); background: var(--white); padding: 20px 25px;">
                                        <i class="fa-solid fa-circle-question text-primary me-3"></i>
                                        Can I see fabric and material samples before ordering?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#customiseFAQ">
                                    <div class="accordion-body"
                                        style="color: var(--text); padding: 20px 25px; background: var(--bg-9);">
                                        Absolutely! We highly recommend viewing physical samples before making your
                                        final selection. Contact our design team or visit our showroom to see and feel
                                        fabric swatches, wood finishes, and material samples. We can also send you
                                        samples for your chosen options so you can see how they look in your space.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item mb-3"
                                style="border: 1px solid var(--line); border-radius: 8px; overflow: hidden;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq4" aria-expanded="false" aria-controls="faq4"
                                        style="font-weight: 600; color: var(--black); background: var(--white); padding: 20px 25px;">
                                        <i class="fa-solid fa-circle-question text-primary me-3"></i>
                                        What is the minimum order quantity for custom pieces?
                                    </button>
                                </h2>
                                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#customiseFAQ">
                                    <div class="accordion-body"
                                        style="color: var(--text); padding: 20px 25px; background: var(--bg-9);">
                                        We offer custom furniture for single pieces with no minimum order requirement.
                                        Whether you need one custom dining chair or a complete living room set, we're
                                        here to help. This allows you to create exactly what you need for your space
                                        without any constraints.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item mb-3"
                                style="border: 1px solid var(--line); border-radius: 8px; overflow: hidden;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq5" aria-expanded="false" aria-controls="faq5"
                                        style="font-weight: 600; color: var(--black); background: var(--white); padding: 20px 25px;">
                                        <i class="fa-solid fa-circle-question text-primary me-3"></i>
                                        Can I modify dimensions to fit my space?
                                    </button>
                                </h2>
                                <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#customiseFAQ">
                                    <div class="accordion-body"
                                        style="color: var(--text); padding: 20px 25px; background: var(--bg-9);">
                                        Yes! Custom dimensions are one of our most popular customisation options. You
                                        can adjust width, length, height, depth, and other measurements to perfectly fit
                                        your space. Our design consultants will work with you to ensure the proportions
                                        remain aesthetically pleasing while meeting your spatial requirements.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item mb-3"
                                style="border: 1px solid var(--line); border-radius: 8px; overflow: hidden;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq6" aria-expanded="false" aria-controls="faq6"
                                        style="font-weight: 600; color: var(--black); background: var(--white); padding: 20px 25px;">
                                        <i class="fa-solid fa-circle-question text-primary me-3"></i>
                                        How much does customisation cost?
                                    </button>
                                </h2>
                                <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#customiseFAQ">
                                    <div class="accordion-body"
                                        style="color: var(--text); padding: 20px 25px; background: var(--bg-9);">
                                        Customisation pricing varies based on the complexity of modifications, materials
                                        selected, and size of the piece. Simple changes like fabric selection may have
                                        minimal cost impact, while extensive modifications like custom dimensions,
                                        premium materials, or special features will have additional charges. Contact us
                                        with your specifications for a detailed quote.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item mb-3"
                                style="border: 1px solid var(--line); border-radius: 8px; overflow: hidden;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq7" aria-expanded="false" aria-controls="faq7"
                                        style="font-weight: 600; color: var(--black); background: var(--white); padding: 20px 25px;">
                                        <i class="fa-solid fa-circle-question text-primary me-3"></i>
                                        What if I change my mind about the design?
                                    </button>
                                </h2>
                                <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#customiseFAQ">
                                    <div class="accordion-body"
                                        style="color: var(--text); padding: 20px 25px; background: var(--bg-9);">
                                        Minor changes can be accommodated if requested before production begins. Once we
                                        start manufacturing your piece, changes become difficult to implement. For
                                        significant modifications after production has started, our team will discuss
                                        available options and any potential impact on timeline and pricing. We recommend
                                        finalising all details during the design consultation phase.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item mb-3"
                                style="border: 1px solid var(--line); border-radius: 8px; overflow: hidden;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq8" aria-expanded="false" aria-controls="faq8"
                                        style="font-weight: 600; color: var(--black); background: var(--white); padding: 20px 25px;">
                                        <i class="fa-solid fa-circle-question text-primary me-3"></i>
                                        Do you offer delivery and installation services?
                                    </button>
                                </h2>
                                <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#customiseFAQ">
                                    <div class="accordion-body"
                                        style="color: var(--text); padding: 20px 25px; background: var(--bg-9);">
                                        Yes, we provide professional delivery services to your location. Our experienced
                                        team ensures your custom furniture arrives safely and in perfect condition.
                                        Professional installation and assembly services are available for an additional
                                        fee. Contact us for delivery options, rates, and scheduling specific to your
                                        area.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item mb-3"
                                style="border: 1px solid var(--line); border-radius: 8px; overflow: hidden;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq9" aria-expanded="false" aria-controls="faq9"
                                        style="font-weight: 600; color: var(--black); background: var(--white); padding: 20px 25px;">
                                        <i class="fa-solid fa-circle-question text-primary me-3"></i>
                                        What warranty do custom pieces come with?
                                    </button>
                                </h2>
                                <div id="faq9" class="accordion-collapse collapse" data-bs-parent="#customiseFAQ">
                                    <div class="accordion-body"
                                        style="color: var(--text); padding: 20px 25px; background: var(--bg-9);">
                                        All custom furniture comes with a 2-year structural warranty covering
                                        manufacturing defects in materials and workmanship. This includes frame
                                        construction, joints, and structural components. Fabric durability and wear vary
                                        by material type; we provide detailed care instructions to help maintain your
                                        piece and ensure its longevity.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item"
                                style="border: 1px solid var(--line); border-radius: 8px; overflow: hidden;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq10" aria-expanded="false" aria-controls="faq10"
                                        style="font-weight: 600; color: var(--black); background: var(--white); padding: 20px 25px;">
                                        <i class="fa-solid fa-circle-question text-primary me-3"></i>
                                        How do I get started with a custom order?
                                    </button>
                                </h2>
                                <div id="faq10" class="accordion-collapse collapse" data-bs-parent="#customiseFAQ">
                                    <div class="accordion-body"
                                        style="color: var(--text); padding: 20px 25px; background: var(--bg-9);">
                                        Getting started is easy! Contact us through our website, visit our showroom, or
                                        call our design team. We'll schedule a consultation to discuss your vision,
                                        space requirements, style preferences, and budget. Our experts will guide you
                                        through fabric selections, design options, and customisation possibilities. Once
                                        you approve the design and quote, we'll begin crafting your perfect piece.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /FAQ Section -->

        <!-- Customisation Enquiry Form -->
        <section style="padding: 80px 0; background: var(--linear-5);">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-5 mb-4 mb-lg-0">
                        <span class="text-uppercase text-primary fw-semibold mb-3 d-block"
                            style="letter-spacing: 2px; font-size: 14px;">GET STARTED</span>
                        <h2 class="fw-bold mb-4" style="font-size: 2.2rem; line-height: 1.3; color: var(--black);">
                            Have a Custom Requirement?
                        </h2>
                        <p class="mb-4" style="font-size: 1.05rem; line-height: 1.8; color: var(--text);">
                            Not sure if your design idea is possible? Share your requirements with us, and our expert
                            team will get back to you with customisation options and solutions.
                        </p>
                        <div class="mb-3">
                            <div class="d-flex align-items-start mb-3">
                                <div
                                    style="width: 50px; height: 50px; background: var(--white); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="fa-solid fa-check text-primary" style="font-size: 1.2rem;"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="fw-bold mb-1" style="color: var(--black);">Free Consultation</h5>
                                    <p style="color: var(--text); margin: 0; font-size: 0.95rem;">Get expert advice on
                                        your custom furniture project</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-3">
                                <div
                                    style="width: 50px; height: 50px; background: var(--white); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="fa-solid fa-clock text-primary" style="font-size: 1.2rem;"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="fw-bold mb-1" style="color: var(--black);">Quick Response</h5>
                                    <p style="color: var(--text); margin: 0; font-size: 0.95rem;">We'll respond to your
                                        enquiry within 24 hours</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start">
                                <div
                                    style="width: 50px; height: 50px; background: var(--white); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="fa-solid fa-lightbulb text-primary" style="font-size: 1.2rem;"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="fw-bold mb-1" style="color: var(--black);">Custom Solutions</h5>
                                    <p style="color: var(--text); margin: 0; font-size: 0.95rem;">Tailored
                                        recommendations for your unique needs</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="p-5 bg-white rounded-3" style="box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
                            <h3 class="fw-bold mb-4" style="color: var(--black);">Submit Your Customisation Request</h3>

                            <div id="customiseMessage" style="display: none;" class="alert mb-4"></div>

                            <form id="customiseEnquiryForm" method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" style="color: var(--black);">Your Name
                                            <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control"
                                            placeholder="Enter your name" required
                                            style="padding: 12px 15px; border: 1px solid var(--line);">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" style="color: var(--black);">Email Address
                                            <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control"
                                            placeholder="your@email.com" required
                                            style="padding: 12px 15px; border: 1px solid var(--line);">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" style="color: var(--black);">Phone Number
                                            <span class="text-danger">*</span></label>
                                        <input type="tel" name="phone" class="form-control"
                                            placeholder="+91 XXXXX XXXXX" required
                                            style="padding: 12px 15px; border: 1px solid var(--line);">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" style="color: var(--black);">Furniture
                                            Type <span class="text-danger">*</span></label>
                                        <select name="furniture_type" class="form-select" required
                                            style="padding: 12px 15px; border: 1px solid var(--line);">
                                            <option value="">Select furniture type</option>
                                            <option value="Sofa">Sofa</option>
                                            <option value="Chair">Chair</option>
                                            <option value="Table">Table & Storage</option>
                                            <option value="Bed">Bed & Bedroom Furniture</option>
                                            <option value="Furnishing">Furnishing & Decor</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold" style="color: var(--black);">Customisation
                                            Requirements <span class="text-danger">*</span></label>
                                        <textarea name="requirements" class="form-control" rows="5"
                                            placeholder="Please describe your customisation requirements in detail. Include dimensions, materials, colors, special features, etc."
                                            required
                                            style="padding: 12px 15px; border: 1px solid var(--line);"></textarea>
                                        <small class="text-muted">Be as detailed as possible to help us understand your
                                            needs better</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" style="color: var(--black);">Preferred
                                            Timeline</label>
                                        <select name="timeline" class="form-select"
                                            style="padding: 12px 15px; border: 1px solid var(--line);">
                                            <option value="">Select timeline</option>
                                            <option value="Urgent (1-2 weeks)">Urgent (1-2 weeks)</option>
                                            <option value="Normal (4-6 weeks)">Normal (4-6 weeks)</option>
                                            <option value="Flexible (6-12 weeks)">Flexible (6-12 weeks)</option>
                                            <option value="No rush">No rush</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" style="color: var(--black);">Budget
                                            Range</label>
                                        <select name="budget" class="form-select"
                                            style="padding: 12px 15px; border: 1px solid var(--line);">
                                            <option value="">Select budget range</option>
                                            <option value="Under ₹25,000"> Under 50,000</option>
                                            <option value="₹25,000 - ₹50,000">₹50,000 - ₹1,00,000</option>
                                            <option value="₹50,000 - ₹1,00,000">₹50,000 - ₹1,00,000</option>
                                            <option value="Above ₹1,00,000">Above ₹1,00,000</option>
                                            <option value="Not decided yet">Not decided yet</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="tf-btn w-100 animate-btn" id="submitBtn"
                                            style="padding: 15px;">
                                            <span id="btnText">Submit Enquiry</span>
                                            <i class="icon icon-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Customisation Enquiry Form -->

        <?php include 'includes/footer.php'; ?>
    </div>

    <!-- Js -->
    <?php include 'includes/scripts.php'; ?>

    <script>
        document.getElementById('customiseEnquiryForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const messageDiv = document.getElementById('customiseMessage');

            // Disable button and show loading
            submitBtn.disabled = true;
            btnText.textContent = 'Submitting...';
            messageDiv.style.display = 'none';

            const formData = new FormData(this);

            try {
                const response = await fetch('ajax/submit-customise-enquiry.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    messageDiv.className = 'alert alert-success mb-4';
                    messageDiv.innerHTML = '<i class="fa-solid fa-circle-check me-2"></i>' + result.message;
                    messageDiv.style.display = 'block';
                    this.reset();

                    // Scroll to message
                    messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    messageDiv.className = 'alert alert-danger mb-4';
                    messageDiv.innerHTML = '<i class="fa-solid fa-circle-exclamation me-2"></i>' + result.message;
                    messageDiv.style.display = 'block';
                }
            } catch (error) {
                messageDiv.className = 'alert alert-danger mb-4';
                messageDiv.innerHTML = '<i class="fa-solid fa-circle-exclamation me-2"></i>An error occurred. Please try again.';
                messageDiv.style.display = 'block';
            } finally {
                submitBtn.disabled = false;
                btnText.textContent = 'Submit Enquiry';
            }
        });
    </script>

</body>

</html>