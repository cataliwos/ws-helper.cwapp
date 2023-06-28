<?php
namespace Catali;
use TymFrontiers\HTTP\Header,
    TymFrontiers\Generic,
    TymFrontiers\Data;
require_once "../.appinit.php";

$gen = new Generic;
$params = $gen->requestParam([
  "rdt" =>["rdt", "url"]
], $_GET, []);
if (!$params) Header::badRequest(true);

$rdt = !empty($params['rdt']) ? $params['rdt'] : WHOST;
if ($session->isLoggedIn()) Header::redirect($rdt);
$url = Generic::setGet("https://app.cataliws.com/ws-login/" . get_constant("PRJ_DOMAIN"), [
  "rdt" => $rdt
]);
Header::redirect($url);