<?php
namespace TymFrontiers;
require_once "../.appinit.php";
\header("Content-Type: application/json");

$post = $_POST;
$gen = new Generic;
$rqp = [
  "name" => ["name","text",3,72],
  "email" => ["email","email"],
  "status" => ["status","username", 3, 14],
  "request" => ["request","text",3,0],
  "message" => ["message","text",3,0],
  "detail" => ["detail","text",25,1020],
  "form" => ["form","text",2,72],
  "CSRF_token" => ["CSRF_token","text",5,1024]
];

$params = $gen->requestParam($rqp, $post, ["name", "email", "detail", "form", "CSRF_token"]);
if (!$params || !empty($gen->errors)) {
  $errors = (new InstanceError ($gen, false))->get("requestParam",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request halted"
  ]);
  exit;
}

if ( !$gen->checkCSRF($params["form"],$params["CSRF_token"]) ) {
  $errors = (new InstanceError ($gen, false))->get("checkCSRF",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request halted."
  ]);
  exit;
}
$params['user'] = $session->name;
$bug = new MultiForm(\get_database("base"), "http_errors", "id");
foreach ($params as $key => $value) {
  if (!empty($value)) $bug->$key = $value;
}
if (!$bug->create()) {
  echo \json_encode([
    "status" => "4.1",
    "errors" => ["Sorry we could not send your report at this time, please try again later."],
    "message" => "Request halted."
  ]);
  exit;
}
echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Thank you for your kindness, your report have been received and will be attended to."
]);
exit;
