<?php
namespace Catali;
use TymFrontiers\HTTP\Header,
    TymFrontiers\Generic,
    TymFrontiers\API\Authentication,
    TymFrontiers\Data;
require_once "../.appinit.php";
$gen = new Generic;
$params = $gen->requestParam([
  "rdt" =>["rdt", "url"],
  "token" => ["token", "text", 5, 5120],
  "code" => ["code", "pattern", "/^252([\d]{8,12})$/"],
  "name" => ["name", "name"],
  "surname" => ["surname", "name"],
  "status" => ["status", "username", 3, 32, [], "UPPER", ["-"]],
  "avatar" => ["avatar", "url"],
  "country_code" => ["country_code", "username", 2, 2],
  "remember" => ["remember", "int"]
], $_POST, ["token", "code", "name", "surname", "country_code", "avatar"]);
if (!$params) Header::badRequest(true);
$rdt = !empty($params['rdt']) ? $params['rdt'] : WHOST;
$params['uniqueid'] = $params['code'];
if ($session->isLoggedIn()) Header::redirect($rdt);
// validate token
$token = api_token_decode(\html_entity_decode($params['token']));
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
Header::redirect($params['rdt']);