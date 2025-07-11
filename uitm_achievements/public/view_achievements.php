<?php
// This page displays achievements.
// Can show a single achievement if 'id' is provided, or a list with filters.
$page_title = "View Achievements - UiTM Achievements";
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../functions/functions.php';

$pdo = get_pdo_connection();
$achievement_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$single_achievement = null;
$achievement_media = [];
$achievements_list = [];
$categories = ['Academic', 'Research', 'Student Development', 'Industrial Linkages', 'Internationalization', 'Recognition & Awards', 'Corporate Social Responsibility (CSR)']; // From DB enum
$levels = ['International', 'National', 'Institutional'];

// For list view: search and filter parameters
$search_term = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$filter_category = isset($_GET['category']) && in_array($_GET['category'], $categories) ? $_GET['category'] : '';
$filter_level = isset($_GET['level']) && in_array($_GET['level'], $levels) ? $_GET['level'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'updated_at_desc'; // e.g., 'likes_desc', 'title_asc'

// Pagination for list view
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10; // Configurable?
$offset = ($current_page - 1) * $records_per_page;
$total_records = 0;


if (!$pdo) {
    set_flash_message("Database connection error.", "danger");
    // Allow page to render but show error.
}

if ($achievement_id > 0 && $pdo) { // Display single achievement
    try {
        $stmt = $pdo->prepare("
            SELECT a.*, u.name as author_name, u.id as author_id,
                   (SELECT COUNT(*) FROM achievement_likes WHERE achievement_id = a.id) as like_count
            FROM achievements a
            JOIN users u ON a.user_id = u.id
            WHERE a.id = ? AND a.status = 'approved'
        ");
        $stmt->execute([$achievement_id]);
        $single_achievement = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($single_achievement) {
            $page_title = htmlspecialchars($single_achievement['title']) . " - UiTM Achievements";
            // Fetch media (images and videos)
            $stmt_media = $pdo->prepare("SELECT media_type, media_url FROM achievement_media WHERE achievement_id = ? ORDER BY id ASC");
            $stmt_media->execute([$achievement_id]);
            $achievement_media = $stmt_media->fetchAll(PDO::FETCH_ASSOC);
        } else {
            set_flash_message("Achievement not found or not approved for public viewing.", "warning");
        }
    } catch (PDOException $e) {
        error_log("View Single Achievement PDOException: " . $e->getMessage());
        set_flash_message("Error loading achievement: " . $e->getMessage(), "danger");
    }
} elseif ($pdo) { // Display list of achievements (with filters and pagination)
    $page_title = "All Achievements - UiTM Achievements";
    if ($search_term) $page_title = "Search Results for '".htmlspecialchars($search_term)."' - UiTM Achievements";
    elseif ($filter_category) $page_title = htmlspecialchars($filter_category)." Achievements - UiTM Achievements";

    $base_sql_select = "SELECT a.*, u.name as author_name, u.id as author_id,
                        (SELECT COUNT(*) FROM achievement_likes WHERE achievement_id = a.id) as like_count,
                        (SELECT GROUP_CONCAT(am.media_url SEPARATOR '||') FROM achievement_media am WHERE am.achievement_id = a.id AND am.media_type = 'image' ORDER BY am.id LIMIT 1) as first_image_url";
    $base_sql_from = " FROM achievements a JOIN users u ON a.user_id = u.id";
    $sql_where = " WHERE a.status = 'approved'";
    $params = [];

    if (!empty($search_term)) {
        $sql_where .= " AND (a.title LIKE :search_term OR a.description LIKE :search_term OR u.name LIKE :search_term)";
        $params[':search_term'] = '%' . $search_term . '%';
    }
    if (!empty($filter_category)) {
        $sql_where .= " AND a.category = :category";
        $params[':category'] = $filter_category;
    }
    if (!empty($filter_level)) {
        $sql_where .= " AND a.level = :level";
        $params[':level'] = $filter_level;
    }

    // Count total records for pagination
    $stmt_count = $pdo->prepare("SELECT COUNT(a.id)" . $base_sql_from . $sql_where);
    $stmt_count->execute($params);
    $total_records = $stmt_count->fetchColumn();

    // Determine sort order
    $order_by_sql = " ORDER BY ";
    switch ($sort_by) {
        case 'likes_desc':
            $order_by_sql .= "like_count DESC, a.updated_at DESC";
            break;
        case 'title_asc':
            $order_by_sql .= "a.title ASC";
            break;
        case 'title_desc':
            $order_by_sql .= "a.title DESC";
            break;
        case 'date_asc':
            $order_by_sql .= "a.updated_at ASC";
            break;
        // case 'updated_at_desc':
        default:
            $order_by_sql .= "a.updated_at DESC";
            break;
    }

    $sql_limit = " LIMIT :limit OFFSET :offset";

    $stmt_list = $pdo->prepare($base_sql_select . $base_sql_from . $sql_where . $order_by_sql . $sql_limit);
    // Bind common params
    foreach ($params as $key => $val) {
        $stmt_list->bindValue($key, $val);
    }
    // Bind limit and offset
    $stmt_list->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
    $stmt_list->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt_list->execute();
    $achievements_list = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
}


include_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="container mt-4">
    <?php if ($single_achievement): ?>
        <!-- Single Achievement View -->
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <article class="achievement-single">
                    <header class="mb-4">
                        <h1 class="display-4"><?php echo htmlspecialchars($single_achievement['title']); ?></h1>
                        <p class="lead achievement-meta">
                            By <a href="<?php echo SITE_URL . 'public/staff_profile.php?user_id=' . $single_achievement['author_id']; ?>"><?php echo htmlspecialchars($single_achievement['author_name']); ?></a> |
                            <i class="fas fa-calendar-alt"></i> <?php echo date("F j, Y", strtotime($single_achievement['updated_at'])); ?> |
                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($single_achievement['category']); ?> |
                            <i class="fas fa-flag"></i> Level: <?php echo htmlspecialchars($single_achievement['level']); ?>
                        </p>
                        <p>
                            <button class="btn btn-outline-primary btn-sm like-button" data-achievement-id="<?php echo $single_achievement['id']; ?>">
                                <i class="fas fa-thumbs-up"></i> Like <span class="like-count"><?php echo $single_achievement['like_count']; ?></span>
                            </button>
                            <!-- Add share buttons here -->
                        </p>
                    </header>

                    <?php if (!empty($achievement_media)): ?>
                        <div id="achievementMediaCarousel" class="carousel slide mb-4" data-ride="carousel">
                            <div class="carousel-inner bg-light">
                                <?php foreach ($achievement_media as $index => $media): ?>
                                    <div class="carousel-item <?php echo $index == 0 ? 'active' : ''; ?> text-center">
                                        <?php if ($media['media_type'] == 'image'):
                                            $image_url = SITE_URL . 'uploads/images/' . basename($media['media_url']);
                                            // Basic check if file exists, otherwise show placeholder
                                            $image_path = file_exists(ROOT_PATH . 'uploads/images/' . basename($media['media_url'])) ? $image_url : SITE_URL . 'assets/uitm_logo.png';
                                        ?>
                                            <img src="<?php echo $image_path; ?>" class="d-block mx-auto" style="max-height: 500px; width:auto; max-width:100%;" alt="Achievement Image <?php echo $index + 1; ?>">
                                        <?php elseif ($media['media_type'] == 'video_youtube'):
                                            // Extract YouTube video ID from URL
                                            preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $media['media_url'], $matches);
                                            $youtube_video_id = $matches[1] ?? null;
                                            if ($youtube_video_id):
                                        ?>
                                            <div class="embed-responsive embed-responsive-16by9">
                                                <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/<?php echo $youtube_video_id; ?>" allowfullscreen></iframe>
                                            </div>
                                            <?php else: ?>
                                                <p class="text-danger">Invalid YouTube Link: <?php echo htmlspecialchars($media['media_url']); ?></p>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($achievement_media) > 1): ?>
                            <a class="carousel-control-prev" href="#achievementMediaCarousel" role="button" data-slide="prev" style="background-color: rgba(0,0,0,0.2);">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#achievementMediaCarousel" role="button" data-slide="next" style="background-color: rgba(0,0,0,0.2);">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span><span class="sr-only">Next</span>
                            </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <section class="achievement-content">
                        <?php echo nl2br(htmlspecialchars($single_achievement['description'])); // Consider using a Markdown parser if content is Markdown ?>
                    </section>

                    <hr class="my-4">
                    <a href="<?php echo SITE_URL; ?>public/view_achievements.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to All Achievements</a>
                </article>
            </div>
        </div>

    <?php else: ?>
        <!-- List of Achievements View -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-6">
                <h3>
                    <?php
                    if ($search_term) echo "Search Results: <small class='text-muted'>\"" . htmlspecialchars($search_term) . "\"</small>";
                    elseif ($filter_category) echo htmlspecialchars($filter_category) . " Achievements";
                    elseif ($filter_level) echo htmlspecialchars($filter_level) . " Achievements";
                    else echo "All Approved Achievements";
                    ?>
                </h3>
            </div>
            <div class="col-md-6">
                <form action="<?php echo SITE_URL; ?>public/view_achievements.php" method="get" class="form-inline float-md-right">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($filter_category); ?>">
                    <input type="hidden" name="level" value="<?php echo htmlspecialchars($filter_level); ?>">
                    <label for="sort" class="mr-2">Sort by:</label>
                    <select name="sort" id="sort" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="updated_at_desc" <?php if($sort_by == 'updated_at_desc') echo 'selected'; ?>>Date (Newest)</option>
                        <option value="date_asc" <?php if($sort_by == 'date_asc') echo 'selected'; ?>>Date (Oldest)</option>
                        <option value="likes_desc" <?php if($sort_by == 'likes_desc') echo 'selected'; ?>>Most Liked</option>
                        <option value="title_asc" <?php if($sort_by == 'title_asc') echo 'selected'; ?>>Title (A-Z)</option>
                        <option value="title_desc" <?php if($sort_by == 'title_desc') echo 'selected'; ?>>Title (Z-A)</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Filter bar for list view (collapsible or more prominent) -->
         <form action="<?php echo SITE_URL; ?>public/view_achievements.php" method="get" class="mb-4 p-3 bg-light border rounded">
            <div class="row">
                <div class="col-md-5 form-group">
                    <label for="search_box">Search Keyword</label>
                    <input type="text" name="search" id="search_box" class="form-control form-control-sm" placeholder="Enter title, description, or author" value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <div class="col-md-3 form-group">
                    <label for="filter_cat">Category</label>
                    <select name="category" id="filter_cat" class="form-control form-control-sm">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php if($filter_category == $cat) echo 'selected'; ?>><?php echo htmlspecialchars($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 form-group">
                     <label for="filter_lev">Level</label>
                    <select name="level" id="filter_lev" class="form-control form-control-sm">
                        <option value="">All Levels</option>
                        <?php foreach ($levels as $lvl): ?>
                        <option value="<?php echo htmlspecialchars($lvl); ?>" <?php if($filter_level == $lvl) echo 'selected'; ?>><?php echo htmlspecialchars($lvl); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 form-group d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fas fa-filter"></i> Filter / Search</button>
                </div>
            </div>
        </form>


        <?php if (!empty($achievements_list)): ?>
            <div class="row">
                <?php foreach ($achievements_list as $achievement):
                    $first_image = $achievement['first_image_url'] ? SITE_URL . 'uploads/images/' . basename($achievement['first_image_url']) : SITE_URL . 'assets/uitm_logo.png';
                    $image_path = file_exists(ROOT_PATH . 'uploads/images/' . basename($achievement['first_image_url'])) ? $first_image : SITE_URL . 'assets/uitm_logo.png';
                ?>
                <div class="col-md-6 col-lg-4 mb-4 d-flex align-items-stretch">
                    <div class="card achievement-card h-100 w-100">
                        <a href="<?php echo SITE_URL; ?>public/view_achievements.php?id=<?php echo $achievement['id']; ?>">
                             <img src="<?php echo $image_path; ?>" class="card-img-top achievement-image" alt="<?php echo htmlspecialchars($achievement['title']); ?>" style="height: 180px; object-fit: cover;">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <a href="<?php echo SITE_URL; ?>public/view_achievements.php?id=<?php echo $achievement['id']; ?>"><?php echo htmlspecialchars($achievement['title']); ?></a>
                            </h5>
                            <p class="card-text achievement-meta">
                                <small class="text-muted">
                                    <i class="fas fa-user"></i> <a href="<?php echo SITE_URL . 'public/staff_profile.php?user_id=' . $achievement['author_id']; ?>"><?php echo htmlspecialchars($achievement['author_name']); ?></a><br>
                                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($achievement['category']); ?> |
                                    <i class="fas fa-flag"></i> <?php echo htmlspecialchars($achievement['level']); ?><br>
                                    <i class="fas fa-calendar-alt"></i> <?php echo date("d M Y", strtotime($achievement['updated_at'])); ?> |
                                    <i class="fas fa-thumbs-up"></i> <?php echo $achievement['like_count']; ?> Likes
                                </small>
                            </p>
                            <p class="card-text flex-grow-1 text-truncate-3-lines"><?php echo htmlspecialchars(strip_tags($achievement['description'])); // substr removed, use CSS for truncation ?></p>
                            <a href="<?php echo SITE_URL; ?>public/view_achievements.php?id=<?php echo $achievement['id']; ?>" class="btn btn-sm btn-outline-primary mt-auto align-self-start">Read More <i class="fas fa-angle-double-right"></i></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_records > $records_per_page):
                $total_pages = ceil($total_records / $records_per_page);
            ?>
            <nav aria-label="Achievements Pagination">
                <ul class="pagination justify-content-center">
                    <?php if ($current_page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search_term); ?>&category=<?php echo urlencode($filter_category); ?>&level=<?php echo urlencode($filter_level); ?>&sort=<?php echo urlencode($sort_by); ?>">Previous</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++):
                        // Show limited page numbers: e.g., first, last, current, and pages around current
                        if ($i == 1 || $i == $total_pages || ($i >= $current_page - 2 && $i <= $current_page + 2)):
                    ?>
                        <li class="page-item <?php if ($i == $current_page) echo 'active'; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_term); ?>&category=<?php echo urlencode($filter_category); ?>&level=<?php echo urlencode($filter_level); ?>&sort=<?php echo urlencode($sort_by); ?>"><?php echo $i; ?></a></li>
                    <?php elseif (($i == $current_page - 3 && $current_page -3 > 1) || ($i == $current_page + 3 && $current_page + 3 < $total_pages)): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search_term); ?>&category=<?php echo urlencode($filter_category); ?>&level=<?php echo urlencode($filter_level); ?>&sort=<?php echo urlencode($sort_by); ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="alert alert-info text-center">
                <h4><i class="fas fa-search-minus"></i> No Achievements Found</h4>
                <p>No achievements match your current criteria. Try broadening your search or filter.</p>
                <a href="<?php echo SITE_URL; ?>public/view_achievements.php" class="btn btn-primary"><i class="fas fa-list"></i> View All Achievements</a>
            </div>
        <?php endif; ?>
    <?php endif; // End of list view ?>
</div>
<style>
.text-truncate-3-lines {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.4em; /* Adjust based on your font size */
    max-height: calc(1.4em * 3); /* line-height * number of lines */
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const likeButtons = document.querySelectorAll('.like-button');
    likeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const achievementId = this.dataset.achievementId;
            const likeCountSpan = this.querySelector('.like-count');

            // AJAX request to handle the like
            // This is a placeholder. ajax.php needs to be implemented.
            // For now, just visually increment.

            // Create a FormData object for POST request
            const formData = new FormData();
            formData.append('action', 'like_achievement');
            formData.append('achievement_id', achievementId);
            // If user is logged in, you might send user_id. Otherwise, rely on IP limiting on server.
            <?php if(is_logged_in()): ?>
            formData.append('user_id', '<?php echo $_SESSION['user_id']; ?>');
            <?php endif; ?>


            fetch('<?php echo SITE_URL; ?>functions/ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (likeCountSpan) {
                        likeCountSpan.textContent = data.new_like_count;
                    }
                    // Optionally, change button state (e.g., "Liked")
                    this.classList.add('liked'); // Example class
                    this.querySelector('i').classList.remove('far'); // If using far for unliked
                    this.querySelector('i').classList.add('fas');   // fas for liked
                    if(data.message) alert(data.message); // Or use a less intrusive notification
                } else {
                    alert(data.message || 'Could not process like.');
                }
            })
            .catch(error => {
                console.error('Error liking achievement:', error);
                alert('An error occurred. Please try again.');
            });
        });
    });
});
</script>

<?php
include_once dirname(__FILE__) . '/../includes/footer.php';
?>
