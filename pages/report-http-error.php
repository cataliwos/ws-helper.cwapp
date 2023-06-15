<?php
namespace TymFrontiers;
require_once "../.appinit.php";

$errors = [];
$gen = new Generic;
$pre_params = [
  "status" => ["status","text",3,14],
  "request" => ["request","text",3,0],
  "message" => ["message","text",3,0]
];
$params = $gen->requestParam($pre_params,$_GET,[]);
if (!$params || !empty($gen->errors)) {
  $errs = (new InstanceError ($gen, false))->get("requestParam",true);
  foreach ($errs as $er) {
    $errors[] = $er;
  }
}
?>
<script type="text/javascript">
  if (typeof window["param"] == undefined || !window["param"]) window["param"] = {};
  <?php
    foreach ($params as $k=>$val) {
      echo "param['{$k}'] = '{$val}';";
    }
   ?>
</script>
<div id="fader-flow">
  <div class="view-space">
    <div class="padding -p20">&nbsp;</div>
    <br class="c-f">
    <div class="grid-9-tablet grid-7-desktop center-tablet">
      <div class="sec-div theme-color blue bg-white drop-shadow">
        <header class="paddn -pall -p20 color-bg">
          <h1> <i class="fas fa-exclamation-circle"></i> Report error</h1>
        </header>

        <div class="paddn -pall -p20">
          <?php if(!empty($errors)){ ?>
            <h3>Unresolved error(s)</h3>
            <ol>
              <?php foreach($errors as $err){
                echo " <li>{$err}</li>";
              } ?>
            </ol>
          <?php }else{ ?>
            <form data-theme="block-ui"
            id="http-bug-report-form"
            class="block-ui"
            method="post"
            action="/app/cataliwos/ws-helper.cwapp/src/ReportHTTPError.php"
            data-validate="false"
            onsubmit="cwos.form.submit(this, sendErReport);return false;"
            >
            <input type="hidden" name="status" value="<?php echo empty($params['status']) ? "" : \addslashes($params['status']); ?>">
            <input type="hidden" name="message" value="<?php echo empty($params['message']) ? "" : \addslashes($params['message']); ?>">
            <input type="hidden" name="request" value="<?php echo empty($params['request']) ? "" : \addslashes($params['request']); ?>">
            <input type="hidden" name="form" value="http-bug-report-form">
            <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("http-bug-report-form");?>">

            <div class="grid-12-tablet">
              <p>Please provide us with enough information about this mixup so that we can quickly resolve it.</p>
            </div>
            <div class="grid-8-tablet">
              <label for="name"> <i class="fas fa-user"></i> Your full name </label>
              <input type="text" placeholder="Name Surname" name="name" autocomplete="given-name" id="name" required>
            </div>
            <div class="grid-7-tablet">
              <label for="email"> <i class="fas fa-envelope"></i> Your email </label>
              <input type="email" placeholder="your-email@domain.com" name="email" autocomplete="email" id="email" required>
            </div>
            <div class="grid-12-tablet">
              <label for="detail"> <i class="fas fa-info-circle"></i> Detailed information <i class="fas fa-asterisk fa-border fa-sm rq-tag"></i></label>
              <textarea id="detail" placeholder="What were you trying to do before you encountered this error?" maxlength="1024" minlength="25" name="detail" class="autosize" required></textarea>
            </div>
            <div class="grid-5-phone grid-4-tablet">
              <button type="submit" class="theme-button blue"> <i class="fas fa-paper-plane"></i> Submit </button>
            </div>

            <br class="c-f">
          </form>
        <?php } ?>
      </div>
    </div>
  </div>
  <br class="c-f">
</div>
</div>

<script type="text/javascript">
  function sendErReport (resp) {
    if( resp && objectLength(resp.errors) < 1 ){
      setTimeout(function(){
        cwos.faderBox.close();
        removeAlert();
      },1500);
    }
  }
  (function(){
    $("textarea.autosize").autosize();
  })();
</script>
