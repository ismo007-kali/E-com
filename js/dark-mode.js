/**
 * Système de mode nuit/jour pour MODE ET TENDANCE
 * Gestion du thème sombre/clair avec sauvegarde des préférences
 */

class ThemeManager {
    constructor() {
        this.toggleButton = null;
        this.init();
    }

    init() {
        try {
            // Créer le bouton de basculement
            this.createToggleButton();
            
            // Charger le thème sauvegardé
            this.loadSavedTheme();
            
            // Écouter les événements
            this.bindEvents();
            
            // Détecter la préférence système
            this.detectSystemPreference();
            
            console.log('ThemeManager initialisé avec succès');
        } catch (error) {
            console.error('Erreur lors de l\'initialisation du ThemeManager:', error);
        }
    }

    createToggleButton() {
        // Créer le bouton de basculement
        const toggleButton = document.createElement('button');
        toggleButton.className = 'theme-toggle';
        toggleButton.setAttribute('aria-label', 'Basculer entre mode jour et nuit');
        toggleButton.setAttribute('title', 'Changer le thème');
        
        // Forcer le positionnement en haut sur mobile
        toggleButton.style.position = 'fixed';
        toggleButton.style.top = '20px';
        toggleButton.style.right = '20px';
        toggleButton.style.bottom = 'auto';
        
        // Ajouter les icônes
        toggleButton.innerHTML = `
            <i class="fa fa-sun-o sun-icon" aria-hidden="true"></i>
            <i class="fa fa-moon-o moon-icon" aria-hidden="true"></i>
        `;
        
        // Ajouter au body
        document.body.appendChild(toggleButton);
        
        this.toggleButton = toggleButton;
        
        // Créer l'indicateur de raccourci clavier
        this.createShortcutIndicator();
        
        // Gérer le conflit avec le menu hamburger sur mobile
        this.handleMobileMenuConflict();
        
        // Ajuster la position selon la taille d'écran
        this.adjustPositionForMobile();
    }

    createShortcutIndicator() {
        // Créer l'indicateur de raccourci
        const shortcutIndicator = document.createElement('div');
        shortcutIndicator.className = 'theme-shortcut';
        shortcutIndicator.textContent = 'Ctrl+Shift+D';
        shortcutIndicator.setAttribute('title', 'Raccourci clavier pour basculer le thème');
        
        // Ajouter au body
        document.body.appendChild(shortcutIndicator);
        
        this.shortcutIndicator = shortcutIndicator;
    }

    adjustPositionForMobile() {
        const updatePosition = () => {
            if (window.innerWidth <= 576) {
                this.toggleButton.style.top = '12px';
                this.toggleButton.style.right = '65px';
                this.toggleButton.style.position = 'fixed';
                this.toggleButton.style.bottom = 'auto';
                
                // Ajuster l'indicateur de raccourci
                if (this.shortcutIndicator) {
                    this.shortcutIndicator.style.top = '60px';
                    this.shortcutIndicator.style.right = '60px';
                    this.shortcutIndicator.style.display = 'none'; // Masquer sur très petits écrans
                }
            } else if (window.innerWidth <= 768) {
                this.toggleButton.style.top = '15px';
                this.toggleButton.style.right = '70px';
                this.toggleButton.style.position = 'fixed';
                this.toggleButton.style.bottom = 'auto';
                
                // Ajuster l'indicateur de raccourci
                if (this.shortcutIndicator) {
                    this.shortcutIndicator.style.top = '65px';
                    this.shortcutIndicator.style.right = '65px';
                    this.shortcutIndicator.style.display = 'block';
                }
            } else {
                this.toggleButton.style.top = '20px';
                this.toggleButton.style.right = '20px';
                this.toggleButton.style.position = 'fixed';
                this.toggleButton.style.bottom = 'auto';
                
                // Ajuster l'indicateur de raccourci
                if (this.shortcutIndicator) {
                    this.shortcutIndicator.style.top = '75px';
                    this.shortcutIndicator.style.right = '15px';
                    this.shortcutIndicator.style.display = 'block';
                }
            }
        };

        // Appliquer immédiatement
        updatePosition();

        // Écouter les changements de taille d'écran
        window.addEventListener('resize', updatePosition);
        window.addEventListener('orientationchange', () => {
            setTimeout(updatePosition, 100);
        });
    }

    handleMobileMenuConflict() {
        // Observer les changements du menu hamburger
        const navbarToggler = document.querySelector('.navbar-toggler');
        if (navbarToggler) {
            // Écouter les clics sur le menu hamburger
            navbarToggler.addEventListener('click', () => {
                setTimeout(() => {
                    const isExpanded = navbarToggler.getAttribute('aria-expanded') === 'true';
                    if (isExpanded) {
                        this.toggleButton.style.opacity = '0.7';
                        this.toggleButton.style.zIndex = '9998';
                    } else {
                        this.toggleButton.style.opacity = '1';
                        this.toggleButton.style.zIndex = '9999';
                    }
                }, 50);
            });
        }
    }

    bindEvents() {
        // Vérifier que le bouton existe avant d'ajouter les événements
        if (!this.toggleButton) {
            console.error('Impossible d\'ajouter les événements : bouton non trouvé');
            return;
        }

        // Événement de clic sur le bouton
        this.toggleButton.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            console.log('Clic sur le bouton de mode nuit détecté');
            this.toggleTheme();
        });

        // Écouter les changements de préférence système
        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            mediaQuery.addListener((e) => {
                if (!this.hasUserPreference()) {
                    this.setTheme(e.matches ? 'dark' : 'light');
                }
            });
        }

        // Raccourci clavier (Ctrl + Shift + D)
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                e.preventDefault();
                this.toggleTheme();
            }
        });
    }

    toggleTheme() {
        try {
            const currentTheme = this.getCurrentTheme();
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            console.log(`Basculement de ${currentTheme} vers ${newTheme}`);
            
            this.setTheme(newTheme);
            this.saveTheme(newTheme);
            
            // Animation de feedback
            this.animateToggle();
        } catch (error) {
            console.error('Erreur lors du basculement de thème:', error);
        }
    }

    setTheme(theme) {
        const body = document.body;
        
        if (theme === 'dark') {
            body.classList.add('dark-mode');
            this.updateMetaThemeColor('#1a1a1a');
        } else {
            body.classList.remove('dark-mode');
            this.updateMetaThemeColor('#ffffff');
        }
        
        // Mettre à jour l'attribut data-theme pour le CSS
        document.documentElement.setAttribute('data-theme', theme);
        
        // Déclencher un événement personnalisé
        window.dispatchEvent(new CustomEvent('themeChanged', { 
            detail: { theme: theme } 
        }));
    }

    getCurrentTheme() {
        return document.body.classList.contains('dark-mode') ? 'dark' : 'light';
    }

    saveTheme(theme) {
        try {
            localStorage.setItem('mode-et-tendance-theme', theme);
            localStorage.setItem('mode-et-tendance-theme-timestamp', Date.now().toString());
        } catch (error) {
            console.warn('Impossible de sauvegarder le thème:', error);
        }
    }

    loadSavedTheme() {
        try {
            const savedTheme = localStorage.getItem('mode-et-tendance-theme');
            const timestamp = localStorage.getItem('mode-et-tendance-theme-timestamp');
            
            // Vérifier si la préférence n'est pas trop ancienne (30 jours)
            if (timestamp) {
                const thirtyDaysAgo = Date.now() - (30 * 24 * 60 * 60 * 1000);
                if (parseInt(timestamp) < thirtyDaysAgo) {
                    this.clearSavedTheme();
                    return;
                }
            }
            
            if (savedTheme && (savedTheme === 'dark' || savedTheme === 'light')) {
                this.setTheme(savedTheme);
            }
        } catch (error) {
            console.warn('Impossible de charger le thème sauvegardé:', error);
        }
    }

    hasUserPreference() {
        try {
            return localStorage.getItem('mode-et-tendance-theme') !== null;
        } catch (error) {
            return false;
        }
    }

    clearSavedTheme() {
        try {
            localStorage.removeItem('mode-et-tendance-theme');
            localStorage.removeItem('mode-et-tendance-theme-timestamp');
        } catch (error) {
            console.warn('Impossible de supprimer le thème sauvegardé:', error);
        }
    }

    detectSystemPreference() {
        // Si l'utilisateur n'a pas de préférence sauvegardée, utiliser celle du système
        if (!this.hasUserPreference() && window.matchMedia) {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (prefersDark) {
                this.setTheme('dark');
            }
        }
    }

    updateMetaThemeColor(color) {
        // Mettre à jour la couleur de thème pour les navigateurs mobiles
        let metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (!metaThemeColor) {
            metaThemeColor = document.createElement('meta');
            metaThemeColor.name = 'theme-color';
            document.head.appendChild(metaThemeColor);
        }
        metaThemeColor.content = color;
    }

    animateToggle() {
        // Animation de rotation du bouton
        this.toggleButton.style.transform = 'rotate(360deg)';
        setTimeout(() => {
            this.toggleButton.style.transform = '';
        }, 300);
    }

    // Méthode publique pour changer le thème depuis l'extérieur
    static setTheme(theme) {
        if (window.themeManager) {
            window.themeManager.setTheme(theme);
            window.themeManager.saveTheme(theme);
        }
    }

    // Méthode publique pour obtenir le thème actuel
    static getCurrentTheme() {
        if (window.themeManager) {
            return window.themeManager.getCurrentTheme();
        }
        return 'light';
    }
}

// Initialisation automatique quand le DOM est prêt
document.addEventListener('DOMContentLoaded', () => {
    // Créer une instance globale
    window.themeManager = new ThemeManager();
    
    // Ajouter des méthodes globales pour faciliter l'utilisation
    window.toggleTheme = () => window.themeManager.toggleTheme();
    window.setTheme = (theme) => ThemeManager.setTheme(theme);
    window.getCurrentTheme = () => ThemeManager.getCurrentTheme();
    
    console.log('🌙 Système de mode nuit/jour initialisé pour MODE ET TENDANCE');
});

// Gestion des erreurs globales
window.addEventListener('error', (event) => {
    if (event.filename && event.filename.includes('dark-mode.js')) {
        console.error('Erreur dans le système de thème:', event.error);
    }
});

// Export pour utilisation en module (si nécessaire)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeManager;
}
