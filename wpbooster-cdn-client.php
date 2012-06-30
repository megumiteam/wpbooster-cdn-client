<?php
/*
Plugin Name: The WPBooster CDN Client
Author: Digitalcube Co,.Ltd (Takayuki Miyauchi)
Description: Deliver static files from WPBooster CDN.
Version: 1.1.0
Author URI: http://wpbooster.net/
Domain Path: /languages
Text Domain: wpbooster-cdn-client
*/

/*
Copyright (c) 2012 Takayuki Miyauchi (DigitalCube Co,.Ltd).

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

new WPBoosterCDN();

class WPBoosterCDN {

private $cdn = 'cdn.wpbooster.net';
private $api = 'http://api.wpbooster.net/check_host/%s';
private $key = 'wpboosterapikey';
private $is_active = 'wpbooster-is-active';
private $exp = 86400;

function __construct()
{
    register_activation_hook(__FILE__, array(&$this, "is_active_host"));
    add_action("plugins_loaded", array(&$this, "plugins_loaded"));
    add_action('admin_init', array(&$this, 'admin_init'));
}

public function admin_init()
{
    if (isset($_POST['wpbooster-api']) && $_POST['wpbooster-api']) {
        if (preg_match("/^[a-zA-Z0-9]{32}$/", $_POST['wpbooster-api'])) {
            update_option($this->key, $_POST['wpbooster-api']);
            wp_redirect(admin_url());
        }
    }
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
    $cdn = get_transient($this->is_active);
    return str_replace(
        $cdn->base_url,
        'http://'.$this->cdn.'/'.$cdn->id.'/',
        $uri
    );
}

public function is_active_host()
{
    if (!$api = get_option($this->key)) {
        delete_transient($this->key);
        add_action('admin_notices', array(&$this, 'admin_notice'));
        return false;
    }

    if (get_transient($this->is_active)) {
        return true;
    } else {
        $res = wp_remote_get(sprintf($this->api, $api));
        if ($res['response']['code'] === 200) {
            set_transient($this->is_active, json_decode($res['body']), $this->exp);
            return true;
        } else {
            delete_transient($this->is_active);
            add_action('admin_notices', array(&$this, 'admin_notice'));
            return false;
        }
    }
}

public function admin_notice()
{
    printf(
        '<div class="error"><form method="post">Please input WP Booster API Key: <input size=30 type="text" value="" name="wpbooster-api"><input type="submit" value="Save"> <span>%s</span></form></div>',
        __('<a href="http://wpbooster.net/cpanel">Sign In</a>', 'wpbooster-cdn-client')
    );
}

private function get_hostname()
{
    $url = parse_url(home_url());
    return $url['host'];
}

} // MegumiCDN

// EOF
