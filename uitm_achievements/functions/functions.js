// This file will contain reusable JavaScript functions.

// Example: Function to confirm deletion
function confirmDeletion(event, message = 'Are you sure you want to delete this item?') {
    if (!confirm(message)) {
        event.preventDefault(); // Stop the default action (e.g., following a link or submitting a form)
    }
}

// Example: AJAX request helper (requires jQuery or can be vanilla JS)
// This is a very basic example. You might want a more robust handler.
/*
function makeAjaxRequest(url, method, data, successCallback, errorCallback) {
    $.ajax({
        url: url,
        type: method,
        data: data,
        dataType: 'json', // Assuming JSON responses
        success: function(response) {
            if (successCallback) {
                successCallback(response);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
            if (errorCallback) {
                errorCallback(jqXHR, textStatus, errorThrown);
            } else {
                // Generic error message display
                alert('An error occurred while processing your request. Please try again.');
            }
        }
    });
}
*/

// Vanilla JS AJAX example
function makeVanillaAjaxRequest(url, method, data, successCallback, errorCallback, responseType = 'json') {
    const xhr = new XMLHttpRequest();

    let queryParams = '';
    if (method.toUpperCase() === 'GET' && data) {
        queryParams = '?' + new URLSearchParams(data).toString();
    }

    xhr.open(method, url + queryParams, true);

    if (method.toUpperCase() === 'POST' || method.toUpperCase() === 'PUT') {
        if (data instanceof FormData) {
            // FormData will set Content-Type automatically
        } else if (typeof data === 'object') {
            xhr.setRequestHeader('Content-Type', 'application/json');
            data = JSON.stringify(data);
        } else {
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        }
    }

    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            let response = xhr.responseText;
            if (responseType === 'json') {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    console.error("Failed to parse JSON response:", e, xhr.responseText);
                    if (errorCallback) errorCallback(xhr, 'parseerror', e);
                    return;
                }
            }
            if (successCallback) successCallback(response);
        } else {
            console.error("AJAX Error:", xhr.statusText, xhr.responseText);
            if (errorCallback) errorCallback(xhr, xhr.statusText, null);
        }
    };

    xhr.onerror = function() {
        console.error("Network Error");
        if (errorCallback) errorCallback(xhr, 'Network Error', null);
    };

    if ((method.toUpperCase() === 'POST' || method.toUpperCase() === 'PUT') && data) {
        xhr.send(data);
    } else {
        xhr.send();
    }
}


// Add other client-side utility functions here:
// - Form validation helpers
// - Dynamic content updates
// - Event listeners for common UI elements

document.addEventListener('DOMContentLoaded', function() {
    // Example: Attach confirmDeletion to all elements with class 'confirm-delete'
    const deleteButtons = document.querySelectorAll('.confirm-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            const message = this.dataset.confirmMessage || 'Are you sure you want to delete this item?';
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });

    // Example: Auto-dismiss alerts after a few seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        if (!alert.classList.contains('no-auto-dismiss')) { // Add 'no-auto-dismiss' class to prevent auto-dismiss
            setTimeout(() => {
                // Use Bootstrap's alert close method if available, otherwise just hide
                if (typeof(bootstrap) !== 'undefined' && bootstrap.Alert) {
                    const bsAlert = bootstrap.Alert.getInstance(alert);
                    if (bsAlert) {
                        bsAlert.close();
                    } else {
                        alert.style.display = 'none';
                    }
                } else {
                     alert.style.display = 'none';
                }
            }, 5000); // 5 seconds
        }
    });
});
