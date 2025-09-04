<?php
// Vérifier les droits d'administration
require_once '../includes/init.php';
requireAdmin();

// Inclure les classes nécessaires
require_once ROOT_PATH . 'includes/classes/BackupManager.php';

$backupManager = BackupManager::getInstance();
$message = '';
$error = '';

// Gérer les actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'create_backup':
                    // Créer une sauvegarde manuelle
                    $result = $backupManager->createBackup();
                    
                    if ($result['success']) {
                        $message = 'Sauvegarde créée avec succès !';
                        
                        // Ajouter des détails sur la sauvegarde
                        $message .= '\n- Base de données : ' . basename($result['database']['file']) . 
                                  ' (' . formatSize($result['database']['size']) . ')';
                        
                        if (!empty($result['files'])) {
                            $message .= '\n- Fichiers : ' . count($result['files']) . 
                                      ' fichiers (' . formatSize($result['total_size']) . ')';
                        }
                    } else {
                        $error = 'Erreur lors de la création de la sauvegarde : ' . 
                                implode('\n', $result['errors']);
                    }
                    break;
                    
                case 'schedule_backup':
                    // Planifier une sauvegarde automatique
                    $time = filter_input(INPUT_POST, 'backup_time', FILTER_SANITIZE_STRING) ?: '02:00';
                    $frequency = filter_input(INPUT_POST, 'backup_frequency', FILTER_SANITIZE_STRING) ?: 'daily';
                    
                    if ($backupManager->scheduleBackup($time, $frequency)) {
                        $message = 'Sauvegarde planifiée avec succès !';
                        $message .= "\n- Fréquence : " . ucfirst($frequency);
                        $message .= "\n- Heure : $time";
                    } else {
                        $error = 'Impossible de planifier la sauvegarde automatique';
                    }
                    break;
                    
                case 'cancel_schedule':
                    // Annuler la planification
                    if ($backupManager->unscheduleBackup()) {
                        $message = 'Planification annulée avec succès';
                    } else {
                        $error = 'Impossible d\'annuler la planification';
                    }
                    break;
                    
                case 'restore_backup':
                    // Restaurer une sauvegarde
                    $backupFile = filter_input(INPUT_POST, 'backup_file', FILTER_SANITIZE_STRING);
                    $restoreDb = isset($_POST['restore_database']);
                    $restoreFiles = isset($_POST['restore_files']);
                    
                    if (empty($backupFile) || (!file_exists($backupFile) && !file_exists('../' . $backupFile))) {
                        $error = 'Fichier de sauvegarde introuvable';
                    } else {
                        $fullPath = file_exists($backupFile) ? $backupFile : ('../' . $backupFile);
                        $result = $backupManager->restoreBackup($fullPath, $restoreDb, $restoreFiles);
                        
                        if ($result['success']) {
                            $message = 'Restauration terminée avec succès !';
                            
                            if ($restoreDb && $result['database']) {
                                $message .= '\n- Base de données : ' . 
                                          count($result['database']['tables']) . ' tables restaurées';
                            }
                            
                            if ($restoreFiles && $result['files']) {
                                $message .= '\n- Fichiers : ' . 
                                          count($result['files']['restored']) . ' fichiers restaurés';
                            }
                        } else {
                            $error = 'Erreur lors de la restauration : ' . 
                                    implode('\n', $result['errors']);
                        }
                    }
                    break;
            }
        } catch (Exception $e) {
            $error = 'Erreur : ' . $e->getMessage();
        }
    }
}

// Récupérer la liste des sauvegardes disponibles
$backupDir = ROOT_PATH . 'backups/';
$backups = [];

if (is_dir($backupDir)) {
    $files = scandir($backupDir, SCANDIR_SORT_DESCENDING);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $filePath = $backupDir . $file;
        $fileInfo = [
            'name' => $file,
            'path' => 'backups/' . $file,
            'size' => filesize($filePath),
            'date' => filemtime($filePath),
            'type' => strpos($file, '_db_') !== false ? 'database' : 
                     (strpos($file, '_files_') !== false ? 'files' : 'unknown')
        ];
        
        $backups[] = $fileInfo;
    }
}

// Trier par date (du plus récent au plus ancien)
usort($backups, function($a, $b) {
    return $b['date'] - $a['date'];
});

// Fonction utilitaire pour formater la taille
function formatSize($bytes) {
    $units = ['o', 'Ko', 'Mo', 'Go', 'To'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-4">
    <h2><i class="fas fa-database"></i> Gestion des sauvegardes</h2>
    
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo nl2br(htmlspecialchars($message)); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo nl2br(htmlspecialchars($error)); ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Créer une sauvegarde</h5>
                </div>
                <div class="card-body">
                    <p>Créez une sauvegarde manuelle du site et de la base de données.</p>
                    <form method="post" class="mb-0">
                        <input type="hidden" name="action" value="create_backup">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Créer une sauvegarde maintenant
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-clock"></i> Planifier une sauvegarde automatique</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="action" value="schedule_backup">
                        
                        <div class="form-group">
                            <label for="backup_frequency">Fréquence :</label>
                            <select name="backup_frequency" id="backup_frequency" class="form-control">
                                <option value="daily">Quotidienne</option>
                                <option value="weekly">Hebdomadaire</option>
                                <option value="monthly">Mensuelle</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="backup_time">Heure :</label>
                            <input type="time" name="backup_time" id="backup_time" 
                                   class="form-control" value="02:00" required>
                        </div>
                        
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-calendar-plus"></i> Planifier
                        </button>
                        
                        <button type="submit" name="action" value="cancel_schedule" 
                                class="btn btn-outline-danger float-right">
                            <i class="fas fa-calendar-times"></i> Annuler la planification
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-undo"></i> Restaurer une sauvegarde</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($backups)): ?>
                        <p class="text-muted">Aucune sauvegarde disponible.</p>
                    <?php else: ?>
                        <form method="post">
                            <input type="hidden" name="action" value="restore_backup">
                            
                            <div class="form-group">
                                <label for="backup_file">Sélectionner une sauvegarde :</label>
                                <select name="backup_file" id="backup_file" class="form-control" required>
                                    <?php foreach ($backups as $backup): ?>
                                        <option value="<?php echo htmlspecialchars($backup['path']); ?>">
                                            <?php 
                                                echo htmlspecialchars($backup['name']) . ' - ' . 
                                                    date('d/m/Y H:i', $backup['date']) . ' - ' . 
                                                    formatSize($backup['size']);
                                            ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="restore_database" 
                                       id="restore_database" value="1" checked>
                                <label class="form-check-label" for="restore_database">
                                    Restaurer la base de données
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="restore_files" 
                                       id="restore_files" value="1" checked>
                                <label class="form-check-label" for="restore_files">
                                    Restaurer les fichiers
                                </label>
                            </div>
                            
                            <div class="alert alert-danger">
                                <strong>Attention !</strong> La restauration écrasera les données existantes.
                                Assurez-vous d'avoir une sauvegarde récente avant de continuer.
                            </div>
                            
                            <button type="submit" class="btn btn-warning" 
                                    onclick="return confirm('Êtes-vous sûr de vouloir restaurer cette sauvegarde ? Cette action est irréversible.');">
                                <i class="fas fa-undo"></i> Restaurer la sauvegarde
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-list"></i> Liste des sauvegardes</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($backups)): ?>
                <p class="p-3 mb-0">Aucune sauvegarde disponible.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Nom du fichier</th>
                                <th>Date</th>
                                <th>Taille</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($backups as $backup): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($backup['name']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', $backup['date']); ?></td>
                                    <td><?php echo formatSize($backup['size']); ?></td>
                                    <td>
                                        <?php 
                                            $badgeClass = $backup['type'] === 'database' ? 'badge-primary' : 
                                                         ($backup['type'] === 'files' ? 'badge-success' : 'badge-secondary');
                                            echo '<span class="badge ' . $badgeClass . '">' . 
                                                 ucfirst($backup['type']) . '</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($backup['path']); ?>" 
                                           class="btn btn-sm btn-info" download>
                                            <i class="fas fa-download"></i> Télécharger
                                        </a>
                                        <a href="#" class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Supprimer cette sauvegarde ?') && 
                                           window.location.href='delete_backup.php?file=<?php echo urlencode($backup['path']); ?>';">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
