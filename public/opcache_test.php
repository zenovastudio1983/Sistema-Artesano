<?php $s=opcache_get_status(); echo $s["opcache_enabled"]?"ENABLED":"DISABLED"; echo " scripts:".$s["opcache_statistics"]["num_cached_scripts"]; ?>
