// Gestion du panier
function updateCart() {
    const cartCount = document.querySelector('.cart-count');
    if (cartCount) {
        fetch('/api/cart/count.php')
            .then(response => response.json())
            .then(data => {
                cartCount.textContent = data.count;
            });
    }
}

// Gestion des formulaires
function handleFormSubmit(form) {
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
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message);
                }
            } else {
                alert(data.message);
            }
        });
    });
}

// Gestion des notifications
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 5000);
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Mettre à jour le nombre d'articles dans le panier
    updateCart();
    
    // Gérer les formulaires
    const forms = document.querySelectorAll('form');
    forms.forEach(form => handleFormSubmit(form));
});
