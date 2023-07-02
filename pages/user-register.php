<?php
namespace Catali;
use TymFrontiers\HTTP\Header,
    TymFrontiers\Generic,
    TymFrontiers\Validator,
    TymFrontiers\Data;
require_once "../.appinit.php";

$gen = new Generic;
$data = new Data;
$params = $gen->requestParam([
  "rdt" =>["rdt", "url"]
], $_GET, []);
if (!$params) Header::badRequest(true);

$rdt = !empty($params['rdt']) ? $params['rdt'] : WHOST;
if ($session->isLoggedIn()) Header::redirect($rdt);
if (!empty($_COOKIE["_wscartusr"]) && $ck_user = $data->decodeDecrypt($_COOKIE["_wscartusr"])) {
  $ck_user = (new Validator)->pattern($ck_user, ["user", "pattern", "/^USER([\d]{5,})$/"]) ? $data->encodeEncrypt($ck_user) : "";
} else {
  $ck_user = "";
}
$url = Generic::setGet("https://app.cataliws.com/ws-login/" . get_constant("PRJ_DOMAIN"), [
  "rdt" => $rdt,
  "usr" => $ck_user
]);
$url = Generic::setGet("https://app.cataliws.com/user/sign-up", [
  "rdt" => $url
]);
Header::redirect($url);