// Test simple pour vérifier le mode nuit
console.log('Test du mode nuit - Début');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM chargé');
    
    // Vérifier si le bouton existe
    const button = document.querySelector('.theme-toggle');
    if (button) {
        console.log('Bouton trouvé:', button);
        
        // Test de basculement
        button.addEventListener('click', function() {
            console.log('Clic détecté');
            document.body.classList.toggle('dark-mode');
            console.log('Mode sombre:', document.body.classList.contains('dark-mode'));
        });
    } else {
        console.error('Bouton de mode nuit non trouvé');
    }
});
