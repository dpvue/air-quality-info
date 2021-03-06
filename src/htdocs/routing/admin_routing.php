<?php
namespace AirQualityInfo;

Lib\CsrfToken::verifyToken(isset($_POST['_csrf_token']) ? $_POST['_csrf_token'] : null);

$authorizedUser = null;
$userModel = $diContainer->injectClass('\\AirQualityInfo\\Model\\UserModel');
if (isset($_SESSION['user_id'])) {
    $authorizedUser = $userModel->getUserById($_SESSION['user_id']);
    if ($authorizedUser != null) {
        date_default_timezone_set($authorizedUser['timezone']);
    }
}

$routes = array(
    'GET /' => array('main', 'index'),
    'GET /map' => array('map', 'index'),
    'GET /map/data.json' => array('map', 'data'),
    'GET /about/:pageName' => array('main', 'static'),

    'GET /login' => array('user', 'login'),
    'POST /login' => array('user', 'doLogin'),
    'GET /logout' => array('user', 'logout'),
    'GET /register' => array('user', 'register'),
    'POST /register' => array('user', 'doRegister'),
    'GET /user/edit' => array('user', 'edit'),
    'POST /user/edit' => array('user', 'edit'),
    'GET /user/settings' => array('user', 'settings'),
    'POST /user/settings' => array('user', 'settings'),

    'GET /device_hierarchy/[:node_id]' => array('device_hierarchy', 'index'),
    'GET /device_hierarchy/[:node_id]/edit_dir' => array('device_hierarchy', 'editDirectory'),
    'POST /device_hierarchy/[:node_id]/edit_dir' => array('device_hierarchy', 'editDirectory'),
    'GET /device_hierarchy/[:node_id]/edit_device' => array('device_hierarchy', 'editDevice'),
    'POST /device_hierarchy/[:node_id]/edit_device' => array('device_hierarchy', 'editDevice'),
    'GET /device_hierarchy/[:node_id]/create_dir' => array('device_hierarchy', 'createDir'),
    'POST /device_hierarchy/[:node_id]/create_dir' => array('device_hierarchy', 'createDir'),
    'GET /device_hierarchy/[:node_id]/create_device' => array('device_hierarchy', 'createDevice'),
    'POST /device_hierarchy/[:node_id]/create_device' => array('device_hierarchy', 'createDevice'),
    'GET /device_hierarchy/[:node_id]/create_external_device' => array('device_hierarchy', 'createExternalDevice'),
    'POST /device_hierarchy/[:node_id]/create_external_device' => array('device_hierarchy', 'createExternalDevice'),
    'POST /device_hierarchy/[:node_id]/move' => array('device_hierarchy', 'move'),
    'DELETE /device_hierarchy/[:node_id]' => array('device_hierarchy', 'deleteNode'),

    'GET /device' => array('device', 'index'),
    'GET /device/create' => array('device', 'create'),
    'POST /device/create' => array('device', 'create'),
    'GET /device/:device_id' => array('device', 'edit'),
    'POST /device/:device_id' => array('device', 'edit'),
    'DELETE /device/:device_id' => array('device', 'deleteDevice'),
    'GET /device/:device_id/import_madavi' => array('device', 'importMadaviWrapper'),
    'POST /device/:device_id/import_madavi' => array('device', 'importMadavi'),
    'POST /device/:device_id/reset_password' => array('device', 'resetHttpPassword'),
    'POST /device/:device_id/default' => array('device', 'makeDefault'),

    'POST /device/:device_id/mapping' => array('device', 'createMapping'),
    'DELETE /device/:device_id/mapping/:mapping_id' => array('device', 'deleteMapping'),

    'GET /device/:device_id/json' => array('device_json', 'index'),
    'GET /device/:device_id/json/:timestamp' => array('device_json', 'get'),

    'GET /csv/file/[:path]' => array('csv', 'downloadFile'),
    'GET /csv/dir/[:path]' => array('csv', 'downloadDir'),
    'GET /csv/[:path]' => array('csv', 'index'),

    'GET /template/edit' => array('template', 'edit'),
    'POST /template/edit' => array('template', 'edit'),
    'GET /attachment/:name' => array('attachment', 'get'),

    'GET /widget' => array('widget', 'index'),
    'POST /widget' => array('widget', 'updateWidgetSettings'),
    'GET /widget_device/:device_id' => array('widget', 'showDeviceWidget'),
    'GET /widget/create' => array('widget', 'create'),
    'POST /widget/create' => array('widget', 'create'),
    'GET /widget/edit/:widget_id' => array('widget', 'edit'),
    'POST /widget/edit/:widget_id' => array('widget', 'edit'),
    'DELETE /widget/:widget_id' => array('widget', 'delete'),
);

$router = new Lib\Router($routes);
$uri = urldecode(explode("?", $_SERVER['REQUEST_URI'])[0]);
list($route, $args) = $router->findRoute(
    $_SERVER['REQUEST_METHOD'],
    $uri
);

if ($route === null) {
    Lib\Router::send404();
}

$currentController = $route[0];
$currentAction = $route[1];

$templateVariables = array(
    'currentController' => $currentController,
    'currentAction' => $currentAction,
    'currentLocale' => $currentLocale,
);
$diContainer->addBindings($templateVariables);
$diContainer->setBinding('templateVariables', $templateVariables);
$diContainer->setBinding('authorizedUser', $authorizedUser);

$controller = $diContainer->injectClass('\\AirQualityInfo\\Admin\\Controller\\'.Lib\StringUtils::camelize($currentController).'Controller');
$controller->beforeAction();
$controller->$currentAction(...array_values($args));

?>