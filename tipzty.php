<?php

/**
 * @package Tipzty
 *
 * Plugin Name: Tipzty
 * Plugin URI: https://tipzty.com
 * Description: Sincroniza las reseñas de tus productos con <strong>tipzty</strong>.
 * Version: 1.10.0
 * Author: Tipzty Inc.
 * Author URI: https://www.linkedin.com/company/tipzty/about
 * Text Domain: Tipzty
 */

if (!function_exists("tipzty_activate")) {
  function tipzty_activate()
  {
  }
}

if (!function_exists("tipzty_deactivate")) {
  function tipzty_deactivate()
  {
    flush_rewrite_rules();
  }
}

register_activation_hook(__FILE__, 'tipzty_activate');
register_deactivation_hook(__FILE__, 'tipzty_deactivate');

if (!function_exists("tipzty_create_menu")) {
  function tipzty_create_menu()
  {
    add_menu_page(
      'Tipzty',
      'Tipzty',
      'manage_options',
      plugin_dir_path(__FILE__) . 'admin/dashboard.php',
      null,
      "https://tipzty-cdn.onrender.com/images/floaticon.svg",
      null
    );
  }
}

if (!function_exists("tipzty_admin_enqueue_scripts")) {
  function tipzty_admin_enqueue_scripts()
  {
    wp_enqueue_style('event_css', 'https://tipzty-cdn.onrender.com/plugin/widget-1.0.0.css');
  }
}

if (!function_exists("tipzty_client_enqueue_scripts")) {
  function tipzty_client_enqueue_scripts()
  {
    wp_enqueue_style('event_css', 'https://tipzty-cdn.onrender.com/plugin/widget-1.0.0.css');
    wp_enqueue_script('client_js', plugins_url('client/js/main.js', __FILE__), array('jquery'));
    wp_enqueue_script('widget_call_js', 'https://tipzty-cdn.onrender.com/plugin/widget-call.js', array('jquery', 'client_js'));
  }
}

if (!function_exists("tipzty_get_token")) {
  function tipzty_get_token()
  {
    global $wpdb;

    $token = '';
    $table = $wpdb->prefix . "options";
    $query = "SELECT option_value FROM `$table` WHERE option_name = 'xTipztyToken'";
    $result = $wpdb->get_results($query, ARRAY_A);

    if (!empty($result)) {
      $token = $result[0]['option_value'];
    }

    return $token;
  }
}

if (!function_exists("tipzty_add_token_info")) {
  function tipzty_add_token_info()
  {
    $token = tipzty_get_token();

    echo "
      <script>
        localStorage.setItem('@token_tipzty_plugin', '" . $token . "');
      </script>
    ";
  }
}

if (!function_exists("tipzty_add_product_widget")) {
  function tipzty_add_product_widget()
  {
    global $product;

    echo "
      <script>
        localStorage.setItem('@product', JSON.stringify(" . $product . "));
      </script>
    ";
?>
    <div class="t-widget">
      <div class="t-block-preview t-right-16">
        <div class="t-block-preview-close-icon" onclick="tToggleButtonPlay()">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="#2A3D44" width="20" height="20">
            <path d="M0 256C0 114.6 114.6 0 256 0C397.4 0 512 114.6 512 256C512 397.4 397.4 512 256 512C114.6 512 0 397.4 0 256zM175 208.1L222.1 255.1L175 303C165.7 312.4 165.7 327.6 175 336.1C184.4 346.3 199.6 346.3 208.1 336.1L255.1 289.9L303 336.1C312.4 346.3 327.6 346.3 336.1 336.1C346.3 327.6 346.3 312.4 336.1 303L289.9 255.1L336.1 208.1C346.3 199.6 346.3 184.4 336.1 175C327.6 165.7 312.4 165.7 303 175L255.1 222.1L208.1 175C199.6 165.7 184.4 165.7 175 175C165.7 184.4 165.7 199.6 175 208.1V208.1z" />
          </svg>
        </div>
        <video playsinline class="t-video" src="" width="100" height="100" onclick="tToggleWidget()" loop autoplay muted preload></video>
        <p id="video-text" onclick="tToggleWidget()">Video 👀</p>
      </div>
      <div class="t-review t-right-16">
        <div class="t-overlay">
          <svg class="t-overlay-close" id="Group_34983" data-name="Group 34983" xmlns="http://www.w3.org/2000/svg" width="12.031" height="12.03" viewBox="0 0 12.031 12.03" fill="#fff">
            <path id="color" d="M.294.294A1,1,0,0,0,.21,1.617l.083.094L5.306,6.724a1,1,0,0,0,1.323.083l.094-.083,5.013-5.013A1,1,0,0,0,10.413.21l-.094.083-4.3,4.3-4.3-4.3A1,1,0,0,0,.388.21Z" transform="translate(12.031 0) rotate(90)" />
            <path id="color-2" data-name="color" d="M.294,6.724A1,1,0,0,1,.21,5.4l.083-.094L5.306.294A1,1,0,0,1,6.63.21l.094.083,5.013,5.013a1,1,0,0,1-1.323,1.5l-.094-.083-4.3-4.3-4.3,4.3a1,1,0,0,1-1.323.083Z" transform="translate(7.018 0) rotate(90)" />
          </svg>
        </div>
        <iframe class="t-iframe" src="" frameborder="0" allowfullscreen></iframe>
      </div>
      <div class="t-block-play t-right-16" onclick="tToggleButtonPlay()">
        <svg xmlns="http://www.w3.org/2000/svg" width="67.335" height="67.335" viewBox="0 0 67.335 67.335">
          <g data-name="Grupo 68990">
            <g data-name="Grupo 68989">
              <path data-name="Trazado 62574" d="m170.667 147.634 20.2-15.15-20.2-15.15z" transform="translate(-143.733 -98.816)" style="fill: #fff" />
              <path data-name="Trazado 62575" d="M33.668 0a33.668 33.668 0 1 0 33.667 33.668A33.658 33.658 0 0 0 33.668 0zm0 60.6A26.934 26.934 0 1 1 60.6 33.668 26.97 26.97 0 0 1 33.668 60.6z" style="fill: #fff" />
            </g>
          </g>
        </svg>
      </div>
    </div>
  <?php
    wp_enqueue_script('product_js', plugins_url('client/js/product.js', __FILE__), array('jquery'));
  }
}

if (!function_exists("tipzty_add_home_widget")) {
  function tipzty_add_home_widget()
  {
  ?>
    <div class="t-home-widget">
      <div class="t-block-preview t-right-16">
        <div class="t-block-preview-close-icon" onclick="tToggleButtonPlay()">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="#2A3D44" width="20" height="20">
            <path d="M0 256C0 114.6 114.6 0 256 0C397.4 0 512 114.6 512 256C512 397.4 397.4 512 256 512C114.6 512 0 397.4 0 256zM175 208.1L222.1 255.1L175 303C165.7 312.4 165.7 327.6 175 336.1C184.4 346.3 199.6 346.3 208.1 336.1L255.1 289.9L303 336.1C312.4 346.3 327.6 346.3 336.1 336.1C346.3 327.6 346.3 312.4 336.1 303L289.9 255.1L336.1 208.1C346.3 199.6 346.3 184.4 336.1 175C327.6 165.7 312.4 165.7 303 175L255.1 222.1L208.1 175C199.6 165.7 184.4 165.7 175 175C165.7 184.4 165.7 199.6 175 208.1V208.1z" />
          </svg>
        </div>
        <video playsinline class="t-video" src="" width="100" height="100" onclick="tToggleWidget()" loop autoplay muted preload></video>
        <p id="video-text" onclick="tToggleWidget()">Video 👀</p>
      </div>
      <div class="t-review t-right-16">
        <div class="t-overlay">
          <svg class="t-overlay-close" id="Group_34983" data-name="Group 34983" xmlns="http://www.w3.org/2000/svg" width="12.031" height="12.03" viewBox="0 0 12.031 12.03" fill="#fff">
            <path id="color" d="M.294.294A1,1,0,0,0,.21,1.617l.083.094L5.306,6.724a1,1,0,0,0,1.323.083l.094-.083,5.013-5.013A1,1,0,0,0,10.413.21l-.094.083-4.3,4.3-4.3-4.3A1,1,0,0,0,.388.21Z" transform="translate(12.031 0) rotate(90)" />
            <path id="color-2" data-name="color" d="M.294,6.724A1,1,0,0,1,.21,5.4l.083-.094L5.306.294A1,1,0,0,1,6.63.21l.094.083,5.013,5.013a1,1,0,0,1-1.323,1.5l-.094-.083-4.3-4.3-4.3,4.3a1,1,0,0,1-1.323.083Z" transform="translate(7.018 0) rotate(90)" />
          </svg>
        </div>
        <iframe class="t-iframe" src="" frameborder="0" allowfullscreen></iframe>
      </div>
      <div class="t-block-play t-right-16" onclick="tToggleButtonPlay()">
        <svg xmlns="http://www.w3.org/2000/svg" width="67.335" height="67.335" viewBox="0 0 67.335 67.335">
          <g data-name="Grupo 68990">
            <g data-name="Grupo 68989">
              <path data-name="Trazado 62574" d="m170.667 147.634 20.2-15.15-20.2-15.15z" transform="translate(-143.733 -98.816)" style="fill: #fff" />
              <path data-name="Trazado 62575" d="M33.668 0a33.668 33.668 0 1 0 33.667 33.668A33.658 33.658 0 0 0 33.668 0zm0 60.6A26.934 26.934 0 1 1 60.6 33.668 26.97 26.97 0 0 1 33.668 60.6z" style="fill: #fff" />
            </g>
          </g>
        </svg>
      </div>
    </div>
    <?php
    wp_enqueue_script('home_js', plugins_url('client/js/home.js', __FILE__), array('jquery'));
  }
}

if (!function_exists("tipzty_wc_multiple_products_to_cart")) {
  function tipzty_wc_multiple_products_to_cart()
  {
    $query_ids = $_REQUEST['t-add-to-cart'];

    if (!class_exists('WC_Form_Handler') || empty($query_ids) || $query_ids === "" || false === strpos($query_ids, ',')) {
      return;
    }

    WC()->cart->empty_cart();

    $products = explode(',', $query_ids);

    foreach ($products as $product) {
      $parts = explode(":", $product);
      $product_id = $parts[0];
      $quantity = $parts[1];
      $variation_id = null;

      if (count($parts) > 2) {
        $variation_id = $parts[2];
      }

      $product_cart_id = WC()->cart->generate_cart_id($product_id);

      if (!WC()->cart->find_product_in_cart($product_cart_id)) {
        WC()->cart->add_to_cart($product_id, $quantity, $variation_id);
      }
    }
  }
}

if (!function_exists('tipzty_page_shortcode_reviews')) {
  function tipzty_page_shortcode_reviews($atts)
  {
    $a = shortcode_atts(array(
      'mode' => 'normal',
    ), $atts);
    $token = tipzty_get_token();
    $args = array(
      "method" => "GET",
      "headers" => array(
        "x-tipzty-key" => $token
      ),
    );
    $url = "https://go.tipzty.com/api/brand";
    $response = wp_remote_request($url, $args);
    $iframe_src = "https://tipz.tv";

    if ($response["response"]["code"] == 200) {
      $body = json_decode(wp_remote_retrieve_body($response));

      if (property_exists($body, "full_name")) {
        $full_name = $body->full_name;
        $full_name = strtolower($full_name);
        $full_name = str_replace(" ", "", $full_name);

        $iframe_src = "https://tipz.tv/reviews/$full_name?mode=widget&from=page";
      }
    }

    if ($a['mode'] == 'expanded') {
    ?>
      <iframe class="t-shortcode-iframe-reviews t-expanded" src="<?= $iframe_src ?>" frameborder="0"></iframe>
    <?php
    } else if ($a['mode'] == 'fullscreen') {
    ?>
      <iframe class="t-shortcode-iframe-reviews t-fullscreen" src="<?= $iframe_src ?>" frameborder="0" allowfullscreen></iframe>
    <?php
    } else {
    ?>
      <iframe class="t-shortcode-iframe-reviews" src="<?= $iframe_src ?>" frameborder="0"></iframe>
<?php
    }
  }
}

add_action('wp_loaded', 'tipzty_wc_multiple_products_to_cart', 15);
add_action('wp_footer', 'tipzty_add_token_info', 15);
add_action('wp_footer', 'tipzty_add_home_widget', 15);
add_action('wp_footer', 'tipzty_add_product_widget', 15);
add_filter('wc_add_to_cart_message_html', '__return_false');
add_action('admin_menu', 'tipzty_create_menu');

add_shortcode('tipzty_page_reviews', 'tipzty_page_shortcode_reviews');

// Admin scripts
add_action('admin_enqueue_scripts', 'tipzty_admin_enqueue_scripts');

// Client scripts
add_action('wp_enqueue_scripts', 'tipzty_client_enqueue_scripts');
