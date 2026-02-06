<?php
require_once __DIR__ . '/includes/init.php';
?>
<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<head>
    <meta charset="utf-8">
    <title>Blog & Articles - Innovative Homesi | Furniture Insights & Inspiration</title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="Explore our collection of articles on furniture design, sofa selection guides, home decor trends, and maintenance tips. Your guide to creating the perfect living space with Innovative Homesi.">
    <meta name="keywords" content="furniture articles, sofa guides, home decor blog, furniture trends, interior design articles, furniture inspiration">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://innovativehomesi.com/blog-grid">

    <!-- Open Graph Meta Tags -->
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Blog & Articles - Innovative Homesi">
    <meta property="og:description" content="Explore furniture design, sofa selection guides, home decor trends, and expert tips.">
    <meta property="og:url" content="https://innovativehomesi.com/blog-grid">
    <meta property="og:site_name" content="Innovative Homesi">
    <meta property="og:image" content="https://innovativehomesi.com/images/logo/logo.png">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Blog & Articles - Innovative Homesi">
    <meta name="twitter:description" content="Explore furniture design, sofa selection guides, and home decor trends.">
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
        <section class="s-page-title" style="background-color: #faf1e5;">
            <div class="container">
                <div class="content">
                    <h1 class="title-page">Blog Grid</h1>
                    <ul class="breadcrumbs-page">
                        <li><a href="index.php" class="h6 link">Home</a></li>
                        <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                        <li>
                            <h6 class="current-page fw-normal">Blog</h6>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
        <!-- /Page Title -->
        <!-- Blog Grid -->
        <div class="flat-spacing">
            <div class="container">
                <div class="tf-grid-layout sm-col-2 lg-col-3" id="blogGrid">
                    <!-- Blog posts will be loaded here dynamically -->
                    <div class="text-center" style="grid-column: 1 / -1; padding: 60px 20px;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">Loading blog posts...</p>
                    </div>
                </div>

                <!-- Load More Button -->
                <div class="text-center mt-5" id="loadMoreContainer" style="display: none;">
                    <button class="tf-btn animate-btn" id="loadMoreBtn">
                        Load More Articles
                        <i class="icon icon-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- /Blog Grid -->
        <?php include 'includes/footer.php'; ?>
    <!-- Javascript -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap-select.min.js"></script>
    <script src="js/lazysize.min.js"></script>
    <script src="js/wow.min.js"></script>
    <script src="js/parallaxie.js"></script>
    <script src="js/main.js"></script>

    <script src="js/sibforms.js" defer></script>
    <script>
        // Blog state
        let blogPosts = [];
        let currentOffset = 0;
        let postsPerPage = 9;
        let totalPosts = 0;

        // Fetch blog posts from API
        async function fetchBlogPosts(offset = 0) {
            try {
                const response = await fetch(`api/blogs.php?limit=${postsPerPage}&offset=${offset}`);
                const data = await response.json();

                if (data.success) {
                    return data;
                } else {
                    console.error('API Error:', data.error);
                    return { data: [], total: 0 };
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                return { data: [], total: 0 };
            }
        }

        // Format date
        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        }

        // Render blog posts
        async function renderBlogs(append = false) {
            const result = await fetchBlogPosts(currentOffset);

            if (!append) {
                blogPosts = result.data || [];
            } else {
                blogPosts = [...blogPosts, ...(result.data || [])];
            }

            totalPosts = result.total || 0;

            const blogGrid = document.getElementById('blogGrid');

            if (blogPosts.length === 0) {
                blogGrid.innerHTML = `
                    <div class="text-center" style="grid-column: 1 / -1; padding: 60px 20px;">
                        <i class="icon icon-note-pencil" style="font-size: 60px; color: #d4a574;"></i>
                        <h4 class="mt-3">No blog posts found</h4>
                        <p>Check back soon for new articles!</p>
                    </div>
                `;
                return;
            }

            const blogHTML = blogPosts.map(blog => {
                const publishedDate = blog.published_at || blog.created_at;
                return `
                    <div class="article-blog hover-img4">
                        <div class="blog-image">
                            <a href="blog-detail.php?slug=${blog.slug}" class="entry_image img-style4">
                                <img src="${blog.featured_image || 'images/blog/default.jpg'}"
                                     data-src="${blog.featured_image || 'images/blog/default.jpg'}"
                                     alt="${blog.title}"
                                     class="lazyload">
                            </a>
                            <div class="entry_tag">
                                <span class="name-tag h6">${blog.category}</span>
                            </div>
                        </div>
                        <div class="blog-content">
                            <a href="blog-detail.php?slug=${blog.slug}" class="entry_name link h4">
                                ${blog.title}
                            </a>
                            <p class="entry_date">${formatDate(publishedDate)}</p>
                            ${blog.excerpt ? `<p class="entry_excerpt text-muted mt-2">${blog.excerpt.substring(0, 100)}...</p>` : ''}
                        </div>
                    </div>
                `;
            }).join('');

            blogGrid.innerHTML = blogHTML;

            // Show/hide load more button
            const loadMoreContainer = document.getElementById('loadMoreContainer');
            if (blogPosts.length < totalPosts) {
                loadMoreContainer.style.display = 'block';
            } else {
                loadMoreContainer.style.display = 'none';
            }
        }

        // Load more posts
        async function loadMore() {
            currentOffset += postsPerPage;
            await renderBlogs(true);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            renderBlogs(false);

            // Load more button click
            document.getElementById('loadMoreBtn').addEventListener('click', loadMore);
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

