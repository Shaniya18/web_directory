// Global JavaScript functions
function switchTab(tabName) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.querySelector('[data-tab="' + tabName + '"]').classList.add('active');
    document.getElementById(tabName).classList.add('active');
}

function setRating(rating) {
    const ratingInput = document.getElementById('rating-input');
    if (ratingInput) {
        ratingInput.value = rating;
        updateStarDisplay(rating);
    }
}

function updateStarDisplay(rating) {
    for (let i = 1; i <= 5; i++) {
        const star = document.getElementById('star-' + i);
        if (star) {
            star.innerHTML = i <= rating ? '★' : '☆';
            star.style.color = i <= rating ? '#ffa500' : '#ddd';
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize star ratings
    const ratingInput = document.getElementById('rating-input');
    if (ratingInput) {
        updateStarDisplay(parseInt(ratingInput.value));
    }
    
    // Auto-hide messages after 5 seconds
    setTimeout(() => {
        const messages = document.querySelectorAll('.message');
        messages.forEach(msg => {
            msg.style.opacity = '0';
            msg.style.transition = 'opacity 0.5s ease';
            setTimeout(() => msg.remove(), 500);
        });
    }, 5000);
    
    // Form validation enhancements
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let valid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.style.borderColor = '#cc0000';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });
});