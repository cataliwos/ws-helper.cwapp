<?php
namespace Catali;
global $access_ranks,
  $reverse_access_ranks,
  $email_replace_pattern,
  $file_upload_groups,
  $api_sign_patterns,
  $mysql_dt_string,
  $mysql_date_string,
  $renewal_terms,
  $renewal_options,
  $color_theme,
  $recur_terms,
  $priority_titles,
$currency_symbols;

$access_ranks = [
  "GUEST"       => 0,
  "USER"        => 1,
  "ANALYST"     => 2,
  "ADVERTISER"  => 3,
  "MODERATOR"   => 4,
  "EDITOR"      => 5,
  "ADMIN"       => 6,
  "DEVELOPER"   => 7,
  "SUPERADMIN"  => 8,
  "OWNER"       => 14
];
$reverse_access_ranks = \array_flip($access_ranks);

$file_upload_groups = [
  "image" => [
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'jpe' => 'image/jpeg',
    'gif' => 'image/gif'
  ],
  "document" => [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'rtf' => 'application/rtf',
    'xls' => 'application/vnd.ms-excel',
    'xlsx'=> 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'ppt' => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'odt' => 'application/vnd.oasis.opendocument.text',
    'ods' => 'application/vnd.oasis.opendocument.spreadsheet'
  ]
];
$currency_symbols =  [
  'ALL' => 'Lek',
  'AFN' => '؋',
  'ARS' => '$',
  'AWG' => 'ƒ',
  'AUD' => '$',
  'AZN' => '₼',
  'BSD' => '$',
  'BBD' => '$',
  'BYN' => 'Br',
  'BZD' => 'BZ$',
  'BMD' => '$',
  'BOB' => '$b',
  'BAM' => 'KM',
  'BWP' => 'P',
  'BGN' => 'лв',
  'BRL' => 'R$',
  'BND' => '$',
  'KHR' => '៛',
  'CAD' => '$',
  'KYD' => '$',
  'CLP' => '$',
  'CNY' => '¥',
  'COP' => '$',
  'CRC' => '₡',
  'HRK' => 'kn',
  'CUP' => '₱',
  'CZK' => 'Kč',
  'DKK' => 'kr',
  'DOP' => 'RD$',
  'XCD' => '$',
  'EGP' => '£',
  'SVC' => '$',
  'EUR' => '€',
  'FKP' => '£',
  'FJD' => '$',
  'GHS' => '¢',
  'GIP' => '£',
  'GTQ' => 'Q',
  'GGP' => '£',
  'GYD' => '$',
  'HNL' => 'L',
  'HKD' => '$',
  'HUF' => 'Ft',
  'ISK' => 'kr',
  'INR' => '₹',
  'IDR' => 'Rp',
  'IRR' => '﷼',
  'IMP' => '£',
  'ILS' => '₪',
  'JMD' => 'J$',
  'JPY' => '¥',
  'JEP' => '£',
  'KZT' => 'лв',
  'KPW' => '₩',
  'KRW' => '₩',
  'KGS' => 'лв',
  'LAK' => '₭',
  'LBP' => '£',
  'LRD' => '$',
  'MKD' => 'ден',
  'MYR' => 'RM',
  'MUR' => '₨',
  'MXN' => '$',
  'MNT' => '₮',
  'MAD' => 'د.إ',
  'MZN' => 'MT',
  'NAD' => '$',
  'NPR' => '₨',
  'ANG' => 'ƒ',
  'NZD' => '$',
  'NIO' => 'C$',
  'NGN' => '₦',
  'NOK' => 'kr',
  'OMR' => '﷼',
  'PKR' => '₨',
  'PAB' => 'B/.',
  'PYG' => 'Gs',
  'PHP' => '₱',
  'PLN' => 'zł',
  'QAR' => '﷼',
  'RON' => 'lei',
  'RUB' => '₽',
  'SHP' => '£',
  'SAR' => '﷼',
  'RSD' => 'Дин.',
  'SCR' => '₨',
  'SGD' => '$',
  'SBD' => '$',
  'SOS' => 'S',
  'ZAR' => 'R',
  'LKR' => '₨',
  'SEK' => 'kr',
  'CHF' => 'CHF',
  'SRD' => '$',
  'SYP' => '£',
  'TWD' => 'NT$',
  'THB' => '฿',
  'TTD' => 'TT$',
  'TRY' => '₺',
  'TVD' => '$',
  'UAH' => '₴',
  'AED' => 'د.إ',
  'GBP' => '£',
  'USD' => '$',
  'UYU' => '$U',
  'UZS' => 'лв',
  'VEF' => 'Bs',
  'VND' => '₫',
  'YER' => '﷼',
  'ZWD' => 'Z$'
];
$api_sign_patterns = [
  "/path/to/request" => [
    "var1", "var2", "var3" // in order of inclusion
  ]
];
// payment variables
$mysql_dt_string = "Y-m-d H:i:s";
$mysql_date_string = "Y-m-d";

$recur_terms = [
  "OFF" => [
    "title" => "One-off",
    "due" => NULL,
    "divider" => 1,
    "divider_comment" => ""
  ],
  "DAILY" => [
    "title" => "Daily",
    "due" => \date($mysql_dt_string, \strtotime("+1 Day")),
    "divider" => 24,
    "divider_comment" => "Per Hour"
  ],
  "WEEKLY" => [
    "title" => "Weekly",
    "due" => \date($mysql_dt_string, \strtotime("+1 Week")),
    "divider" => 7,
    "divider_comment" => "Per Day"
  ],
  "MONTHLY" => [
    "title" => "Monthly",
    "due" => \date($mysql_dt_string, \strtotime("+1 Month")),
    "divider" => 1,
    "divider_comment" => ""
  ],
  "QUARTERLY" => [
    "title" => "Every 3 Months",
    "due" => \date($mysql_dt_string, \strtotime("+3 Months")),
    "divider" => 3,
    "divider_comment" => "Per Month"
  ],
  "BIANNUAL" => [
    "title" => "Every 6 Months",
    "due" => \date($mysql_dt_string, \strtotime("+6 Months")),
    "divider" => 6,
    "divider_comment" => "Per Month"
  ],
  "YEARLY" => [
    "title" => "Yearly",
    "due" => \date($mysql_dt_string, \strtotime("+1 Year")),
    "divider" => 12,
    "divider_comment" => "Per Month"
  ],
  "BIYEARLY" => [
    "title" => "Every 2 Years",
    "due" => \date($mysql_dt_string, \strtotime("+2 Years")),
    "divider" => 24,
    "divider_comment" => "Per Month"
  ]
];
$renewal_terms = [
  "ONEOFF" => NULL,
  "WEEKLY" => \date($mysql_dt_string, \strtotime("+1 Week")),
  "MONTHLY" => \date($mysql_dt_string, \strtotime("+1 Month")),
  "QUARTERLY" => \date($mysql_dt_string, \strtotime("+3 Months")),
  "BIYEARLY" => \date($mysql_dt_string, \strtotime("+6 Months")),
  "YEARLY" => \date($mysql_dt_string, \strtotime("+1 Year")),
  "18MONTHS" => \date($mysql_dt_string, \strtotime("+18 Months"))
];
$renewal_options = [
  "ONEOFF" => "One-Time - No renewal needed",
  "WEEKLY" => "Renews every week",
  "MONTHLY" => "Renews monthly",
  "QUARTERLY" => "Renews every 3 months",
  "BIYEARLY" => "Renews every 6 months",
  "YEARLY" => "Once a year renewal",
  "18MONTHS" => "One Year + 6 Months"
];
$priority_titles = [
  1 => "Critical",
  2 => "Urgent",
  3 => "Important",
  4 => "Normal",
  5 => "Low",
  6 => "Defer"
];
// color theme
$color_theme = [
  "catali-blue"      => ["title" => "Catali Blue", "hexcode" => "#1976D2", "color" => "#ffffff"],
  "catali-purple"    => ["title" => "Catali Purple", "hexcode" => "#9c27b0", "color" => "#ffffff"],
  "catali-amber"     => ["title" => "Catali Amber", "hexcode" => "#FFB700", "color" => "#000"],

  "gold"             => ["title" => "Gold", "hexcode" => "#EBBD63", "color" => "#000"],
  "rose-gold"        => ["title" => "Rose Gold", "hexcode" => "#FDD09F", "color" => "#000"],
  "black-gold"       => ["title" => "Black-Golden", "hexcode" => "#000", "color" => "#EBBD63"],
  "red"              => ["title" => "Red", "hexcode" => "#F44336", "color" => "#ffffff"],
  "blue"             => ["title" => "Blue", "hexcode" => "#2196F3", "color" => "#ffffff"],
  "light-blue"       => ["title" => "Light Blue", "hexcode" => "#03A9F4", "color" => "#ffffff"],
  "midnight-blue"    => ["title" => "Midnight Blue", "hexcode" => "#2c3e50", "color" => "#ffffff"],
  "blue-grey"        => ["title" => "Blue Grey", "hexcode" => "#607D8B", "color" => "#ffffff"],
  "navy-blue"        => ["title" => "Navy Blue", "hexcode" => "#000066", "color" => "#ffffff"],
  "green"            => ["title" => "Green", "hexcode" => "#4CAF50", "color" => "#ffffff"],
  "nigeria-green"    => ["title" => "Nigeria Green", "hexcode" => "#008751", "color" => "#ffffff"],
  "light-green"      => ["title" => "Light Green", "hexcode" => "#8BC34A", "color" => "#000"],
  "green-sea"        => ["title" => "Green Sea", "hexcode" => "#16a085", "color" => "#ffffff"],
  "yellow"           => ["title" => "Yellow", "hexcode" => "#FFEB3B", "color" => "#000"],
  "amber"            => ["title" => "Amber", "hexcode" => "#FFC107", "color" => "#000"],
  "asphalt"          => ["title" => "Asphalt", "hexcode" => "#34495e", "color" => "#ffffff"],
  "pink"             => ["title" => "Pink", "hexcode" => "#E91E63", "color" => "#ffffff"],
  "purple"           => ["title" => "Purple", "hexcode" => "#9C27B0", "color" => "#ffffff"],
  "deep-purple"      => ["title" => "Deep Purple", "hexcode" => "#673AB7", "color" => "#ffffff"],
  "olive"            => ["title" => "Olive", "hexcode" => "#808000", "color" => "#ffffff"],
  "indigo"           => ["title" => "Indigo", "hexcode" => "#3F51B5", "color" => "#ffffff"],
  "cyan"             => ["title" => "Cyan", "hexcode" => "#00BCD4", "color" => "#ffffff"],
  "teal"             => ["title" => "Teal", "hexcode" => "#009688", "color" => "#ffffff"],
  "lime"             => ["title" => "Lime", "hexcode" => "#CDDC39", "color" => "#000"],
  "carrot"           => ["title" => "Carrot", "hexcode" => "#e67e22", "color" => "#ffffff"],
  "pumpkin"          => ["title" => "Pumpkin", "hexcode" => "#d35400", "color" => "#ffffff"],
  "coffee"           => ["title" => "Coffee", "hexcode" => "#45362E", "color" => "#ffffff"],
  "orange"           => ["title" => "Orange", "hexcode" => "#FF9800", "color" => "#000"],
  "deep-orange"      => ["title" => "Deep Orange", "hexcode" => "#FF5722", "color" => "#ffffff"],
  "brown"            => ["title" => "Brown", "hexcode" => "#795548", "color" => "#ffffff"],
  "black"            => ["title" => "Black", "hexcode" => "#000", "color" => "#ffffff"],
  "white"            => ["title" => "White", "hexcode" => "#ffffff", "color" => "#000"]
];