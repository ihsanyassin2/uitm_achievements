<?php
$page_title = "UiTM Staff Profiles - UiTM Achievements";
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../functions/functions.php';

$pdo = get_pdo_connection();
$staff_list = [];

// Pagination (optional for this page, but good practice if many staff)
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 15;
$offset = ($current_page - 1) * $records_per_page;
$total_records = 0;

// Search/filter (optional)
$search_staff = isset($_GET['search_staff']) ? sanitize_input($_GET['search_staff']) : '';

if ($pdo) {
    try {
        $sql_where = "";
        $params = [];

        // Consider only listing staff who have at least one approved achievement, or all users.
        // For now, listing all users. Add a join to achievements and a count if needed.
        // "SELECT u.*, COUNT(a.id) as achievement_count FROM users u LEFT JOIN achievements a ON u.id = a.user_id AND a.status = 'approved' GROUP BY u.id HAVING achievement_count > 0"

        $base_sql_select = "SELECT u.id, u.name, u.email, u.biography,
                                (SELECT COUNT(a.id) FROM achievements a WHERE a.user_id = u.id AND a.status = 'approved') as approved_achievements_count";
        $base_sql_from = " FROM users u";

        if (!empty($search_staff)) {
            $sql_where = " WHERE (u.name LIKE :search_staff OR u.email LIKE :search_staff)"; // Add more fields to search if needed e.g. biography
            $params[':search_staff'] = '%' . $search_staff . '%';
        }

        // Count total records for pagination
        $stmt_count = $pdo->prepare("SELECT COUNT(u.id)" . $base_sql_from . $sql_where);
        $stmt_count->execute($params);
        $total_records = $stmt_count->fetchColumn();

        $sql_order = " ORDER BY u.name ASC"; // Or by number of achievements, etc.
        $sql_limit = " LIMIT :limit OFFSET :offset";

        $stmt_list = $pdo->prepare($base_sql_select . $base_sql_from . $sql_where . $sql_order . $sql_limit);
        foreach ($params as $key => $val) {
            $stmt_list->bindValue($key, $val);
        }
        $stmt_list->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
        $stmt_list->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt_list->execute();
        $staff_list = $stmt_list->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Staff List PDOException: " . $e->getMessage());
        set_flash_message("Error loading staff list: " . $e->getMessage(), "danger");
    }
}

include_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-users"></i> UiTM Staff Profiles</h1>
        <!-- Optional: Link to an "Add your profile" or "How to get listed" page -->
    </div>

    <!-- Search Form -->
    <form method="get" action="<?php echo SITE_URL; ?>public/staff_profiles_list.php" class="mb-4">
        <div class="input-group">
            <input type="text" name="search_staff" class="form-control" placeholder="Search staff by name or email..." value="<?php echo htmlspecialchars($search_staff); ?>">
            <div class="input-group-append">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
            </div>
        </div>
    </form>

    <?php if (!empty($staff_list)): ?>
        <div class="row">
            <?php foreach ($staff_list as $staff): ?>
                <div class="col-md-6 col-lg-4 mb-4 d-flex align-items-stretch">
                    <div class="card h-100 w-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <a href="<?php echo SITE_URL; ?>public/staff_profile.php?user_id=<?php echo $staff['id']; ?>">
                                    <i class="fas fa-user-tie mr-2"></i><?php echo htmlspecialchars($staff['name']); ?>
                                </a>
                            </h5>
                            <p class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($staff['email']); ?></p>
                            <?php if(!empty($staff['biography'])): ?>
                                <p class="card-text small text-truncate-3-lines flex-grow-1">
                                    <?php echo htmlspecialchars(strip_tags($staff['biography'])); ?>
                                </p>
                            <?php else: ?>
                                <p class="card-text small text-muted flex-grow-1"><i>No biography provided.</i></p>
                            <?php endif; ?>
                            <p class="card-text mt-2">
                                <small class="text-success font-weight-bold">
                                    <?php echo $staff['approved_achievements_count']; ?> Approved Achievement<?php echo ($staff['approved_achievements_count'] != 1) ? 's' : ''; ?>
                                </small>
                            </p>
                            <a href="<?php echo SITE_URL; ?>public/staff_profile.php?user_id=<?php echo $staff['id']; ?>" class="btn btn-sm btn-outline-primary mt-auto align-self-start">View Profile <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_records > $records_per_page):
            $total_pages = ceil($total_records / $records_per_page);
        ?>
        <nav aria-label="Staff List Pagination">
            <ul class="pagination justify-content-center">
                <?php if ($current_page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $current_page - 1; ?>&search_staff=<?php echo urlencode($search_staff); ?>">Previous</a></li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++):
                    if ($i == 1 || $i == $total_pages || ($i >= $current_page - 2 && $i <= $current_page + 2)):
                ?>
                    <li class="page-item <?php if ($i == $current_page) echo 'active'; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&search_staff=<?php echo urlencode($search_staff); ?>"><?php echo $i; ?></a></li>
                <?php elseif (($i == $current_page - 3 && $current_page -3 > 1) || ($i == $current_page + 3 && $current_page + 3 < $total_pages)): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif; endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $current_page + 1; ?>&search_staff=<?php echo urlencode($search_staff); ?>">Next</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-info text-center">
            <h4><i class="fas fa-search-minus"></i> No Staff Profiles Found</h4>
            <p>No staff profiles match your current criteria<?php if($search_staff) echo ' for "'.htmlspecialchars($search_staff).'"'; ?>.</p>
            <?php if($search_staff): ?>
                 <a href="<?php echo SITE_URL; ?>public/staff_profiles_list.php" class="btn btn-primary"><i class="fas fa-list"></i> View All Staff</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<style>
.text-truncate-3-lines { /* Ensure this is also in main style.css if used widely */
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.4em;
    max-height: calc(1.4em * 3);
}
</style>
<?php
include_once dirname(__FILE__) . '/../includes/footer.php';
?>
