<?php
if (file_exists(".awave")):
    require_once ".awave/src/require.php";
else:
    echo "Can't find .awave directory, try to reinstall";
endif;
