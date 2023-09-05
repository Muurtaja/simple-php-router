<?php
include "routes.php";
foreach ($routes as $route => $action) {
    Router::{$action[0]}($route, $action[1]);
}
Router::notFound();
