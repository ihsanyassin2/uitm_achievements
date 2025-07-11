<?php
$page_title = "Manage Achievements - Admin";
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../functions/functions.php';

// Protect page: Admin role required
protect_page('admin');

// Placeholder content
// This page will list all achievements, allow filtering, searching.
// Admins can view, edit, approve, reject submissions.

include_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include_once dirname(__FILE__) . '/../includes/admin_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <h2><i class="fas fa-trophy"></i> Manage Achievements</h2>
        <p>Review, approve, edit, or reject user submissions.</p>
        <hr>

        <div class="alert alert-info">
            <strong>Under Construction!</strong> This section will provide a comprehensive interface to manage all achievement submissions. Features will include filtering by status (Pending, Approved, Rejected), searching, and actions to approve, reject, edit, or provide feedback.
        </div>

        <!-- Filters and Search -->
        <div class="card mb-3">
            <div class="card-header">Filter and Search Achievements</div>
            <div class="card-body">
                <form class="form-row">
                    <div class="col-md-4 form-group">
                        <input type="text" class="form-control" name="search" placeholder="Search by title, user...">
                    </div>
                    <div class="col-md-3 form-group">
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="needs_revision">Needs Revision</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                         <select name="category" class="form-control">
                            <option value="">All Categories</option>
                             <option value="Academic">Academic</option>
                            <!-- Add other categories -->
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <button type="submit" class="btn btn-primary btn-block" disabled><i class="fas fa-search"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Example Table Structure -->
        <table class="table table-hover table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Submitted By</th>
                    <th>Category</th>
                    <th>Date Submitted</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>101</td>
                    <td> Breakthrough in Quantum Computing Research</td>
                    <td>Dr. Jane Doe (user@uitm.edu.my)</td>
                    <td>Research</td>
                    <td>2023-11-01</td>
                    <td><span class="badge badge-warning">Pending</span></td>
                    <td class="action-icons">
                        <a href="#" class="text-info" title="View Details"><i class="fas fa-eye"></i></a>
                        <a href="#" class="text-success" title="Approve"><i class="fas fa-check-circle"></i></a>
                        <a href="#" class="text-danger" title="Reject"><i class="fas fa-times-circle"></i></a>
                        <a href="#" class="text-primary" title="Edit"><i class="fas fa-edit"></i></a>
                        <a href="#" class="text-secondary" title="Send Feedback"><i class="fas fa-comment-dots"></i></a>
                    </td>
                </tr>
                 <tr>
                    <td>102</td>
                    <td>Student Team Wins National Robotics Competition</td>
                    <td>Ahmad Bakar (student@uitm.edu.my)</td>
                    <td>Student Development</td>
                    <td>2023-10-25</td>
                    <td><span class="badge badge-success">Approved</span></td>
                    <td class="action-icons">
                        <a href="#" class="text-info" title="View Details"><i class="fas fa-eye"></i></a>
                        <a href="#" class="text-primary" title="Edit"><i class="fas fa-edit"></i></a>
                         <a href="#" class="text-warning" title="Unapprove (Needs confirmation)"><i class="fas fa-undo"></i></a>
                    </td>
                </tr>
                <!-- More rows will be added dynamically -->
            </tbody>
        </table>
        <p class="text-muted">No achievements to display based on current filters, or feature under construction.</p>
        <!-- Pagination would go here -->

    </div>
</div>

<?php
include_once dirname(__FILE__) . '/../includes/footer.php';
?>
