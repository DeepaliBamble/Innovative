<?php
require_once __DIR__ . '/includes/init.php';
?>
<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">

<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<head>
    <meta charset="utf-8">
    <title>About Us - Innovative Homesi | Your Home, Your Story, Our Craft</title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="Discover the Innovative Homesi story - from a small workshop to a trusted furniture manufacturer. We blend timeless craftsmanship with modern design, creating sustainable sofas and furniture built to last and designed to inspire.">
    <meta name="keywords" content="about innovative homesi, furniture manufacturer story, sofa craftsmanship, sustainable furniture maker, custom furniture company, furniture workshop">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://innovativehomesi.com/about-us">

    <!-- Open Graph Meta Tags -->
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="website">
    <meta property="og:title" content="About Innovative Homesi - Where Comfort Meets Craftsmanship">
    <meta property="og:description" content="From a small workshop to a trusted furniture manufacturer - learn how we create furniture that tells your story.">
    <meta property="og:url" content="https://innovativehomesi.com/about-us">
    <meta property="og:site_name" content="Innovative Homesi">
    <meta property="og:image" content="https://innovativehomesi.com/images/logo/logo.png">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="About Innovative Homesi - Where Comfort Meets Craftsmanship">
    <meta name="twitter:description" content="From a small workshop to a trusted furniture manufacturer - learn how we create furniture that tells your story.">
    <meta name="twitter:image" content="https://innovativehomesi.com/images/logo/logo.png">

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
        /* About Us Page Enhanced Styling */

        /* Hero Section */
        .page-title-image .page_content .heading {
            font-size: 3.5rem;
            line-height: 1.2;
            color: var(--white);
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            margin-bottom: 30px;
        }

        .page-title-image .tf-btn {
            padding: 16px 40px;
            font-size: 16px;
            font-weight: 600;
            background: linear-gradient(135deg, var(--primary) 0%, var(--third) 100%);
            border: none;
            box-shadow: 0 8px 25px rgba(158, 103, 71, 0.3);
            transition: all 0.3s ease;
        }

        .page-title-image .tf-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(158, 103, 71, 0.4);
        }

        /* Section Spacing */
        .flat-spacing {
            padding: 80px 0;
        }

        /* Text Styling */
        .text-uppercase.text-primary,
        .text-uppercase.fw-semibold {
            color: var(--primary) !important;
            font-weight: 700;
            letter-spacing: 3px;
            font-size: 13px;
        }

        .about-us-heading {
            font-size: 2.8rem;
            line-height: 1.3;
            color: var(--text);
            font-weight: 700;
            margin-bottom: 25px;
        }

        .about-us-text {
            font-size: 1.05rem;
            line-height: 1.9;
            color: var(--text);
            margin-bottom: 20px;
        }

        /* Image Styling */
        .about-image-wrapper {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(158, 103, 71, 0.2);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }

        .about-image-wrapper:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(158, 103, 71, 0.3);
        }

        .about-image-wrapper img {
            border-radius: 20px;
        }

        /* Stats Section */
        .stat-card {
            background: var(--white);
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(158, 103, 71, 0.08);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(158, 103, 71, 0.15);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary);
            line-height: 1;
            margin-bottom: 10px;
        }

        .stat-label {
            color: var(--text);
            font-size: 1.05rem;
            margin: 0;
            font-weight: 500;
        }

        /* Mission Section */
        .mission-box {
            background: linear-gradient(135deg, var(--bg-9) 0%, var(--white) 100%);
            padding: 45px;
            border-radius: 20px;
            border: 2px solid var(--line);
            box-shadow: 0 8px 30px rgba(158, 103, 71, 0.1);
        }

        .mission-box h4 {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 25px;
        }

        .mission-box p {
            font-size: 1.05rem;
            line-height: 1.9;
            color: var(--text);
        }

        /* Feature Cards */
        .feature-card {
            background: var(--white);
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 8px 30px rgba(158, 103, 71, 0.08);
            transition: all 0.4s ease;
            height: 100%;
            border: 2px solid transparent;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 50px rgba(158, 103, 71, 0.15);
            border-color: var(--primary);
        }

        .feature-icon-wrapper {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, var(--bg-10) 0%, var(--bg-9) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon-wrapper {
            background: linear-gradient(135deg, var(--primary) 0%, var(--third) 100%);
            transform: scale(1.1);
        }

        .feature-icon-wrapper i {
            font-size: 40px;
            color: var(--primary);
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon-wrapper i {
            color: var(--white);
        }

        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 15px;
        }

        .feature-card p {
            color: var(--text-2);
            line-height: 1.8;
            margin: 0;
        }

        /* Testimonial Section */
        .testimonial-V05 {
            background: var(--white);
            padding: 45px 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(158, 103, 71, 0.1);
            border-left: 5px solid var(--primary);
        }

        .testimonial-V05 .tes_icon i {
            font-size: 50px;
            color: var(--primary);
            opacity: 0.2;
        }

        .testimonial-V05 .author-name {
            color: var(--text);
            font-weight: 700;
            font-size: 1.3rem;
        }

        .testimonial-V05 .author-verified {
            color: var(--success);
            font-size: 20px;
        }

        .testimonial-V05 .rate_wrap i {
            color: var(--third);
            font-size: 18px;
        }

        .testimonial-V05 .tes_text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text);
            font-style: italic;
        }

        .slider-btn-thumbs .btn-thumbs {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid transparent;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .slider-btn-thumbs .btn-thumbs:hover,
        .slider-btn-thumbs .btn-thumbs.active {
            border-color: var(--primary);
            transform: scale(1.1);
            box-shadow: 0 5px 20px rgba(158, 103, 71, 0.3);
        }

        /* Gallery Images */
        .gallery-grid-image {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(158, 103, 71, 0.12);
            transition: all 0.3s ease;
        }

        .gallery-grid-image:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(158, 103, 71, 0.2);
        }

        /* Responsive */
        @media (max-width: 991px) {
            .page-title-image .page_content .heading {
                font-size: 2.5rem;
            }

            .flat-spacing {
                padding: 60px 0;
            }

            .about-us-heading {
                font-size: 2.2rem;
            }

            .stat-number {
                font-size: 2.5rem;
            }

            .mission-box {
                padding: 35px 25px;
            }

            .feature-card {
                padding: 35px 25px;
            }
        }

        @media (max-width: 767px) {
            .page-title-image .page_content .heading {
                font-size: 2rem;
            }

            .flat-spacing {
                padding: 40px 0;
            }

            .about-us-heading {
                font-size: 1.8rem;
            }

            .about-us-text {
                font-size: 1rem;
            }

            .stat-number {
                font-size: 2rem;
            }

            .mission-box {
                padding: 30px 20px;
            }

            .feature-card {
                padding: 30px 20px;
                margin-bottom: 20px;
            }

            .testimonial-V05 {
                padding: 30px 20px;
            }
        }

        /* Process Section Styles */
        .process-card {
            background: var(--white);
            border-radius: 20px;
            padding: 40px 25px;
            box-shadow: 0 8px 30px rgba(158, 103, 71, 0.08);
            transition: all 0.4s ease;
            position: relative;
            border: 2px solid transparent;
        }

        .process-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 50px rgba(158, 103, 71, 0.15);
            border-color: var(--primary);
        }

        .process-number {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 3rem;
            font-weight: 800;
            color: rgba(158, 103, 71, 0.1);
            line-height: 1;
        }

        .process-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--bg-10) 0%, var(--bg-9) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            transition: all 0.3s ease;
        }

        .process-card:hover .process-icon {
            background: linear-gradient(135deg, var(--primary) 0%, var(--third) 100%);
            transform: scale(1.1);
        }

        .process-icon i {
            font-size: 32px;
            color: var(--primary);
            transition: all 0.3s ease;
        }

        .process-card:hover .process-icon i {
            color: var(--white);
        }

        .process-card h4 {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 12px;
        }

        .process-card p {
            color: var(--text-2);
            line-height: 1.7;
            margin: 0;
            font-size: 0.95rem;
        }

        /* Why Choose Us Styles */
        .why-choose-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 30px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            height: 100%;
        }

        .why-choose-card:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-5px);
        }

        .why-choose-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .why-choose-icon i {
            font-size: 24px;
            color: var(--white);
        }

        .why-choose-card h4 {
            color: var(--white);
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .why-choose-card p {
            color: rgba(255, 255, 255, 0.85);
            line-height: 1.7;
            margin: 0;
            font-size: 0.95rem;
        }

        /* Timeline Styles */
        .timeline-wrapper {
            position: relative;
            max-width: 800px;
            margin: 0 auto;
        }

        .timeline-wrapper::before {
            content: '';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 3px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary), var(--third));
            border-radius: 3px;
        }

        .timeline-item {
            display: flex;
            justify-content: flex-end;
            padding-right: 50%;
            position: relative;
            margin-bottom: 40px;
        }

        .timeline-item:nth-child(even) {
            justify-content: flex-start;
            padding-right: 0;
            padding-left: 50%;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 20px;
            height: 20px;
            background: var(--primary);
            border-radius: 50%;
            border: 4px solid var(--white);
            box-shadow: 0 0 0 4px rgba(158, 103, 71, 0.2);
        }

        .timeline-year {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            top: -30px;
            background: var(--primary);
            color: var(--white);
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .timeline-content {
            background: var(--white);
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(158, 103, 71, 0.1);
            max-width: 350px;
            margin: 20px 30px 0;
            border-left: 4px solid var(--primary);
        }

        .timeline-item:nth-child(even) .timeline-content {
            border-left: none;
            border-right: 4px solid var(--primary);
        }

        .timeline-content h4 {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 10px;
        }

        .timeline-content p {
            color: var(--text-2);
            line-height: 1.7;
            margin: 0;
            font-size: 0.95rem;
        }

        /* Values Section Styles */
        .values-list {
            margin-top: 30px;
        }

        .value-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
            padding: 20px;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(158, 103, 71, 0.08);
            transition: all 0.3s ease;
        }

        .value-item:hover {
            transform: translateX(10px);
            box-shadow: 0 8px 25px rgba(158, 103, 71, 0.12);
        }

        .value-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--third) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-right: 20px;
        }

        .value-icon i {
            font-size: 20px;
            color: var(--white);
        }

        .value-content h5 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 8px;
        }

        .value-content p {
            color: var(--text-2);
            line-height: 1.6;
            margin: 0;
            font-size: 0.95rem;
        }

        /* CTA Section Styles */
        .cta-section {
            background: linear-gradient(135deg, var(--bg-10) 0%, var(--bg-9) 100%);
        }

        .cta-wrapper {
            max-width: 700px;
            margin: 0 auto;
            padding: 40px;
        }

        .cta-heading {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 20px;
        }

        .cta-text {
            font-size: 1.1rem;
            color: var(--text-2);
            margin-bottom: 30px;
            line-height: 1.8;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .cta-buttons .btn-fill {
            background: linear-gradient(135deg, var(--primary) 0%, var(--third) 100%);
            color: var(--white);
            border: none;
        }

        .cta-buttons .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .cta-buttons .btn-outline:hover {
            background: var(--primary);
            color: var(--white);
        }

        .cta-contact p {
            color: var(--text);
            font-size: 1.1rem;
        }

        .cta-contact a {
            color: var(--primary);
            font-weight: 700;
            text-decoration: none;
        }

        .cta-contact a:hover {
            text-decoration: underline;
        }

        /* Responsive for new sections */
        @media (max-width: 991px) {
            .timeline-wrapper::before {
                left: 30px;
            }

            .timeline-item,
            .timeline-item:nth-child(even) {
                padding-left: 80px;
                padding-right: 0;
                justify-content: flex-start;
            }

            .timeline-item::before {
                left: 30px;
            }

            .timeline-year {
                left: 30px;
            }

            .timeline-content,
            .timeline-item:nth-child(even) .timeline-content {
                border-left: 4px solid var(--primary);
                border-right: none;
                margin-left: 0;
                max-width: 100%;
            }

            .cta-heading {
                font-size: 2rem;
            }
        }

        @media (max-width: 767px) {
            .process-card {
                padding: 30px 20px;
                margin-bottom: 20px;
            }

            .process-number {
                font-size: 2rem;
            }

            .why-choose-card {
                padding: 25px 20px;
            }

            .timeline-wrapper::before {
                left: 20px;
            }

            .timeline-item,
            .timeline-item:nth-child(even) {
                padding-left: 60px;
            }

            .timeline-item::before {
                left: 20px;
                width: 16px;
                height: 16px;
            }

            .timeline-year {
                left: 20px;
                font-size: 0.8rem;
                padding: 4px 12px;
            }

            .timeline-content {
                padding: 20px;
            }

            .value-item {
                flex-direction: column;
                text-align: center;
            }

            .value-icon {
                margin-right: 0;
                margin-bottom: 15px;
            }

            .cta-heading {
                font-size: 1.6rem;
            }

            .cta-text {
                font-size: 1rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .cta-buttons .tf-btn {
                width: 100%;
                max-width: 280px;
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
        <section class="page-title-image">
            <div class="page_image overflow-hidden">
                <img class="lazyload ani-zoom" src="images/section/about-us.jpg" data-src="images/section/about-us.jpg" alt="Banner">
            </div>
            <div class="page_content">
                <div class="container">
                    <div class="content">
                        <h1 class="heading fw-bold">
                            WHERE COMFORT MEETS <br class="d-none d-sm-block">
                            CRAFTSMANSHIP
                        </h1>
                        <a href="shop.php" class="tf-btn animate-btn">
                            Our Products
                            <i class="icon icon-caret-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Page Title -->
        <!-- Hero Section -->
        <section class="flat-spacing bg-white">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <span class="text-uppercase text-primary fw-semibold mb-3 d-block">INNOVATIVE HOMESI</span>
                        <h2 class="about-us-heading">Your Home. Your Story. <br>Our Craft.</h2>
                        <p class="about-us-text">
                            At InnovativeHomesi, we take pride in crafting every piece of furniture in our own manufacturing unit. From design to finishing, every step is handled in-house by our skilled team, ensuring complete quality control and consistent craftsmanship. <br> <br> We do not outsource our production or rely on third-party manufacturers, which allows us to maintain the highest standards and deliver reliable products. Before dispatch, every item undergoes a strict quality inspection to ensure it meets our durability, comfort, and finish standards. This commitment reflects our dedication to trust, excellence, and complete customer satisfaction.</p>
                        
                        <a href="shop.php" class="tf-btn animate-btn">
                            Explore Our Collection
                            <i class="icon icon-caret-right"></i>
                        </a>
                    </div>
                    <div class="col-lg-6">
                        <div class="about-image-wrapper">
                            <img class="lazyload img-fluid" src="images/imgs/4.jpeg" data-src="images/imgs/4.jpeg" alt="Innovative Homesi Furniture">
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Hero Section -->
        <!-- What Sets Us Apart -->
        <section class="flat-spacing" style="background-color: var(--bg-body);">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0">
                        <div class="ps-lg-5">
                            <span class="text-uppercase fw-semibold mb-3 d-block">WHAT SETS US APART</span>
                            <h2 class="about-us-heading">Transform Your Home With Our Premium Furniture</h2>
                            <p class="about-us-text">
                                Blending Elegance, Comfort, And Durability. Enjoy Timeless Style Designed For Lasting Beauty And Functionality.
                            </p>
                            <p class="about-us-text">
                                What sets us apart is our dedication to creating furniture that's built to last, designed to inspire, and made to feel like home. Whether it's a custom piece tailored to your vision or a signature design from our collection, we're here to help you create spaces that truly reflect who you are.
                            </p>
                            <div class="row mt-5">
                                <div class="col-6">
                                    <div class="stat-card mb-4">
                                        <h3 class="stat-number">15+</h3>
                                        <p class="stat-label">Years Experience</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-card mb-4">
                                        <h3 class="stat-number">5000+</h3>
                                        <p class="stat-label">Happy Customers</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 order-lg-1">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="gallery-grid-image">
                                   <img class="lazyload img-fluid" src="images/imgs/6.jpeg" data-src="images/imgs/6.jpeg" alt="Furniture 2" style="height: 300px; object-fit: cover;">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="gallery-grid-image">
                                    <img class="lazyload img-fluid" src="images/imgs/2.jpeg" data-src="images/imgs/2.jpeg" alt="Furniture 1" style="height: 300px; object-fit: cover;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /What Sets Us Apart -->
        <!-- Our Aim -->
        <section class="flat-spacing bg-white">
            <div class="container">
                <div class="text-center mb-5">
                    <span class="text-uppercase fw-semibold mb-3 d-block">OUR MISSION</span>
                    <h2 class="about-us-heading">Our Aim</h2>
                    <p class="mx-auto about-us-text" style="max-width: 700px;">
                        Redefining The Way You Experience Furniture
                    </p>
                </div>
                <div class="row justify-content-center mb-5">
                    <div class="col-lg-10">
                        <div class="mission-box text-center">
                            <h4>Exceptional Craftsmanship Meets Sustainable Practices</h4>
                            <p class="mb-3">
                                Our aim is to redefine the way you experience furniture. We strive to create furniture that seamlessly blends comfort, functionality, and style, ensuring every piece enhances your home and lifestyle. Our mission is to deliver exceptional craftsmanship, using high-quality materials and sustainable practices, to create furniture that stands the test of time.
                            </p>
                            <p>
                                We aim to inspire you with designs that reflect your unique personality while providing the utmost comfort and durability. Whether it's a cozy sofa for family gatherings or a statement piece that elevates your decor, we are committed to helping you create spaces that feel truly yours. At Innovative Homesi, we don't just make furniture—we craft experiences that turn houses into homes.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="row g-4">
                    <!-- Feature 1 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="feature-card text-center">
                            <div class="feature-icon-wrapper">
                                <i class="icon icon-eco"></i>
                            </div>
                            <h3>Sustainable Materials</h3>
                            <p>Eco-friendly practices with high-quality, sustainable materials that protect our environment</p>
                        </div>
                    </div>
                    <!-- Feature 2 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="feature-card text-center">
                            <div class="feature-icon-wrapper">
                                <i class="icon icon-star"></i>
                            </div>
                            <h3>Premium Craftsmanship</h3>
                            <p>Built to last with exceptional attention to detail and expert craftsmanship in every piece</p>
                        </div>
                    </div>
                    <!-- Feature 3 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="feature-card text-center">
                            <div class="feature-icon-wrapper">
                                <i class="icon icon-note-pencil"></i>
                            </div>
                            <h3>Custom Designs</h3>
                            <p>Personalized furniture tailored to your unique vision and lifestyle needs</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Brand Story -->
        <!-- Customer Review -->
        <section class="flat-spacing" style="background-color: var(--bg-body);">
            <div class="container">
                <div class="text-center mb-5">
                    <span class="text-uppercase fw-semibold mb-3 d-block">TESTIMONIALS</span>
                    <h2 class="about-us-heading">What Our Customers Say</h2>
                    <p class="mx-auto about-us-text" style="max-width: 700px;">
                        Real Reviews From Real Homes
                    </p>
                </div>
                <div class="row">
                    <div class="col-lg-8 offset-lg-2">
                        <div class="slider-thumb-wrap">
                            <div dir="ltr" class="swiper tf-swiper slider-content-thumb">
                                <div class="swiper-wrapper">
                                    <!-- item 1 -->
                                    <div class="swiper-slide">
                                        <div class="testimonial-V05">
                                            <div class="tes_icon">
                                                <i class="icon icon-block-quote"></i>
                                            </div>
                                            <div class="tes_author">
                                                <p class="author-name h4">Brooklyn Simmons</p>
                                                <i class="author-verified icon-check-circle"></i>
                                            </div>
                                            <div class="rate_wrap">
                                                <i class="icon-star text-star"></i>
                                                <i class="icon-star text-star"></i>
                                                <i class="icon-star text-star"></i>
                                                <i class="icon-star text-star"></i>
                                                <i class="icon-star text-star"></i>
                                            </div>
                                            <p class="tes_text h4">
                                                "A sofa is more than just a piece of furniture—it's the heart of your home, where memories are made, conversations flow, and comfort meets style. Invest in a sofa that reflects your personality and stands the test of time."
                                            </p>
                                        </div>
                                    </div>
                                    <!-- item 2 -->
                                    <div class="swiper-slide">
                                        <div class="testimonial-V05">
                                            <div class="tes_icon">
                                                <i class="icon icon-block-quote"></i>
                                            </div>
                                            <div class="tes_author">
                                                <p class="author-name h4">Mas Shin</p>
                                                <i class="author-verified icon-check-circle"></i>
                                            </div>
                                            <div class="rate_wrap">
                                                <i class="icon-star text-star"></i>
                                                <i class="icon-star text-star"></i>
                                                <i class="icon-star text-star"></i>
                                                <i class="icon-star text-star"></i>
                                                <i class="icon-star text-star"></i>
                                            </div>
                                            <p class="tes_text h4">
                                                "The quality of the sofa we ordered from Innovative Homesi is exceptional. The craftsmanship and attention to detail are evident in every stitch. It's not just furniture—it's a work of art that has transformed our living room."
                                            </p>
                                        </div>
                                    </div>
                                    <!-- item 3 -->
                                    <div class="swiper-slide">
                                        <div class="testimonial-V05">
                                            <div class="tes_icon">
                                                <i class="icon icon-block-quote"></i>
                                            </div>
                                            <div class="tes_author">
                                                <p class="author-name h4">Sil Vox</p>
                                                <i class="author-verified icon-check-circle"></i>
                                            </div>
                                            <div class="rate_wrap">
                                                <i class="icon-star text-star"></i>
                                                <i class="icon-star text-star"></i>
                                                <i class="icon-star text-star"></i>
                                                <i class="icon-star text-star"></i>
                                                <i class="icon-star text-star"></i>
                                            </div>
                                            <p class="tes_text h4">
                                                "We worked with Innovative Homesi to create a custom dining set, and we couldn't be happier with the results. The team was professional, the design process was seamless, and the final product exceeded our expectations. Truly built to last!"
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="slider-btn-thumbs">
                                <!-- Btn 1 -->
                                <div class="btn-thumbs active">
                                    <img src="images/avatar/avatar-1.jpg" alt="Avatar">
                                </div>
                                <!-- Btn 2 -->
                                <div class="btn-thumbs">
                                    <img src="images/avatar/avatar-2.jpg" alt="Avatar">
                                </div>
                                <!-- Btn 3 -->
                                <div class="btn-thumbs">
                                    <img src="images/avatar/avatar-3.jpg" alt="Avatar">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Customer Review -->

        <!-- Our Process Section -->
        <section class="flat-spacing bg-white">
            <div class="container">
                <div class="text-center mb-5">
                    <span class="text-uppercase fw-semibold mb-3 d-block">HOW WE WORK</span>
                    <h2 class="about-us-heading">Our Process</h2>
                    <p class="mx-auto about-us-text" style="max-width: 700px;">
                        From concept to creation, we ensure every step is handled with care and precision
                    </p>
                </div>
                <div class="row g-4">
                    <!-- Step 1 -->
                    <div class="col-lg-3 col-md-6">
                        <div class="process-card text-center">
                            <div class="process-number">01</div>
                            <div class="process-icon">
                                <i class="fa-solid fa-comments"></i>
                            </div>
                            <h4>Consultation</h4>
                            <p>We listen to your needs, understand your style, and discuss your vision for the perfect furniture piece.</p>
                        </div>
                    </div>
                    <!-- Step 2 -->
                    <div class="col-lg-3 col-md-6">
                        <div class="process-card text-center">
                            <div class="process-number">02</div>
                            <div class="process-icon">
                                <i class="fa-solid fa-pencil-ruler"></i>
                            </div>
                            <h4>Design</h4>
                            <p>Our designers create detailed sketches and 3D models, ensuring every detail matches your expectations.</p>
                        </div>
                    </div>
                    <!-- Step 3 -->
                    <div class="col-lg-3 col-md-6">
                        <div class="process-card text-center">
                            <div class="process-number">03</div>
                            <div class="process-icon">
                                <i class="fa-solid fa-hammer"></i>
                            </div>
                            <h4>Crafting</h4>
                            <p>Skilled artisans bring your design to life using premium materials and time-honored techniques.</p>
                        </div>
                    </div>
                    <!-- Step 4 -->
                    <div class="col-lg-3 col-md-6">
                        <div class="process-card text-center">
                            <div class="process-number">04</div>
                            <div class="process-icon">
                                <i class="fa-solid fa-truck"></i>
                            </div>
                            <h4>Delivery</h4>
                            <p>We carefully deliver and install your furniture, ensuring it's perfectly placed in your home.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Our Process Section -->

        <!-- Why Choose Us Section -->
        <section class="flat-spacing" style="background: linear-gradient(135deg, #9e6747 0%, #7d5139 100%);">
            <div class="container">
                <div class="text-center mb-5">
                    <span class="text-uppercase fw-semibold text-white mb-3 d-block" >WHY CHOOSE US</span>
                    <h2 class="about-us-heading text-white">The Innovative Homesi Difference</h2>
                    <p class="mx-auto about-us-text text-white" style="max-width: 700px; opacity: 0.9;">
                        Experience the perfect blend of quality, craftsmanship, and customer service
                    </p>
                </div>
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="why-choose-card">
                            <div class="why-choose-icon">
                                <i class="fa-solid fa-award"></i>
                            </div>
                            <h4>Quality Guaranteed</h4>
                            <p>Every piece comes with our quality promise. We use only the finest materials and rigorous quality checks.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="why-choose-card">
                            <div class="why-choose-icon">
                                <i class="fa-solid fa-hand-holding-heart"></i>
                            </div>
                            <h4>Handcrafted With Love</h4>
                            <p>Each furniture piece is handcrafted by skilled artisans who take pride in their work and attention to detail.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="why-choose-card">
                            <div class="why-choose-icon">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <h4>Timely Delivery</h4>
                            <p>We respect your time. Our efficient process ensures your furniture arrives when promised.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="why-choose-card">
                            <div class="why-choose-icon">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <h4>5-Year Warranty</h4>
                            <p>We stand behind our products with a comprehensive 5-year warranty on all furniture pieces.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="why-choose-card">
                            <div class="why-choose-icon">
                                <i class="fa-solid fa-indian-rupee-sign"></i>
                            </div>
                            <h4>Competitive Pricing</h4>
                            <p>Premium quality doesn't have to break the bank. We offer fair prices without compromising on quality.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="why-choose-card">
                            <div class="why-choose-icon">
                                <i class="fa-solid fa-headset"></i>
                            </div>
                            <h4>Dedicated Support</h4>
                            <p>Our customer service team is always ready to help you with any questions or concerns.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Why Choose Us Section -->

        <!-- Our Journey/Timeline Section -->
        <section class="flat-spacing bg-white">
            <div class="container">
                <div class="text-center mb-5">
                    <span class="text-uppercase fw-semibold mb-3 d-block">OUR JOURNEY</span>
                    <h2 class="about-us-heading">Milestones That Define Us</h2>
                    <p class="mx-auto about-us-text" style="max-width: 700px;">
                        A look at the key moments that shaped Innovative Homesi
                    </p>
                </div>
                <div class="timeline-wrapper">
                    <div class="timeline-item">
                        <div class="timeline-year">2009</div>
                        <div class="timeline-content">
                            <h4>The Beginning</h4>
                            <p>Started as a small workshop with a passion for creating quality furniture and a dream to transform homes.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-year">2014</div>
                        <div class="timeline-content">
                            <h4>Big Manufacturing Unit</h4>
                            <p>Opened our first showroom and expanded our team to include skilled artisans and designers.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-year">2018</div>
                        <div class="timeline-content">
                            <h4>Custom Design Studio</h4>
                            <p>Launched our custom design service, allowing customers to create their dream furniture pieces.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-year">2022</div>
                        <div class="timeline-content">
                            <h4>Digital Transformation</h4>
                            <p>Launched our online platform, making it easier for customers across India to access our products.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-year">2024</div>
                        <div class="timeline-content">
                            <h4>5000+ Happy Homes</h4>
                            <p>Celebrated the milestone of furnishing over 5000 homes with our premium furniture.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Our Journey Section -->

        <!-- Our Values Section -->
        <section class="flat-spacing" style="background-color: var(--bg-body);">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <span class="text-uppercase fw-semibold mb-3 d-block">OUR VALUES</span>
                        <h2 class="about-us-heading">What We Stand For</h2>
                        <div class="values-list">
                            <div class="value-item">
                                <div class="value-icon">
                                    <i class="fa-solid fa-gem"></i>
                                </div>
                                <div class="value-content">
                                    <h5>Quality First</h5>
                                    <p>We never compromise on quality. Every material, every stitch, every finish is carefully chosen and executed.</p>
                                </div>
                            </div>
                            <div class="value-item">
                                <div class="value-icon">
                                    <i class="fa-solid fa-leaf"></i>
                                </div>
                                <div class="value-content">
                                    <h5>Sustainability</h5>
                                    <p>We're committed to eco-friendly practices, using sustainable materials and minimizing our environmental footprint.</p>
                                </div>
                            </div>
                            <div class="value-item">
                                <div class="value-icon">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <div class="value-content">
                                    <h5>Customer Focus</h5>
                                    <p>Your satisfaction is our priority. We listen, adapt, and go the extra mile to exceed your expectations.</p>
                                </div>
                            </div>
                            <div class="value-item">
                                <div class="value-icon">
                                    <i class="fa-solid fa-lightbulb"></i>
                                </div>
                                <div class="value-content">
                                    <h5>Innovation</h5>
                                    <p>We constantly evolve, embracing new designs and technologies while honoring traditional craftsmanship.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="about-image-wrapper">
                            <img class="lazyload img-fluid" src="images/imgs/3.jpeg" data-src="images/imgs/3.jpeg" alt="Our Values">
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Our Values Section -->

        <!-- Call to Action Section -->
        <section class="flat-spacing cta-section">
            <div class="container">
                <div class="cta-wrapper text-center">
                    <h2 class="cta-heading">Ready to Transform Your Space?</h2>
                    <p class="cta-text">Let's create something beautiful together. Visit our showroom or browse our collection online.</p>
                    <div class="cta-buttons">
                        <a href="shop.php" class="tf-btn animate-btn btn-fill">
                            Browse Collection
                            <i class="icon icon-caret-right"></i>
                        </a>
                        <a href="contact-us.php" class="tf-btn animate-btn btn-outline">
                            Contact Us
                            <i class="icon icon-caret-right"></i>
                        </a>
                    </div>
                    <div class="cta-contact mt-4">
                        <p><i class="fa-solid fa-phone"></i> Call us: <a href="tel:+919892827404">+91 9892827404</a></p>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Call to Action Section -->

        <?php include 'includes/footer.php'; ?>
    <!-- Javascript -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/swiper-bundle.min.js"></script>
    <script src="js/carousel.js"></script>
    <script src="js/bootstrap-select.min.js"></script>
    <script src="js/lazysize.min.js"></script>
    <script src="js/wow.min.js"></script>
    <script src="js/parallaxie.js"></script>
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
</body>

</html>


