<?php
declare(strict_types=1);
/**
 * Best-effort public-IP ASN information. Never claims to identify a SIM, Wi-Fi
 * router, exact mobile plan, or the owner of the user's subscription.
 * IPinfo Lite (free token) is recommended; IPinfo legacy public lookup is
 * used when no token is configured and may be rate limited.
 */
require_once dirname(__DIR__) . '/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, max-age=180');
header('X-Content-Type-Options: nosniff');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') { http_response_code(405);header('Allow: GET');echo json_encode(['ok'=>false]);exit; }
function publicIp(string $input): bool {
    return filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)!==false;
}
function visitorIp(): ?string {
    // Render proxies supply the originating IP in X-Forwarded-For. Use only
    // for display-related lookups, never for authorization or security.
    $candidates = explode(',',(string)($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''));
    $candidates[] = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    foreach ($candidates as $candidate) { $candidate=trim($candidate);if(publicIp($candidate))return $candidate; }
    return null;
}
function lookup(string $url): ?array {
    $context=stream_context_create(['http'=>['timeout'=>4,'ignore_errors'=>true,'header'=>"Accept: application/json\r\nUser-Agent: LineSpeed/3.0\r\n"]]);
    $body=@file_get_contents($url,false,$context);
    if($body===false||strlen($body)>10000)return null;
    $data=json_decode($body,true);
    return is_array($data) && empty($data['error']) ? $data : null;
}
function friendlyProvider(string $org): string {
    $known=[
        '/bharti.*airtel|airtel/i'=>'Airtel',
        '/reliance.*jio|jio.*infocomm|jio.*platforms/i'=>'Jio',
        '/excitel/i'=>'Excitel',
        '/bsnl|bharat sanchar/i'=>'BSNL',
        '/vodafone.*idea|idea cellular|vi india/i'=>'Vi (Vodafone Idea)',
        '/act fibernet|atria convergence/i'=>'ACT Fibernet',
        '/tata play|tata communications/i'=>'Tata',
        '/hathway/i'=>'Hathway',
        '/spectra/i'=>'Spectra',
        '/railwire|railtel/i'=>'RailWire',
        '/verizon/i'=>'Verizon',
        '/comcast|xfinity/i'=>'Xfinity / Comcast',
        '/at&t/i'=>'AT&T',
        '/t.mobile/i'=>'T-Mobile',
        '/starlink/i'=>'Starlink',
    ];
    foreach($known as $pattern=>$name)if(preg_match($pattern,$org))return $name;
    return trim(preg_replace('/^AS\d+\s*/','',$org));
}
$ip=visitorIp();
if($ip===null){echo json_encode(['ok'=>false,'provider'=>null,'country'=>null,'source'=>null]);exit;}
session_name('linespeed_session');
if(session_status()!==PHP_SESSION_ACTIVE)@session_start();
$cache=$_SESSION['network_info']??null;
$force=($_GET['refresh']??'')==='1';
if(is_array($cache)&&($cache['ip']??'')===$ip&&time()-(int)($cache['time']??0)<($force?120:600)){
    $data=$cache['data'];
    session_write_close();
    echo json_encode($data,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);exit;
}
// Avoid holding the PHP session lock during external requests.
session_write_close();
$token=trim((string)(getenv('IPINFO_TOKEN')?:''));
$info=null;
if($token!=='')$info=lookup('https://api.ipinfo.io/lite/'.rawurlencode($ip).'?token='.rawurlencode($token));
if(!$info)$info=lookup('https://ipinfo.io/'.rawurlencode($ip).'/json');
$rawOrg=trim((string)($info['as_name']??$info['org']??''));
$org=($rawOrg!==''&&strlen($rawOrg)<180)?friendlyProvider($rawOrg):null;
$country=trim((string)($info['country_name']??$info['country']??''));
if(strlen($country)===2&&ctype_alpha($country)){
    // In legacy IPinfo the country field is a 2-letter code.
    $countryCodes=['IN'=>'India','US'=>'United States','GB'=>'United Kingdom','CA'=>'Canada','AU'=>'Australia','DE'=>'Germany','FR'=>'France','BR'=>'Brazil','JP'=>'Japan','CN'=>'China','BD'=>'Bangladesh','PK'=>'Pakistan','AE'=>'United Arab Emirates','NG'=>'Nigeria','ZA'=>'South Africa','ID'=>'Indonesia','IT'=>'Italy','ES'=>'Spain','TR'=>'Türkiye','VN'=>'Vietnam','PH'=>'Philippines','NL'=>'Netherlands'];
    $country=$countryCodes[strtoupper($country)]??strtoupper($country);
}
$data=['ok'=>$org!==null,'provider'=>$org,'country'=>$country!==''?$country:null,'source'=>$info?'IPinfo':null];
if(session_status()!==PHP_SESSION_ACTIVE)@session_start();
$_SESSION['network_info']=['ip'=>$ip,'time'=>time(),'data'=>$data];
session_write_close();
echo json_encode($data,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
