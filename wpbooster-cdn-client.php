<?php
/*
Plugin Name: WPBooster CDN Client
Author: Digitalcube Co,.Ltd (Takayuki Miyauchi)
Description: Deliver static files from WPBooster CDN.
Version: 0.1.0
Author URI: http://digitalcube.jp/
Domain Path: /languages
Text Domain: wpbooster-cdn
*/


new WPBoosterCDN();

class WPBoosterCDN {

private $cdn = 'cdn.wpbooster.net';
private $api = 'http://api.wpbooster.net/check_host/%s';
private $key = 'wpbooster-cdn-is-active';
private $exp = 86400;

function __construct()
{
    $hooks = array(
        "stylesheet_directory_uri",
        "template_directory_uri",
        "plugins_url",
        "includes_url",
    );
    foreach ($hooks as $hook) {
        add_filter(
            $hook,
            array(&$this, "filter")
        );
    }
    add_filter('the_content', array(&$this, 'the_content'));
}

public function the_content($html)
{
    $up = wp_upload_dir();
    $upload_url = $up['baseurl'];
    $filterd_url = $this->filter($upload_url);
    return str_replace($upload_url, $filterd_url, $html);
}

public function filter($uri)
{
    return str_replace(
        untrailingslashit(home_url()),
        untrailingslashit(esc_url($this->get_url())),
        $uri
    );
}

private function is_active_host()
{
    if ($res = get_transient($this->key)) {
        return $res;
    } else {
        $res = wp_remote_head(sprintf($this->api, $this->get_hostname()));
        if ($res['response']['code'] === 200) {
            set_transient($this->key, true, $this->exp);
            return true;
        }
    }
}

private function get_url()
{
    if ($this->is_active_host()) {
        return str_replace(
            $this->get_hostname(),
            $this->get_cdn_path(),
            home_url()
        );
    } else {
        return home_url();
    }
}

private function get_cdn_path()
{
    $url = parse_url(home_url("/"));
    return $this->cdn.'/'.$this->get_hostname();
}

private function get_hostname()
{
    $url = parse_url(home_url("/"));
    return $url['host'];
}

} // MegumiCDN



// EOF
