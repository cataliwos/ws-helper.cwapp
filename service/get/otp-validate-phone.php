<?php
namespace Catali;
require_once "../../.appinit.php";
use \TymFrontiers\Generic,
    \TymFrontiers\InstanceError;

\header("Content-Type: application/json");
$post = !empty($_POST) ? $_POST : $_GET;
$gen = new Generic;
$params = $gen->requestParam([
  "phone" =>["phone","tel"],
  "otp" =>["otp","username", 3, 28, [], "mixed", [" ", "-", "_", "."]]
], $post, ["otp", "phone"]);
if (!$params || !empty($gen->errors)) {
  $errors = (new InstanceError ($gen, false))->get("requestParam",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request halted"
  ]);
  exit;
}
$params['otp'] = \str_replace([" ", "-", "_", "."],"", $params["otp"]);
// send request
$rest = client_query("https://ws." . get_constant("PRJ_BASE_DOMAIN") ."/ws-service/get/otp/validate-phone", [
  "ws" => get_constant("PRJ_WSCODE"),
  "phone" => $params['phone'],
  "otp" => $params['otp']
], "POST");
if ($rest && \gettype($rest) == "object") {
  if ($rest->status !== "0.0") {
    die ( \json_encode($rest) );
  }
} else {
  echo \json_encode([
    "status" => "4.1",
    "errors" => ["Failed to validate OTP at this time, try again later."],
    "message" => "Request failed"
  ]);
  exit;
}
die( \json_encode($rest) );