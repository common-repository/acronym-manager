<?php
   $filename = 'Acronym_Manager_Backup-'.date("Y-m-d").'.amf';
   header('Content-Type: text/plain');
   header('Content-Disposition: attachment; filename='.$filename);
   print $_POST['acronym-export'];
?>
