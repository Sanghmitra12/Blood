// BloodLink Campus — Main JS

function toggleNav() {
    document.getElementById('navLinks').classList.toggle('open');
}

// Auto-dismiss flash messages
document.addEventListener('DOMContentLoaded', () => {
    const flash = document.querySelector('.flash-message');
    if (flash) {
        setTimeout(() => flash.style.opacity = '0', 3500);
        setTimeout(() => flash.remove(), 4000);
        flash.style.transition = 'opacity 0.5s';
    }

    // Animate stat numbers
    document.querySelectorAll('.stat-num[data-target]').forEach(el => {
        const target = parseInt(el.dataset.target);
        let current = 0;
        const step = Math.ceil(target / 60);
        const interval = setInterval(() => {
            current = Math.min(current + step, target);
            el.textContent = current.toLocaleString();
            if (current >= target) clearInterval(interval);
        }, 25);
    });
});

// Confirm delete
function confirmDelete(msg) {
    return confirm(msg || 'Are you sure you want to delete this?');
}

// Preview image upload
function previewImage(input, imgId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById(imgId).src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}

// Filter donor cards by blood group
function filterDonors(bloodGroup) {
    document.querySelectorAll('.donor-card-wrap').forEach(card => {
        if (!bloodGroup || card.dataset.blood === bloodGroup) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.toggle('active', b.dataset.blood === bloodGroup));
}
