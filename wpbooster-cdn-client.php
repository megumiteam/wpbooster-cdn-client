<?php
/*
Plugin Name: The WPBooster CDN Client
Author: Digitalcube Co,.Ltd (Takayuki Miyauchi)
Description: Deliver static files from WPBooster CDN.
Version: 1.0.0
Author URI: http://wpbooster.net/
Domain Path: /languages
Text Domain: wpbooster-cdn-client
*/


new WPBoosterCDN();

class WPBoosterCDN {

private $cdn = 'cdn.wpbooster.net';
private $api = 'http://api.wpbooster.net/check_host/%s';
private $key = 'wpbooster-cdn-is-active';
private $exp = 86400;

function __construct()
{
    register_activation_hook(__FILE__, array(&$this, "is_active_host"));
    add_action("plugins_loaded", array(&$this, "plugins_loaded"));
}

public function plugins_loaded()
{
    if ($this->is_active_host()) {
        if (!is_user_logged_in()) {
            $hooks = array(
                "stylesheet_directory_uri",
                "template_directory_uri",
                "plugins_url",
                "wp_get_attachment_url",
                "theme_mod_header_image",
                "theme_mod_background_image",
            );
            foreach ($hooks as $hook) {
                add_filter(
                    $hook,
                    array(&$this, "filter")
                );
            }
            add_filter('the_content', array(&$this, 'the_content'));
        }
    }
}

public function the_content($html)
{
    $up = wp_upload_dir();
    $upload_url = $up['baseurl'];
    $filtered_url = $this->filter($upload_url);
    return str_replace($upload_url, $filtered_url, $html);
}

public function filter($uri)
{
    return str_replace(
        'http://'.$this->get_hostname(),
        'http://'.$this->get_cdn_path(),
        $uri
    );
}

public function is_active_host()
{
    if (get_transient($this->key)) {
        return true;
    } else {
        $res = wp_remote_head(sprintf($this->api, $this->get_hostname()));
        if ($res['response']['code'] === 200) {
            set_transient($this->key, true, $this->exp);
            return true;
        } else {
            delete_transient($this->key);
            add_action('admin_notices', array(&$this, 'admin_notice'));
            return false;
        }
    }
}

public function admin_notice()
{
    printf(
        '<div class="error"><p>%s</p></div>',
        __('You can not activate WPBooster CDN. <a href="http://wpbooster.net/">Please Register to WPBooster Account.</a>')
    );
}

private function get_cdn_path()
{
    return $this->cdn.'/'.$this->get_hostname();
}

private function get_hostname()
{
    $url = parse_url(home_url());
    return $url['host'];
}

} // MegumiCDN

// EOF
