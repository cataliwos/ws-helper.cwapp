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
  "type" => ["type","username", 3, 72, [], "LOWER", ["-", "."]],
  "category" => ["category","username", 3, 72, [], "LOWER", ["-", "."]],
  "name" => ["name","username", 3, 72, [], "LOWER", ["-", "."]],
  "ws" => ["ws","username",5,16, [], 'MIXED', ["-","."]],
  "search" => ["search","text",3,25],
  "surfix" => ["surfix","username", 3, 72, [], "LOWER", ["-", "."]],
  "page" => ["page","int"],
  "limit" => ["limit","int"],
  "esc_author" => ["esc_author","boolean"]
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
if (!empty($params['ws'])) $params['ws'] = \str_replace([" ", ".", "-"], "", $params['ws']);
if (empty($params['surfix'])) $params['surfix'] = $session->name;
$wscode = get_constant("PRJ_WSCODE");
$server_name = get_constant("PRJ_SERVER_NAME");
$ent_db = \get_database("enterprise");
$db_name = \get_database("data");
$conn =& $database;

$count = 0;
$data = new MultiForm($db_name, 'offer_subcategories','name', $conn);
$data->current_page = $page = (int)$params['page'] > 0 ? (int)$params['page'] : 1;
$query =
"SELECT scatg.name, scatg.category, scatg.title,
        scatg.description, scatg._updated, scatg._created,
        catg.title AS category_title,
        (
          SELECT COUNT(*)
          FROM `{$ent_db}`.offers
          WHERE subcategory = scatg.name
        ) AS use_count,
        (
          SELECT COUNT(*)
          FROM `{$ent_db}`.offers
          WHERE subcategory = scatg.name
          AND ws = '{$conn->escapeValue($wscode)}'
          AND `status` = 'PUBLISHED'
        ) AS 'records'
 FROM :db:.:tbl: AS scatg ";
 $join = " LEFT JOIN :db:.offer_categories AS catg ON catg.name = scatg.category ";

$cond = (bool)$params["esc_author"] ? " WHERE 1=1 " : " WHERE (
            (scatg.`user_input` = FALSE AND scatg.`reserved` = FALSE)
            OR scatg.`author` = '{$conn->escapeValue($session->name)}'
            OR scatg.`author` = '{$params['author']}'
          ) ";
if (!empty($params['name'])) {
  $cond .= " AND (
    scatg.name = '{$conn->escapeValue($params['name'])}'
    OR scatg.name = CONCAT('{$conn->escapeValue($params['name'])}', '.', '{$conn->escapeValue($params['surfix'])}')
  ) ";
} else {
  if (!empty($params['category'])) {
    $cond .= " AND (
      scatg.category = '{$conn->escapeValue($params['category'])}'
      OR scatg.category = CONCAT('{$conn->escapeValue($params['category'])}', '.', '{$conn->escapeValue($params['surfix'])}')
    ) ";
  } else {
    if (!empty($params['type'])) {
      $cond .= " AND scatg.category IN (
        SELECT `name` 
        FROM :db:.offer_categories
        WHERE `type` = '{$conn->escapeValue($params['type'])}'
      ) ";
    }
  }
  if (!empty($params['ws'])) {
    $cond .= " AND scatg.name IN (
      SELECT `subcategory`
      FROM `{$ent_db}`.offers
      WHERE `ws` = '{$conn->escapeValue($params['ws'])}'
    ) ";
  }
  if( !empty($params['search']) ){
    $params['search'] = $db->escapeValue(\strtolower($params['search']));
    $cond .= " AND (
      scatg.name = '{$params['search']}'
      OR LOWER(scatg.title) LIKE '%{$params['search']}%'
    ) ";
  }
}

$count = $data->findBySql("SELECT COUNT(*) AS cnt FROM :db:.:tbl: AS scatg {$cond} ");
// echo $db->last_query;
$count = $data->total_count = $count ? $count[0]->cnt : 0;

$data->per_page = $limit = !empty($params['name']) ? 1 : (
    (int)$params['limit'] > 0 ? (int)$params['limit'] : 10000
  );
$query .= $join;
$query .= $cond;
$sort = " ORDER BY scatg.title ASC, scatg.title ASC ";

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

$result["message"] = "Request completed.";
$result["errors"] = [];
$result["status"] = "0.0";
foreach($found as $k=>$cat){
  $result["data"][] = [
    "name" => $cat->name,
    "title" => $cat->title,
    "category" => (object)[
      "name" => $cat->category,
      "title" => $cat->category_title,
    ],
    "description" => $cat->description,
    "useCount" => (int)$cat->use_count,
    "records" => (int)$cat->records,
    
    "updated_date" => $cat->updated(),
    "updated" => $tym->MDY($cat->updated()),
    "created_date" => $cat->created(),
    "created" => $tym->MDY($cat->created()),
  ];
}

echo \json_encode($result);
exit;
// End dev process
