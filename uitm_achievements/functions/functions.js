// functions.js
// This file will contain reusable JavaScript functions and AJAX handlers.

$(document).ready(function() {
    // Initialize Bootstrap components that require JS, if not already done in footer.php
    // Example: $('[data-toggle="tooltip"]').tooltip();
    // Example: $('[data-toggle="popover"]').popover();

    // Example: Confirm dialog for delete actions
    // Add class="confirm-delete" to any link or button to trigger this.
    // Use data-message attribute for custom message.
    $('.confirm-delete').on('click', function(e) {
        var message = $(this).data('message') || 'Are you sure you want to delete this item?';
        if (!confirm(message)) {
            e.preventDefault();
        }
    });

    // Example: AJAX form submission
    // Give your form an ID e.g., id="ajaxForm"
    /*
    $('#ajaxForm').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        var formData = $(this).serialize(); // Get form data
        var formAction = $(this).attr('action'); // Get form action URL
        var formMethod = $(this).attr('method'); // Get form method (POST/GET)

        $.ajax({
            url: formAction,
            type: formMethod,
            data: formData,
            dataType: 'json', // Expect JSON response from server
            beforeSend: function() {
                // Show a loader or disable submit button
                $('#submitButton').prop('disabled', true).text('Processing...');
            },
            success: function(response) {
                // Handle success (e.g., display success message, redirect)
                if(response.success) {
                    alert(response.message || 'Operation successful!');
                    if(response.redirect_url) {
                        window.location.href = response.redirect_url;
                    }
                } else {
                    alert(response.message || 'An error occurred.');
                }
            },
            error: function(xhr, status, error) {
                // Handle AJAX errors (e.g., network issue, server error)
                console.error("AJAX Error: ", status, error);
                alert('An error occurred while submitting the form. Please try again.');
            },
            complete: function() {
                // Re-enable submit button or hide loader
                $('#submitButton').prop('disabled', false).text('Submit');
            }
        });
    });
    */

    // Add more global JavaScript functions or event listeners here.
    // For example, functions for dynamic content loading, form validation, UI interactions.

    // Character counter for textareas
    $('textarea[data-max-length]').each(function() {
        var $this = $(this);
        var maxLength = parseInt($this.data('max-length'));
        var $charCount = $('<div class="char-count text-right text-muted small"></div>');
        $this.after($charCount);

        function updateCount() {
            var currentLength = $this.val().length;
            $charCount.text(currentLength + '/' + maxLength);
            if (currentLength > maxLength) {
                $charCount.addClass('text-danger');
                // Optionally, prevent further input or trim
                // $this.val($this.val().substring(0, maxLength));
            } else {
                $charCount.removeClass('text-danger');
            }
        }
        updateCount(); // Initial count
        $this.on('input', updateCount);
    });


    // Preview image before upload
    // Usage: <input type="file" class="image-preview-input" data-preview-target="#imagePreviewElement">
    //        <img id="imagePreviewElement" src="#" alt="Preview" style="max-width: 200px; max-height: 200px; display: none;">
    $('.image-preview-input').on('change', function() {
        var input = this;
        var previewTargetSelector = $(this).data('preview-target');
        if (input.files && input.files[0] && previewTargetSelector) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $(previewTargetSelector).attr('src', e.target.result).show();
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            $(previewTargetSelector).attr('src', '#').hide();
        }
    });

});

// Example of a globally accessible function
function showGlobalModal(title, bodyContent, footerContent) {
    // Check if a global modal exists, if not, create it (or ensure one is in your main layout)
    if ($('#globalModal').length === 0) {
        $('body').append(`
            <div class="modal fade" id="globalModal" tabindex="-1" role="dialog" aria-labelledby="globalModalLabel" aria-hidden="true">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="globalModalLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    ...
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <!-- Additional buttons can be added here -->
                  </div>
                </div>
              </div>
            </div>
        `);
    }

    $('#globalModal .modal-title').html(title);
    $('#globalModal .modal-body').html(bodyContent);
    if (footerContent) {
        $('#globalModal .modal-footer').html(footerContent);
    } else {
        $('#globalModal .modal-footer').html('<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>');
    }
    $('#globalModal').modal('show');
}
console.log("functions.js loaded.");
