<?php
namespace Catali;
use TymFrontiers\HTTP\Header,
    TymFrontiers\Generic,
    TymFrontiers\API\Authentication,
    TymFrontiers\Data;
require_once "../.appinit.php";
$gen = new Generic;
$data = new Data;
$post = $_POST;
$params = $gen->requestParam([
  "uniqueid" => ["uniqueid", "pattern", "/^252([\d]{8,12})$/"],
  "code" => ["code", "pattern", "/^252([\d]{8,12})$/"],
  "access_group" => ["access_group", "username", 3, 32],
  "access_rank" => ["access_rank", "int", 1, 0],
  "rdt" =>["rdt", "url"],
  "usr" => ["usr", "text", 5, 5120],
  "token" => ["token", "text", 5, 10240],
  "name" => ["name", "name"],
  "surname" => ["surname", "name"],
  "status" => ["status", "username", 3, 32, [], "UPPER", ["-"]],
  "avatar" => ["avatar", "url"],
  "country_code" => ["country_code", "username", 2, 2],
  "remember" => ["remember", "int"]
  ], $post, ["token", "code", "name", "surname", "country_code", "avatar"]);

if (!$params) Header::badRequest(true);
$rdt = !empty($params['rdt']) ? $params['rdt'] : WHOST;
$token_code = $params['token'];
unset($params['token']);
if ($session->isLoggedIn()) Header::redirect($rdt);
// validate token
$token = api_token_decode(\html_entity_decode(\trim($token_code)), $params);
if (!$token) Header::badRequest(true);
$conn = \query_conn();
$auth = new Authentication ((!empty($api_sign_patterns) ? $api_sign_patterns : []), "", 0, false, $conn, $token);
$http_auth = $auth->validApp ();
if (!$http_auth) {
  Header::unauthorized (true,'', Generic::authErrors ($auth,"Request [Auth-App]: Authetication failed.",'self',true));
}
if (!$params['remember']) $params['remember'] = \strtotime("+1 Hour");
$session->login((object)[
  "code" => $params["code"],
  "name" => $params["name"],
  "surname" => $params["surname"],
  "status" => $params["status"],
  "avatar" => $params["avatar"],
  "country_code" => $params["country_code"],
  "remember" => $params["remember"],
  "uniqueid" => $params["uniqueid"],
  "access_group" => "USER",
  "access_rank" => 1
], $params['remember']);
if (!empty($params['usr']) && $usr = $data->decodeDecrypt(\trim(\html_entity_decode($params['usr'])))) {
  $db_name = \get_database("base");
  $conn->query("UPDATE `{$db_name}`.`shopping_cart` SET `user` = '{$conn->escapeValue($session->name)}' WHERE `user` = '{$usr}'");
}
Header::redirect($params['rdt']);