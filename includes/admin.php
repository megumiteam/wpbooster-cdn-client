<?php

new WPBooster_Admin();

class WPBooster_Admin {

private $key = null;
private $get_point_by_api = 'http://api.wpbooster.net/get_point_by_api/%s';
private $get_requests_by_api = 'http://api.wpbooster.net/get_requests_by_api/%s';
private $transient_expire = 3600;
private $transient_key = 'wpbooster-site-data';
private $is_active = 'wpbooster-is-active';
private $feed_items = 5;

function __construct()
{
    $this->key = get_option("wpboosterapikey");
    if ($this->key) {
        add_action("admin_menu", array(&$this, "admin_menu"));
    }
}

public function admin_menu()
{
    $hook = add_menu_page(
        "WP Booster",
        "WP Booster",
        "update_core",
        "wpbooster-cdn-client",
        array(&$this, "admin_panel"),
        WPBOOSTER_CDN_CLIENT_URL.'/img/icon.png',
        234040
    );
    add_action('admin_print_styles-'.$hook, array(&$this, 'enqueue_style'));
    add_action('admin_print_scripts-'.$hook, array(&$this, 'enqueue_script'));
}

public function enqueue_script()
{
    wp_enqueue_script(
        'highchart',
        WPBOOSTER_CDN_CLIENT_URL.'/Highcharts-2.3.2/js/highcharts.js',
        array('jquery'),
        filemtime(WPBOOSTER_CDN_CLIENT_DIR.'/Highcharts-2.3.2/js/highcharts.js'),
        true
    );
    wp_enqueue_script(
        'wpbooster-cdn-client',
        WPBOOSTER_CDN_CLIENT_URL.'/script.js',
        array('highchart'),
        filemtime(WPBOOSTER_CDN_CLIENT_DIR.'/script.js'),
        true
    );
}

public function enqueue_style()
{
    wp_enqueue_style(
        'wpbooster-cdn-client',
        WPBOOSTER_CDN_CLIENT_URL.'/style.css',
        array(),
        filemtime(WPBOOSTER_CDN_CLIENT_DIR.'/style.css')
    );
}

public function admin_panel()
{
    $data = $this->get_data();

    echo '<div class="wrap" id="wpbooster-cdn-client">';
    echo '<h2>'.__("WP Booster CDN Client", "wpbooster-cdn-client").'</h2>';

    echo '<p class="api-key">';
    echo "API KEY: ".$this->key;
    echo '</p>';

    echo '<p class="balance">';
    echo '<span class="number">';
    echo number_format($data['point']);
    echo '</span>';
    echo ' points.';
    echo '</p>';

/*
    echo '<p class="used">';
    echo "Used points in the last 30 days: ";
    echo '</p>';
*/

    $this->add_box('History', $this->get_history(), "");
    $this->add_box(
        'Information',
        $this->get_feed(__('http://www.wpbooster.net/feed', 'wpbooster-cdn-client')),
        "half align-left"
    );

/*
    $this->add_box(
        'Support Forum',
        $this->get_feed(__('http://wordpress.org/support/rss/plugin/wpbooster-cdn-client', 'wpbooster-cdn-client')),
        "half align-right"
    );
*/

    $this->add_box(
        'Get The Point',
        $this->get_point_box(),
        "half align-right"
    );

    $footer =<<<EOL
        <div class="wpbooster-footer">
            <div class="ft-3column-left">
                <a href="http://en.digitalcube.jp/about/wordpress_consultant/" target="_blank">
                    <img src="http://cdn.wpbooster.net/7be616c30d28aa68ee31110d2da565c9/wp-content/themes/digitalcube/images/footer/wordpress_consultant_1.gif" width="225" height="95" alt="WordPress CONSULTANT" title="WordPress CONSULTANT"/>
                </a>
            </div>
            <div class="ft-3column-left">
                <a href="http://www.wpbooster.net/" target="_blank">
                    <img src="http://cdn.wpbooster.net/7be616c30d28aa68ee31110d2da565c9/wp-content/themes/digitalcube/images/footer/wp_booster.gif" width="225" height="95" alt="WP Booster" title="WP Booster"/>
                </a>
            </div>
            <div class="ft-3column-left">
                <a href="http://megumi-cloud.com/" target="_blank">
                    <img src="http://cdn.wpbooster.net/7be616c30d28aa68ee31110d2da565c9/wp-content/themes/digitalcube/images/footer/megumicloud_amimoto.gif" width="225" height="95" alt="megumi cloud" title="megumi cloud"/>
                </a>
            </div>
            <div class="ft-3column-left">
                <a href="http://wp.remotemanager.me/" target="_blank">
                    <img src="http://cdn.wpbooster.net/7be616c30d28aa68ee31110d2da565c9/wp-content/themes/digitalcube/images/footer/wp_remote_manager.gif" width="225" height="95" alt="WP remote" title="WP remote"/>
                </a>
            </div>
        </div>
EOL;

    printf(
        $footer,
        __('http://en.digitalcube.jp/about/wordpress_consultant/', 'wpbooster-cdn-client'),
        __('http://www.wpbooster.net/', 'wpbooster-cdn-client'),
        __('http://megumi-cloud.com/', 'wpbooster-cdn-client'),
        __('http://wp.remotemanager.me/', 'wpbooster-cdn-client')
    );

    echo '</div><!-- end #wpbooster-cdn-client -->';

    echo '<script type="text/javascript">';
    $categories = array();
    $transfers = array();
    $datas = array_reverse($data['data']);
    foreach ($datas as $stat) {
        $date = preg_replace("/^[0-9]+\-[0-9]+\-/", '', $stat->date);
        $categories[] = $date;
        $transfers[] = intval($stat->bytes/1024/1024);
        $requests[] = intval($stat->request);
        $used[] = intval(ceil($stat->used));
    }
    echo 'var categories = '.json_encode($categories).";\n";
    echo 'var transfers = '.json_encode($transfers).";\n";
    echo 'var requests = '.json_encode($requests).";\n";
    echo 'var used = '.json_encode($used).";\n";
    echo '</script>';
}


private function get_point_box()
{
    $html = '';
    $html .= '<p>';
    $html .= 'Megumi payment” is a service to pay for WordPress-related services provided by <a href="http://www.digitalcube.jp/">DigitalCube Co. Ltd</a>.';
    $html .= '</p>';
    $html .= '<p>';
    $html .= __('<a href="https://payment.digitalcube.jp/auth/login?language=en">Get the point in Megumi Payment</a>', 'wpbooster-cdn-client');
    $html .= '</p>';

    return $html;
}

private function get_feed($url)
{
    $feed = fetch_feed($url);
    if (!is_wp_error($feed)) {
        $maxitems = $feed->get_item_quantity($this->feed_items);
        $items = $feed->get_items(0, $maxitems);
    }

    $html = '<dl id="wpbooster-info">';
    foreach ($items as $item) {
        $html .= sprintf("<dt>%s</dt>", esc_html($item->get_date('Y/m/d H:i:s')));
        $html .= sprintf(
            '<dd><a href="%s">%s</a></dd>',
            esc_attr($item->get_permalink()),
            esc_html($item->get_title())
        );
    }
    $html .= '</dl>';
    return $html;
}

private function get_history()
{
    $html = '<div id="booster-chart">';
    $html .= '</div>';
    return $html;
}

private function add_box($title, $content, $style = null)
{
    echo sprintf('<div class="postbox %s">', $style);
    echo '<h3 class="hndle" style="padding:10px;"><span>'.esc_html($title).'</span></h3>';
    echo '<div class="inside">';
    echo $content;
    echo '</div><!-- end .inside -->';
    echo '</div><!-- end .postbox -->';
}

private function get_data()
{
    if ($data = get_transient($this->transient_key)) {
        return $data;
    }

    $get_point = sprintf($this->get_point_by_api, $this->key);
    $get_request = sprintf($this->get_requests_by_api, $this->key);

    $point = $this->remote_get($get_point);
    $request = $this->remote_get($get_request);
    if ($point && $request) {
        $data = array('point' => $point->point, 'data' => $request);
        set_transient(
            $this->transient_key,
            $data,
            3600
        );
        return $data;
    }

    return false;
}

private function remote_get($url)
{
    $res = wp_remote_get($url);
    if ($res['response']['code'] === 200) {
        return json_decode($res['body']);
    }
    return false;
}

} // end of class


// EOF
