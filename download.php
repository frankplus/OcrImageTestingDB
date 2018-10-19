
<?php
session_start();
if (!isset( $_SESSION['user'] ) ) {
  header("location: /index.php");
}


    $zip = new ZipArchive;
    $download = 'tmp/fotoDb.zip';
    $zip->open($download, ZipArchive::CREATE);
    foreach (glob("foto/*") as $file) {
        $zip->addFile($file);
    }
    $zip->close();

    header('Content-Type: application/zip');
    header("Content-Disposition: attachment; filename = $download");
    header('Content-Length: ' . filesize($download));
?>
