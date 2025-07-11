<?php
$page_title = "UiTM Achievements Showcase"; // Public facing title
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../functions/functions.php'; // For get_pdo_connection, etc.

$pdo = get_pdo_connection();
$featured_achievements = [];
$latest_achievements = []; // A few latest, excluding featured
$categories = ['Academic', 'Research', 'Student Development', 'Industrial Linkages', 'Internationalization', 'Recognition & Awards', 'Corporate Social Responsibility (CSR)']; // From DB enum ideally

if ($pdo) {
    try {
        // Fetch Top 5 Most Liked Achievements (Featured)
        // This query needs the achievement_likes table and a way to count likes per achievement.
        // For now, let's assume a simple structure. A more complex query might be needed.
        // If achievement_likes stores user_id for logged-in likes and IP for public,
        // we might count distinct IPs or user_ids.
        // For simplicity, let's assume a `likes_count` column in `achievements` table (denormalized, updated by trigger/app logic)
        // OR, we join and count.
        // The README says: "Top 5 most liked projects should be automatically featured"
        // Let's try a join and count approach for now.
        $stmt_featured = $pdo->query("
            SELECT a.*, u.name as author_name, COUNT(al.id) as like_count, GROUP_CONCAT(am.media_url SEPARATOR '||') as media_urls
            FROM achievements a
            JOIN users u ON a.user_id = u.id
            LEFT JOIN achievement_likes al ON a.id = al.achievement_id
            LEFT JOIN (SELECT achievement_id, media_url FROM achievement_media WHERE media_type='image' ORDER BY id LIMIT 1) am ON a.id = am.achievement_id
            WHERE a.status = 'approved'
            GROUP BY a.id
            ORDER BY like_count DESC, a.updated_at DESC
            LIMIT 5
        ");
        $featured_achievements = $stmt_featured->fetchAll(PDO::FETCH_ASSOC);

        // Fetch Latest Achievements (e.g., 6 more, excluding those already featured)
        $featured_ids = array_column($featured_achievements, 'id');
        $placeholders = !empty($featured_ids) ? str_repeat('?,', count($featured_ids) - 1) . '?' : '';

        $latest_sql = "
            SELECT a.*, u.name as author_name, GROUP_CONCAT(am.media_url SEPARATOR '||') as media_urls
            FROM achievements a
            JOIN users u ON a.user_id = u.id
            LEFT JOIN (SELECT achievement_id, media_url FROM achievement_media WHERE media_type='image' ORDER BY id LIMIT 1) am ON a.id = am.achievement_id
            WHERE a.status = 'approved'" .
            (!empty($featured_ids) ? " AND a.id NOT IN ($placeholders)" : "") .
            " GROUP BY a.id
            ORDER BY a.updated_at DESC
            LIMIT 6";

        $stmt_latest = $pdo->prepare($latest_sql);
        if (!empty($featured_ids)) {
            $stmt_latest->execute($featured_ids);
        } else {
            $stmt_latest->execute();
        }
        $latest_achievements = $stmt_latest->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Public Index PDOException: " . $e->getMessage());
        // Set a user-friendly error message if needed, but the page should still try to render.
        set_flash_message("Could not load all achievement data due to a database error.", "warning");
    }
}

include_once dirname(__FILE__) . '/../includes/header.php'; // Navbar is included in header
?>

<!-- Hero Section / Featured Achievements Carousel -->
<?php if (!empty($featured_achievements)): ?>
<div id="featuredAchievementsCarousel" class="carousel slide mb-5" data-ride="carousel">
    <ol class="carousel-indicators">
        <?php foreach ($featured_achievements as $index => $achievement): ?>
        <li data-target="#featuredAchievementsCarousel" data-slide-to="<?php echo $index; ?>" class="<?php echo $index == 0 ? 'active' : ''; ?>"></li>
        <?php endforeach; ?>
    </ol>
    <div class="carousel-inner" style="max-height: 500px; background-color: #333;">
        <?php foreach ($featured_achievements as $index => $achievement):
            $images = $achievement['media_urls'] ? explode('||', $achievement['media_urls']) : [];
            $first_image = !empty($images) ? SITE_URL . 'uploads/images/' . basename($images[0]) : SITE_URL . 'assets/uitm_logo.png'; // Placeholder
            // For now, use a generic placeholder if no image. Later, ensure uploads/images/ path is correct.
            $image_path = file_exists(ROOT_PATH . 'uploads/images/' . basename($images[0])) ? $first_image : SITE_URL . 'assets/uitm_logo.png';

        ?>
        <div class="carousel-item <?php echo $index == 0 ? 'active' : ''; ?>">
            <img src="<?php echo $image_path; ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($achievement['title']); ?>" style="object-fit: cover; max-height: 500px; filter: brightness(0.7);">
            <div class="carousel-caption d-none d-md-block text-left" style="background-color: rgba(0,0,0,0.3); padding: 15px; border-radius: 5px; bottom: 5%;">
                <h5 class="text-light"><?php echo htmlspecialchars($achievement['title']); ?></h5>
                <p class="text-light"><?php echo htmlspecialchars(substr(strip_tags($achievement['description']), 0, 150)); ?>...</p>
                <p><small class="text-light">By <?php echo htmlspecialchars($achievement['author_name']); ?> | Category: <?php echo htmlspecialchars($achievement['category']); ?> | Likes: <?php echo $achievement['like_count'] ?? 0; ?></small></p>
                <a href="<?php echo SITE_URL; ?>public/view_achievements.php?id=<?php echo $achievement['id']; ?>" class="btn btn-primary btn-sm">Read More <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <a class="carousel-control-prev" href="#featuredAchievementsCarousel" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#featuredAchievementsCarousel" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
    </a>
</div>
<?php else: ?>
<div class="jumbotron text-center">
    <h1 class="display-4">Showcasing UiTM's Success</h1>
    <p class="lead">Discover the latest innovations, research breakthroughs, and outstanding achievements from Universiti Teknologi MARA.</p>
    <hr class="my-4">
    <p>No featured achievements at the moment. Check back soon!</p>
    <a class="btn btn-primary btn-lg" href="<?php echo SITE_URL; ?>public/view_achievements.php" role="button">Explore All Achievements</a>
</div>
<?php endif; ?>


<!-- Main Content Area -->
<div class="container mt-4">

    <!-- Search and Filter Bar -->
    <div class="row mb-4">
        <div class="col-md-12">
            <form action="<?php echo SITE_URL; ?>public/view_achievements.php" method="get" class="form-inline">
                <input type="text" name="search" class="form-control mr-sm-2 flex-grow-1" placeholder="Search achievements...">
                <select name="category" class="form-control mr-sm-2">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-outline-success"><i class="fas fa-search"></i> Search</button>
            </form>
        </div>
    </div>

    <!-- Latest Achievements Section -->
    <h2 class="mb-4">Latest Achievements</h2>
    <?php if (!empty($latest_achievements)): ?>
    <div class="row">
        <?php foreach ($latest_achievements as $achievement):
            $images = $achievement['media_urls'] ? explode('||', $achievement['media_urls']) : [];
            $first_image = !empty($images) ? SITE_URL . 'uploads/images/' . basename($images[0]) : SITE_URL . 'assets/uitm_logo.png'; // Placeholder
            $image_path = file_exists(ROOT_PATH . 'uploads/images/' . basename($images[0])) ? $first_image : SITE_URL . 'assets/uitm_logo.png'; // Fallback
        ?>
        <div class="col-md-4 mb-4">
            <div class="card achievement-card h-100">
                <img src="<?php echo $image_path; ?>" class="card-img-top achievement-image" alt="<?php echo htmlspecialchars($achievement['title']); ?>" style="height: 200px; object-fit: cover;">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?php echo htmlspecialchars($achievement['title']); ?></h5>
                    <p class="card-text achievement-meta">
                        <small class="text-muted">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($achievement['author_name']); ?> |
                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($achievement['category']); ?> |
                            <i class="fas fa-calendar-alt"></i> <?php echo date("d M Y", strtotime($achievement['updated_at'])); ?>
                        </small>
                    </p>
                    <p class="card-text flex-grow-1"><?php echo htmlspecialchars(substr(strip_tags($achievement['description']), 0, 100)); ?>...</p>
                    <a href="<?php echo SITE_URL; ?>public/view_achievements.php?id=<?php echo $achievement['id']; ?>" class="btn btn-sm btn-outline-primary mt-auto align-self-start">Read More <i class="fas fa-angle-double-right"></i></a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center mt-3 mb-5">
        <a href="<?php echo SITE_URL; ?>public/view_achievements.php" class="btn btn-lg btn-success">
            <i class="fas fa-th-list"></i> View All Achievements
        </a>
    </div>
    <?php else: ?>
    <div class="alert alert-info">No recent achievements to display. Please check back later.</div>
    <?php endif; ?>

    <!-- Categories Section (Optional) -->
    <h2 class="mb-3 mt-5">Browse by Category</h2>
    <div class="list-group list-group-horizontal-md flex-wrap">
        <?php foreach ($categories as $cat): ?>
        <a href="<?php echo SITE_URL; ?>public/view_achievements.php?category=<?php echo urlencode($cat); ?>" class="list-group-item list-group-item-action" style="min-width: 150px; text-align:center;">
            <i class="fas fa-folder-open mr-2"></i><?php echo htmlspecialchars($cat); ?>
        </a>
        <?php endforeach; ?>
    </div>

     <!-- Staff Profiles Link (as per README) -->
    <div class="text-center mt-5 mb-4">
         <h2 class="mb-3">Meet Our Talents</h2>
         <p>Explore profiles of UiTM staff and their contributions.</p>
        <a href="<?php echo SITE_URL; ?>public/staff_profiles_list.php" class="btn btn-info btn-lg"> <!-- Assuming a listing page for staff -->
            <i class="fas fa-users"></i> View Staff Profiles
        </a>
        <!-- Example direct link: <a href="<?php echo SITE_URL; ?>public/staff_profile.php?uitm_id=12345" class="btn btn-link">Example Staff Profile</a> -->
    </div>


</div> <!-- /.container -->

<?php
include_once dirname(__FILE__) . '/../includes/footer.php';
?>
