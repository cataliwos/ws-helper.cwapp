<?php
namespace Catali;
require_once "../../.appinit.php";
use TymFrontiers\HTTP\Header,
    TymFrontiers\Generic,
    TymFrontiers\MultiForm,
    TymFrontiers\MySQLDatabase,
    TymFrontiers\InstanceError,
    TymFrontiers\Data;

\header("Content-Type: application/json");
$post = \json_decode( \file_get_contents('php://input'), true); // json data
$post = !empty($post) ? $post : (
  !empty($_POST) ? $_POST : $_GET
);
$gen = new Generic;
// $auth = new API\Authentication ($api_sign_patterns);
// $http_auth = $auth->validApp ();
// if ( !$http_auth && ( empty($post['form']) || empty($post['CSRFToken']) ) ){
//   HTTP\Header::unauthorized (false,'', Generic::authErrors ($auth,"Request [Auth-App]: Authetication failed.",'self',true));
// }

$rqp = [
  "code" => ["code","username", 2, 2],
  "search" => ["search","text",3,25],
  "page" => ["page","int"],
  "limit" => ["limit","int"]
];
$req = [];
// if (!$http_auth) {
//   $req[] = 'form';
//   $req[] = 'CSRFToken';
// }
$params = $gen->requestParam($rqp,$post,$req);
if (!$params || !empty($gen->errors)) {
  $errors = (new InstanceError ($gen, false))->get("requestParam",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request halted"
  ]);
  exit;
}
$server_name = get_constant("PRJ_SERVER_NAME");
$ent_db = \get_database("enterprise");
$db_name = \get_database("data");
$conn =& $database;
$count = 0;
$data = new MultiForm($db_name, 'countries','code', $conn);
$data->current_page = $page = (int)$params['page'] > 0 ? (int)$params['page'] : 1;
$query =
"SELECT cntr.`name`, cntr.`code`, ctis.dvalue AS iso3,
        ctn.dvalue AS number_code,
        ctp.dvalue AS phone_code,
        (
          SELECT COUNT(*)
          FROM :db:.`states`
          WHERE country_code = cntr.`code`
        ) AS states,
        (
          SELECT COUNT(*)
          FROM `{$ent_db}`.ws_contact
          WHERE country_code = cntr.`code`
        ) AS use_count
 FROM :db:.:tbl: AS cntr ";
 $join = " LEFT JOIN :db:.`country_data` AS ctis ON ctis.country_code = cntr.code AND ctis.dkey = 'ISO3'
 LEFT JOIN :db:.`country_data` AS ctn ON ctn.country_code = cntr.code AND ctn.dkey = 'NUMBERCODE'
 LEFT JOIN :db:.`country_data` AS ctp ON ctp.country_code = cntr.code AND ctp.dkey = 'PHONECODE' ";

$cond = " WHERE 1=1 ";
if (!empty($params['code'])) {
  $cond .= " AND cntr.`code` = '{$conn->escapeValue($params['code'])}' ";
} else {
  if( !empty($params['search']) ){
    $params['search'] = $db->escapeValue(\strtolower($params['search']));
    $cond .= " AND (
      LOWER(cntr.`code`) = '{$params['search']}'
      OR LOWER(cntr.`name`) LIKE '%{$params['search']}%'
    ) ";
  }
}

$count = $data->findBySql("SELECT COUNT(*) AS cnt FROM :db:.:tbl: AS cntr {$cond} ");
// echo $db->last_query;
$count = $data->total_count = $count ? $count[0]->cnt : 0;

$data->per_page = $limit = !empty($params['code']) ? 1 : (
    (int)$params['limit'] > 0 ? (int)$params['limit'] : 10000
  );
$query .= $join;
$query .= $cond;
$sort = " ORDER BY cntr.name ASC ";

$query .= $sort;
$query .= " LIMIT {$data->per_page} ";
$query .= " OFFSET {$data->offset()}";

// echo \str_replace(':tbl:','ws_categories',\str_replace(':db:',$db_name,$query));
// exit;
$found = $data->findBySql($query);
$tym = new \TymFrontiers\BetaTym;
if( !$found ){
  die( \json_encode([
    "message" => "No result found.",
    "errors" => [],
    "status" => "0.2"
    ]) );
}
$result = [
  'records' => (int)$count,
  'page'  => $data->current_page,
  'pages' => $data->totalPages(),
  'limit' => $limit,
  'hasPreviousPage' => $data->hasPreviousPage(),
  'hasNextPage' => $data->hasNextPage(),
  'previousPage' => $data->hasPreviousPage() ? $data->previousPage() : 0,
  'nextPage' => $data->hasNextPage() ? $data->nextPage() : 0
];
foreach($found as $k=>$cntr){
  $result["data"][] = [
    "code" => $cntr->code,
    "name" => $cntr->name,
    "iso3" => $cntr->iso3,
    "numberCode" => $cntr->number_code,
    "phoneCode" => $cntr->phone_code,
    "states" => (int)$cntr->states,
    "userCount" => (int)$cntr->user_count
  ];
}
$result["message"] = "Request completed.";
$result["errors"] = [];
$result["status"] = "0.0";

echo \json_encode($result);
exit;
