<?php
if (file_exists(".aw")):
    require_once ".aw/src/require.php";
else:
    echo "Can't find .aw directory, try to reinstall";
endif;
