<?php
namespace Catali;

use stdClass;
use TymFrontiers\InstanceError,
    TymFrontiers\Generic,
    TymFrontiers\Data,
    TymFrontiers\BetaTym,
    TymFrontiers\Validator,
    TymFrontiers\API,
    TymFrontiers\HTTP\Header;
use TymFrontiers\MultiForm;
use TymFrontiers\MySQLDatabase;
use TymFrontiers\Session;

function get_constant (string $name) {
  return \defined("CPRJ_PREFIX")
    ? (\defined(CPRJ_PREFIX . $name) ? \constant(CPRJ_PREFIX . $name) : null)
    : (\defined($name) ? \constant($name) : null);
}
function set_constant (string $name, $value) {
  $prfx = \defined("CPRJ_PREFIX") ? CPRJ_PREFIX : "";
  if (empty(get_constant($name))) {
    \define($prfx. $name, $value);
  }
}
function round (float $num, string $currency = "NGN") {
  global $cryptos;
  if (!empty($cryptos) && \is_array($cryptos)) {
    return \round($num, (\array_key_exists($currency, $cryptos) ? 8 : 2));
  }
  return false;
}
function currency_decimals ($currency) {
  global $cryptos;
  return \array_key_exists($currency, $cryptos) ? 8 : 2;
}
function coupon_summary (string $type, float $value, string|null $ext_type, int $ext_count, float $max_value = 0.00, string $currency = "USD") {
  global $cryptos;
  $return  = "";
  if ($value > 0) {
    $surfix = $type == "FLUID" ? "%" : "";
    $prefix = $type == "FIXED" ? currency_symbol($currency) : "";
    $value = $value <= 0 ? 0.00 : \number_format($value, (\in_array($currency, $cryptos) ? 8 : 2), ".", ",");
    $max_value = $max_value <= 0 ? 0.00 : \number_format($max_value, (\in_array($currency, $cryptos) ? 8 : 2), ".", ",");
    $return .= $prefix . $value . $surfix;

    if ($max_value > 0) {
  
      $return .= (" (up to ". currency_symbol($currency). "{$max_value})");
    }
    $return .= " OFF";
  } if (!empty($ext_type) && $ext_count > 0) {
    $return .= " + additional {$ext_count} " .\ucwords(\strtolower($ext_type));
    $return .= $ext_count > 1 ? "s" : "";
  }
  return $return;
}
function currency_symbol (string $currency):string {
  global $currency_symbols;
  return \array_key_exists($currency, $currency_symbols) ? $currency_symbols[$currency] : $currency;
}
function session_check_rank (int $rank, bool $strict = false) {
  global $session;
  if ($session->isLoggedIn() && (($strict && $session->access_rank() == $rank) || (!$strict && $session->access_rank() >= $rank))) {
    return true;
  }
  return false;
}
function code_split (string $code, string $sep = "-") {
  if ($prfx = \substr($code, 0, 3)) {
    return "{$prfx}{$sep}" . Data::charSplit(\str_replace($prfx,"",$code), 4, $sep);
  }
  return null;
}
function destroy_cookie (string $cname) {
  global $_COOKIE;
  if (isset($_COOKIE[$cname])) {
    unset($_COOKIE[$cname]);
    \setcookie($cname, 0, -1, '/');
    return true;
  }
  return false;
}
function email_mask ( string $email, string $mask_char="*", int $percent=50 ){
  list( $user, $domain ) = \preg_split("/@/", $email );
  $len = \strlen( $user );
  $mask_count = \floor( $len * $percent /100 );
  $offset = \floor( ( $len - $mask_count ) / 2 );
  $masked = \substr( $user, 0, $offset )
    . \str_repeat( $mask_char, $mask_count )
    . \substr( $user, $mask_count+$offset );

  return( $masked.'@'.$domain );
}
function phone_mask (string $number){
  $mask_number =  \str_repeat("*", \strlen($number)-4) . \substr($number, -4);
  return $mask_number;
}
function file_set(string $mime){
  global $file_upload_groups;
  $return = "unknown";
  foreach($file_upload_groups as $type=>$arr){
    if( \in_array($mime,$arr) ){
      $return = $type;
      break;
    }
  }
  return $return;
}
function auth_errors (API\Authentication $auth, string $message, string $errname, bool $override=true) {
  $auth_errors = (new InstanceError ($auth,$override))->get($errname,true);
  $out_errors = [
  "Message" => $message
  ];
  $i=0;
  if (!empty($auth_errors)) {
    foreach ($auth_errors as $err) {
      $out_errors["Error-{$i}"] = $err;
      $i++;
    }
  }
  $out_errors["Status"] = "1" . (\count($out_errors) - 1);
  return $out_errors;
}
function setup_page(string $page_name, string $page_group = "base", bool $show_dnav = true, int $dnav_ini_top_pos=0, string $dnav_stick_on='#page-head', bool $cartbot = false, string $cartbotCb = "", string $dnav_clear_elem = '#main-content', string $dnav_pos = "affix"){
  $set = "<input ";
  $set .=   "type='hidden' ";
  $set .=   "data-setup='page' ";
  $set .=   ("data-show-nav = '" . ($show_dnav ? 1 : 0) ."' ");
  $set .=   "data-group = '{$page_group}' ";
  $set .=   "data-name = '{$page_name}' ";
  $set .= "> ";
  $set .= "<input ";
  $set .=   "type='hidden' ";
  $set .=   "data-setup='dnav' ";
  $set .=   "data-clear-elem='{$dnav_clear_elem}' ";
  $set .=   "data-ini-top-pos={$dnav_ini_top_pos} ";
  $set .=   "data-pos='{$dnav_pos}' ";
  $set .=   "data-cart-bot='". ($cartbot ? 1 : 0)."' ";
  $set .=   "data-cart-bot-click='{$cartbotCb}' ";
  $set .=   "data-stick-on='{$dnav_stick_on}' ";
  $set .= ">";
  echo $set;
}
function file_size_unit($bytes) {
  if ($bytes >= 1073741824) {
    $bytes = number_format($bytes / 1073741824, 2) . ' GB';
  } elseif ($bytes >= 1048576) {
    $bytes = number_format($bytes / 1048576, 2) . ' MB';
  } elseif ($bytes >= 1024) {
    $bytes = number_format($bytes / 1024, 2) . ' KB';
  } elseif ($bytes > 1) {
    $bytes = $bytes . ' bytes';
  } elseif ($bytes == 1) {
    $bytes = $bytes . ' byte';
  } else {
    $bytes = '0 bytes';
  }
  return $bytes;
}
function require_login (bool $redirect = true, string $rd_path = "/helper/user/login") {
  global $session;
  if (!$session->isLoggedIn() ) {
    if ($redirect) {
      Header::redirect(Generic::setGet($rd_path,['rdt'=>THIS_PAGE]));
    } else {
      Header::unauthorized(false,'',["Message"=>"Login is required for requested resource!"]);
    }
  }
}
// Web Store functions
function wsinfo (string $wsid = "", int $id_type = WSID_WSCODE):object|null {
  if (empty($wsid)) {
    $wsid = get_constant("PRJ_WSCODE");
    $id_type = WSID_WSCODE;
  } if (!\in_array($id_type,[WSID_WSCODE, WSID_DOMAIN, WSID_EMAIL])) {
    throw new \Exception("Invalid ID type given in: \$param: 2", 1);
  }
  global $database;
  global $color_theme;
  global $session;
  global $access_ranks;
  $wsid = $database->escapeValue($wsid);
  $data_db = \get_database("data");
  $cnd = "";
  switch ($id_type) {
    case WSID_WSCODE:
      $cnd = " AND ws.`code` = '{$wsid}' ";
      break;
    case WSID_DOMAIN:
      $cnd = " AND ws.`domain` = '{$wsid}' ";
      break;
    case WSID_EMAIL:
      $cnd = " AND ws.`email` = '{$wsid}' ";
      break;
    default:
      $cnd = " AND ws.`code` = '{$wsid}' ";
      break;
  }
  $wsobj = new MultiForm(\get_database("enterprise"), "ws", "code");
  if ($found = $wsobj->findBySql("SELECT ws.code, ws.published, ws.status, ws.domain, ws.email, 
                        ws.owner, ws.`type`, ws.category, ws.subcategory, ws.`name`, 
                        ws.acronym, ws.description, ws.keywords, ws.brand_color, 
                        ws._created AS created,
                        tp.title AS type_title,
                        ct.title AS category_title,
                        sct.title AS subcategory_title
                FROM :db:.:tbl: AS ws
                LEFT JOIN `{$data_db}`.`business_types` AS tp ON tp.`name` = ws.`type`
                LEFT JOIN `{$data_db}`.`business_category` AS ct ON ct.`name` = ws.category
                LEFT JOIN `{$data_db}`.`business_subcategory` AS sct ON sct.`name` = ws.subcategory
                WHERE ws.`status` NOT IN ('BANNED', 'SUSPENDED')
                {$cnd}
                LIMIT 1")
  ) {
    $found = $found[0];
    return (object) [
      "wscode" => $found->code,
      "published" => (bool)$found->published,
      "status" => $found->status,
      "domain" => $found->domain,
      "email" => $found->email,
      "owner" => $found->owner,
      "type" => (object) [
        "name" => $found->type,
        "title" => $found->type_title
      ],
      "category" => (object) [
        "name" => $found->category,
        "title" => $found->category_title
      ],
      "subcategory" => (object) [
        "name" => $found->subcategory,
        "title" => $found->subcategory_title
      ],
      "name" => $found->name,
      "acronym" => $found->acronym,
      "description" => $found->description,
      "keywords" => \explode(",", $found->keywords),
      "brand_color" => (object)[
        "bg" => $color_theme[$found->brand_color]["hexcode"],
        "fg" => $color_theme[$found->brand_color]["color"]
      ],
      "created" => $found->created
    ];
  } else {
    // find what the error was
    $err_output = [];
    $wsobj->mergeErrors();
    if ($errors = (new InstanceError($wsobj, ($session->access_rank() <= $access_ranks["ADMIN"])))->get("", true)) {
      foreach ($errors as $method => $errs) {
        foreach ($errs as $err) {
          $err_output[] = "[{$method}]: " . $err;
        }
      }
    } if (!empty($err_output)) {
      throw new \Exception(\implode(PHP_EOL, $err_output), 1);
    }
  }
  return null;
}
function wsowner (string $wsid = "", int $id_type = WSID_WSCODE):object|null {
  if (empty($wsid)) {
    $wsid = get_constant("PRJ_WSCODE");
    $id_type = WSID_WSCODE;
  } if (!\in_array($id_type,[WSID_WSCODE, WSID_DOMAIN, WSID_EMAIL])) {
    throw new \Exception("Invalid ID type given in: \$param: 2", 1);
  }
  $db_user = \get_dbuser("CWS");
  $db_server = \get_dbserver("CWS");
  $db_name = \get_database("base", "CWS");
  $data_db = \get_database("data", "CWS");
  $ent_db = \get_database("enterprise", "CWS");
  $conn = new MySQLDatabase($db_server, $db_user[0], $db_user[1], $db_name);
  if (!$conn || !$conn instanceof MySQLDatabase) {
    throw new \Exception("Server connection failed", 1);
  }
  $wsid = $conn->escapeValue($wsid);
  $cnd = "";
  switch ($id_type) {
    case WSID_WSCODE:
      $cnd = " AND `ws` = '{$wsid}' ";
      break;
    case WSID_DOMAIN:
      $cnd = " AND `domain` = '{$wsid}' ";
      break;
    case WSID_EMAIL:
      $cnd = " AND `email` = '{$wsid}' ";
      break;
    default:
      $cnd = " AND `ws` = '{$wsid}' ";
      break;
  }

  $owner = new MultiForm($db_name, "users", "code", $conn);
  if ($user = $owner
    ->findBySql("SELECT usr.`code`, usr.status, usr.`name`, usr.surname, usr.email, 
                        usr.phone, usr.country_code,
                        ct.name AS country
                FROM :db:.:tbl: AS usr
                LEFT JOIN `{$data_db}`.countries AS ct ON ct.`code` = usr.country_code
                WHERE usr.`code` = (
                  SELECT `owner`
                  FROM `{$ent_db}`.ws_profile
                  WHERE 1
                  {$cnd}
                  LIMIT 1
                )
                LIMIT 1")) {
    $user = $user[0];
    $conn->closeConnection();

    return (object) [
      "wscode" => $user->code,
      "status" => $user->status,
      "name" => $user->name,
      "surname" => $user->surname,
      "email" => $user->email,
      "phone" => $user->phone,
      "country" => (object) [
        "code" => $user->country_code,
        "name" => $user->country
      ],
    ];
  } else {
    // find what the error was
    global $session;
    global $access_ranks;
    $err_output = [];
    $owner->mergeErrors();
    if ($errors = (new InstanceError($owner, ($session->access_rank() <= $access_ranks["ADMIN"])))->get("", true)) {
      foreach ($errors as $method => $errs) {
        foreach ($errs as $err) {
          $err_output[] = "[{$method}]: " . $err;
        }
      }
    } if (!empty($err_output)) {
      throw new \Exception(\implode(PHP_EOL, $err_output), 1);
    }
  }
  return null;
}
function checkws () {
  $wsowner = wsowner();
  if (!$wsowner || \in_array($wsowner->status, ["BANNED", "SUSPENDED", "DISABLED"])) {
    Header::badRequest(true, "This web store cannot be viewed at this time. If you are the owner; kindly contact admin/support.");
  }
  $wsinfo = wsinfo();
  if (!$wsinfo || \in_array($wsinfo->status, ["BANNED", "SUSPENDED", "DISABLED"])) {
    Header::badRequest(true, "WS: This web store cannot be viewed at this time. If you are the owner; kindly contact admin/support.");
  }
}
