<?php
namespace Catali;
use TymFrontiers\HTTP\Header,
    TymFrontiers\Generic,
    TymFrontiers\Data;
require_once "../.appinit.php";

if ($session->isLoggedIn()) {
  // remove cookie
  \setcookie("_wscartusr", FALSE, [
    'expires' => \time() - 3600, 
    'path' => '/', 
    'domain' => get_constant("PRJ_DOMAIN"),
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
  ]);
  if (isset($_COOKIE["_wscartusr"])) unset($_COOKIE["_wscartusr"]);
  $session->logout();
}
Header::redirect("/");