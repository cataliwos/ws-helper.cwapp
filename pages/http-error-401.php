<?php
namespace Catali;
require_once "../.appinit.php";
use TymFrontiers\Generic;
$gen = new Generic;
$params = $gen->requestParam([
  "request" => ["request","text",3,0],
  "message" => ["message","text",3,0]
],'get',[]);
if (!empty($params['message'])) $params['message'] = \urldecode($params['message']);
$page_name = "401";
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>(401) Unauthorized</title>
    <?php  include APP_ROOT . "/src/inc-iconset.php"; ?>
    <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'>
    <meta name="description" content="<?php echo get_constant("PRJ_DESCRIPTION"); ?>">
    <meta name="author" content="<?php echo get_constant("PRJ_AUTHOR"); ?>">
    <meta name="creator" content="<?php echo get_constant("PRJ_CREATOR"); ?>">
    <meta name="publisher" content="<?php echo get_constant("PRJ_PUBLISHER"); ?>">
    <meta name="robots" content='nofollow'>
    <!-- Theming styles -->
    <link rel="stylesheet" href="/app/cataliwos/plugin.cwapp/css/font-awesome.min.css">
    <link rel="stylesheet" href="/app/cataliwos/plugin.cwapp/css/theme.min.css">
    <!-- Project styling -->
    <link rel="stylesheet" href="/assets/css/base.min.css">
    <link rel="stylesheet" href="/app/cataliwos/ws-helper.cwapp/css/helper.min.css">
    <link rel="stylesheet" href="/app/cataliwos/ws-helper.cwapp/css/http-errors.min.css">
  </head>
  <body>
    <?php include APP_ROOT . "/src/inc-header.php"; ?>
    <br class="c-f">
    <section id="main-content">
      <div class="view-space-mini">
        <div class="grid-7-tablet">
          <div class="sec-div error-art-mob show-phone">
            <img src="/app/cataliwos/ws-helper.cwapp/img/401.png" alt="401 Error">
          </div>
          <div class="sec-div paddn -pall -p20">
            <p class="http-error-info">
              <span class="notice">HTTP\</span>
              <span class="status-code code">401</span>
            </p>
            <h2>Unauthorized</h2>
            <p>Sorry, such request is not authorized here.</p>
            <?php if (!empty($params["message"])): ?>
              <blockquote >
                <p> <b>Server Message\</b> <br><?php echo $params["message"]; ?></p>
              </blockquote>
            <?php endif; ?>
            <p>If you are sure there is a mixup somewhere, please <a class="bold" href="#" onclick="cwos.faderBox.url('/app/cataliwos/ws-helper.cwapp/pages/report-http-error.php',{request : '<?php echo empty($params['request']) ? "" : \addslashes($params['request']); ?>', status : '401', message : '<?php echo empty($params['message']) ? "" : \addslashes($params['message']); ?>'});"> <i class="fas fa-share-square"></i> report the errror</a>, that will help us resolve it.</p>
            <p class="align-center"> <a href="/"> <i class="fas fa-arrow-left"></i> Go back to home page</a></p>
          </div>
        </div>
        <div class="grid-5-tablet hide-phone">
          <div class="sec-div error-art">
            <img src="/app/cataliwos/ws-helper.cwapp/img/401.png" alt="401 Error">
          </div>
        </div>
        <br class="c-f">

      </div>
    </section>
    <?php include APP_ROOT . "/src/inc-footer.php"; ?>
    <!-- Required scripts -->
    <script src="/app/cataliwos/plugin.cwapp/js/jquery.min.js"></script>
    <script src="/app/cataliwos/plugin.cwapp/js/functions.min.js"></script>
    <script src="/app/cataliwos/plugin.cwapp/js/class-object.min.js"></script>
    <script src="/app/cataliwos/plugin.cwapp/js/theme.min.js"></script>
    <!-- project scripts -->
    <script src="/assets/js/base.min.js" ></script>
  </body>
</html>
