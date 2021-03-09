<?php
	echo "Bonjour";
	echo $upload_max_filesize ;
    phpinfo();
    $upload_max_size = ini_get('upload_max_filesize');
    echo $upload_max_size ;
?>
