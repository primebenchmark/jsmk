<?php
require_once __DIR__ . '/../config.php';
requireAdmin();

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

if ($action === 'export') {
    $tarFile = sys_get_temp_dir() . '/mcq_backup_' . date('Ymd_His') . '.tar';
    $gzFile = $tarFile . '.gz';
    
    try {
        if (file_exists($tarFile)) unlink($tarFile);
        if (file_exists($gzFile)) unlink($gzFile);
        
        $phar = new PharData($tarFile);
        $phar->buildFromDirectory(DATA_PATH);
        $phar->compress(Phar::GZ);
        
        unlink($tarFile); // remove the uncompressed tar
        
        header('Content-Type: application/x-gzip');
        header('Content-disposition: attachment; filename=mcq_backup_' . date('Ymd_His') . '.tar.gz');
        header('Content-Length: ' . filesize($gzFile));
        readfile($gzFile);
        unlink($gzFile);
        exit;
    } catch (Exception $e) {
        die('Error creating backup: ' . htmlspecialchars($e->getMessage()));
    }
}

if ($action === 'import' && isset($_FILES['backup_zip'])) {
    $gzFile = $_FILES['backup_zip']['tmp_name'];
    
    try {
        $phar = new PharData($gzFile);
        $phar->extractTo(DATA_PATH, null, true); // true = overwrite existing files
        
        header('Location: settings.php?msg=Import+successful&msgType=success');
        exit;
    } catch (Exception $e) {
        header('Location: settings.php?msg=Import+failed&msgType=error');
        exit;
    }
}
