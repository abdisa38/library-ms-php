/**
 * Library Management System - Main JavaScript
 */

// Sidebar Toggle
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });
    
    // Set active menu item
    const currentPath = window.location.pathname;
    const menuLinks = document.querySelectorAll('.sidebar-menu a');
    
    menuLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath || 
            currentPath.includes(link.getAttribute('href'))) {
            link.classList.add('active');
        }
    });
});

// Modal Functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('active');
    }
}

// Form Validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = 'var(--danger-color)';
            isValid = false;
        } else {
            input.style.borderColor = 'var(--gray-300)';
        }
    });
    
    return isValid;
}

// Confirm Delete
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

// Toast Notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type}`;
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '9999';
    toast.style.minWidth = '300px';
    toast.style.animation = 'slideInRight 0.3s ease-out';
    
    toast.innerHTML = `
        ${message}
        <button type="button" class="close" onclick="this.parentElement.remove()">&times;</button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// Add animation keyframes
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Search functionality (AJAX)
function searchBooks(query) {
    if (query.length < 2) return;
    
    fetch(`search.php?q=${encodeURIComponent(query)}&type=books`)
        .then(response => response.json())
        .then(data => {
            displaySearchResults(data, 'books');
        })
        .catch(error => console.error('Error:', error));
}

function searchStudents(query) {
    if (query.length < 2) return;
    
    fetch(`search.php?q=${encodeURIComponent(query)}&type=students`)
        .then(response => response.json())
        .then(data => {
            displaySearchResults(data, 'students');
        })
        .catch(error => console.error('Error:', error));
}

function displaySearchResults(data, type) {
    const resultsContainer = document.getElementById('searchResults');
    if (!resultsContainer) return;
    
    resultsContainer.innerHTML = '';
    
    if (data.length === 0) {
        resultsContainer.innerHTML = '<p class="text-muted">No results found</p>';
        return;
    }
    
    data.forEach(item => {
        const div = document.createElement('div');
        div.className = 'search-result-item';
        
        if (type === 'books') {
            div.innerHTML = `
                <div class="d-flex align-center gap-2">
                    <img src="uploads/books/${item.book_cover}" class="book-cover-sm" alt="${item.title}">
                    <div>
                        <strong>${item.title}</strong><br>
                        <small class="text-muted">${item.author_name} - ${item.isbn}</small>
                    </div>
                </div>
            `;
        } else if (type === 'students') {
            div.innerHTML = `
                <div>
                    <strong>${item.full_name}</strong><br>
                    <small class="text-muted">${item.student_id} - ${item.email}</small>
                </div>
            `;
        }
        
        resultsContainer.appendChild(div);
    });
}

// Real-time search with debounce
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// File Upload Preview
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Format Date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

// Calculate Fine
function calculateFine(dueDate, returnDate = null) {
    const due = new Date(dueDate);
    const returned = returnDate ? new Date(returnDate) : new Date();
    const diffTime = returned - due;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays <= 0) return 0;
    
    const dailyRate = parseFloat(document.getElementById('dailyFineRate')?.value || 5);
    return diffDays * dailyRate;
}

// Update fine display in real-time
function updateFineDisplay() {
    const dueDateInput = document.getElementById('dueDate');
    const returnDateInput = document.getElementById('returnDate');
    const fineDisplay = document.getElementById('fineDisplay');
    
    if (dueDateInput && returnDateInput && fineDisplay) {
        const fine = calculateFine(dueDateInput.value, returnDateInput.value);
        fineDisplay.textContent = `$${fine.toFixed(2)}`;
        
        if (fine > 0) {
            fineDisplay.className = 'text-danger';
        } else {
            fineDisplay.className = 'text-success';
        }
    }
}

// DataTable-like functionality
function initDataTable(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    // Add sorting functionality
    const headers = table.querySelectorAll('th[data-sortable]');
    
    headers.forEach((header, index) => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', () => {
            sortTable(table, index);
        });
    });
}

function sortTable(table, column) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    const currentOrder = table.getAttribute('data-sort-order') || 'asc';
    const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
    
    rows.sort((a, b) => {
        const aValue = a.cells[column].textContent.trim();
        const bValue = b.cells[column].textContent.trim();
        
        if (newOrder === 'asc') {
            return aValue.localeCompare(bValue, undefined, { numeric: true });
        } else {
            return bValue.localeCompare(aValue, undefined, { numeric: true });
        }
    });
    
    rows.forEach(row => tbody.appendChild(row));
    table.setAttribute('data-sort-order', newOrder);
}

// Export to CSV
function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const csvRow = [];
        
        cols.forEach(col => {
            let data = col.textContent.trim();
            data = data.replace(/"/g, '""'); // Escape quotes
            csvRow.push(`"${data}"`);
        });
        
        csv.push(csvRow.join(','));
    });
    
    downloadCSV(csv.join('\n'), filename);
}

function downloadCSV(csv, filename) {
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', filename);
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

// Print functionality
function printPage() {
    window.print();
}

// AJAX Form Submit
function submitFormAjax(formId, successCallback) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Operation successful!', 'success');
                if (successCallback) successCallback(data);
            } else {
                showToast(data.message || 'Operation failed!', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred!', 'danger');
        });
    });
}

// Auto-dismiss alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Password strength indicator
function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    
    return strength;
}

function updatePasswordStrength(inputId, displayId) {
    const input = document.getElementById(inputId);
    const display = document.getElementById(displayId);
    
    if (!input || !display) return;
    
    input.addEventListener('input', function() {
        const strength = checkPasswordStrength(this.value);
        const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        const colors = ['#e74a3b', '#f6c23e', '#f6c23e', '#1cc88a', '#1cc88a'];
        
        display.textContent = labels[strength - 1] || 'Very Weak';
        display.style.color = colors[strength - 1] || '#e74a3b';
    });
}

// Confirm password match
function checkPasswordMatch(password1Id, password2Id, displayId) {
    const password1 = document.getElementById(password1Id);
    const password2 = document.getElementById(password2Id);
    const display = document.getElementById(displayId);
    
    if (!password1 || !password2 || !display) return;
    
    password2.addEventListener('input', function() {
        if (password1.value === password2.value && password2.value !== '') {
            display.textContent = 'Passwords match';
            display.style.color = '#1cc88a';
            password2.style.borderColor = '#1cc88a';
        } else if (password2.value !== '') {
            display.textContent = 'Passwords do not match';
            display.style.color = '#e74a3b';
            password2.style.borderColor = '#e74a3b';
        } else {
            display.textContent = '';
            password2.style.borderColor = 'var(--gray-300)';
        }
    });
}

// Auto-complete functionality
function initAutocomplete(inputId, dataSource, onSelect) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    let debounceTimer;
    
    input.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        
        const query = this.value;
        
        if (query.length < 2) {
            hideAutocomplete();
            return;
        }
        
        debounceTimer = setTimeout(() => {
            fetch(`${dataSource}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    showAutocomplete(input, data, onSelect);
                })
                .catch(error => console.error('Error:', error));
        }, 300);
    });
}

function showAutocomplete(input, data, onSelect) {
    hideAutocomplete();
    
    if (data.length === 0) return;
    
    const container = document.createElement('div');
    container.id = 'autocomplete-container';
    container.style.cssText = `
        position: absolute;
        background: white;
        border: 1px solid var(--gray-300);
        border-radius: var(--border-radius);
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: var(--box-shadow);
        width: ${input.offsetWidth}px;
    `;
    
    data.forEach(item => {
        const div = document.createElement('div');
        div.style.cssText = `
            padding: 0.75rem;
            cursor: pointer;
            border-bottom: 1px solid var(--gray-200);
        `;
        div.textContent = item.label || item.name || item.title;
        
        div.addEventListener('click', () => {
            onSelect(item);
            hideAutocomplete();
        });
        
        div.addEventListener('mouseenter', () => {
            div.style.backgroundColor = 'var(--gray-100)';
        });
        
        div.addEventListener('mouseleave', () => {
            div.style.backgroundColor = 'white';
        });
        
        container.appendChild(div);
    });
    
    input.parentNode.style.position = 'relative';
    input.parentNode.appendChild(container);
}

function hideAutocomplete() {
    const container = document.getElementById('autocomplete-container');
    if (container) {
        container.remove();
    }
}

// Close autocomplete when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.matches('input[type="text"]')) {
        hideAutocomplete();
    }
});
