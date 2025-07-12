<?php
// public/staff_profile.php
$page_title = "Staff Profile - UiTM Achievements"; // Dynamic title
$config_path = __DIR__ . '/../config/config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Critical error: Main configuration file not found.");
}

// Get uitm_id from query string
$uitm_id_param = isset($_GET['uitm_id']) ? sanitize_input($_GET['uitm_id']) : null;

if (!$uitm_id_param) {
    $_SESSION['error_message'] = "No UiTM ID provided for staff profile.";
    redirect(SITE_URL . 'public/index.php'); // Or a dedicated error page
}

$staff_data = null;
$staff_achievements = [];

$db = db_connect();
if (!$db) {
    $_SESSION['error_message'] = "Database connection error.";
    // Fall through to display error in the page content
} else {
    // Fetch staff data
    $stmt_staff = $db->prepare("SELECT id, uitm_id, full_name, email, biography, phone_number, profile_picture, cv_link, google_scholar_link, linkedin_link, scopus_link, isi_link, orcid_link FROM users WHERE uitm_id = ? AND role IN ('user', 'admin')"); // Assuming staff can be 'user' or 'admin'
    if ($stmt_staff) {
        $stmt_staff->bind_param("s", $uitm_id_param);
        $stmt_staff->execute();
        $result_staff = $stmt_staff->get_result();
        if ($result_staff->num_rows === 1) {
            $staff_data = $result_staff->fetch_assoc();
            $page_title = htmlspecialchars($staff_data['full_name']) . " - Profile - UiTM Achievements";

            // Fetch approved achievements for this staff member
            $stmt_achievements = $db->prepare(
                "SELECT a.id, a.title, a.description, a.achievement_date, c.name as category_name,
                        (SELECT file_path_or_url FROM achievement_media am WHERE am.achievement_id = a.id AND am.media_type = 'image' ORDER BY am.id ASC LIMIT 1) as image_url
                 FROM achievements a
                 JOIN achievement_categories c ON a.category_id = c.id
                 WHERE a.user_id = ? AND a.status = 'approved'
                 ORDER BY a.achievement_date DESC, a.approved_at DESC"
            );
            if ($stmt_achievements) {
                $stmt_achievements->bind_param("i", $staff_data['id']);
                $stmt_achievements->execute();
                $result_achievements = $stmt_achievements->get_result();
                while ($row = $result_achievements->fetch_assoc()) {
                    $staff_achievements[] = $row;
                }
                $stmt_achievements->close();
            } else {
                 $_SESSION['error_message'] = "Error fetching staff achievements: " . $db->error;
            }

        } else {
            $_SESSION['error_message'] = "Staff profile not found for ID: " . htmlspecialchars($uitm_id_param);
        }
        $stmt_staff->close();
    } else {
        $_SESSION['error_message'] = "Database query error fetching staff data: " . $db->error;
    }
    $db->close();
}


include_once SITE_ROOT . 'includes/header.php';
?>

<div class="container public-container mt-4 mb-5">
    <?php
    display_message('success_message');
    display_message('error_message'); // Display errors from session (e.g., DB connection, staff not found)
    ?>

    <?php if ($staff_data): ?>
        <div class="row">
            <!-- Profile Sidebar -->
            <div class="col-md-4">
                <div class="card sticky-top" style="top: 70px;"> <!-- Adjust top for navbar height -->
                    <img src="<?php echo !empty($staff_data['profile_picture']) ? SITE_URL . htmlspecialchars($staff_data['profile_picture']) : SITE_URL . 'assets/default_avatar.png'; ?>"
                         class="card-img-top" alt="<?php echo htmlspecialchars($staff_data['full_name']); ?>'s Profile Picture"
                         style="max-height: 300px; object-fit: cover;">
                    <div class="card-body">
                        <h4 class="card-title"><?php echo htmlspecialchars($staff_data['full_name']); ?></h4>
                        <p class="card-text text-muted">UiTM ID: <?php echo htmlspecialchars($staff_data['uitm_id']); ?></p>
                        <?php if (!empty($staff_data['email'])): // Email might be sensitive, consider if it should be public ?>
                            <p class="card-text"><i class="fas fa-envelope fa-fw mr-2"></i><a href="mailto:<?php echo htmlspecialchars($staff_data['email']); ?>"><?php echo htmlspecialchars($staff_data['email']); ?></a></p>
                        <?php endif; ?>
                        <?php if (!empty($staff_data['phone_number'])): // Phone number also sensitive ?>
                             <p class="card-text"><i class="fas fa-phone fa-fw mr-2"></i><?php echo htmlspecialchars($staff_data['phone_number']); ?></p>
                        <?php endif; ?>

                        <hr>
                        <p class="font-weight-bold">Professional Links:</p>
                        <?php
                        $links = [
                            'cv_link' => ['icon' => 'fas fa-file-pdf', 'label' => 'CV/Resume'],
                            'google_scholar_link' => ['icon' => 'fab fa-google', 'label' => 'Google Scholar'], // Needs FontAwesome Pro for 'fa-google-scholar'
                            'linkedin_link' => ['icon' => 'fab fa-linkedin', 'label' => 'LinkedIn'],
                            'scopus_link' => ['icon' => 'fas fa-book', 'label' => 'Scopus Profile'], // Generic icon
                            'isi_link' => ['icon' => 'fas fa-atom', 'label' => 'Web of Science'], // Generic icon
                            'orcid_link' => ['icon' => 'fab fa-orcid', 'label' => 'ORCID']
                        ];
                        $has_links = false;
                        foreach ($links as $key => $link_info) {
                            if (!empty($staff_data[$key])) {
                                echo '<p><a href="'.htmlspecialchars($staff_data[$key]).'" target="_blank" rel="noopener noreferrer"><i class="'.$link_info['icon'].' fa-fw mr-2"></i>'.$link_info['label'].'</a></p>';
                                $has_links = true;
                            }
                        }
                        if (!$has_links) {
                            echo "<p class='text-muted small'>No professional links provided.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Main Content: Biography and Achievements -->
            <div class="col-md-8">
                <?php if (!empty($staff_data['biography'])): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-user-circle fa-fw mr-2"></i>About <?php echo htmlspecialchars(explode(' ', $staff_data['full_name'])[0]); // First name ?></h5>
                    </div>
                    <div class="card-body">
                        <?php echo nl2br(htmlspecialchars($staff_data['biography'])); ?>
                    </div>
                </div>
                <?php endif; ?>

                <h3 class="mb-3"><i class="fas fa-trophy fa-fw mr-2"></i>Published Achievements</h3>
                <?php if (!empty($staff_achievements)): ?>
                    <div class="list-group">
                        <?php foreach ($staff_achievements as $achievement): ?>
                            <a href="<?php echo SITE_URL . 'public/view_achievements.php?id=' . $achievement['id']; ?>" class="list-group-item list-group-item-action achievement-card-public mb-3">
                                <div class="row no-gutters">
                                    <?php if (!empty($achievement['image_url'])): ?>
                                    <div class="col-md-3">
                                        <img src="<?php echo SITE_URL . htmlspecialchars($achievement['image_url']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($achievement['title']); ?>" style="object-fit: cover; height: 120px; width:100%;">
                                    </div>
                                    <?php endif; ?>
                                    <div class="<?php echo !empty($achievement['image_url']) ? 'col-md-9' : 'col-md-12'; ?>">
                                        <div class="pl-md-3">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h5 class="mb-1"><?php echo htmlspecialchars($achievement['title']); ?></h5>
                                                <small class="text-muted"><?php echo !empty($achievement['achievement_date']) ? date("M Y", strtotime($achievement['achievement_date'])) : 'N/A'; ?></small>
                                            </div>
                                            <p class="mb-1 small text-muted">Category: <?php echo htmlspecialchars($achievement['category_name']); ?></p>
                                            <p class="mb-1 d-none d-sm-block"><?php echo htmlspecialchars(substr(strip_tags($achievement['description']), 0, 150)) . (strlen(strip_tags($achievement['description'])) > 150 ? '...' : ''); ?></p>
                                            <small>Click to read more.</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">This staff member has no publicly visible achievements at this time.</div>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif (empty($_SESSION['error_message'])): // No staff data and no specific error message set yet (e.g. from DB failure) ?>
        <div class="alert alert-warning">
            Profile not found. The UiTM ID provided may be incorrect or the profile is not public.
        </div>
    <?php endif; ?>

    <div class="mt-4 text-center">
        <a href="<?php echo SITE_URL; ?>public/index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Main Page</a>
    </div>

</div>

<style>
.achievement-card-public {
    transition: transform .2s ease-in-out, box-shadow .2s ease-in-out;
    border: 1px solid #ddd;
}
.achievement-card-public:hover {
    transform: translateY(-3px);
    box-shadow: 0 3px 10px rgba(0,0,0,.1);
    z-index: 10;
}
</style>

<?php
include_once SITE_ROOT . 'includes/footer.php';
?>
