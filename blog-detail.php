<?php
require_once __DIR__ . '/includes/init.php';

// Get blog slug from URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : null;

// Fetch blog data
$blogData = null;
if ($slug) {
    try {
        $sql = "SELECT * FROM blogs WHERE slug = :slug AND is_published = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();
        $blogData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($blogData) {
            // Increment view count
            $updateViewsSql = "UPDATE blogs SET views = views + 1 WHERE id = :id";
            $updateStmt = $pdo->prepare($updateViewsSql);
            $updateStmt->bindParam(':id', $blogData['id'], PDO::PARAM_INT);
            $updateStmt->execute();

            // Get related posts
            $relatedSql = "SELECT id, title, slug, featured_image, published_at
                           FROM blogs
                           WHERE category = :category AND id != :id AND is_published = 1
                           ORDER BY published_at DESC
                           LIMIT 3";
            $relatedStmt = $pdo->prepare($relatedSql);
            $relatedStmt->bindParam(':category', $blogData['category'], PDO::PARAM_STR);
            $relatedStmt->bindParam(':id', $blogData['id'], PDO::PARAM_INT);
            $relatedStmt->execute();
            $relatedPosts = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get comments
            $commentsSql = "SELECT * FROM blog_comments
                            WHERE blog_id = :blog_id AND is_approved = 1
                            ORDER BY created_at DESC";
            $commentsStmt = $pdo->prepare($commentsSql);
            $commentsStmt->bindParam(':blog_id', $blogData['id'], PDO::PARAM_INT);
            $commentsStmt->execute();
            $comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        // Handle error silently, blogData will remain null
    }
}

// If blog not found, show default content
if (!$blogData) {
    $blogData = [
        'title' => 'Blog Post Not Found',
        'category' => 'Error',
        'content' => '<p>The blog post you are looking for could not be found.</p>',
        'author_name' => 'Innovative Homesi',
        'published_at' => date('Y-m-d H:i:s'),
        'tags' => '',
        'featured_image' => 'images/blog/default.jpg'
    ];
    $relatedPosts = [];
    $comments = [];
}
?>
<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($blogData['meta_title'] ?? $blogData['title'] . ' - Innovative Homesi'); ?></title>
    <meta name="author" content="<?php echo htmlspecialchars($blogData['author_name']); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="<?php echo htmlspecialchars($blogData['meta_description'] ?? $blogData['excerpt'] ?? 'Discover expert furniture tips, interior design ideas, and home decor inspiration.'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($blogData['tags'] ?? 'furniture blog, home decor, interior design'); ?>">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://innovativehomesi.com/blog-detail">

    <!-- Open Graph Meta Tags -->
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?php echo htmlspecialchars($blogData['title'] . ' - Innovative Homesi'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($blogData['meta_description'] ?? $blogData['excerpt'] ?? ''); ?>">
    <meta property="og:url" content="https://innovativehomesi.com/blog-detail.php?slug=<?php echo htmlspecialchars($slug ?? ''); ?>">
    <meta property="og:site_name" content="Innovative Homesi">
    <meta property="og:image" content="https://innovativehomesi.com/<?php echo htmlspecialchars($blogData['featured_image'] ?? 'images/logo/logo.png'); ?>">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($blogData['title'] . ' - Innovative Homesi'); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($blogData['meta_description'] ?? $blogData['excerpt'] ?? ''); ?>">
    <meta name="twitter:image" content="https://innovativehomesi.com/<?php echo htmlspecialchars($blogData['featured_image'] ?? 'images/logo/logo.png'); ?>">

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
        <section class="flat-spacing-10" style="padding-top: 150px;">
            <div class="container">
                <div class="text-center mb-4">
                    <div class="entry_tag name-tag h6 d-inline-block px-3 py-2 mb-3" style="background-color: #f8f9fa; border-radius: 20px;">
                        <?php echo htmlspecialchars($blogData['category']); ?>
                    </div>
                    <h1 class="heading mb-3">
                        <?php echo htmlspecialchars($blogData['title']); ?>
                    </h1>
                    <div class="entry_author text-muted">
                        <span>Written by</span>
                        <strong><?php echo htmlspecialchars($blogData['author_name']); ?></strong>
                        <span class="mx-2">•</span>
                        <span><?php echo date('F j, Y', strtotime($blogData['published_at'] ?? $blogData['created_at'])); ?></span>
                    </div>
                </div>

                <!-- Featured Image Below Header -->
                <?php if (!empty($blogData['featured_image'])): ?>
                <div class="blog-featured-image mb-5">
                    <img src="<?php echo htmlspecialchars($blogData['featured_image']); ?>"
                         alt="<?php echo htmlspecialchars($blogData['title']); ?>"
                         class="img-fluid w-100"
                         style="max-height: 500px; object-fit: cover; border-radius: 8px;">
                </div>
                <?php endif; ?>
            </div>
        </section>
        <!-- /Page Title -->
        <!-- Blog Detail -->
        <section class="s-blog-detail pb-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <!-- Blog Content -->
                        <div class="blog-detail_content mb-5">
                            <div class="box-text">
                                <?php echo $blogData['content']; ?>
                            </div>
                        </div>

                        <!-- Tags and Share Section -->
                        <div class="blog-meta-footer d-flex flex-wrap justify-content-between align-items-center py-4 border-top border-bottom mb-5">
                            <div class="tag-post mb-3 mb-md-0">
                                <p class="h6 mb-2">Tags:</p>
                                <ul class="tag-list d-flex flex-wrap gap-2 mb-0">
                                    <?php
                                    if (!empty($blogData['tags'])) {
                                        $tags = explode(',', $blogData['tags']);
                                        foreach ($tags as $tag) {
                                            $tag = trim($tag);
                                            echo '<li><a href="#" class="badge bg-light text-dark px-3 py-2" style="text-decoration: none;">#' . htmlspecialchars($tag) . '</a></li>';
                                        }
                                    }
                                    ?>
                                </ul>
                            </div>
                            <div class="share-post">
                                <p class="h6 mb-2">Share:</p>
                                <ul class="tf-social-icon d-flex gap-2 mb-0">
                                    <li>
                                        <a href="https://www.facebook.com/innovativehomesi/" target="_blank" class="social-facebook">
                                            <i class="fa-brands fa-facebook-f"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.instagram.com/innovative_homesi/" target="_blank" class="social-instagram">
                                            <i class="fa-brands fa-instagram"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.youtube.com/@innovativehomesi" target="_blank" class="social-youtube">
                                            <i class="fa-brands fa-youtube"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://in.pinterest.com/innovativehomes3/" target="_blank" class="social-pinterest">
                                            <i class="fa-brands fa-pinterest-p"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Comments Section (without count in title) -->
                        <?php if (count($comments) > 0): ?>
                        <div class="blog-detail_comment mb-5">
                            <h2 class="title mb-4">Comments</h2>
                            <ul class="comment-list">
                                <?php foreach ($comments as $comment): ?>
                                    <li class="comment-item mb-4 pb-4 border-bottom">
                                        <div class="d-flex">
                                            <div class="cmt_image me-3">
                                                <img loading="lazy" width="60" height="60" src="images/avatar/avatar-5.jpg" alt="avatar" class="rounded-circle">
                                            </div>
                                            <div class="cmt_content flex-grow-1">
                                                <div class="head mb-2">
                                                    <div class="cmt__infor">
                                                        <h5 class="author_name mb-1"><?php echo htmlspecialchars($comment['author_name']); ?></h5>
                                                        <p class="text-small text-muted"><?php echo date('F j, Y', strtotime($comment['created_at'])); ?></p>
                                                    </div>
                                                    <?php if ($comment['rating']): ?>
                                                        <div class="rate_wrap mt-2">
                                                            <?php for ($i = 0; $i < $comment['rating']; $i++): ?>
                                                                <i class="icon-star text-warning"></i>
                                                            <?php endfor; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="cmt__text">
                                                    <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <!-- Leave a Comment Form -->
                        <?php if (isset($blogData['id'])): ?>
                        <div class="blog-comment-form mt-5">
                            <h2 class="title mb-4">Leave a Comment</h2>
                            <div id="commentAlert" class="alert d-none mb-4"></div>
                            <form id="blogCommentForm">
                                <input type="hidden" name="blog_id" value="<?php echo $blogData['id']; ?>">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="author_name" class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="author_name" name="author_name" required placeholder="Your name">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="author_email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="author_email" name="author_email" required placeholder="Your email">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="author_phone" class="form-label">Phone (Optional)</label>
                                        <input type="tel" class="form-control" id="author_phone" name="author_phone" placeholder="Your phone number">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Rating (Optional)</label>
                                        <div class="rating-input d-flex gap-2">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <label class="rating-star" style="cursor: pointer;">
                                                <input type="radio" name="rating" value="<?php echo $i; ?>" style="display: none;">
                                                <i class="fa-regular fa-star" data-star="<?php echo $i; ?>" style="font-size: 1.5rem; color: #ccc;"></i>
                                            </label>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="comment" class="form-label">Comment <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="comment" name="comment" rows="5" required placeholder="Write your comment here..."></textarea>
                                </div>
                                <button type="submit" class="tf-btn btn-fill" id="submitCommentBtn">
                                    <span class="text">Submit Comment</span>
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Blog Detail -->
        <!-- Relate -->
        <?php if (count($relatedPosts) > 0): ?>
        <section class="flat-spacing pt-0">
            <div class="container">
                <div class="sect-title">
                    <h1>Related Articles</h1>
                </div>
                <div dir="ltr" class="swiper tf-swiper" data-preview="3" data-tablet="3" data-mobile-sm="2" data-mobile="1" data-space-lg="48"
                    data-space-md="30" data-space="15" data-pagination="1" data-pagination-sm="2" data-pagination-md="3" data-pagination-lg="3">
                    <div class="swiper-wrapper">
                        <?php foreach ($relatedPosts as $relatedPost): ?>
                        <div class="swiper-slide">
                            <div class="article-blog hover-img4">
                                <div class="blog-image">
                                    <a href="blog-detail.php?slug=<?php echo htmlspecialchars($relatedPost['slug']); ?>" class="entry_image img-style4">
                                        <img src="<?php echo htmlspecialchars($relatedPost['featured_image'] ?? 'images/blog/default.jpg'); ?>"
                                             data-src="<?php echo htmlspecialchars($relatedPost['featured_image'] ?? 'images/blog/default.jpg'); ?>"
                                             alt="<?php echo htmlspecialchars($relatedPost['title']); ?>"
                                             class="lazyload">
                                    </a>
                                </div>
                                <div class="blog-content p-0">
                                    <a href="blog-detail.php?slug=<?php echo htmlspecialchars($relatedPost['slug']); ?>" class="entry_name link h4">
                                        <?php echo htmlspecialchars($relatedPost['title']); ?>
                                    </a>
                                    <p class="entry_date"><?php echo date('F j, Y', strtotime($relatedPost['published_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="sw-dot-default tf-sw-pagination"></div>
                </div>
            </div>
        </section>
        <?php endif; ?>
        <!-- /Relate -->
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

    <!-- Blog Comment Form Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Star rating functionality
        const ratingStars = document.querySelectorAll('.rating-star i');
        ratingStars.forEach(function(star) {
            star.addEventListener('click', function() {
                const starValue = parseInt(this.getAttribute('data-star'));
                const radioInput = this.parentElement.querySelector('input[type="radio"]');
                radioInput.checked = true;

                // Update visual state
                ratingStars.forEach(function(s, index) {
                    if (index < starValue) {
                        s.classList.remove('fa-regular');
                        s.classList.add('fa-solid');
                        s.style.color = '#ffc107';
                    } else {
                        s.classList.remove('fa-solid');
                        s.classList.add('fa-regular');
                        s.style.color = '#ccc';
                    }
                });
            });

            // Hover effect
            star.addEventListener('mouseenter', function() {
                const starValue = parseInt(this.getAttribute('data-star'));
                ratingStars.forEach(function(s, index) {
                    if (index < starValue) {
                        s.style.color = '#ffc107';
                    }
                });
            });

            star.addEventListener('mouseleave', function() {
                const checkedRadio = document.querySelector('.rating-star input[type="radio"]:checked');
                const checkedValue = checkedRadio ? parseInt(checkedRadio.value) : 0;
                ratingStars.forEach(function(s, index) {
                    if (index < checkedValue) {
                        s.style.color = '#ffc107';
                    } else {
                        s.style.color = '#ccc';
                    }
                });
            });
        });

        // Comment form submission
        const commentForm = document.getElementById('blogCommentForm');
        if (commentForm) {
            commentForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const submitBtn = document.getElementById('submitCommentBtn');
                const alertDiv = document.getElementById('commentAlert');
                const originalBtnText = submitBtn.innerHTML;

                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="text">Submitting...</span>';

                // Collect form data
                const formData = new FormData(commentForm);
                const data = {};
                formData.forEach(function(value, key) {
                    data[key] = value;
                });

                // Submit via fetch
                fetch('api/blog-comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(result) {
                    alertDiv.classList.remove('d-none', 'alert-danger', 'alert-success');

                    if (result.success) {
                        alertDiv.classList.add('alert-success');
                        alertDiv.textContent = result.message;
                        commentForm.reset();

                        // Reset stars
                        ratingStars.forEach(function(s) {
                            s.classList.remove('fa-solid');
                            s.classList.add('fa-regular');
                            s.style.color = '#ccc';
                        });
                    } else {
                        alertDiv.classList.add('alert-danger');
                        alertDiv.textContent = result.error || 'An error occurred. Please try again.';
                    }

                    // Scroll to alert
                    alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                })
                .catch(function(error) {
                    alertDiv.classList.remove('d-none', 'alert-success');
                    alertDiv.classList.add('alert-danger');
                    alertDiv.textContent = 'An error occurred. Please try again.';
                })
                .finally(function() {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
            });
        }
    });
    </script>
</body>
</html>

