<?php
namespace Catali;
use TymFrontiers\InstanceError,
    TymFrontiers\Generic,
    TymFrontiers\Data,
    TymFrontiers\BetaTym,
    TymFrontiers\Validator,
    TymFrontiers\API,
    TymFrontiers\HTTP\Header,
    TymFrontiers\HTTP\Client;
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

function currency_symbol (string $currency):string {
  global $currency_symbols;
  return \array_key_exists($currency, $currency_symbols) ? $currency_symbols[$currency] : $currency;
}
function currency_decimals ($currency) {
  global $currency_symbols;
  return \array_key_exists($currency, $currency_symbols) ? 2 : 8;
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
function get_navgroup (string $group_name):array {
  global $session;
  $return_navs = [];
  $nav_file = get_constant("PRJ_LIBRARY") . "/navigation.json";
  if (\file_exists($nav_file)) {
    $navs = \file_get_contents($nav_file);
    if ($navs && $navs = \json_decode($navs)) {
      if (!empty($navs->$group_name)) {
        $ws = ws_info();
        $replace = [
          "%{ws-name}" => $ws->name,
          "%{ws-type}" => $ws->type->name,
          "%{ws-type-title}" => $ws->type->title,
          "%{ws-category}" => $ws->category->name,
          "%{ws-category-title}" => $ws->category->title,
          "%{ws-subcategory}" => $ws->subcategory->name,
          "%{ws-subcategory-title}" => $ws->subcategory->title,
        ];
        foreach ($navs->$group_name->links as $nav) {
          if (
            ((bool)$nav->strict_access && $nav->access_rank == $session->access_rank())
            || (!(bool)$nav->strict_access && $nav->access_rank <= $session->access_rank())
          ) {
            // $nav->path = $path;
            unset($nav->strict_access);
            unset($nav->access_rank);
            foreach ($nav as $prop => $val) {
              if (!empty($val)) {
                foreach ($replace as $regex => $value) {
                  $nav->$prop = \str_replace($regex, $value, $nav->$prop);
                }
              }
            }
            $return_navs[] = $nav;
          }
        }
      }
    }
  }
  return $return_navs;
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
function ws_info (string $wsid = "", int $id_type = WSID_WSCODE):object|null {
  if (empty($wsid)) {
    $wsid = get_constant("PRJ_WSCODE");
    $id_type = WSID_WSCODE;
  } if (!\in_array($id_type,[WSID_WSCODE, WSID_DOMAIN, WSID_EMAIL])) {
    throw new \Exception("Invalid ID type given in: \$param: 2", 1);
  }
  $server_name = get_constant("PRJ_SERVER_NAME");
  $conn = \query_conn($server_name);
  global $color_theme;
  global $session;
  global $access_ranks;
  $wsid = $conn->escapeValue($wsid);
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
  $wsobj = new MultiForm(\get_database("enterprise"), "ws", "code", $conn);
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
        "name" => $found->brand_color,
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
function ws_owner (string $wsid = "", int $id_type = WSID_WSCODE):object|null {
  if (empty($wsid)) {
    $wsid = get_constant("PRJ_WSCODE");
    $id_type = WSID_WSCODE;
  } if (!\in_array($id_type,[WSID_WSCODE, WSID_DOMAIN, WSID_EMAIL])) {
    throw new \Exception("Invalid ID type given in: \$param: 2", 1);
  }
  $db_name = \get_database("base", "CWS");
  $data_db = \get_database("data", "CWS");
  $ent_db = \get_database("enterprise", "CWS");
  $conn = \query_conn("CWS");
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
  // check every 14 minutes
  $ck_name = "_wsinfstat";
  if (!isset($_COOKIE[$ck_name])) {
    $wsowner = ws_owner();
    if (!$wsowner || \in_array($wsowner->status, ["BANNED", "SUSPENDED", "DISABLED"])) {
      Header::badRequest(true, "This web store cannot be viewed at this time. If you are the owner; kindly contact admin/support.");
    }
    $wsinfo = ws_info();
    if (!$wsinfo || \in_array($wsinfo->status, ["BANNED", "SUSPENDED", "DISABLED"])) {
      Header::badRequest(true, "WS: This web store cannot be viewed at this time. If you are the owner; kindly contact admin/support.");
    }
    // create cookie
    \setcookie($ck_name, 1, \strtotime("+14 Minutes"), "/", get_constant("PRJ_DOMAIN"), false, true);
  }

}
function ws_social (string $wscode = ""):null|array {
  if (!$wscode) $wscode = get_constant("PRJ_WSCODE");
  $conn = \query_conn(get_constant("PRJ_SERVER_NAME"));
  if ($found = (new MultiForm(get_database("enterprise"), "ws_social", "id", $conn))->findBySql("SELECT * FROM :db:.:tbl: WHERE ws = '{$conn->escapeValue($wscode)}'")) {
    $social_conn = [];
    foreach ($found as $f) {
      $social_conn[$f->type] = (object)[
        "handle" => $f->handle,
        "is_business" => (bool)$f->is_corp
      ];
    }
    return $social_conn;
  }
  return null;
}
function ws_contact (string $wscode = ""):null|object {
  if (!$wscode) $wscode = get_constant("PRJ_WSCODE");
  $conn = \query_conn(get_constant("PRJ_SERVER_NAME"));
  $db_name = get_database("enterprise");
  $data_db = get_database("data");
  if ($found = (new MultiForm($db_name, "ws_contact", "ws", $conn))
    ->findBySql("SELECT wsc.email, wsc.phone, wsc.country_code, wsc.zip_code, wsc.landmark, wsc.street, wsc.apartment,
                        c.`name` AS country,
                        st.`name`AS 'state',
                        ci.`name` AS city,
                        lga.`name` AS lga
                FROM :db:.:tbl: AS wsc
                LEFT JOIN `{$data_db}`.countries  AS c    ON c.`code`   = wsc.country_code
                LEFT JOIN `{$data_db}`.states     AS st   ON st.`code`  = wsc.state_code
                LEFT JOIN `{$data_db}`.cities     AS ci   ON ci.`code`  = wsc.city_code
                LEFT JOIN `{$data_db}`.lgas       AS lga  ON lga.`code` = wsc.lga_code
                WHERE ws = '{$conn->escapeValue($wscode)}'
                LIMIT 1")
  ) {
    return (object)[
      "email" => $found[0]->email,
      "phone" => $found[0]->phone,
      "country_code" => $found[0]->country_code,
      "zip_code" => $found[0]->zip_code,
      "landmark" => $found[0]->landmark,
      "street" => $found[0]->street,
      "apartment" => $found[0]->apartment,
      "country" => $found[0]->country,
      "state" => $found[0]->state,
      "city" => $found[0]->city,
      "lga" => $found[0]->lga,
      "address" => \implode(", ", [
        $found[0]->apartment,
        $found[0]->street,
        PHP_EOL . $found[0]->landmark,
        $found[0]->city . (empty($found[0]->zip_code) ? "" : " - {$found[0]->zip_code}"),
        PHP_EOL . \implode(", ", [
          $found[0]->state,
          $found[0]->country
        ])
      ])
    ];
  } 
  return null;
}

// Generic
function client_query (string $path, array $query_param = [], string $type = "POST", null|API\DevApp $app = null):object|null {
  if (!$app) {
    $app = \api_appcred();
  }
  $types = [
    "GET" => Client::GET,
    "POST" => Client::POST,
    "PATCH" => Client::PATCH,
    "DELETE" => Client::DELETE
  ];
  $type = \array_key_exists($type, $types) ? $type : $types['GET'];
  $request_cred = API\AuthHeader::generate($app);
  $state_code = "0.0";
  $status_msg = "No request performed";
  $rest = new Client($type, $path, $query_param, $request_cred, [
      "data_type" => "json",
      "raw_param" => "json"
    ]);
  $status_code = $rest->statusCode();
  if ( $rest->statusCode() == 200 ) {
    $rest_body = \json_decode($rest->body());
    // echo $rest->body();
    if (!$rest_body || !\is_object($rest_body)) {
      return (object)[
        "status" => $state_code,
        "message" => $status_msg,
        "errors" => ["Error parsing response body: {$rest->body()}"]
      ];
    } else {
      return $rest_body;
    }
  } else {
    return (object)[
      "status" => "2.1",
      "message" => $status_msg,
      "errors" => ["Incomplete process. Request halted with HTTP status: {$status_code}"]
    ];
  }
  return null;
}
function add_to_cart (string $offer, int $quantity):bool|int {
  if ($offer && $quantity) {
    global $session;
    $conn = \query_conn();
    $cookie_name = '_wscartusr';
    $db_name = get_database("base");
    $ws = get_constant("PRJ_WSCODE");  
    $user = !empty($_COOKIE[$cookie_name]) ? $_COOKIE[$cookie_name] : $session->name;
    if ($session->isLoggedIn() && $user !== $session->name) {
      // update cart register
      $conn->query("UPDATE `{$db_name}`.`shopping_cart` SET `user` = '{$conn->escapeValue($session->name)}' WHERE `user` = '{$user}'");
      destroy_cookie($cookie_name);
      $user = $session->name;
    }
    $is_new = true;
    if ($cart = (new MultiForm($db_name, "shopping_cart", "id", $conn))
      ->findBySql("SELECT * 
                  FROM :db:.:tbl: 
                  WHERE `ws` = '{$conn->escapeValue($ws)}'
                  AND `user` = '{$conn->escapeValue($user)}'
                  AND `offer` = '{$conn->escapeValue($offer)}'
                  LIMIT 1")
    ) {
      $is_new = false;
      $cart = $cart[0];
    } else {
      $cart = new MultiForm($db_name, "shopping_cart", "id", $conn);
    }
    $cart->ws = $ws;
    $cart->user = $user;
    $cart->offer = $offer;
    $cart->quantity = $is_new ? $quantity : ((int)$cart->quantity + $quantity);
    $saved = $is_new ? $cart->create() : $cart->update();
    if ($saved) {
      if (empty($_COOKIE[$cookie_name])) \setcookie($cookie_name, $user, [
        'expires' => \strtotime("+1 Week"),
        'path' => '/',
        'domain' => get_constant("PRJ_DOMAIN"),
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
      ]);
      return get_cart();
    }
  }
  return false;
}
function get_cart ():int {
  $conn = \query_conn();
  global $session;
  $cookie_name = '_wscartusr';
  $db_name = get_database("base");
  $ws = get_constant("PRJ_WSCODE");
  $user = !empty($_COOKIE[$cookie_name]) ? $_COOKIE[$cookie_name] : $session->name;
  if ($session->isLoggedIn() && $user !== $session->name) {
    // update cart register
    $conn->query("UPDATE `{$db_name}`.`shopping_cart` SET `user` = '{$conn->escapeValue($session->name)}' WHERE `user` = '{$user}'");
    destroy_cookie($cookie_name);
    $user = $session->name;
  }
  if ($found = (new MultiForm($db_name, "shopping_cart", "id", $conn))
  ->findBySql("SELECT SUM(quantity) AS quantity, `user`
                FROM :db:.:tbl:
                WHERE ws = '{$ws}'
                AND `user` = '{$user}'")
  ) {
    if (empty($_COOKIE[$cookie_name])) \setcookie($cookie_name, $found[0]->user, [
      'expires' => \strtotime("+1 Week"),
      'path' => '/',
      'domain' => get_constant("PRJ_DOMAIN"),
      'secure' => true,
      'httponly' => true,
      'samesite' => 'Strict'
    ]);
    return (int)$found[0]->quantity;
  }
  return 0;
}
function api_token (string $app_name = ""):string|false {
  $server_name = get_constant("PRJ_SERVER_NAME");
  if (empty($app_name)) $app_name = get_constant("API_APP_NAME");
  $app = \api_appcred($app_name);
  if ($cred = API\AuthHeader::generate($app)) {
    $cred_r = [];
    $data = new Data;
    foreach ($cred as $key => $value) {
      $cred_r[] = $key . TXT_VALUE_ASSIGNMENT . $value;
    }
    $cred = \implode(TXT_SEGMENT_SPLIT, $cred_r);
    return $server_name . TXT_VALUE_ASSIGNMENT . $data->encodeEncrypt($cred);
  }
  return false;
}
function api_token_decode (string $token):array|null {
  @list($server_name, $token) = \explode(TXT_VALUE_ASSIGNMENT, \html_entity_decode(\trim($token)));
  if (!empty($server_name) && !empty($token)) {
    if ($server_name !== get_constant("PRJ_SERVER_NAME")) return null; 
    $server_name = \trim($server_name);
    $token = \trim($token);
    $data = new Data;
    if ($token = $data->decodeDecrypt($token)) {
      $app_token = [];
      foreach (\explode(TXT_SEGMENT_SPLIT, \html_entity_decode($token)) as $token_r) {
        @list($key, $value) = \explode(TXT_VALUE_ASSIGNMENT, \html_entity_decode($token_r));
        if (!empty($key) && !empty($value)) $app_token[$key] = $value;
      }
      return $app_token ? $app_token : null;
    }
  }
  return null;
}