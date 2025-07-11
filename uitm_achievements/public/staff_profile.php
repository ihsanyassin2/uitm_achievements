<?php
// This page displays a single staff member's profile.
// It will take a user ID (uitm_id in README, but likely maps to `users.id`) from GET parameter.
$page_title = "Staff Profile - UiTM Achievements";
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../functions/functions.php';

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (isset($_GET['uitm_id']) ? (int)$_GET['uitm_id'] : 0);
$profile_data = null;
$achievements_by_user = [];
$pdo = get_pdo_connection();

if ($user_id <= 0) {
    set_flash_message("Invalid user ID provided.", "danger");
    // Consider redirecting to a staff list page or homepage
    // redirect(SITE_URL . 'public/index.php');
} else {
    if ($pdo) {
        try {
            // Fetch user profile data
            // Only select users who have made at least one approved achievement or are admins?
            // For now, fetch any user, but public profiles should ideally be for active contributors.
            $stmt_profile = $pdo->prepare("SELECT id, name, email, phone_number, biography, cv_link, google_scholar_link, linkedin_link, scopus_link, isi_link, orcid_link FROM users WHERE id = ?");
            $stmt_profile->execute([$user_id]);
            $profile_data = $stmt_profile->fetch(PDO::FETCH_ASSOC);

            if ($profile_data) {
                $page_title = htmlspecialchars($profile_data['name']) . " - Profile";
                // Fetch achievements by this user (only approved ones for public view)
                $stmt_achievements = $pdo->prepare("
                    SELECT a.*, GROUP_CONCAT(am.media_url SEPARATOR '||') as media_urls
                    FROM achievements a
                    LEFT JOIN (SELECT achievement_id, media_url FROM achievement_media WHERE media_type='image' ORDER BY id LIMIT 1) am ON a.id = am.achievement_id
                    WHERE a.user_id = ? AND a.status = 'approved'
                    GROUP BY a.id
                    ORDER BY a.updated_at DESC
                ");
                $stmt_achievements->execute([$user_id]);
                $achievements_by_user = $stmt_achievements->fetchAll(PDO::FETCH_ASSOC);
            } else {
                set_flash_message("Staff profile not found.", "warning");
            }
        } catch (PDOException $e) {
            error_log("Staff Profile PDOException: " . $e->getMessage());
            set_flash_message("Error loading staff profile: " . $e->getMessage(), "danger");
        }
    } else {
        set_flash_message("Database connection error.", "danger");
    }
}

include_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="container mt-4">
    <?php if ($profile_data): ?>
        <div class="row">
            <!-- Profile Sidebar -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header profile-header text-center">
                        <i class="fas fa-user-tie fa-3x mb-2"></i> <!-- Placeholder for profile picture -->
                        <h3><?php echo htmlspecialchars($profile_data['name']); ?></h3>
                        <p class="text-light small"><?php echo htmlspecialchars($profile_data['email']); ?></p>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($profile_data['phone_number'])): ?>
                        <p><i class="fas fa-phone fa-fw mr-2"></i> <?php echo htmlspecialchars($profile_data['phone_number']); ?></p>
                        <?php endif; ?>

                        <hr>
                        <h5><i class="fas fa-link fa-fw mr-2"></i>Professional Links:</h5>
                        <div class="profile-links">
                            <?php if (!empty($profile_data['cv_link'])): ?>
                                <a href="<?php echo htmlspecialchars($profile_data['cv_link']); ?>" target="_blank"><i class="fas fa-file-pdf fa-fw"></i> View CV</a>
                            <?php endif; ?>
                            <?php if (!empty($profile_data['google_scholar_link'])): ?>
                                <a href="<?php echo htmlspecialchars($profile_data['google_scholar_link']); ?>" target="_blank"><i class="fab fa-google fa-fw"></i> Google Scholar</a>
                            <?php endif; ?>
                            <?php if (!empty($profile_data['linkedin_link'])): ?>
                                <a href="<?php echo htmlspecialchars($profile_data['linkedin_link']); ?>" target="_blank"><i class="fab fa-linkedin fa-fw"></i> LinkedIn</a>
                            <?php endif; ?>
                            <?php if (!empty($profile_data['scopus_link'])): ?>
                                <a href="<?php echo htmlspecialchars($profile_data['scopus_link']); ?>" target="_blank"><i class="fas fa-atom fa-fw"></i> Scopus Profile</a>
                            <?php endif; ?>
                            <?php if (!empty($profile_data['isi_link'])): ?>
                                <a href="<?php echo htmlspecialchars($profile_data['isi_link']); ?>" target="_blank"><i class="fas fa-flask fa-fw"></i> Web of Science</a>
                            <?php endif; ?>
                            <?php if (!empty($profile_data['orcid_link'])): ?>
                                <a href="<?php echo htmlspecialchars($profile_data['orcid_link']); ?>" target="_blank"><i class="fab fa-orcid fa-fw"></i> ORCID</a>
                            <?php endif; ?>
                        </div>
                        <?php if (empty($profile_data['cv_link']) && empty($profile_data['google_scholar_link']) && empty($profile_data['linkedin_link']) && empty($profile_data['scopus_link']) && empty($profile_data['isi_link']) && empty($profile_data['orcid_link'])): ?>
                            <p class="text-muted">No professional links provided.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Profile Content -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-info-circle"></i> About <?php echo htmlspecialchars(explode(' ', $profile_data['name'])[0]); // First name ?></h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($profile_data['biography'])): ?>
                            <p><?php echo nl2br(htmlspecialchars($profile_data['biography'])); ?></p>
                        <?php else: ?>
                            <p class="text-muted">No biography provided by this user.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h4><i class="fas fa-trophy"></i> Achievements by <?php echo htmlspecialchars(explode(' ', $profile_data['name'])[0]); ?></h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($achievements_by_user)): ?>
                            <div class="list-group">
                                <?php foreach ($achievements_by_user as $achievement): ?>
                                    <a href="<?php echo SITE_URL; ?>public/view_achievements.php?id=<?php echo $achievement['id']; ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1"><?php echo htmlspecialchars($achievement['title']); ?></h5>
                                            <small><?php echo date("d M Y", strtotime($achievement['updated_at'])); ?></small>
                                        </div>
                                        <p class="mb-1"><small class="text-muted">Category: <?php echo htmlspecialchars($achievement['category']); ?> | Level: <?php echo htmlspecialchars($achievement['level']); ?></small></p>
                                        <p class="mb-1 text-truncate"><?php echo htmlspecialchars(substr(strip_tags($achievement['description']), 0, 150)); ?>...</p>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">This user has no publicly visible achievements at the moment.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php if ($user_id > 0): // Only show if a specific user was queried but not found ?>
        <div class="alert alert-warning text-center">
            <h4><i class="fas fa-user-slash"></i> Profile Not Found</h4>
            <p>The staff profile you are looking for could not be found or is not publicly available.</p>
            <a href="<?php echo SITE_URL; ?>public/index.php" class="btn btn-primary"><i class="fas fa-home"></i> Go to Homepage</a>
            <a href="<?php echo SITE_URL; ?>public/staff_profiles_list.php" class="btn btn-info"><i class="fas fa-users"></i> View All Staff Profiles</a>
        <?php else: // No user_id provided or invalid ?>
             <div class="alert alert-danger text-center">
                <h4><i class="fas fa-exclamation-triangle"></i> Invalid Request</h4>
                <p>No valid staff ID was provided to display a profile.</p>
                <a href="<?php echo SITE_URL; ?>public/index.php" class="btn btn-primary"><i class="fas fa-home"></i> Go to Homepage</a>
             </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
include_once dirname(__FILE__) . '/../includes/footer.php';
?>
