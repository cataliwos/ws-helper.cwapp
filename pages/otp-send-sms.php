<?php
namespace Catali;
require_once "../.appinit.php";
use TymFrontiers\Generic,
    TymFrontiers\Data,
    TymFrontiers\InstanceError;
use TymFrontiers\MySQLDatabase;

$data_obj = new Data;
$post = empty($_POST) ? $_GET : $_POST;
$errors = [];
$gen = new Generic;
if (!empty($post['country_code']) && !empty($post['phone'])) $post['phone'] = $data_obj->phoneToIntl($post['phone'], $post['country_code']);
$params = $gen->requestParam([
  "country_code" => ["country_code","username",2,2],
  "phone" => ["phone","tel"],
  "name" => ["name","name"],
  "surname" => ["surname","name"],
  "cb" => ["callback","username",3,35,[],'MIXED', ["_","."]],
  "MUST_EXIST" =>["MUST_EXIST","boolean"],
  "MUST_NOT_EXIST" =>["MUST_NOT_EXIST","boolean"],
  "code_variant" =>["code_variant","option",[
    Data::RAND_MIXED,
    Data::RAND_NUMBERS,
    Data::RAND_LOWERCASE,
    Data::RAND_UPPERCASE,
    Data::RAND_MIXED_LOWER,
    Data::RAND_MIXED_UPPER,
    ]],
    "code_length" =>["code_length","int", 7, 16],

], $post, ["phone",'cb']);
if (!$params || !empty($gen->errors)) {
  $errs = (new InstanceError ($gen, false))->get("requestParam",true);
  foreach ($errs as $er) {
    $errors[] = $er;
  }
}
$otp = false;
if ($params && !empty($params['phone'])) {
  if( (bool)$params['MUST_EXIST'] && !(new User)->valExist($params['phone'], "phone") ){
    $errors[] = "Phone: [{$params['phone']}] is not in record.";
  }
  if( (bool)$params['MUST_NOT_EXIST'] && (new User)->valExist($params['phone'],"phone") ){
    $errors[] = "Phone: [{$params['phone']}] is already in use.";
  }
  $token_type = !empty($params['code_variant']) ? $params['code_variant'] : Data::RAND_NUMBERS;
  $otp = false;
  // opt \Dev connection
  $eml_server = get_constant("PRJ_EMAIL_SERVER");
  $dev_usr = db_cred($eml_server, "DEVELOPER");
  $conn = new MySQLDatabase(get_dbserver($eml_server), $dev_usr[0], $dev_usr[1]);
  if (!$conn || !$conn instanceof MySQLDatabase) {
    $errors[] = "Failed to open database connection, contact Admin";
  }
  try {
    $otp = new OTP\ByPhone("", $conn);
  } catch (\Throwable $th) {
    $errors[] = $th->getMessage();
  } if ($otp) {
    if (!$otp->setReceiver($params['phone'])) {
      $errors[] = "Unable to set OTP sender.";
    }
  }
  if (empty($errors) && $otp) {
    if ($sms_class = setting_get_value("SYSTEM", "OTP.SMS-CLASS", get_server_domain(\Catali\get_constant("PRJ_BASE_SERVER")))) {
      try {
        $sms = new $sms_class($otp);
        $reference = $sms->send($token_type, \strtotime("+28 Minutes"));
      } catch (\Throwable $th) {
        $reference = false;
        $errors[] = $th->getMessage();
      }
    }
    if ($reference == false) {
      $errs = (new InstanceError($otp, false))->get('send',true);
      if (!empty($errs)) $errors[] = "Failed to send OPT message";
      foreach ($errs as $err) {
        $errors[] = $err;
      }
    }
  }
}
?>
<div id="fader-flow">
  <input type="hidden" id="otp-phone" value="<?php echo $params['phone']; ?>">
  <input type="hidden" id="otp-callback" value="<?php echo $params['cb']; ?>">
  <div class="view-space">
    <div class="paddn -pall -p20">&nbsp;</div>
    <br class="c-f">
    <div class="grid-8-tablet grid-6-desktop center-tablet">
      <div class="sec-div theme-color blue bg-white drop-shadow">
        <header class="paddn -pall -p20 color-bg">
          <h1> <i class="fad fa-key-skeleton"></i> OTP required</h1>
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
              action="/app/helper/src/ResendPhoneOTP.php"
              data-validate="false"
              onsubmit="cwos.form.submit(this,otpResent);return false;" >

            <input type="hidden" name="reference" value="<?php echo !$reference ? "" : $reference; ?>">
            <input type="hidden" name="form" value="otp-resend-form">
            <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("otp-resend-form"); ?>">
            <div class="grid-12-tablet">
              <p>OTP has been sent to your phone number <code><?php echo phone_mask($params['phone']); ?></code></p>
              <p>If you do not see the text, you can hit resend after the counter finishes.</p>
            </div>
            <div class="grid-7-tablet">
              <div id="res-cnt-view" class="align-c code">
                Resend in: <br>
                <span class="bold font-1-5" id="cnt-timer">0:00</span>
              </div>
            </div>
            <div class="grid-5-tablet">
              <button type="submit" id="otp-rsd-click" disabled class="theme-btn no-shadow"> Resend <i class="fad fa-repeat-alt"></i></button>
            </div> <br class="c-f">
            <div class="border -bthin -btop paddn -pall -p20">&nbsp;</div>
            <div class="grid-7-tablet">
              <label for="otp-val">Enter OTP here</label>
              <input type="text" id="otp-val" placeholder="000 000" class="vcode-text code">
            </div>
            <div class="grid-5-tablet"> <br>
              <button type="button" onclick="verifyPhoneOTP();" class="theme-btn blue no-shadow">Continue <i class="fad fa-arrow-right"></i></button>
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
<?php $conn->closeConnection(); ?>
<script type="text/javascript">
  cb = $("#otp-callback").val();
  let phone = $("#otp-phone").val();
  const verifyPhoneOTP = () => {
    let otp = $("#otp-val").val();
    if (otp && otp.length) {
      alert("Validating OTP code ..", {type:"progress", exit:false, exitBtn: false});
      helpr_rsc(`/app/cataliws/helper.cwapp/service/get/otp-phone-validate.php`, function(resp) {
        // check if it succeeded
        if (resp && objectLength(resp.errors) <1 ) {
          let data = resp && "data" in resp ? resp.data : resp;
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
      }, {phone : phone, otp: otp}, {}, function(status, msg){
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
