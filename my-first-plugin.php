<?php
/**
 * Plugin Name: My First Plugin
 * Plugin URI:  https://example.com
 * Description: A simple WordPress plugin example.
 * Version:     1.0
 * Author:      Your Name
 * Author URI:  https://example.com
 * License:     GPL2
 */
 
 function my_plugin_footer_message() {
    echo '<p style="text-align:center; color:blue;">Thanks for reading!</p>';
}
add_action('wp_footer', 'my_plugin_footer_message');

