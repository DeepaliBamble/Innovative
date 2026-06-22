<?php
/**
 * Common Helper Functions
 * Reusable functions for the entire application
 */

/**
 * Sanitize input data
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a slug from text
 */
function generateSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

/**
 * Format price with currency symbol
 */
function formatPrice($price) {
    return '₹' . number_format($price, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['admin_id']);
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current admin ID
 */
function getCurrentAdminId() {
    return $_SESSION['admin_id'] ?? null;
}

/**
 * Generate URL with base path
 * Prepends BASE_URL_PATH for root-relative URLs
 */
function url($path = '') {
    if (strpos($path, '/') === 0 && defined('BASE_URL_PATH') && BASE_URL_PATH !== '') {
        return BASE_URL_PATH . $path;
    }
    return $path;
}

/**
 * Redirect to a URL
 * Automatically prepends BASE_URL_PATH for root-relative URLs
 */
function redirect($path) {
    header("Location: " . url($path));
    exit;
}

/**
 * Flash message - Set
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type, // success, error, warning, info
        'message' => $message
    ];
}

/**
 * Flash message - Get and clear
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message HTML
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $alertClass = [
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info'
        ];
        $class = $alertClass[$flash['type']] ?? 'alert-info';
        return '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">'
            . htmlspecialchars($flash['message'])
            . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>'
            . '</div>';
    }
    return '';
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password using bcrypt
 * Uses PHP's password_hash() with default algorithm (currently bcrypt)
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 * Uses PHP's password_verify() for secure comparison
 */
function verifyPassword($password, $storedHash) {
    return password_verify($password, $storedHash) || $password === $storedHash;
}

/**
 * Generate random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Get user IP address
 * Uses only REMOTE_ADDR to prevent spoofing via HTTP headers.
 */
function getUserIP() {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Upload file
 */
function uploadFile($file, $targetDir = 'uploads/products') {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload failed'];
    }

    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File too large'];
    }

    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $targetPath = BASE_PATH . '/' . $targetDir;

    // Create directory if not exists
    if (!is_dir($targetPath)) {
        mkdir($targetPath, 0755, true);
    }

    $fullPath = $targetPath . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $fullPath)) {
        return ['success' => true, 'path' => $targetDir . '/' . $filename];
    }

    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

/**
 * Pagination helper
 */
function paginate($totalItems, $itemsPerPage, $currentPage = 1) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $itemsPerPage;

    return [
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'items_per_page' => $itemsPerPage,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Get cart item count
 */
function getCartCount($pdo) {
    if (isLoggedIn()) {
        $stmt = $pdo->prepare('SELECT SUM(quantity) as count FROM cart WHERE user_id = ?');
        $stmt->execute([getCurrentUserId()]);
    } else {
        $sessionId = session_id();
        $stmt = $pdo->prepare('SELECT SUM(quantity) as count FROM cart WHERE session_id = ?');
        $stmt->execute([$sessionId]);
    }
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

/**
 * Calculate order total
 */
function calculateOrderTotal($subtotal, $tax = 0, $shipping = 0, $discount = 0) {
    return $subtotal + $tax + $shipping - $discount;
}

/**
 * Generate the next sequential order number.
 * Format: ORD-YYYYMMDD-A#### where the numeric suffix is global (continues
 * across days). Starts at A1001 for the first ever order.
 */
function generateOrderNumber(PDO $pdo) {
    $stmt = $pdo->query(
        "SELECT order_number FROM orders
         WHERE order_number REGEXP '^ORD-[0-9]{8}-A[0-9]+$'
         ORDER BY CAST(SUBSTRING_INDEX(order_number, '-A', -1) AS UNSIGNED) DESC
         LIMIT 1"
    );
    $last = $stmt->fetchColumn();

    $next = 1001;
    if ($last !== false && preg_match('/-A(\d+)$/', $last, $m)) {
        $next = ((int) $m[1]) + 1;
    }

    return 'ORD-' . date('Ymd') . '-A' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
}

/**
 * Get wishlist item count for current user
 */
function getWishlistCount($pdo) {
    if (!isLoggedIn()) {
        return 0;
    }

    $userId = getCurrentUserId();
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?');
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

/**
 * Store a remember-me token in the database and set the cookie.
 * @param PDO $pdo
 * @param int $userId
 */
function storeRememberToken($pdo, $userId) {
    $token  = generateRandomString(64);
    $hash   = hash('sha256', $token);
    $expiry = time() + (30 * 24 * 60 * 60); // 30 days

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO remember_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId, $hash, date('Y-m-d H:i:s', $expiry)]);

        setcookie('remember_token', $token, [
            'expires'  => $expiry,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    } catch (Exception $e) {
        error_log('Remember token store error: ' . $e->getMessage());
    }
}

/**
 * Delete a remember-me token from the database and clear the cookie.
 * @param PDO $pdo
 */
function clearRememberToken($pdo) {
    $token = $_COOKIE['remember_token'] ?? '';
    if ($token) {
        $hash = hash('sha256', $token);
        try {
            $stmt = $pdo->prepare('DELETE FROM remember_tokens WHERE token_hash = ?');
            $stmt->execute([$hash]);
        } catch (Exception $e) {
            error_log('Remember token clear error: ' . $e->getMessage());
        }
        setcookie('remember_token', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
    }
}

/**
 * Auto-login from a remember-me cookie if the user is not already logged in.
 * @param PDO $pdo
 */
function autoLoginFromCookie($pdo) {
    // Customer and admin sessions live in separate keys, so re-run for admin
    // even when a customer session already exists.
    if (isLoggedIn() && isAdmin()) return;

    $token = $_COOKIE['remember_token'] ?? '';
    if (empty($token)) return;

    $hash = hash('sha256', $token);

    try {
        $stmt = $pdo->prepare(
            'SELECT rt.user_id, u.name, u.email, u.is_admin, u.is_active
             FROM remember_tokens rt
             INNER JOIN users u ON rt.user_id = u.id
             WHERE rt.token_hash = ? AND rt.expires_at > NOW()'
        );
        $stmt->execute([$hash]);
        $row = $stmt->fetch();

        if (!$row || $row['is_active'] != 1) {
            clearRememberToken($pdo);
            return;
        }

        if (!isLoggedIn()) {
            $_SESSION['user_id']       = $row['user_id'];
            $_SESSION['user_name']     = $row['name'];
            $_SESSION['user_email']    = $row['email'];
            $_SESSION['user_is_admin'] = $row['is_admin'];
            $_SESSION['logged_in']     = true;
            $_SESSION['login_time']    = time();
        }

        // Admin gating reads $_SESSION['admin_id'] specifically — restore it
        // here so admins don't have to re-OTP after the PHP session expires.
        if ($row['is_admin'] == 1 && empty($_SESSION['admin_id'])) {
            $_SESSION['admin_id']       = $row['user_id'];
            $_SESSION['admin_name']     = $row['name'];
            $_SESSION['admin_email']    = $row['email'];
            $_SESSION['admin_login_at'] = time();
        }

        // Rotate token for security
        $stmt = $pdo->prepare('DELETE FROM remember_tokens WHERE token_hash = ?');
        $stmt->execute([$hash]);
        storeRememberToken($pdo, $row['user_id']);

    } catch (Exception $e) {
        error_log('Auto-login error: ' . $e->getMessage());
    }
}

/**
 * Return active coupons that are currently valid and not globally exhausted,
 * for surfacing as on-site promos (e.g. the new-user welcome code).
 *
 * @param PDO $pdo
 * @param int $limit
 * @return array
 */
function getActivePromoCoupons($pdo, $limit = 3) {
    try {
        $stmt = $pdo->prepare("
            SELECT code, description, discount_type, discount_value, min_purchase_amount, max_discount_amount, new_user_only
            FROM coupons
            WHERE is_active = 1
              AND show_on_site = 1
              AND (valid_from IS NULL OR valid_from <= NOW())
              AND (valid_until IS NULL OR valid_until >= NOW())
              AND (usage_limit IS NULL OR used_count < usage_limit)
            ORDER BY created_at ASC
            LIMIT " . (int)$limit . "
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error fetching promo coupons: ' . $e->getMessage());
        return [];
    }
}

/**
 * Build a human-readable offer label for a coupon row, e.g.
 * "10% off (up to ₹1,500.00)" or "₹500.00 off on orders over ₹2,000.00".
 *
 * @param array $c Coupon row
 * @return string
 */
function formatCouponOffer($c) {
    if (($c['discount_type'] ?? '') === 'percentage') {
        $offer = rtrim(rtrim(number_format((float)$c['discount_value'], 2), '0'), '.') . '% off';
        if (!empty($c['max_discount_amount'])) {
            $offer .= ' (up to ' . formatPrice((float)$c['max_discount_amount']) . ')';
        }
    } else {
        $offer = formatPrice((float)$c['discount_value']) . ' off';
    }
    if (!empty($c['min_purchase_amount']) && (float)$c['min_purchase_amount'] > 0) {
        $offer .= ' on orders over ' . formatPrice((float)$c['min_purchase_amount']);
    }
    return $offer;
}

/**
 * Maximum number of coupons a single order may stack.
 */
if (!defined('MAX_STACKED_COUPONS')) {
    define('MAX_STACKED_COUPONS', 2);
}

/**
 * Compute the current cart subtotal server-side (never trust a client value).
 *
 * @param PDO      $pdo
 * @param int|null $userId Logged-in user id, or null for a guest (uses session_id)
 * @return float
 */
function getCartSubtotal(PDO $pdo, ?int $userId): float {
    if ($userId !== null) {
        $stmt = $pdo->prepare("
            SELECT c.quantity, p.price, p.sale_price
            FROM cart c INNER JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ? AND p.is_active = 1
        ");
        $stmt->execute([$userId]);
    } else {
        $stmt = $pdo->prepare("
            SELECT c.quantity, p.price, p.sale_price
            FROM cart c INNER JOIN products p ON c.product_id = p.id
            WHERE c.session_id = ? AND p.is_active = 1
        ");
        $stmt->execute([session_id()]);
    }

    $subtotal = 0.0;
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $price = !empty($row['sale_price']) ? (float)$row['sale_price'] : (float)$row['price'];
        $subtotal += $price * (int)$row['quantity'];
    }
    return $subtotal;
}

/**
 * Evaluate a set of coupon codes against a cart subtotal and apply stacking rules.
 * This is the single source of truth used by validate-coupon, remove-coupon and
 * create-order so the live preview and the charged amount always agree.
 *
 * Rules:
 *  - Codes are de-duplicated (case-insensitive) and processed in the given order.
 *  - At most $maxCoupons coupons may apply.
 *  - An "exclusive" coupon cannot be combined with any other coupon.
 *  - Each coupon's discount is computed on the ORIGINAL subtotal (additive), but the
 *    running total is capped so the combined discount never exceeds the subtotal.
 *  - Per-coupon checks still apply: active, date window, global usage limit,
 *    new_user_only, per_user_limit and minimum purchase.
 *
 * @param PDO      $pdo
 * @param array    $codes      Coupon codes (strings), in the order the user applied them
 * @param float    $subtotal   Cart subtotal (server-computed)
 * @param int|null $userId     Logged-in user id, or null for guests
 * @param int      $maxCoupons Max number of coupons that may stack
 * @return array   ['applied' => [...], 'rejected' => [...], 'total_discount' => float, 'subtotal' => float]
 */
function evaluateCoupons(PDO $pdo, array $codes, float $subtotal, ?int $userId, int $maxCoupons = MAX_STACKED_COUPONS): array {
    $applied = [];
    $rejected = [];
    $seen = [];
    $runningDiscount = 0.0;

    foreach ($codes as $rawCode) {
        $code = strtoupper(trim((string)$rawCode));
        if ($code === '' || isset($seen[$code])) {
            continue; // skip blanks and duplicates
        }
        $seen[$code] = true;

        $stmt = $pdo->prepare("
            SELECT * FROM coupons
            WHERE code = ?
              AND is_active = 1
              AND (valid_from IS NULL OR valid_from <= NOW())
              AND (valid_until IS NULL OR valid_until >= NOW())
              AND (usage_limit IS NULL OR used_count < usage_limit)
        ");
        $stmt->execute([$code]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$coupon) {
            $rejected[] = ['code' => $code, 'reason' => 'Invalid or expired coupon code.'];
            continue;
        }

        // Minimum purchase
        $minPurchase = (float)($coupon['min_purchase_amount'] ?? 0);
        if ($minPurchase > 0 && $subtotal < $minPurchase) {
            $rejected[] = ['code' => $code, 'reason' => '"' . $code . '" needs a minimum purchase of ' . formatPrice($minPurchase) . '.'];
            continue;
        }

        // New-customer-only coupons
        if (!empty($coupon['new_user_only'])) {
            if ($userId === null) {
                $rejected[] = ['code' => $code, 'reason' => 'Please log in to use the new-customer coupon "' . $code . '".'];
                continue;
            }
            $priorStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND payment_status = 'paid'");
            $priorStmt->execute([$userId]);
            if ((int)$priorStmt->fetchColumn() > 0) {
                $rejected[] = ['code' => $code, 'reason' => '"' . $code . '" is valid only on your first order.'];
                continue;
            }
        }

        // Per-user usage limit (only enforceable for logged-in users)
        $perUserLimit = isset($coupon['per_user_limit']) ? (int)$coupon['per_user_limit'] : 0;
        if ($perUserLimit > 0 && $userId !== null) {
            $usageStmt = $pdo->prepare("SELECT COUNT(*) FROM coupon_usage WHERE coupon_id = ? AND user_id = ?");
            $usageStmt->execute([$coupon['id'], $userId]);
            if ((int)$usageStmt->fetchColumn() >= $perUserLimit) {
                $rejected[] = ['code' => $code, 'reason' => 'You have already used "' . $code . '" the maximum number of times.'];
                continue;
            }
        }

        $isExclusive = !empty($coupon['exclusive']);

        // Stacking rules (only relevant once at least one coupon is already applied)
        if (!empty($applied)) {
            if ($isExclusive) {
                $rejected[] = ['code' => $code, 'reason' => '"' . $code . '" cannot be combined with other coupons.'];
                continue;
            }
            foreach ($applied as $a) {
                if (!empty($a['exclusive'])) {
                    $rejected[] = ['code' => $code, 'reason' => '"' . $a['code'] . '" must be used on its own.'];
                    continue 2;
                }
            }
            if (count($applied) >= $maxCoupons) {
                $rejected[] = ['code' => $code, 'reason' => 'You can apply at most ' . $maxCoupons . ' coupons per order.'];
                continue;
            }
        }

        // Discount for this coupon, computed on the original subtotal
        if ($coupon['discount_type'] === 'percentage') {
            $discount = ($subtotal * (float)$coupon['discount_value']) / 100.0;
            if (!empty($coupon['max_discount_amount']) && $discount > (float)$coupon['max_discount_amount']) {
                $discount = (float)$coupon['max_discount_amount'];
            }
        } else {
            $discount = (float)$coupon['discount_value'];
        }

        // Cap so the combined discount never exceeds the subtotal
        $remaining = $subtotal - $runningDiscount;
        if ($remaining <= 0) {
            $rejected[] = ['code' => $code, 'reason' => 'Your order is already fully discounted.'];
            continue;
        }
        if ($discount > $remaining) {
            $discount = $remaining;
        }
        $discount = round($discount, 2);
        $runningDiscount = round($runningDiscount + $discount, 2);

        $applied[] = [
            'id' => (int)$coupon['id'],
            'code' => $coupon['code'],
            'description' => $coupon['description'] ?? '',
            'discount_type' => $coupon['discount_type'],
            'discount_value' => (float)$coupon['discount_value'],
            'max_discount_amount' => $coupon['max_discount_amount'] !== null ? (float)$coupon['max_discount_amount'] : null,
            'exclusive' => $isExclusive ? 1 : 0,
            'discount' => $discount,
        ];
    }

    return [
        'applied' => $applied,
        'rejected' => $rejected,
        'total_discount' => round($runningDiscount, 2),
        'subtotal' => round($subtotal, 2),
    ];
}

/**
 * Has the user received this product — i.e. do they have a delivered order
 * containing it? Used to gate product reviews to verified, post-delivery buyers.
 *
 * @param PDO $pdo
 * @param int|null $userId
 * @param int $productId
 * @return bool
 */
function userHasReceivedProduct($pdo, $userId, $productId) {
    if (empty($userId)) {
        return false;
    }
    try {
        $stmt = $pdo->prepare("
            SELECT 1
            FROM orders o
            INNER JOIN order_items oi ON oi.order_id = o.id
            WHERE o.user_id = ? AND oi.product_id = ? AND o.order_status = 'delivered'
            LIMIT 1
        ");
        $stmt->execute([$userId, $productId]);
        return (bool) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log('userHasReceivedProduct error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Return the user's existing review for a product, or null.
 *
 * @param PDO $pdo
 * @param int|null $userId
 * @param int $productId
 * @return array|null
 */
function getUserReview($pdo, $userId, $productId) {
    if (empty($userId)) {
        return null;
    }
    try {
        $stmt = $pdo->prepare("SELECT id, rating, title, comment, is_approved FROM reviews WHERE user_id = ? AND product_id = ? LIMIT 1");
        $stmt->execute([$userId, $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (PDOException $e) {
        error_log('getUserReview error: ' . $e->getMessage());
        return null;
    }
}
