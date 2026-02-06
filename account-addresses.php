<?php
require_once __DIR__ . '/includes/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = '/account-addresses.php';
    setFlashMessage('error', 'Please login to access your addresses');
    redirect('/login.php');
    exit;
}

// Get current user data
$userId = getCurrentUserId();
$stmt = $pdo->prepare('SELECT id, name, email, phone, created_at FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    redirect('/login.php');
    exit;
}

// Handle address deletion
if (isset($_POST['delete_address']) && isset($_POST['address_id'])) {
    $addressId = (int)$_POST['address_id'];
    $deleteStmt = $pdo->prepare('DELETE FROM addresses WHERE id = ? AND user_id = ?');
    $deleteStmt->execute([$addressId, $userId]);
    setFlashMessage('success', 'Address deleted successfully');
    redirect('/account-addresses.php');
    exit;
}

// Handle address add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_address'])) {
    $fullName = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $addressLine1 = sanitize($_POST['address_line1'] ?? '');
    $addressLine2 = sanitize($_POST['address_line2'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $postalCode = sanitize($_POST['postal_code'] ?? '');
    $addressType = sanitize($_POST['address_type'] ?? 'shipping');
    $isDefault = isset($_POST['is_default']) ? 1 : 0;
    $addressId = isset($_POST['address_id']) ? (int)$_POST['address_id'] : 0;

    if ($fullName && $phone && $addressLine1 && $city && $state && $postalCode) {
        // If setting as default, unset other defaults first
        if ($isDefault) {
            $pdo->prepare('UPDATE addresses SET is_default = 0 WHERE user_id = ? AND address_type = ?')
                ->execute([$userId, $addressType]);
        }

        if ($addressId > 0) {
            // Update existing
            $updateStmt = $pdo->prepare('UPDATE addresses SET full_name = ?, phone = ?, address_line1 = ?, address_line2 = ?, city = ?, state = ?, postal_code = ?, address_type = ?, is_default = ? WHERE id = ? AND user_id = ?');
            $updateStmt->execute([$fullName, $phone, $addressLine1, $addressLine2, $city, $state, $postalCode, $addressType, $isDefault, $addressId, $userId]);
            setFlashMessage('success', 'Address updated successfully');
        } else {
            // Insert new
            $insertStmt = $pdo->prepare('INSERT INTO addresses (user_id, full_name, phone, address_line1, address_line2, city, state, postal_code, address_type, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $insertStmt->execute([$userId, $fullName, $phone, $addressLine1, $addressLine2, $city, $state, $postalCode, $addressType, $isDefault]);
            setFlashMessage('success', 'Address added successfully');
        }
        redirect('/account-addresses.php');
        exit;
    } else {
        setFlashMessage('error', 'Please fill in all required fields');
    }
}

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$addressesPerPage = 5;

$totalAddressesStmt = $pdo->prepare('SELECT COUNT(*) FROM addresses WHERE user_id = ?');
$totalAddressesStmt->execute([$userId]);
$totalAddresses = $totalAddressesStmt->fetchColumn();
$pagination = paginate($totalAddresses, $addressesPerPage, $page);

// Fetch addresses
$addressesStmt = $pdo->prepare('SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC LIMIT ? OFFSET ?');
$addressesStmt->execute([$userId, $addressesPerPage, $pagination['offset']]);
$addresses = $addressesStmt->fetchAll();
?>
<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">

<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<head>
    <meta charset="utf-8">
    <title>My Addresses - Innovative Homesi | Manage Delivery Addresses</title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="Manage your delivery and billing addresses for Innovative Homesi orders.">
    <meta name="robots" content="noindex, nofollow">

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
    <link rel="stylesheet" type="text/css" href="css/shop-custom-additions.css">

    <!-- Favicon and Touch Icons  -->
    <link rel="shortcut icon" href="images/logo/favicon.svg">
    <link rel="apple-touch-icon-precomposed" href="images/logo/favicon.svg">

    <style>
        /* Account Page Enhancements */
        .user-avatar-modern {
            position: relative;
            overflow: visible !important;
        }

        .user-avatar-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #9e6747 0%, #b07d5e 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 48px;
            box-shadow: 0 8px 24px rgba(158, 103, 71, 0.3);
            transition: all 0.3s ease;
        }

        .user-avatar-placeholder:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 32px rgba(158, 103, 71, 0.4);
        }

        .author_avatar {
            position: relative;
            margin-bottom: 20px;
        }

        .author_avatar .image {
            width: 120px;
            height: 120px;
            margin: 0 auto 15px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid #f8f9fa;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .btn-change_img {
            position: absolute;
            bottom: 10px;
            right: calc(50% - 60px);
            background: linear-gradient(135deg, #9e6747 0%, #b07d5e 100%);
            color: #fff !important;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(158, 103, 71, 0.3);
            transition: all 0.3s ease;
            border: 3px solid #fff;
        }

        .btn-change_img:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(158, 103, 71, 0.4);
        }

        .member-since {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e9ecef;
        }

        .member-since small {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 13px;
        }

        .member-since i {
            color: #9e6747;
        }

        /* Sidebar Enhancement */
        .sidebar-account {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            border-radius: 20px;
            padding: 30px 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }

        .my-account-nav_item {
            padding: 14px 20px;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #495057;
            text-decoration: none;
            margin-bottom: 8px;
        }

        .my-account-nav_item:hover,
        .my-account-nav_item.active {
            background: linear-gradient(135deg, #9e6747 0%, #b07d5e 100%);
            color: #fff !important;
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(158, 103, 71, 0.2);
        }

        .my-account-nav_item i {
            font-size: 18px;
            width: 24px;
        }

        /* Address Content Enhancement */
        .my-account-content {
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            border: 1px solid #e9ecef;
        }

        .account-title {
            color: #9e6747;
            margin-bottom: 24px;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .account-title::before {
            content: '';
            width: 4px;
            height: 28px;
            background: linear-gradient(135deg, #9e6747 0%, #b07d5e 100%);
            border-radius: 2px;
        }

        .account-address-item {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #e9ecef;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .account-address-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(158, 103, 71, 0.1);
            border-color: #9e6747;
        }

        /* Responsive */
        @media (max-width: 1199px) {
            .author_avatar .image {
                width: 100px;
                height: 100px;
            }

            .user-avatar-placeholder {
                font-size: 38px;
            }

            .btn-change_img {
                right: calc(50% - 50px);
            }
        }

        @media (max-width: 767px) {
            .my-account-content {
                padding: 20px;
            }

            .account-address-item {
                padding: 20px;
            }
        }

        /* Animation for page load */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .sidebar-account,
        .my-account-content {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .my-account-content {
            animation-delay: 0.2s;
        }
    </style>

</head>

<body>

    <!-- Scroll Top -->
    <button id="goTop">
        <span class="border-progress"></span>
        <span class="icon icon-caret-up"></span>
    </button>

    <div id="wrapper">
        <?php include 'includes/topbar.php'; ?>
        <?php include 'includes/header.php'; ?>
        <!-- Page Title -->
        <section class="s-page-title">
            <div class="container">
                <div class="content">
                    <h1 class="title-page">My Account</h1>
                    <ul class="breadcrumbs-page">
                        <li><a href="index.php" class="h6 link">Home</a></li>
                        <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                        <li>
                            <h6 class="current-page fw-normal">My account</h6>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
        <!-- /Page Title -->
        <!-- Account -->
        <section class="flat-spacing">
            <input class="fileInputDash" type="file" accept="image/*" style="display: none;">
            <div class="container">
                <div class="row">
                    <div class="col-xl-3 d-none d-xl-block">
                        <div class="sidebar-account sidebar-content-wrap sticky-top">
                            <div class="account-author">
                                <div class="author_avatar">
                                    <div class="image user-avatar-modern">
                                        <div class="user-avatar-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    </div>
                                    <div class="btn-change_img box-icon changeImgDash">
                                        <i class="icon icon-camera"></i>
                                    </div>
                                </div>
                                <h4 class="author_name"><?php echo htmlspecialchars($user['name']); ?></h4>
                                <p class="author_email h6"><?php echo htmlspecialchars($user['email']); ?></p>
                                <div class="member-since">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt"></i> Member since <?php echo date('M Y', strtotime($user['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                            <ul class="my-account-nav">
                                <li>
                                    <a href="account-page.php" class="my-account-nav_item h5">
                                        <i class="icon icon-circle-four"></i>
                                        Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a href="account-orders.php" class="my-account-nav_item h5">
                                        <i class="icon icon-box-arrow-down"></i>
                                        Orders
                                    </a>
                                </li>
                                <li>
                                    <a href="account-track-order.php" class="my-account-nav_item h5">
                                        <i class="fas fa-shipping-fast"></i>
                                        Track Order
                                    </a>
                                </li>
                                <li>
                                    <a href="account-wishlist.php" class="my-account-nav_item h5">
                                        <i class="fas fa-heart"></i>
                                        Wishlist
                                    </a>
                                </li>
                                <li>
                                    <p class="my-account-nav_item h5 active">
                                        <i class="icon icon-address-book"></i>
                                        My address
                                    </p>
                                </li>
                                <li>
                                    <a href="account-setting.php" class="my-account-nav_item h5">
                                        <i class="icon icon-setting"></i>
                                        Setting
                                    </a>
                                </li>
                                <li>
                                    <a href="auth/logout.php" class="my-account-nav_item h5">
                                        <i class="icon icon-sign-out"></i>
                                        Log out
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-9">
                        <div class="my-account-content">
                            <?php echo displayFlashMessage(); ?>
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h2 class="account-title type-semibold mb-0">My Address</h2>
                                <a href="#addAddress" data-bs-toggle="modal" class="tf-btn animate-btn">
                                    <i class="icon icon-plus"></i> Add New Address
                                </a>
                            </div>
                            <div class="account-my_address">
                                <?php if (empty($addresses)): ?>
                                <div class="text-center py-5">
                                    <p class="h6">No addresses saved yet.</p>
                                    <a href="#addAddress" data-bs-toggle="modal" class="tf-btn animate-btn mt-3">Add Your First Address</a>
                                </div>
                                <?php else: ?>
                                <?php foreach ($addresses as $address): ?>
                                <div class="account-address-item file-delete">
                                    <div class="address-item_content">
                                        <?php if ($address['is_default']): ?>
                                        <h4 class="address-title">Default</h4>
                                        <?php endif; ?>
                                        <div class="address-info">
                                            <h5 class="fw-semibold"><?php echo htmlspecialchars($address['full_name']); ?></h5>
                                            <p class="h6"><?php echo htmlspecialchars($address['address_line1']); ?>
                                            <?php if ($address['address_line2']): ?>, <?php echo htmlspecialchars($address['address_line2']); ?><?php endif; ?></p>
                                            <p class="h6"><?php echo htmlspecialchars($address['city'] . ', ' . $address['state'] . ' ' . $address['postal_code']); ?></p>
                                        </div>
                                        <div class="address-info">
                                            <h5 class="fw-semibold">Phone</h5>
                                            <p class="h6"><?php echo htmlspecialchars($address['phone']); ?></p>
                                        </div>
                                    </div>
                                    <div class="address-item_action">
                                        <a href="#editAddress<?php echo $address['id']; ?>" data-bs-toggle="modal" class="tf-btn animate-btn">
                                            Edit
                                        </a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this address?');">
                                            <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                            <button type="submit" name="delete_address" class="tf-btn style-line">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Edit Address Modal for this address -->
                                <div class="modal fade" id="editAddress<?php echo $address['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Address</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                                    <div class="mb-3">
                                                        <input type="text" name="full_name" class="form-control" placeholder="Full Name *" value="<?php echo htmlspecialchars($address['full_name']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <input type="text" name="phone" class="form-control" placeholder="Phone *" value="<?php echo htmlspecialchars($address['phone']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <input type="text" name="address_line1" class="form-control" placeholder="Address Line 1 *" value="<?php echo htmlspecialchars($address['address_line1']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <input type="text" name="address_line2" class="form-control" placeholder="Address Line 2" value="<?php echo htmlspecialchars($address['address_line2'] ?? ''); ?>">
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-6">
                                                            <input type="text" name="city" class="form-control" placeholder="City *" value="<?php echo htmlspecialchars($address['city']); ?>" required>
                                                        </div>
                                                        <div class="col-6">
                                                            <input type="text" name="state" class="form-control" placeholder="State *" value="<?php echo htmlspecialchars($address['state']); ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <input type="text" name="postal_code" class="form-control" placeholder="Postal Code *" value="<?php echo htmlspecialchars($address['postal_code']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <select name="address_type" class="form-control">
                                                            <option value="shipping" <?php echo $address['address_type'] === 'shipping' ? 'selected' : ''; ?>>Shipping Address</option>
                                                            <option value="billing" <?php echo $address['address_type'] === 'billing' ? 'selected' : ''; ?>>Billing Address</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-check">
                                                        <input type="checkbox" name="is_default" class="form-check-input" id="defaultEdit<?php echo $address['id']; ?>" <?php echo $address['is_default'] ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="defaultEdit<?php echo $address['id']; ?>">Set as default address</label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="tf-btn style-line" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="save_address" class="tf-btn animate-btn">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>

                                <?php if ($pagination['total_pages'] > 1): ?>
                                <div class="wd-full wg-pagination orther-del">
                                    <?php if ($pagination['has_previous']): ?>
                                    <a href="?page=<?php echo $pagination['current_page'] - 1; ?>" class="pagination-item h6 direct"><i class="icon icon-caret-left"></i></a>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                        <?php if ($i == $pagination['current_page']): ?>
                                        <span class="pagination-item h6 active"><?php echo $i; ?></span>
                                        <?php else: ?>
                                        <a href="?page=<?php echo $i; ?>" class="pagination-item h6"><?php echo $i; ?></a>
                                        <?php endif; ?>
                                    <?php endfor; ?>

                                    <?php if ($pagination['has_next']): ?>
                                    <a href="?page=<?php echo $pagination['current_page'] + 1; ?>" class="pagination-item h6 direct"><i class="icon icon-caret-right"></i></a>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Add Address Modal -->
                            <div class="modal fade" id="addAddress" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Add New Address</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <input type="text" name="full_name" class="form-control" placeholder="Full Name *" required>
                                                </div>
                                                <div class="mb-3">
                                                    <input type="text" name="phone" class="form-control" placeholder="Phone *" required>
                                                </div>
                                                <div class="mb-3">
                                                    <input type="text" name="address_line1" class="form-control" placeholder="Address Line 1 *" required>
                                                </div>
                                                <div class="mb-3">
                                                    <input type="text" name="address_line2" class="form-control" placeholder="Address Line 2">
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <input type="text" name="city" class="form-control" placeholder="City *" required>
                                                    </div>
                                                    <div class="col-6">
                                                        <input type="text" name="state" class="form-control" placeholder="State *" required>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <input type="text" name="postal_code" class="form-control" placeholder="Postal Code *" required>
                                                </div>
                                                <div class="mb-3">
                                                    <select name="address_type" class="form-control">
                                                        <option value="shipping">Shipping Address</option>
                                                        <option value="billing">Billing Address</option>
                                                    </select>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" name="is_default" class="form-check-input" id="defaultNew">
                                                    <label class="form-check-label" for="defaultNew">Set as default address</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="tf-btn style-line" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="save_address" class="tf-btn animate-btn">Add Address</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Account -->
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
</body>


</html>

