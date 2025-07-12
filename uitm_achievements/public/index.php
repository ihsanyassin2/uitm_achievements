<?php
// public/index.php - Main public-facing page
$page_title = "UiTM Achievements - Showcasing Success"; // Dynamic title, can be from settings
$config_path = __DIR__ . '/../config/config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Critical error: Main configuration file not found from public/index.php.");
}

// No login required for this page.
// It will use a different header/footer or a modified version of the main ones.

// For public pages, we might want a simplified header, or the main one without user/admin specific elements.
// For now, let's assume a structure that uses a common navbar but no sidebars.
// The main header.php and footer.php are designed to adapt if not in user/admin area.

// Fetch site title from settings if available
$db_public = db_connect();
if ($db_public) {
    $site_title_from_db = get_setting('site_title');
    if ($site_title_from_db) {
        $page_title = $site_title_from_db;
    }
    // $db_public->close(); // get_setting handles its own connection closing
}


include_once SITE_ROOT . 'includes/header.php';
// The header.php is set up to not include sidebars if not in /user/ or /admin/ paths.
// It will include the main navbar.
?>

<!-- Hero Section -->
<div class="jumbotron jumbotron-fluid text-center bg-light">
    <div class="container public-container">
        <img src="<?php echo SITE_URL; ?>assets/uitm_logo.png" alt="UiTM Logo" style="max-width: 150px;" class="mb-3">
        <h1 class="display-4"><?php echo htmlspecialchars($page_title); ?></h1>
        <p class="lead">Celebrating the remarkable achievements of Universiti Teknologi MARA's staff and students.</p>
        <hr class="my-4">
        <p>Explore the diverse accomplishments across various fields that highlight UiTM's commitment to excellence.</p>
        <a class="btn btn-primary btn-lg" href="<?php echo SITE_URL; ?>public/view_achievements.php" role="button"><i class="fas fa-trophy"></i> Explore All Achievements</a>
    </div>
</div>

<div class="container public-container">

    <!-- Top 5 Most Liked Projects (Placeholder) -->
    <section class="my-5">
        <h2 class="text-center mb-4"><i class="fas fa-heart text-danger"></i> Top 5 Most Liked Achievements</h2>
        <div class="row">
            <?php
            $top_liked_achievements = [];
            if ($db_public) { // $db_public is already db_connect() from top of file
                $stmt_top_liked = $db_public->prepare(
                    "SELECT a.id, a.title, a.description, c.name as category_name, c.id as category_id,
                            (SELECT file_path_or_url FROM achievement_media am WHERE am.achievement_id = a.id AND am.media_type = 'image' ORDER BY am.id ASC LIMIT 1) as image_url,
                            (SELECT COUNT(l.id) FROM likes l WHERE l.achievement_id = a.id) as like_count
                     FROM achievements a
                     JOIN achievement_categories c ON a.category_id = c.id
                     WHERE a.status = 'approved'
                     ORDER BY like_count DESC, a.approved_at DESC
                     LIMIT 5"
                );
                if ($stmt_top_liked) {
                    $stmt_top_liked->execute();
                    $result_top_liked = $stmt_top_liked->get_result();
                    if ($result_top_liked->num_rows > 0) {
                        while ($achievement = $result_top_liked->fetch_assoc()) {
                            $top_liked_achievements[] = $achievement;
                        }
                    }
                    $stmt_top_liked->close();
                } else {
                     echo "<p class='col-12 text-center text-danger'>Error fetching top liked achievements: " . $db_public->error . "</p>";
                }
            }

            if (empty($top_liked_achievements)) {
                echo "<p class='col-12 text-center text-muted'>No highly liked achievements to feature yet. Be the first to like some!</p>";
            } else {
                foreach ($top_liked_achievements as $achievement):
            ?>
            <div class="col-lg col-md-4 mb-4 d-flex align-items-stretch"> <!-- col-lg for 5 items in a row, col-md-4 for responsiveness -->
                <div class="card achievement-card h-100 w-100">
                    <div class="card-img-top-container">
                         <a href="<?php echo SITE_URL; ?>public/view_achievements.php?id=<?php echo $achievement['id']; ?>">
                         <img src="<?php echo !empty($achievement['image_url']) ? SITE_URL . htmlspecialchars($achievement['image_url']) : SITE_URL . 'assets/placeholder_achievement.png'; ?>"
                              class="card-img-top" alt="<?php echo htmlspecialchars($achievement['title']); ?>">
                         </a>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><a href="<?php echo SITE_URL; ?>public/view_achievements.php?id=<?php echo $achievement['id']; ?>"><?php echo htmlspecialchars($achievement['title']); ?></a></h5>
                        <p class="card-text small text-muted">Category: <a href="<?php echo SITE_URL . 'public/view_achievements.php?category=' . $achievement['category_id']; ?>"><?php echo htmlspecialchars($achievement['category_name']); ?></a></p>
                        <p class="card-text flex-grow-1"><?php echo htmlspecialchars(substr(strip_tags($achievement['description']), 0, 100)) . (strlen(strip_tags($achievement['description'])) > 100 ? '...' : ''); ?></p>
                        <a href="<?php echo SITE_URL; ?>public/view_achievements.php?id=<?php echo $achievement['id']; ?>" class="btn btn-sm btn-outline-primary mt-auto align-self-start">Read More</a>
                    </div>
                    <div class="card-footer text-muted">
                        <i class="fas fa-heart text-danger"></i> <?php echo $achievement['like_count']; ?> Likes
                    </div>
                </div>
            </div>
            <?php
                endforeach;
            } ?>
        </div>
        <?php if (empty($top_liked_achievements)): ?>
        <p class="text-center mt-2"><small>Top liked achievements will be featured here dynamically.</small></p>
        <?php endif; ?>
    </section>

    <hr>

    <!-- Achievements by Category -->
    <section class="my-5">
        <h2 class="text-center mb-4"><i class="fas fa-tags"></i> Achievements by Category</h2>
        <div class="row">
            <?php
            $categories_list = [];
            $default_icons = [
                'Academic' => 'fas fa-graduation-cap',
                'Research' => 'fas fa-flask',
                'Student Development' => 'fas fa-users',
                'Industrial Linkages' => 'fas fa-industry',
                'Internationalization' => 'fas fa-globe-americas',
                'Recognition & Awards' => 'fas fa-award',
                'Corporate Social Responsibility (CSR)' => 'fas fa-hands-helping'
            ];
            if ($db_public) {
                $cat_res = $db_public->query("SELECT id, name FROM achievement_categories ORDER BY name ASC");
                if ($cat_res) {
                    while ($cat_row = $cat_res->fetch_assoc()) {
                        $categories_list[] = $cat_row;
                    }
                }
            }

            if (empty($categories_list)) {
                echo "<p class='col-12 text-center text-muted'>No categories available at the moment.</p>";
            } else {
                foreach ($categories_list as $category):
                    $icon = $default_icons[$category['name']] ?? 'fas fa-tag'; // Default icon
            ?>
            <div class="col-md-4 col-lg-3 mb-3">
                <a href="<?php echo SITE_URL; ?>public/view_achievements.php?category=<?php echo $category['id']; ?>" class="btn btn-outline-secondary btn-block btn-lg p-4 text-center category-button">
                    <i class="<?php echo $icon; ?> fa-2x mb-2 d-block"></i>
                    <?php echo htmlspecialchars($category['name']); ?>
                </a>
            </div>
            <?php
                endforeach;
            } ?>
        </div>
        <?php if (!empty($categories_list)): ?>
         <p class="text-center mt-2"><small>Explore achievements based on their respective categories.</small></p>
        <?php endif; ?>
    </section>

    <hr>

    <!-- Call to Action / Search (Placeholder) -->
    <section class="my-5 text-center">
        <h2>Find Specific Achievements</h2>
        <p>Looking for something in particular? Use our detailed search and filter options.</p>
        <form action="<?php echo SITE_URL; ?>public/view_achievements.php" method="get" class="form-inline justify-content-center">
            <input type="text" name="search_query" class="form-control form-control-lg mr-sm-2 mb-2 mb-sm-0 col-md-6" placeholder="Search by keyword, name, department...">
            <button type="submit" class="btn btn-success btn-lg col-md-2"><i class="fas fa-search"></i> Search</button>
        </form>
    </section>

</div> <!-- /container -->

<?php
if ($db_public) $db_public->close();
include_once SITE_ROOT . 'includes/footer.php';
?>

<style>
    .category-button {
        transition: all 0.3s ease-in-out;
    }
    .category-button:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0,0,0,.1);
        color: #0047AB; /* UiTM Blue */
        border-color: #0047AB;
    }
     .jumbotron {
        background-image: url('<?php echo SITE_URL; ?>assets/uitm_background_blurry.jpg'); /* Add a nice blurry background related to UiTM */
        background-size: cover;
        background-position: center;
        color: #fff; /* Adjust text color for readability if background is dark */
        /* text-shadow: 1px 1px 2px rgba(0,0,0,0.5); /* Optional shadow for text */
    }
    .jumbotron h1, .jumbotron p {
         color: #333; /* Or make sure text is readable over background */
    }
     .jumbotron .btn-primary {
        background-color: #0047AB; /* UiTM Blue */
        border-color: #0047AB;
    }
    .jumbotron .btn-primary:hover {
        background-color: #00337A;
        border-color: #00337A;
    }
</style>
