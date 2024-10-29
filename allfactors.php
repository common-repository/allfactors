<?php
/***
Plugin Name: AllFactors
Plugin URI: https://allfactors.com/
Description: AllFactors is a no-code all-in-one web marketing analytics tool. Get actionable insights. Make better decisions. Grow your business.
Version: 0.8.0
Requires at least: 5.7
Requires PHP: 7.4
Author: AllFactors
License: MIT
Text Domain: allfactors
***/

defined('ABSPATH') || exit;

function allfactors_register_settings() {
    add_option('allfactors_hostname', '');
    register_setting('allfactors_options_group', 'allfactors_hostname', array('sanitize_callback' => 'allfactors_options_sanitize'));
}
add_action('admin_init', 'allfactors_register_settings');

function allfactors_register_options_page() {
    add_options_page('AllFactors Settings', 'AllFactors', 'manage_options', 'allfactors', 'allfactors_options_page');
}
add_action('admin_menu', 'allfactors_register_options_page');

function allfactors_plugin_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=allfactors">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'allfactors_plugin_settings_link');

function allfactors_generateRandomUrl($minLevel = 1, $maxLevel = 5) {
    // Cache busting once a day to make sure latest script is loaded
    $result = '';
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $charactersLength = strlen($characters);
  
    $levels = random_int($minLevel, $maxLevel);
    for ($i = 0; $i < $levels; $i++) {
        $suburl = '/';
        $suburlLength = random_int(3, 16);
        for ($j = 0; $j < $suburlLength; $j++) {
            $suburl .= $characters[random_int(0, $charactersLength - 1)];
        }
        $result .= $suburl;
    }
    $result .= ".js";
  
    return $result;
}

function allfactors_insert_script() {
    if (!empty(get_option('allfactors_hostname'))) {
        $script_src = get_transient('allfactors_script');
        if ($script_src === false) {
            $https_hostname = esc_url("https://" . get_option('allfactors_hostname')); // prevent XSS injection
            $script_src = $https_hostname . allfactors_generateRandomUrl();
            set_transient('allfactors_script', $script_src, DAY_IN_SECONDS);
        }

        $script_handle = 'allfactors_script_handle';

        if (version_compare(get_bloginfo('version'), '6.3', '>=')) {
            // WordPress 6.3 and above
            wp_enqueue_script($script_handle, $script_src, array(), null, true, array( 'async' => true ));
        } else {
            // WordPress 5.7 - 6.2
            wp_register_script($script_handle, $script_src, array(), null, true);
            wp_enqueue_script($script_handle);

            // Add async attribute using a filter
            add_filter('script_loader_tag', function($tag, $handle) use ($script_handle) {
                if ($handle === $script_handle) {
                    return str_replace('<script ', '<script async ', $tag);
                }
                return $tag;
            }, 10, 2);
        }
    }
}
add_action('wp_enqueue_scripts', 'allfactors_insert_script');

function allfactors_options_page() {
?>
    <div>
        <h1>AllFactors for Wordpress</h1>
        <form method="post" action="options.php">
            <?php settings_fields('allfactors_options_group'); ?>
            <p>Paste your AllFactors hostname below:<br/>
            <input type="text" name="allfactors_hostname" value="<?php echo esc_attr(get_option('allfactors_hostname')); ?>" /></p>
            <?php submit_button(); ?>
            <p>Don't have AllFactors yet? <a href="https://allfactors.com/" target="_blank" rel="noopener">Sign-up Here!</a></p>
        </form>
    </div>
<?php
}

function allfactors_options_sanitize($input) {
    $hostname = sanitize_text_field($input);
    if (empty($hostname)) {
        add_settings_error('allfactors_options_group', 'invalid_hostname', 'You did not enter a hostname.', 'error');
        return get_option('allfactors_hostname');
    }

    return $input;
}

?>
