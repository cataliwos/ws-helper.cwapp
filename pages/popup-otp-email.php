<?php
namespace Catali;
require_once "../.appinit.php";
use TymFrontiers\Generic,
    TymFrontiers\Data,
    TymFrontiers\InstanceError;
use TymFrontiers\MySQLDatabase;

$post = empty($_POST) ? $_GET : $_POST;
$errors = [];
$gen = new Generic;
$params = $gen->requestParam([
  "email" => ["email","email"],
  "name" => ["name","name"],
  "surname" => ["surname","name"],
  "MUST_EXIST" =>["MUST_EXIST","boolean"],
  "MUST_NOT_EXIST" => ["MUST_NOT_EXIST","boolean"],
  "cb" => ["callback","username",3,35,[],'MIXED', ["_","."]],
  "code_variant" =>["code_variant","option",[
    Data::RAND_MIXED,
    Data::RAND_NUMBERS,
    Data::RAND_LOWERCASE,
    Data::RAND_UPPERCASE,
    Data::RAND_MIXED_LOWER,
    Data::RAND_MIXED_UPPER,
    ]],
  "code_length" =>["code_length","int", 8, 16],
  "theme" =>["theme", "username", 2, 28, [], "LOWER", ["-"]]
], $post, ["email",'cb']);
if (!$params || !empty($gen->errors)) {
  $errs = (new InstanceError ($gen, false))->get("requestParam",true);
  foreach ($errs as $er) {
    $errors[] = $er;
  }
}
$otp_ref = null;
if ($params && !empty($params['email'])) {
  // make request
  $rest = client_query("https://ws." . get_constant("PRJ_BASE_DOMAIN") ."/ws-service/post/otp/send-email", [
    "ws" => get_constant("PRJ_WSCODE"),
    "MUST_EXIST" => (bool)$params["MUST_EXIST"],
    "MUST_NOT_EXIST" => (bool)$params["MUST_NOT_EXIST"],
    "email" => $params['email'],
    "name" => $params['name'],
    "surname" => $params['surname'],
    "code_variant" => $params['code_variant'],
    "code_length" => $params['code_length']
  ], "POST");
  if ($rest && \gettype($rest) == "object") {
    // var_dump($rest);
    if (!empty($rest->reference) && $rest->status == "0.0") {
      $otp_ref = $rest->reference;
    } else {
      $errors[] = "Unable to send OTP at this time, try again later.";
    }
  } else {
    $errors[] = "Unable to send OTP at this time, try again later.";
  }
}
$theme_color = $params['theme'] = ($params && empty($params['theme']) ? "catali-blue" : $params['theme']);
if (!empty($errors)) {
  $errors[] = "<a href='#' class='bold color-red' onclick=\"cwos.faderBox.close();\"><i class='fas fa-times'></i> Close and try again</a>";
}
?>
<div id="fader-flow">
  <input type="hidden" id="otp-email" value="<?php echo @ $params['email']; ?>">
  <input type="hidden" id="otp-callback" value="<?php echo @ $params['cb']; ?>">
  <div class="view-space">
    <div class="paddn -pall -p20">&nbsp;</div>
    <br class="c-f">
    <div class="grid-8-tablet grid-6-desktop center-tablet">
      <div class="sec-div theme-color <?php echo $theme_color; ?> bg-white drop-shadow">
        <header class="paddn -pall -p20 color-bg">
          <h2> <i class="fas fa-key"></i> OTP required</h2>
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
              id="do-post-form"
              class="block-ui"
              method="post"
              action="/app/ws-helper/post/otp/resend-email"
              data-validate="false"
              onsubmit="cwos.form.submit(this,otpResent);return false;" >

            <input type="hidden" name="reference" value="<?php echo @ $otp_ref; ?>">
            <input type="hidden" name="form" value="otp-resend-form">
            <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("otp-resend-form"); ?>">
            <div class="grid-12-tablet">
              <p>OTP has been sent to your email <code><?php echo email_mask($params['email']); ?></code></p>
              <p>If you do not see the email, you can hit resend after the counter finishes.</p>
            </div>
            <div class="grid-7-tablet">
              <div id="res-cnt-view" class="align-c code">
                Resend in: <br>
                <span class="bold font-1-5" id="cnt-timer">0:00</span>
              </div>
            </div>
            <div class="grid-5-tablet">
              <button type="submit" id="otp-rsd-click" disabled class="theme-btn no-shadow"> Resend <i class="fas fa-repeat-alt"></i></button>
            </div> <br class="c-f">
            <div class="border -bthin -btop paddn -pall -p20">&nbsp;</div>
            <div class="grid-7-tablet">
              <label for="otp-val">Enter OTP here</label>
              <input type="text" id="otp-val" placeholder="000 000" class="vcode-text code">
            </div>
            <div class="grid-5-tablet"> <br>
              <button type="button" onclick="verifyOTP();" class="theme-btn <?php echo $theme_color; ?> no-shadow">Continue <i class="fas fa-arrow-right"></i></button>
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
<?php //$conn->closeConnection(); ?>
<script type="text/javascript">
  cb = $("#otp-callback").val();
  var email = $("#otp-email").val();
  function verifyOTP () {
    var otp = $("#otp-val").val();
    if (otp && otp.length) {
      alert("Validating OTP code ..", {type:"progress", exit:false, exitBtn: false});
      helpr_rsc(`/app/ws-helper/get/otp/validate-email`, function(resp) {
        // check if it succeeded
        if (resp && objectLength(resp.errors) <1 ) {
          var data = resp && "data" in resp ? resp.data : resp;
          if (cb || cb.length) {
            window[cb](data.otp);
          } else {
            removeAlert();
            setTimeout(function(){
              alert(resp.message, {type:"success"});
            }, 180);
          }
        } else {
          if ("errors" in resp && "message" in resp && "status" in resp) {
            alert(`<h2>[${resp.status}] ${resp.message}</h2><ol><li>${resp.errors.join("</li><li>")}</li></ol>`, {type:"error", exitBtn: true, exit: true});
          } else {
            alert(`<h2>Error</h2><p>Validation was not successful and response could not be interpreted.</p>`)
          }
        }
      }, {email : email, otp: otp}, {}, function(status, msg){
        removeAlert();
        setTimeout(function(){
          alert(`<h3>Error (${status})</h2> <p>${msg}</p>`, {type:"error"});
        }, 180);
      }); 
    }
  }
  (function(){
    // minuteTimer(1 * 60,"#cnt-timer", enblResend);
    minuteTimer(10 * 60,"#cnt-timer", enblResend);
  })();
</script>
