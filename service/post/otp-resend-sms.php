<?php
namespace Catali;
require_once "../../.appinit.php";
use \TymFrontiers\Generic,
    \TymFrontiers\InstanceError;

\header("Content-Type: application/json");
$post = !empty($_POST) ? $_POST : $_GET;
$gen = new Generic;
$params = $gen->requestParam([
  "reference" =>["reference","text", 3, 0]
], $post, ["reference"]);
if (!$params || !empty($gen->errors)) {
  $errors = (new InstanceError ($gen, false))->get("requestParam",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request halted"
  ]);
  exit;
}
// send request
$rest = client_query("https://ws." . get_constant("PRJ_BASE_DOMAIN") ."/ws-service/post/otp/resend-sms", [
  "ws" => get_constant("PRJ_WSCODE"),
  "reference" => $params['reference']
], "POST");
if ($rest && \gettype($rest) == "object") {
  if ($rest->status !== "0.0") {
    die ( \json_encode($rest) );
  }
} else {
  echo \json_encode([
    "status" => "4.1",
    "errors" => ["Failed to resend OTP at this time, try again later."],
    "message" => "Request failed"
  ]);
  exit;
}
die( \json_encode($rest) );