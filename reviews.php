<?php
require_once __DIR__ . '/includes/init.php';
?>
<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">

<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<head>
    <meta charset="utf-8">
    <title>Customer Reviews - Innovative Homesi | Real Stories From Real Customers</title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="Read authentic customer reviews and testimonials about Innovative Homesi furniture. Discover why thousands of satisfied customers trust us for their home décor needs.">
    <meta name="keywords" content="customer reviews, furniture reviews, testimonials, customer feedback, innovative homesi reviews, furniture ratings">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://innovativehomesi.com/reviews">

    <!-- Open Graph Meta Tags -->
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Customer Reviews - Innovative Homesi | Real Stories From Real Customers">
    <meta property="og:description" content="Read authentic customer reviews and testimonials about our premium furniture and craftsmanship.">
    <meta property="og:url" content="https://innovativehomesi.com/reviews">
    <meta property="og:site_name" content="Innovative Homesi">
    <meta property="og:image" content="https://innovativehomesi.com/images/logo/logo.png">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Customer Reviews - Innovative Homesi | Real Stories From Real Customers">
    <meta name="twitter:description" content="Read authentic customer reviews and testimonials about our premium furniture.">
    <meta name="twitter:image" content="https://innovativehomesi.com/images/logo/logo.png">

    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=GFS+Neohellenic:ital,wght@0,400;0,700;1,400;1,700&family=Luxurious+Roman&family=Maven+Pro:wght@400..900&display=swap" rel="stylesheet">
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
        .review-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 24px;
        }

        .review-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.15);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .reviewer-info {
            display: flex;
            gap: 12px;
        }

        .reviewer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #d4a574;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 20px;
            flex-shrink: 0;
        }

        .reviewer-details h4 {
            margin: 0 0 4px 0;
            font-size: 1rem;
        }

        .reviewer-details p {
            margin: 0;
            font-size: 0.9rem;
            color: #999;
        }

        .review-rating {
            display: flex;
            gap: 4px;
        }

        .review-rating i {
            font-size: 18px;
            color: #EF9122;
        }

        .review-title {
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 12px;
            color: #333;
        }

        .review-text {
            color: #666;
            line-height: 1.8;
            margin-bottom: 16px;
        }

        .review-product {
            display: inline-block;
            background: #f8f8f8;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            color: #d4a574;
            font-weight: 500;
        }

        .review-verified {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.85rem;
            color: #28A745;
            margin-left: 12px;
        }

        .filter-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 40px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
        }

        .filter-group {
            margin-bottom: 20px;
        }

        .filter-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }

        .rating-filter {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .rating-btn {
            padding: 8px 16px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .rating-btn:hover,
        .rating-btn.active {
            border-color: #d4a574;
            background: #d4a574;
            color: white;
        }

        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #d4a574;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #666;
            font-size: 0.95rem;
        }

        .no-reviews {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .no-reviews i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .search-box {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color 0.3s ease;
        }

        .search-box:focus {
            outline: none;
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
        <section class="page-title-image">
            <div class="page_image overflow-hidden">
                <img class="lazyload ani-zoom" src="images/section/about-us.jpg" data-src="images/section/about-us.jpg" alt="Customer Reviews">
            </div>
            <div class="page_content">
                <div class="container">
                    <div class="content">
                        <h1 class="heading fw-bold">
                            WHAT OUR CUSTOMERS <br class="d-none d-sm-block">
                            ARE SAYING
                        </h1>
                        <p class="sub-heading">Discover why thousands of customers love Innovative Homesi</p>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Page Title -->

        <!-- Stats Section -->
        <section class="flat-spacing bg-white">
            <div class="container">
                <div class="stats-section">
                    <div class="stat-card">
                        <div class="stat-number">5000+</div>
                        <div class="stat-label">Happy Customers</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">4.8/5</div>
                        <div class="stat-label">Average Rating</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">98%</div>
                        <div class="stat-label">Satisfaction Rate</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">2500+</div>
                        <div class="stat-label">Verified Reviews</div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Stats Section -->

        <!-- Filters Section -->
        <section class="flat-spacing" style="background-color: #f8f8f8;">
            <div class="container">
                <div class="filter-section">
                    <div class="row">
                        <div class="col-lg-6 mb-3 mb-lg-0">
                            <div class="filter-group">
                                <label for="searchReviews">Search Reviews</label>
                                <input type="text" id="searchReviews" class="search-box" placeholder="Search by customer name, product, or keyword...">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="filter-group">
                                <label>Filter by Rating</label>
                                <div class="rating-filter">
                                    <button class="rating-btn active" data-rating="all">All</button>
                                    <button class="rating-btn" data-rating="5">
                                        <i class="icon icon-star" style="color: #EF9122;"></i> 5 Stars
                                    </button>
                                    <button class="rating-btn" data-rating="4">
                                        <i class="icon icon-star" style="color: #EF9122;"></i> 4 Stars
                                    </button>
                                    <button class="rating-btn" data-rating="3">
                                        <i class="icon icon-star" style="color: #EF9122;"></i> 3 Stars
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reviews Grid -->
                <div id="reviewsContainer">
                    <!-- Reviews will be loaded here dynamically -->
                </div>
            </div>
        </section>
        <!-- /Reviews Section -->


        <?php include 'includes/footer.php'; ?>
    
    <!-- Toolbar -->
    <div class="tf-toolbar-bottom">
        <div class="toolbar-item">
            <a href="shop-default.php">
                <span class="toolbar-icon">
                    <i class="icon icon-storefront"></i>
                </span>
                <span class="toolbar-label">Shop</span>
            </a>
        </div>
        <div class="toolbar-item">
            <a href="#search" data-bs-toggle="modal">
                <span class="toolbar-icon">
                    <i class="icon icon-magnifying-glass"></i>
                </span>
                <span class="toolbar-label">Search</span>
            </a>
        </div>
        <div class="toolbar-item">
            <a href="account-page.php">
                <span class="toolbar-icon">
                    <i class="icon icon-user"></i>
                </span>
                <span class="toolbar-label">Account</span>
            </a>
        </div>
        <div class="toolbar-item">
            <a href="wishlist.php">
                <span class="toolbar-icon">
                    <i class="icon icon-heart"></i>
                    <span class="toolbar-count">7</span>
                </span>
                <span class="toolbar-label">Wishlist</span>
            </a>
        </div>
        <div class="toolbar-item">
            <a href="view-cart.php">
                <span class="toolbar-icon">
                    <i class="icon icon-shopping-cart-simple"></i>
                    <span class="toolbar-count">24</span>
                </span>
                <span class="toolbar-label">Cart</span>
            </a>
        </div>
    </div>
    <!-- /Toolbar -->

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
        // Sample customer reviews data
        const reviews = [
            {
                id: 1,
                name: "Aditya Verma",
                date: "November 28, 2024",
                rating: 5,
                title: "Exceptional Quality and Craftsmanship",
                text: "A sofa is more than just a piece of furniture—it's the heart of your home, where memories are made, conversations flow, and comfort meets style. Innovative Homesi delivered exactly that. The quality is outstanding, and every detail shows their commitment to excellence.",
                product: "Premium Sofa",
                verified: true,
                avatar: "A"
            },
            {
                id: 2,
                name: "Sneha Kapoor",
                date: "November 25, 2024",
                rating: 5,
                title: "Best Product Quality",
                text: "The quality of the sofa we ordered from Innovative Homesi is exceptional. The craftsmanship and attention to detail are evident in every stitch. It's not just furniture—it's a work of art that has transformed our living room. Highly recommend!",
                product: "Custom Sofa",
                verified: true,
                avatar: "S"
            },
            {
                id: 3,
                name: "Karthik Nair",
                date: "November 20, 2024",
                rating: 5,
                title: "Perfect for My Home",
                text: "I was searching for quality furniture that didn't compromise on style. Innovative Homesi exceeded my expectations. The pieces arrived in perfect condition, and the setup was hassle-free. Customer service was incredibly helpful throughout the process.",
                product: "Dining Set",
                verified: true,
                avatar: "K"
            },
            {
                id: 4,
                name: "Manish Agarwal",
                date: "November 18, 2024",
                rating: 4,
                title: "Great Value for Money",
                text: "For the price point, the quality is outstanding. My family loves the new living room setup. The only minor issue was a small scratch on one piece, but customer service resolved it immediately. Very satisfied overall.",
                product: "Living Room Set",
                verified: true,
                avatar: "M"
            },
            {
                id: 5,
                name: "Pooja Bhatt",
                date: "November 15, 2024",
                rating: 5,
                title: "Sustainable and Beautiful",
                text: "I'm impressed by Innovative Homesi's commitment to sustainability. The furniture not only looks beautiful in my home but also gives me peace of mind knowing it's made with eco-friendly materials. Worth every penny!",
                product: "Eco-Friendly Chair",
                verified: true,
                avatar: "P"
            },
            {
                id: 6,
                name: "Deepak Chopra",
                date: "November 12, 2024",
                rating: 5,
                title: "Excellent Delivery Experience",
                text: "From ordering to delivery, everything was smooth. The furniture arrived on time and in excellent condition. The pieces are exactly as shown on the website. I've already recommended Innovative Homesi to several friends.",
                product: "Coffee Table",
                verified: true,
                avatar: "D"
            },
            {
                id: 7,
                name: "Ritu Saxena",
                date: "November 10, 2024",
                rating: 4,
                title: "Beautiful Design, Quick Shipping",
                text: "Love the design aesthetic. The furniture fits perfectly in my apartment. Shipping was faster than expected, and packaging was secure. A few assembly instructions could have been clearer, but overall very happy.",
                product: "Accent Furniture",
                verified: true,
                avatar: "R"
            },
            {
                id: 8,
                name: "Amit Khanna",
                date: "November 8, 2024",
                rating: 5,
                title: "Investment Worth Making",
                text: "I view Innovative Homesi furniture as an investment in my home's comfort and aesthetics. The durability and timeless design mean these pieces will last for years. Truly exceptional work.",
                product: "Premium Bed Frame",
                verified: true,
                avatar: "A"
            },
            {
                id: 9,
                name: "Shruti Desai",
                date: "November 5, 2024",
                rating: 5,
                title: "Customer Service Excellence",
                text: "Had a question about customization options and the team responded within hours with detailed information. They really care about ensuring customer satisfaction. The final product exceeded my expectations!",
                product: "Custom Furniture",
                verified: true,
                avatar: "S"
            },
            {
                id: 10,
                name: "Priya Sharma",
                date: "November 1, 2024",
                rating: 4,
                title: "Quality Craftsmanship",
                text: "The attention to detail is remarkable. Each piece shows genuine craftsmanship. Prices are fair for the quality you get. Would definitely purchase again.",
                product: "Sofa Collection",
                verified: true,
                avatar: "P"
            },
            {
                id: 11,
                name: "Rajesh Kumar",
                date: "October 28, 2024",
                rating: 5,
                title: "Absolutely Stunning Furniture",
                text: "The L-shaped sofa we ordered has completely transformed our living room. The fabric quality is premium, and the comfort level is exceptional. My entire family is extremely happy with this purchase. Innovative Homesi has earned a loyal customer!",
                product: "L-Shape Sofa",
                verified: true,
                avatar: "R"
            },
            {
                id: 12,
                name: "Ananya Patel",
                date: "October 25, 2024",
                rating: 5,
                title: "Perfect Blend of Style and Comfort",
                text: "I was looking for a dining set that could accommodate my large family gatherings. Innovative Homesi delivered beyond expectations! The craftsmanship is superb, and the finish is exactly what I wanted. Highly recommended!",
                product: "8-Seater Dining Set",
                verified: true,
                avatar: "A"
            },
            {
                id: 13,
                name: "Vikram Singh",
                date: "October 22, 2024",
                rating: 4,
                title: "Great Service and Quality",
                text: "From consultation to delivery, the entire experience was smooth. The team helped me choose the right color and design for my space. The sofa is comfortable and looks premium. Minor delay in delivery, but well worth the wait.",
                product: "Recliner Sofa",
                verified: true,
                avatar: "V"
            },
            {
                id: 14,
                name: "Meera Reddy",
                date: "October 18, 2024",
                rating: 5,
                title: "Luxury at Affordable Prices",
                text: "I've shopped at multiple high-end furniture stores, but Innovative Homesi offers the best value. The quality rivals international brands at a fraction of the cost. The velvet upholstery is gorgeous and easy to maintain.",
                product: "Velvet Accent Chairs",
                verified: true,
                avatar: "M"
            },
            {
                id: 15,
                name: "Arjun Malhotra",
                date: "October 15, 2024",
                rating: 5,
                title: "Exceeded All Expectations",
                text: "Bought a complete bedroom set including the bed, side tables, and wardrobe. Every piece is crafted to perfection. The wood quality is excellent, and the design is modern yet timeless. Best furniture investment I've made!",
                product: "Bedroom Collection",
                verified: true,
                avatar: "A"
            },
            {
                id: 16,
                name: "Kavya Iyer",
                date: "October 12, 2024",
                rating: 4,
                title: "Beautiful and Functional",
                text: "The modular sofa is perfect for my compact apartment. Love how I can rearrange it based on my needs. The storage compartments are a bonus. Assembly was straightforward with clear instructions.",
                product: "Modular Sofa",
                verified: true,
                avatar: "K"
            },
            {
                id: 17,
                name: "Sanjay Deshmukh",
                date: "October 8, 2024",
                rating: 5,
                title: "Premium Quality, Worth Every Rupee",
                text: "Initially hesitant about ordering expensive furniture online, but Innovative Homesi's customer service put all my doubts to rest. The product photos don't do justice to the actual quality. Absolutely stunning pieces!",
                product: "Premium Sofa Set",
                verified: true,
                avatar: "S"
            },
            {
                id: 18,
                name: "Neha Gupta",
                date: "October 5, 2024",
                rating: 5,
                title: "Exactly What I Wanted",
                text: "The custom furniture option is fantastic! They created a corner unit exactly to my specifications. The design team was patient with all my changes. The final product is perfect for my space. Thank you, Innovative Homesi!",
                product: "Custom Corner Unit",
                verified: true,
                avatar: "N"
            },
            {
                id: 19,
                name: "Rohit Joshi",
                date: "October 2, 2024",
                rating: 4,
                title: "Solid Build and Comfort",
                text: "The 3-seater sofa is well-built and very comfortable. My back pain has reduced since I started using this. The leather quality is genuine and looks like it will last for years. Great purchase overall!",
                product: "Leather Sofa",
                verified: true,
                avatar: "R"
            },
            {
                id: 20,
                name: "Divya Menon",
                date: "September 28, 2024",
                rating: 5,
                title: "Outstanding Customer Experience",
                text: "From browsing to delivery, everything was handled professionally. The team was responsive to all my queries. The furniture arrived well-packed and on time. The quality speaks for itself. Will definitely shop here again!",
                product: "Living Room Furniture",
                verified: true,
                avatar: "D"
            }
        ];

        // Render reviews function
        function renderReviews(filteredReviews) {
            const container = document.getElementById('reviewsContainer');
            
            if (filteredReviews.length === 0) {
                container.innerHTML = `
                    <div class="no-reviews">
                        <i class="icon icon-empty-cart"></i>
                        <p>No reviews found matching your criteria.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = filteredReviews.map(review => `
                <div class="review-card">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <div class="reviewer-avatar">${review.avatar}</div>
                            <div class="reviewer-details">
                                <h4>${review.name}</h4>
                                <p>${review.date}</p>
                            </div>
                        </div>
                        <div class="review-rating">
                            ${Array.from({length: 5}, (_, i) => `
                                <i class="icon icon-star" style="color: ${i < review.rating ? '#EF9122' : '#ddd'};"></i>
                            `).join('')}
                        </div>
                    </div>
                    <div class="review-title">${review.title}</div>
                    <div class="review-text">${review.text}</div>
                    <div>
                        <span class="review-product">${review.product}</span>
                        ${review.verified ? `<span class="review-verified"><i class="icon icon-check-circle"></i> Verified Purchase</span>` : ''}
                    </div>
                </div>
            `).join('');
        }

        // Filter function
        function filterReviews() {
            const searchTerm = document.getElementById('searchReviews').value.toLowerCase();
            const selectedRating = document.querySelector('.rating-btn.active').dataset.rating;

            let filtered = reviews;

            // Filter by search term
            if (searchTerm) {
                filtered = filtered.filter(review => 
                    review.name.toLowerCase().includes(searchTerm) ||
                    review.product.toLowerCase().includes(searchTerm) ||
                    review.title.toLowerCase().includes(searchTerm) ||
                    review.text.toLowerCase().includes(searchTerm)
                );
            }

            // Filter by rating
            if (selectedRating !== 'all') {
                filtered = filtered.filter(review => review.rating === parseInt(selectedRating));
            }

            renderReviews(filtered);
        }

        // Event listeners
        document.getElementById('searchReviews').addEventListener('input', filterReviews);

        document.querySelectorAll('.rating-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.rating-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                filterReviews();
            });
        });

        // Initial render
        document.addEventListener('DOMContentLoaded', function() {
            renderReviews(reviews);
        });

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
