<?php
// public/view_achievements.php - Page to display all achievements or a single one
$page_title = "View Achievements - UiTM Achievements";
$config_path = __DIR__ . '/../config/config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Critical error: Main configuration file not found.");
}

// Get achievement ID from query string if viewing a single achievement
$achievement_id_param = isset($_GET['id']) ? intval($_GET['id']) : null;

// Filters for list view
$search_query = isset($_GET['search_query']) ? sanitize_input($_GET['search_query']) : '';
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : ''; // Expecting category ID
$level_filter = isset($_GET['level']) ? sanitize_input($_GET['level']) : '';
$sort_by = isset($_GET['sort_by']) ? sanitize_input($_GET['sort_by']) : 'approved_at_desc'; // Default sort

// Pagination
$records_per_page = get_setting('records_per_page') ? intval(get_setting('records_per_page')) : 10;
if ($records_per_page <= 0) $records_per_page = 10;
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $records_per_page;

include_once SITE_ROOT . 'includes/header.php';

$db = db_connect();
if (!$db) {
    $_SESSION['error_message'] = "Database connection error.";
}

// Function to get like count (can be moved to functions.php if used elsewhere)
function get_like_count($db_conn, $achievement_id) {
    $stmt_likes = $db_conn->prepare("SELECT COUNT(*) as count FROM likes WHERE achievement_id = ?");
    if ($stmt_likes) {
        $stmt_likes->bind_param("i", $achievement_id);
        $stmt_likes->execute();
        $count = $stmt_likes->get_result()->fetch_assoc()['count'] ?? 0;
        $stmt_likes->close();
        return $count;
    }
    return 0;
}
// Function to check if current session liked an achievement
function has_session_liked($db_conn, $achievement_id, $session_id) {
    $stmt_check = $db_conn->prepare("SELECT id FROM likes WHERE achievement_id = ? AND session_id = ?");
     if ($stmt_check) {
        $stmt_check->bind_param("is", $achievement_id, $session_id);
        $stmt_check->execute();
        $has_liked = $stmt_check->get_result()->num_rows > 0;
        $stmt_check->close();
        return $has_liked;
    }
    return false;
}


?>

<div class="container public-container mt-4 mb-5">
    <?php
    display_message('success_message');
    display_message('error_message');
    ?>

    <?php if ($achievement_id_param && $db): // Displaying a single achievement detail ?>
        <?php
        // Fetch single achievement details
        $stmt_single = $db->prepare(
            "SELECT a.*, c.name as category_name, u.full_name as submitter_name, u.uitm_id as submitter_uitm_id
             FROM achievements a
             JOIN achievement_categories c ON a.category_id = c.id
             JOIN users u ON a.user_id = u.id
             WHERE a.id = ? AND a.status = 'approved'"
        );
        $achievement = null;
        $media_items = [];
        $like_count = 0;
        $current_session_liked = false;

        if ($stmt_single) {
            $stmt_single->bind_param("i", $achievement_id_param);
            $stmt_single->execute();
            $result_single = $stmt_single->get_result();
            if ($result_single->num_rows === 1) {
                $achievement = $result_single->fetch_assoc();
                $page_title = htmlspecialchars($achievement['title']) . " - UiTM Achievements";

                // Fetch media for this achievement
                $stmt_media = $db->prepare("SELECT media_type, file_path_or_url, caption FROM achievement_media WHERE achievement_id = ? ORDER BY id ASC");
                if($stmt_media){
                    $stmt_media->bind_param("i", $achievement_id_param);
                    $stmt_media->execute();
                    $result_media = $stmt_media->get_result();
                    while($row_media = $result_media->fetch_assoc()){
                        $media_items[] = $row_media;
                    }
                    $stmt_media->close();
                }
                // Fetch like count and status
                $like_count = get_like_count($db, $achievement_id_param);
                $current_session_liked = has_session_liked($db, $achievement_id_param, session_id());

            } else {
                echo "<div class='alert alert-warning'>Achievement not found or not approved for public viewing. <a href='".SITE_URL."public/view_achievements.php'>View all achievements</a>.</div>";
            }
            $stmt_single->close();
        } else {
             echo "<div class='alert alert-danger'>Error preparing statement to fetch achievement details.</div>";
        }
        ?>
        <?php if ($achievement): ?>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>public/index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>public/view_achievements.php">All Achievements</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($achievement['title']); ?></li>
                </ol>
            </nav>

            <h1 class="mb-3 display-5"><?php echo htmlspecialchars($achievement['title']); ?></h1>
            <p class="text-muted">
                Category: <a href="<?php echo SITE_URL; ?>public/view_achievements.php?category=<?php echo urlencode($achievement['category_id']); ?>"><?php echo htmlspecialchars($achievement['category_name']); ?></a> |
                Level: <?php echo htmlspecialchars($achievement['level']); ?> |
                Date: <?php echo !empty($achievement['achievement_date']) ? date("d M Y", strtotime($achievement['achievement_date'])) : "N/A"; ?> <br>
                Submitted by: <a href="<?php echo SITE_URL; ?>public/staff_profile.php?uitm_id=<?php echo htmlspecialchars($achievement['submitter_uitm_id']); ?>"><?php echo htmlspecialchars($achievement['submitter_name']); ?></a>
            </p>
            <p class="text-muted">
                Person In Charge: <?php echo htmlspecialchars($achievement['pic_name']); ?>
                (<?php echo htmlspecialchars($achievement['pic_email']); ?>
                <?php if(!empty($achievement['pic_phone'])) echo ", " . htmlspecialchars($achievement['pic_phone']); ?>)
            </p>


            <?php if (!empty($media_items)): ?>
                <div id="achievementMediaCarousel" class="carousel slide mb-4" data-ride="carousel">
                    <ol class="carousel-indicators">
                        <?php
                        $image_index = 0;
                        foreach ($media_items as $media): ?>
                            <?php if ($media['media_type'] == 'image'): ?>
                            <li data-target="#achievementMediaCarousel" data-slide-to="<?php echo $image_index; ?>" class="<?php echo $image_index == 0 ? 'active' : ''; ?>"></li>
                            <?php $image_index++; endif; ?>
                        <?php endforeach; ?>
                    </ol>
                    <div class="carousel-inner">
                        <?php $first_image = true; ?>
                        <?php foreach ($media_items as $media): ?>
                            <?php if ($media['media_type'] == 'image'): ?>
                            <div class="carousel-item <?php if ($first_image) { echo 'active'; $first_image = false; } ?>">
                                <img src="<?php echo SITE_URL . htmlspecialchars($media['file_path_or_url']); ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($media['caption'] ?? $achievement['title']); ?>" style="max-height: 500px; object-fit: contain; background-color: #f0f0f0;">
                                <?php if (!empty($media['caption'])): ?>
                                <div class="carousel-caption d-none d-md-block bg-dark p-2" style="opacity:0.7;">
                                    <p class="mb-0"><?php echo htmlspecialchars($media['caption']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($image_index > 1) : // Show controls only if more than one image ?>
                    <a class="carousel-control-prev" href="#achievementMediaCarousel" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true" style="background-color: rgba(0,0,0,0.5); border-radius: 50%;"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#achievementMediaCarousel" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true" style="background-color: rgba(0,0,0,0.5); border-radius: 50%;"></span>
                        <span class="sr-only">Next</span>
                    </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="achievement-content lead mb-4">
                <?php echo nl2br(htmlspecialchars($achievement['description'])); ?>
            </div>

            <?php foreach ($media_items as $media): ?>
                <?php if ($media['media_type'] == 'youtube_video'):
                    $youtube_embed_url = preg_replace(
                        "/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
                        "https://www.youtube.com/embed/$2",
                        $media['file_path_or_url']
                    );
                ?>
                <div class="embed-responsive embed-responsive-16by9 my-4 shadow-sm">
                    <iframe class="embed-responsive-item" src="<?php echo htmlspecialchars($youtube_embed_url); ?>" allowfullscreen></iframe>
                </div>
                 <?php if (!empty($media['caption'])): ?><p class="text-center text-muted small"><?php echo htmlspecialchars($media['caption']); ?></p><?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>

            <hr class="my-4">
            <div class="d-flex justify-content-between align-items-center">
                 <button class="btn btn-lg <?php echo $current_session_liked ? 'btn-danger' : 'btn-outline-danger'; ?> like-button" data-achievement-id="<?php echo $achievement['id']; ?>">
                    <i class="<?php echo $current_session_liked ? 'fas' : 'far'; ?> fa-heart"></i>
                    <?php echo $current_session_liked ? 'Liked' : 'Like'; ?>
                    <span class="like-count">(<?php echo $like_count; ?>)</span>
                </button>
                <div>
                    <span class="mr-2 text-muted">Share:</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL . 'public/view_achievements.php?id=' . $achievement['id']); ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fab fa-facebook-f"></i> Facebook</a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL . 'public/view_achievements.php?id=' . $achievement['id']); ?>&text=<?php echo urlencode($achievement['title']); ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fab fa-twitter"></i> Twitter</a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(SITE_URL . 'public/view_achievements.php?id=' . $achievement['id']); ?>&title=<?php echo urlencode($achievement['title']); ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fab fa-linkedin-in"></i> LinkedIn</a>
                </div>
            </div>
        <?php endif; ?>

    <?php else: // Displaying list of achievements with filters ?>
        <h1 class="mb-4">All Achievements</h1>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="mb-4 p-3 border rounded bg-light">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="publicSearchQuery">Search</label>
                    <input type="text" class="form-control form-control-sm" id="publicSearchQuery" name="search_query" placeholder="Keywords, title..." value="<?php echo htmlspecialchars($search_query); ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="publicCategoryFilter">Category</label>
                    <select id="publicCategoryFilter" name="category" class="form-control form-control-sm">
                        <option value="">All Categories</option>
                        <?php
                        if ($db) {
                           $cat_res = $db->query("SELECT id, name FROM achievement_categories ORDER BY name ASC");
                           if ($cat_res) {
                               while($cat_row = $cat_res->fetch_assoc()) {
                                   $selected = ($category_filter == $cat_row['id']) ? 'selected' : '';
                                   echo "<option value='".htmlspecialchars($cat_row['id'])."' ".$selected.">".htmlspecialchars($cat_row['name'])."</option>";
                               }
                           }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label for="publicLevelFilter">Level</label>
                     <select id="publicLevelFilter" name="level" class="form-control form-control-sm">
                        <option value="">All Levels</option>
                        <option value="International" <?php if ($level_filter == 'International') echo 'selected'; ?>>International</option>
                        <option value="National" <?php if ($level_filter == 'National') echo 'selected'; ?>>National</option>
                        <option value="Institutional" <?php if ($level_filter == 'Institutional') echo 'selected'; ?>>Institutional</option>
                    </select>
                </div>
                <div class="form-group col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm mr-2"><i class="fas fa-filter"></i> Filter</button>
                    <a href="<?php echo SITE_URL; ?>public/view_achievements.php" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Clear</a>
                </div>
            </div>
        </form>

        <div class="row">
            <?php
            $achievements_list = [];
            $total_records = 0;
            if ($db) {
                $sql_conditions = ["a.status = 'approved'"];
                $sql_params = [];
                $sql_types = "";

                if (!empty($search_query)) {
                    $sql_conditions[] = "(a.title LIKE ? OR a.description LIKE ? OR u.full_name LIKE ?)";
                    $search_like = "%" . $search_query . "%";
                    array_push($sql_params, $search_like, $search_like, $search_like);
                    $sql_types .= "sss";
                }
                if (!empty($category_filter)) {
                    $sql_conditions[] = "a.category_id = ?";
                    $sql_params[] = $category_filter;
                    $sql_types .= "i";
                }
                if (!empty($level_filter)) {
                    $sql_conditions[] = "a.level = ?";
                    $sql_params[] = $level_filter;
                    $sql_types .= "s";
                }

                $where_clause = !empty($sql_conditions) ? " WHERE " . implode(" AND ", $sql_conditions) : "";

                // Count total records for pagination
                $count_sql = "SELECT COUNT(DISTINCT a.id) as total
                              FROM achievements a
                              JOIN users u ON a.user_id = u.id
                              JOIN achievement_categories c ON a.category_id = c.id" . $where_clause;
                $stmt_count = $db->prepare($count_sql);
                if ($stmt_count) {
                    if (!empty($sql_params)) $stmt_count->bind_param($sql_types, ...$sql_params);
                    $stmt_count->execute();
                    $total_records = $stmt_count->get_result()->fetch_assoc()['total'] ?? 0;
                    $stmt_count->close();
                } else {
                    error_log("SQL Count Error: " . $db->error);
                }

                $total_pages = ceil($total_records / $records_per_page);

                // Fetch records for current page
                $list_sql = "SELECT DISTINCT a.id, a.title, a.description, c.name as category_name, c.id as category_id, u.full_name as submitter_name, u.uitm_id as submitter_uitm_id, a.approved_at,
                                (SELECT file_path_or_url FROM achievement_media am WHERE am.achievement_id = a.id AND am.media_type = 'image' ORDER BY am.id ASC LIMIT 1) as image_url,
                                (SELECT COUNT(l.id) FROM likes l WHERE l.achievement_id = a.id) as like_count
                             FROM achievements a
                             JOIN achievement_categories c ON a.category_id = c.id
                             JOIN users u ON a.user_id = u.id" . $where_clause;

                // Sorting
                $order_by_clause = " ORDER BY a.approved_at DESC, a.id DESC "; // Default
                // Add more sort options if needed based on $sort_by

                $list_sql .= $order_by_clause . " LIMIT ? OFFSET ?";
                $sql_params[] = $records_per_page;
                $sql_params[] = $offset;
                $sql_types .= "ii";

                $stmt_list = $db->prepare($list_sql);
                if ($stmt_list) {
                    $stmt_list->bind_param($sql_types, ...$sql_params);
                    $stmt_list->execute();
                    $result_list = $stmt_list->get_result();
                    while($ach = $result_list->fetch_assoc()){ $achievements_list[] = $ach; }
                    $stmt_list->close();
                } else {
                     echo "<p class='col-12 text-center text-danger'>Error fetching achievements list: " . $db->error . "</p>";
                     error_log("SQL List Error: " . $db->error);
                }
            }

            if (empty($achievements_list)) {
                echo "<p class='col-12 text-center mt-4'>No achievements found matching your criteria.</p>";
            } else {
                foreach ($achievements_list as $ach):
            ?>
            <div class="col-md-6 col-lg-4 mb-4 d-flex align-items-stretch">
                <div class="card achievement-card w-100">
                     <div class="card-img-top-container">
                        <a href="<?php echo SITE_URL; ?>public/view_achievements.php?id=<?php echo $ach['id']; ?>">
                            <img src="<?php echo !empty($ach['image_url']) ? SITE_URL . htmlspecialchars($ach['image_url']) : SITE_URL . 'assets/placeholder_achievement.png'; ?>"
                                 class="card-img-top" alt="<?php echo htmlspecialchars($ach['title']); ?>">
                        </a>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><a href="<?php echo SITE_URL; ?>public/view_achievements.php?id=<?php echo $ach['id']; ?>"><?php echo htmlspecialchars($ach['title']); ?></a></h5>
                        <p class="card-text small text-muted">
                            Category: <a href="<?php echo SITE_URL . 'public/view_achievements.php?category=' . $ach['category_id']; ?>"><?php echo htmlspecialchars($ach['category_name']); ?></a> |
                            By: <a href="<?php echo SITE_URL . 'public/staff_profile.php?uitm_id=' . $ach['submitter_uitm_id']; ?>"><?php echo htmlspecialchars($ach['submitter_name']); ?></a>
                        </p>
                        <p class="card-text flex-grow-1"><?php echo htmlspecialchars(substr(strip_tags($ach['description']), 0, 120)) . (strlen(strip_tags($ach['description'])) > 120 ? '...' : ''); ?></p>
                        <a href="<?php echo SITE_URL; ?>public/view_achievements.php?id=<?php echo $ach['id']; ?>" class="btn btn-sm btn-outline-primary mt-auto align-self-start">Read More</a>
                    </div>
                    <div class="card-footer text-muted d-flex justify-content-between align-items-center">
                        <small>Approved: <?php echo !empty($ach['approved_at']) ? date("d M Y", strtotime($ach['approved_at'])) : 'N/A'; ?></small>
                        <span>
                            <i class="far fa-heart"></i> <?php echo $ach['like_count']; ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php
                endforeach;
            }?>
        </div>

        <!-- Pagination controls -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation achievements" class="mt-4 d-flex justify-content-center">
          <ul class="pagination">
            <?php if ($current_page > 1): ?>
                <li class="page-item"><a class="page-link" href="?page=<?php echo $current_page - 1; ?>&search_query=<?php echo urlencode($search_query); ?>&category=<?php echo $category_filter; ?>&level=<?php echo urlencode($level_filter); ?>">Previous</a></li>
            <?php else: ?>
                <li class="page-item disabled"><span class="page-link">Previous</span></li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++):
                // Show limited page numbers
                if ($i == 1 || $i == $total_pages || ($i >= $current_page - 2 && $i <= $current_page + 2)): ?>
                    <li class="page-item <?php if ($i == $current_page) echo 'active'; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search_query=<?php echo urlencode($search_query); ?>&category=<?php echo $category_filter; ?>&level=<?php echo urlencode($level_filter); ?>"><?php echo $i; ?></a>
                    </li>
                <?php elseif ($i == $current_page - 3 || $i == $current_page + 3) : // Ellipsis ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($current_page < $total_pages): ?>
                <li class="page-item"><a class="page-link" href="?page=<?php echo $current_page + 1; ?>&search_query=<?php echo urlencode($search_query); ?>&category=<?php echo $category_filter; ?>&level=<?php echo urlencode($level_filter); ?>">Next</a></li>
            <?php else: ?>
                 <li class="page-item disabled"><span class="page-link">Next</span></li>
            <?php endif; ?>
          </ul>
        </nav>
        <?php endif; ?>

    <?php endif; ?>
    <?php if ($db) $db->close(); ?>
</div>

<script>
// Basic AJAX for liking (requires jQuery, included in header)
$(document).ready(function() {
    // CSRF token for AJAX requests - get it once or ensure it's available globally if needed
    var csrfToken = "<?php echo generate_csrf_token(); ?>";

    $('.like-button').on('click', function() {
        var button = $(this);
        var achievementId = button.data('achievement-id');
        var likeIcon = button.find('i');
        var likeCountSpan = button.find('.like-count');

        // Optimistic update for better UX, or wait for AJAX response
        // For simplicity, we'll update based on response.

        $.ajax({
            url: '<?php echo SITE_URL; ?>functions/ajax.php',
            type: 'POST',
            data: {
                action: 'like_achievement',
                achievement_id: achievementId,
                csrf_token: csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    likeCountSpan.text('(' + response.like_count + ')');
                    if (response.liked) {
                        likeIcon.removeClass('far').addClass('fas');
                        button.removeClass('btn-outline-danger').addClass('btn-danger').contents().filter(function(){ return this.nodeType == 3; }).first().replaceWith(" Liked ");
                    } else {
                        likeIcon.removeClass('fas').addClass('far');
                        button.removeClass('btn-danger').addClass('btn-outline-danger').contents().filter(function(){ return this.nodeType == 3; }).first().replaceWith(" Like ");
                    }
                    // Potentially update CSRF token if your mechanism requires it after each use
                    // csrfToken = response.new_csrf_token; // If ajax.php returns a new one
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('An AJAX error occurred. Please try again.');
                console.error("AJAX Error: ", status, error, xhr.responseText);
            }
        });
    });
});
</script>
<!-- Ekko Lightbox CSS is now in header.php -->
<!-- Ekko Lightbox JS and initialization are now in footer.php -->


<?php
include_once SITE_ROOT . 'includes/footer.php';
?>
