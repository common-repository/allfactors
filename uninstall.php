<?php

// If uninstall.php is not called by WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
} else {
    // Delete the allfactors_hostname option and transients
    delete_option('allfactors_hostname');
    delete_transient('allfactors_script');
    delete_transient('allfactors_update');
}

?>
