<?php
$page_title = "My Achievements - UiTM Achievements";
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../functions/functions.php';

// Protect page: User or Admin role required
protect_page(['user', 'admin']);

// Placeholder content
// List of achievements submitted by the current user.
// Should show status (Pending, Approved, Rejected, Needs Revision).
// Options to view/edit (if status allows).

include_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include_once dirname(__FILE__) . '/../includes/user_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <h2><i class="fas fa-list-alt"></i> My Submitted Achievements</h2>
        <p>Track the status of your submissions and manage them.</p>
        <hr>

        <div class="alert alert-info">
            <strong>Under Construction!</strong> This page will display a list of all achievements you have submitted, along with their current review status. You will be able to view details and, if applicable, edit or respond to feedback.
        </div>

        <!-- Example Table Structure (to be populated dynamically) -->
        <table class="table table-hover table-striped">
            <thead class="thead-light">
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Submitted On</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Example Achievement 1: Research Breakthrough</td>
                    <td>Research</td>
                    <td>2023-10-15</td>
                    <td><span class="badge badge-success">Approved</span></td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View</a>
                    </td>
                </tr>
                <tr>
                    <td>Example Achievement 2: Student Project Wins Award</td>
                    <td>Student Development</td>
                    <td>2023-10-20</td>
                    <td><span class="badge badge-warning">Pending Review</span></td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View</a>
                        <a href="#" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Edit</a>
                    </td>
                </tr>
                 <tr>
                    <td>Example Achievement 3: International Conference Presentation</td>
                    <td>Academic</td>
                    <td>2023-09-01</td>
                    <td><span class="badge badge-danger">Rejected</span></td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View Feedback</a>
                    </td>
                </tr>
                 <tr>
                    <td>Example Achievement 4: Community Engagement Program</td>
                    <td>CSR</td>
                    <td>2023-11-01</td>
                    <td><span class="badge badge-info">Needs Revision</span></td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View Feedback</a>
                         <a href="#" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Revise & Resubmit</a>
                    </td>
                </tr>
                <!-- More rows will be added dynamically -->
            </tbody>
        </table>
        <p class="text-muted">No achievements submitted yet, or feature under construction.</p>

    </div>
</div>

<?php
include_once dirname(__FILE__) . '/../includes/footer.php';
?>
