<?php

function create_log_request($title, $description, $json)
{
    try {
        $url = "https://api.tipzty.com/api/logs";
        $args = array(
            "method" => "POST",
            "body" => json_encode(array(
                "title"    => $title,
                "description" => $description,
                "json" => $json,
            )),
            "headers" => array(
                "Content-Type" => "application/json",
            ),
        );
        wp_remote_request($url, $args);
    } catch (\Throwable $th) {
    }
}

function check_brand_connect_request($id)
{
    $url = "https://go.tipzty.com/connectshops/brand/$id";
    $args = array(
        "method" => "GET",
        "headers" => [
            "x-tipzty-api-key" => "mMlxBfBK6TRqoW8DTLdrdhZxIYJuL81a"
        ],
    );
    $response = wp_remote_request($url, $args);
    $body = json_decode(wp_remote_retrieve_body($response));

    create_log_request("Wordpress-Plugin", "check_brand_connect_request", [
        'response' => $response,
    ]);

    return $response['response']['code'] === 200 && count($body) > 0;
}

function create_brand_connect_request($id, $ck, $cs)
{
    $url = "https://go.tipzty.com/connectshops";
    $args = array(
        "method" => "POST",
        "body" => json_encode(array(
            "consumer_key"    => $ck,
            "consumer_secret" => $cs,
            "brands_profile_id" => intval($id),
            "shopProvider_id" => 3,
            "status" => true,
        )),
        "headers" => array(
            "Content-Type" => "application/json",
            "x-tipzty-api-key" => "mMlxBfBK6TRqoW8DTLdrdhZxIYJuL81a"
        ),
    );
    $response = wp_remote_request($url, $args);

    create_log_request("Wordpress-Plugin", "create_brand_connect_request", [
        'response' => $response,
    ]);

    return $response['response']['code'] === 201;
}

function update_brand_active_sync_request($id)
{
    $url = "https://go.tipzty.com/brandsprofiles/active_sync/$id";
    $args = array(
        "method" => "PUT",
        "body" => json_encode(array(
            "active_sync" => true,
        )),
        "headers" => array(
            "Content-Type" => "application/json",
            "x-tipzty-api-key" => "mMlxBfBK6TRqoW8DTLdrdhZxIYJuL81a"
        ),
    );
    $response = wp_remote_request($url, $args);

    create_log_request("Wordpress-Plugin", "update_brand_active_sync_request", [
        'response' => $response,
    ]);

    return $response['response']['code'] === 200;
}

function create_brand_sync_request($id)
{
    $url = "https://go.tipzty.com/synchronizations";
    $args = array(
        "method" => "POST",
        "body" => json_encode(array(
            "brands_profile_id" => intval($id),
            "from" => "Plugin Wordpress",
            "new_products" => 0,
            "provider" => "woocommerce",
            "page" => 1,
            "per_page" => 100,
        )),
        "headers" => array(
            "Content-Type" => "application/json",
            "x-tipzty-api-key" => "mMlxBfBK6TRqoW8DTLdrdhZxIYJuL81a"
        ),
    );
    $response = wp_remote_request($url, $args);

    create_log_request("Wordpress-Plugin", "create_brand_sync_request", [
        'response' => $response,
    ]);

    return $response['response']['code'] === 201;
}

global $wpdb;

$table = $wpdb->prefix . "options";

if (isset($_POST['reload'])) {
    header("Refresh:0");
}

if (isset($_POST['logout']) || isset($_GET['logout'])) {
    $results = $wpdb->delete($table, array('option_name' => "xTipztyToken"));
    $results = $wpdb->delete($table, array('option_name' => "xTipztyId"));

    if ($results) {
        header("Refresh:0");
    } else {
        echo "No se pudo cerrar sesión, por favor pulse <b>F5</b> para restaurar la página.";
    }
}

if (isset($_POST['connect'])) {
    $consumer_key = $_POST['consumer_key'];
    $consumer_secret = $_POST['consumer_secret'];
    $query_id = "SELECT option_value FROM `$table` WHERE option_name = 'xTipztyId'";
    $results_id = $wpdb->get_results($query_id, ARRAY_A);
    $brand_id = $results_id[0]['option_value'];

    if (isset($results_id) && isset($consumer_key) && isset($consumer_secret)) {
        $result_ok = create_brand_connect_request($brand_id, $consumer_key, $consumer_secret);

        if ($result_ok) {
            $result_ok_update = update_brand_active_sync_request($brand_id);
            $result_ok_sync = create_brand_sync_request($brand_id);

            create_log_request("Wordpress-Plugin", "dashboard.php:134 - update and sync responses", [
                'result_ok_update' => $result_ok_update,
                'result_ok_sync' => $result_ok_sync,
            ]);

            if ($result_ok_update && $result_ok_sync) {
                header("Refresh:0");
            } else {
                echo "Hubo un error al actualizar los datos de su tienda, por favor pulse <b>F5</b> para recargar la página.";
            }
        }
    }
}

?>
<div class="t-wrap">
    <h1 class="t-h1">Tipzty</h1>
    <br>
    <div class="t-card t-w-100 t-p-0 t-m-auto">
        <div class="t-card-header">
            Vincula tu tienda con Tipzty
        </div>
        <div class="t-card-body">
            <?php
            $email = sanitize_email($_POST["email"]);
            $password = $_POST["password"];

            if (
                isset($email)
                && !empty($email)
                && isset($password)
                && !empty($password)
            ) {
                $args = array(
                    "method" => "POST",
                    "body" => json_encode(array(
                        "email"    => $email,
                        "password" => $password
                    )),
                    "headers" => array(
                        "Content-Type" => "application/json",
                        "x-tipzty-api-key" => "mMlxBfBK6TRqoW8DTLdrdhZxIYJuL81a"
                    ),
                );
                $url = "https://go.tipzty.com/api/login";
                $response = wp_remote_request($url, $args);
                $body = json_decode(wp_remote_retrieve_body($response));

                if ($body && property_exists($body, "token") && isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME'])) {
                    $origin_domain = $_SERVER['SERVER_NAME'];
                    $domain = str_replace("http://", "", $body->host);
                    $domain = str_replace("https://", "", $domain);
                    $domain = str_replace("www.", "", $domain);
                    $same_domain = true;
                    // $same_domain = $domain == $origin_domain;

                    create_log_request("Wordpress-Plugin", "dashboard.php:175 - domain validation", [
                        'origin_domain' => $origin_domain,
                        'domain' => $domain,
                    ]);

                    if ($same_domain) {
                        $data = [
                            'option_name' => "xTipztyToken",
                            'option_value' => $body->token,
                            'autoload' => true
                        ];
                        $data_id = [
                            'option_name' => "xTipztyId",
                            'option_value' => $body->id,
                            'autoload' => true
                        ];

                        $query = "SELECT option_value FROM `$table` WHERE option_name = 'xTipztyToken'";
                        $val_token = $wpdb->get_results($query, ARRAY_A);
                        $response_wp = true;

                        if (empty($val_token)) {
                            $response_wp = $wpdb->insert($table, $data);
                            $response_wp = $wpdb->insert($table, $data_id);
                        } else {
                            $response_wp = $wpdb->update($table, $data, array('option_name' => "xTipztyToken"));
                            $response_wp = $wpdb->update($table, $data_id, array('option_name' => "xTipztyId"));
                        }

                        create_log_request("Wordpress-Plugin", "dashboard.php:203 - token registration", [
                            'val_token' => $val_token,
                            'response'  => $response_wp,
                            'data'      => $data,
                            'data_id'   => $data_id,
                            'wpdb'      => !!$wpdb,
                            'table'     => $table,
                        ]);

                        if ($response_wp) {
                            header("Refresh:0");
                        } else {
            ?>
                            <h4 class="t-h4">No se pudo loguear el usuario. Por favor vuelve a intentarlo</h4>
                            <form id="formReloadTipzty" method="POST">
                                <button name="reload" type="submit" class="t-btn t-btn-0 t-mt-3">Volver</button>
                            </form>

                        <?php
                        }
                    } else {
                        ?>
                        <h2 class="t-h2">Dominio diferente al registrado: <?= esc_html($domain) ?></h2>
                        <form id="formReloadTipzty" method="POST">
                            <button name="reload" type="submit" class="t-btn t-btn-0 t-mt-3">Volver</button>
                        </form>

                    <?php
                    }
                } else {
                    ?>
                    <h4 class="t-h4">Usuario y contraseña incorrectos. Por favor vuelve a intentarlo</h4>
                    <form id="formReloadTipzty" method="POST">
                        <button name="reload" type="submit" class="t-btn t-btn-0 t-mt-3">Volver</button>
                    </form>
                    <?php
                }
            } else {
                if (class_exists('WooCommerce')) {
                    $query = "SELECT option_value FROM `$table` WHERE option_name = 'xTipztyToken'";
                    $query_id = "SELECT option_value FROM `$table` WHERE option_name = 'xTipztyId'";
                    $results = $wpdb->get_results($query, ARRAY_A);
                    $results_id = $wpdb->get_results($query_id, ARRAY_A);
                    $brand_id = $results_id[0]['option_value'];
                    $have_connect = check_brand_connect_request($brand_id);

                    if (empty($results)) {
                        $results = array();
                    ?>
                        <form id="formLoginTipzty" method="POST">
                            <div class="t-row">
                                <div class="t-col">
                                    <label for="email" class="t-label t-form-label">Usuario</label>
                                    <input type="text" name="email" class="t-form-control" id="email" placeholder="Ingresa tu usuario (Email)">
                                </div>
                                <div class="t-col">
                                    <label for="password" class="t-label t-form-label">Contraseña</label>
                                    <input type="password" class="t-form-control" name="password" id="password" placeholder="Ingresa tu contraseña">
                                </div>
                                <div class="t-col-12 t-mt-3">
                                    <button type="submit" class="t-btn t-btn-0">Ingresar</button>
                                </div>
                            </div>
                        </form>
                    <?php
                    } else if (!empty($results) && !$have_connect) {
                    ?>
                        <h4 class="t-h4">¡Estás a un paso de empezar en el mundo del Video-Commerce! Ahora sólo tienes que ingresar las credenciales de tu sitio.</h4>
                        <form id="formConnectTipzty" method="POST">
                            <div class="t-row">
                                <div class="t-col">
                                    <label for="consumer_key" class="t-label t-form-label">Consumer Key</label>
                                    <input type="text" name="consumer_key" class="t-form-control" id="ck" placeholder="Copia y pega aquí el Consumer Key">
                                </div>
                                <div class="t-col">
                                    <label for="consumer_secret" class="t-label t-form-label">Consumer Secret</label>
                                    <input type="text" class="t-form-control" name="consumer_secret" id="cs" placeholder="Copia y pega aquí el Consumer Secret">
                                </div>
                            </div>
                            <p class="t-mt-2">Si no sabes cómo encontrar estas credenciales, puedes ver un tutorial <a href="#/" target="_blank">aquí</a></p>
                            <button name="connect" type="submit" class="t-btn t-btn-0 t-mt-3">Registrar Credenciales</button>
                        </form>
                    <?php
                    } else {
                    ?>
                        <h4 class="t-h4">Usuario logueado correctamente</h4>
                        <form id="formLogoutTipzty" method="POST">
                            <button name="logout" type="submit" class="t-btn t-btn-0 t-mt-3">Cerrar sesión</button>
                        </form>
            <?php
                    }
                } else {
                    echo "<h4 class='t-h4'>Debes tener instalado <b>Woocommerce</b> para utilizar el plugin.</h4>";
                }
            }
            ?>
        </div>
    </div>
</div>