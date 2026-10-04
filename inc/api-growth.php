<?php
/**
 * Hardened endpoints for Uptime Fixer 3.0 growth tools.
 *
 * @package UptimeFixer
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$ufx_growth_endpoints = array(
    'growth_rdap', 'growth_dnssec', 'growth_mx', 'growth_reverse_dns', 'growth_ip_asn',
    'growth_protocol', 'growth_cdn_cache', 'growth_cookies', 'growth_bulk_status',
    'growth_sitemap', 'growth_schema', 'growth_html_validate', 'growth_lighthouse',
    'growth_page_size', 'growth_ttfb', 'growth_email', 'growth_seo', 'growth_ai_text',
    'growth_ai_image', 'growth_transcription', 'growth_speech',
);
foreach ( $ufx_growth_endpoints as $ufx_growth_endpoint ) {
    add_action( 'wp_ajax_ufx_' . $ufx_growth_endpoint, 'ufx_api_' . $ufx_growth_endpoint );
    add_action( 'wp_ajax_nopriv_ufx_' . $ufx_growth_endpoint, 'ufx_api_' . $ufx_growth_endpoint );
}
unset( $ufx_growth_endpoint, $ufx_growth_endpoints );

function ufx_growth_post( $key, $limit = 500 ) {
    $value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
    return alltools_text_slice( trim( $value ), 0, $limit );
}

function ufx_growth_domain_input( $key = 'value', $resolve = false ) {
    return ufx_extra_domain( ufx_growth_post( $key, 300 ), $resolve );
}

function ufx_growth_url_input( $key = 'value' ) {
    return ufx_clean_url( ufx_growth_post( $key, 2000 ) );
}

/** Additional cost guard for commercial APIs on top of the public endpoint throttle. */
function ufx_growth_paid_limit( $provider, $daily_max = 40 ) {
    $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
    $hour_key = 'ufx_paid_h_' . substr( hash_hmac( 'sha256', $provider . '|' . $ip, wp_salt( 'nonce' ) ), 0, 28 );
    $day_key = 'ufx_paid_d_' . substr( hash_hmac( 'sha256', $provider . '|' . gmdate( 'Y-m-d' ), wp_salt( 'nonce' ) ), 0, 28 );
    $hour_count = (int) get_transient( $hour_key );
    $day_count = (int) get_transient( $day_key );
    if ( $hour_count >= 3 || $day_count >= $daily_max ) {
        wp_send_json_error( array( 'message' => 'The secure API quota is currently reached. Please try again later.' ), 429 );
    }
    set_transient( $hour_key, $hour_count + 1, HOUR_IN_SECONDS );
    set_transient( $day_key, $day_count + 1, DAY_IN_SECONDS );
}

function ufx_growth_clean_rows( $rows, $max = 100 ) {
    $out = array();
    foreach ( array_slice( (array) $rows, 0, $max ) as $row ) {
        if ( ! is_array( $row ) ) continue;
        $clean = array();
        foreach ( array_slice( $row, 0, 8, true ) as $key => $value ) {
            if ( is_scalar( $value ) || null === $value ) $clean[ sanitize_key( $key ) ] = sanitize_text_field( (string) $value );
        }
        if ( $clean ) $out[] = $clean;
    }
    return $out;
}

function ufx_api_growth_rdap() {
    ufx_check_nonce();
    $domain = ufx_growth_domain_input( 'value', false );
    if ( ! $domain ) wp_send_json_error( array( 'message' => 'Enter a valid public domain.' ), 400 );
    $res = wp_safe_remote_get( 'https://rdap.org/domain/' . rawurlencode( $domain ), array( 'timeout'=>15, 'redirection'=>3, 'limit_response_size'=>1048576, 'headers'=>array('Accept'=>'application/rdap+json'), 'user-agent'=>'UptimeFixer/3.0 RDAP' ) );
    if ( is_wp_error( $res ) ) wp_send_json_error( array( 'message'=>'RDAP lookup failed.' ), 502 );
    $data = json_decode( wp_remote_retrieve_body( $res ), true );
    if ( ! is_array( $data ) ) wp_send_json_error( array( 'message'=>'The RDAP provider returned an invalid response.' ), 502 );
    $events = array();
    foreach ( isset( $data['events'] ) ? (array) $data['events'] : array() as $event ) if ( ! empty( $event['eventAction'] ) && ! empty( $event['eventDate'] ) ) $events[] = array( 'label'=>ucwords( str_replace( '_', ' ', $event['eventAction'] ) ), 'value'=>$event['eventDate'] );
    $rows = array(
        array( 'label'=>'Domain', 'value'=>isset($data['ldhName'])?$data['ldhName']:$domain ),
        array( 'label'=>'Status', 'value'=>!empty($data['status'])?implode(', ',(array)$data['status']):'Not provided' ),
        array( 'label'=>'Registrar / Handle', 'value'=>isset($data['handle'])?$data['handle']:'Not provided' ),
    );
    wp_send_json_success( array( 'results'=>array_merge($rows,$events), 'notice'=>'RDAP displays public registration data only.' ) );
}

function ufx_api_growth_dnssec() {
    ufx_check_nonce(); $domain = ufx_growth_domain_input( 'value', false );
    if ( ! $domain ) wp_send_json_error( array( 'message'=>'Enter a valid domain.' ), 400 );
    $records = dns_get_record( $domain, DNS_ANY ); $ds = array(); $keys = array();
    foreach ( is_array($records)?$records:array() as $record ) {
        if ( isset($record['type']) && 'DS' === strtoupper($record['type']) ) $ds[] = isset($record['digest'])?$record['digest']:'DS record found';
        if ( isset($record['type']) && 'DNSKEY' === strtoupper($record['type']) ) $keys[] = 'DNSKEY record found';
    }
    if ( defined('DNS_DS') ) foreach ( (array) dns_get_record( $domain, constant('DNS_DS') ) as $record ) if ( ! empty($record['digest']) ) $ds[] = $record['digest'];
    if ( defined('DNS_DNSKEY') ) foreach ( (array) dns_get_record( $domain, constant('DNS_DNSKEY') ) as $record ) $keys[] = 'DNSKEY record found';
    wp_send_json_success( array( 'results'=>array(
        array('label'=>'DNSSEC delegation (DS)','value'=>$ds?'Present':'Not detected'),
        array('label'=>'DNSKEY','value'=>$keys?'Present':'Not detected'),
        array('label'=>'Assessment','value'=>$ds?'DNSSEC appears delegated; validate the chain with your DNS provider.':'No DS delegation was detected.'),
    ) ) );
}

function ufx_api_growth_mx() {
    ufx_check_nonce(); $domain=ufx_growth_domain_input('value',false);
    if(!$domain)wp_send_json_error(array('message'=>'Enter a valid domain.'),400);
    $hosts=array();$weights=array();$ok=getmxrr($domain,$hosts,$weights);$rows=array();
    if($ok)foreach($hosts as $i=>$host)$rows[]=array('label'=>'Priority '.(isset($weights[$i])?(int)$weights[$i]:'—'),'value'=>sanitize_text_field($host));
    wp_send_json_success(array('results'=>$rows?$rows:array(array('label'=>'MX records','value'=>'None found'))));
}

function ufx_api_growth_reverse_dns() {
    ufx_check_nonce(); $ip=ufx_growth_post('value',80);
    if(!filter_var($ip,FILTER_VALIDATE_IP)||!ufx_ip_is_public($ip))wp_send_json_error(array('message'=>'Enter a valid public IP address.'),400);
    $host=gethostbyaddr($ip); wp_send_json_success(array('results'=>array(array('label'=>'IP address','value'=>$ip),array('label'=>'PTR hostname','value'=>$host&&$host!==$ip?$host:'No PTR record found'))));
}

function ufx_api_growth_ip_asn() {
    ufx_check_nonce(); $ip=ufx_growth_post('value',80);
    if(!filter_var($ip,FILTER_VALIDATE_IP)||!ufx_ip_is_public($ip))wp_send_json_error(array('message'=>'Enter a valid public IP address.'),400);
    $res=wp_safe_remote_get('https://ipwho.is/'.rawurlencode($ip).'?fields=success,ip,type,continent,country,region,city,connection,timezone',array('timeout'=>10,'limit_response_size'=>262144,'user-agent'=>'UptimeFixer/3.0 ASN'));
    $data=is_wp_error($res)?array():json_decode(wp_remote_retrieve_body($res),true);$conn=isset($data['connection'])&&is_array($data['connection'])?$data['connection']:array();
    wp_send_json_success( array(
        'results' => array(
            array( 'label'=>'IP', 'value'=>$ip ),
            array( 'label'=>'ASN', 'value'=>isset($conn['asn'])?$conn['asn']:'Unknown' ),
            array( 'label'=>'ISP', 'value'=>isset($conn['isp'])?$conn['isp']:'Unknown' ),
            array( 'label'=>'Organization', 'value'=>isset($conn['org'])?$conn['org']:'Unknown' ),
            array( 'label'=>'Approximate region', 'value'=>implode(', ',array_filter(array(isset($data['city'])?$data['city']:'',isset($data['region'])?$data['region']:'',isset($data['country'])?$data['country']:''))) ),
        ),
        'notice' => 'Location is approximate and derived from public IP allocation data.',
    ) );
}

function ufx_growth_head_response() {
    $url=ufx_growth_url_input('value'); if(!$url)wp_send_json_error(array('message'=>'Enter a valid public HTTP or HTTPS URL.'),400);
    $res=ufx_safe_remote_head($url,array('timeout'=>12,'redirection'=>4,'user-agent'=>'UptimeFixer/3.0 Web Audit'));
    if(is_wp_error($res))$res=ufx_safe_remote_get($url,array('timeout'=>12,'redirection'=>4,'limit_response_size'=>1048576,'user-agent'=>'UptimeFixer/3.0 Web Audit'));
    if(is_wp_error($res))wp_send_json_error(array('message'=>'The public page could not be reached.'),502);
    return array($url,$res);
}

function ufx_api_growth_protocol() {
    ufx_check_nonce(); list($url,$res)=ufx_growth_head_response(); $alt=(string)wp_remote_retrieve_header($res,'alt-svc');$version='Not exposed by the server transport';
    if(isset($res['http_response'])&&is_object($res['http_response'])&&method_exists($res['http_response'],'get_response_object')){$obj=$res['http_response']->get_response_object();if(is_object($obj)&&isset($obj->http_version))$version=(string)$obj->http_version;}
    wp_send_json_success(array('results'=>array(array('label'=>'Observed HTTP version','value'=>$version),array('label'=>'HTTP/3 Alt-Svc hint','value'=>false!==stripos($alt,'h3')?$alt:'Not advertised'),array('label'=>'Status','value'=>wp_remote_retrieve_response_code($res))),'notice'=>'A missing Alt-Svc header does not prove that HTTP/3 is disabled on every edge.'));
}

function ufx_api_growth_cdn_cache() {
    ufx_check_nonce(); list($url,$res)=ufx_growth_head_response();$h=ufx_extra_headers_array($res);$signals=array('Cloudflare'=>array('cf-ray','cf-cache-status'),'Fastly'=>array('x-served-by','x-cache-hits'),'Akamai'=>array('akamai-grn','x-akamai-transformed'),'CloudFront'=>array('x-amz-cf-id','x-amz-cf-pop'),'Vercel'=>array('x-vercel-id'),'Netlify'=>array('x-nf-request-id'));$found=array();
    foreach($signals as $name=>$keys)foreach($keys as $key)if(isset($h[$key])){$found[]=$name;break;}
    wp_send_json_success(array('results'=>array(array('label'=>'Detected CDN','value'=>$found?implode(', ',array_unique($found)):'No common CDN signature detected'),array('label'=>'Cache-Control','value'=>isset($h['cache-control'])?$h['cache-control']:'Missing'),array('label'=>'Age','value'=>isset($h['age'])?$h['age']:'Not provided'),array('label'=>'Vary','value'=>isset($h['vary'])?$h['vary']:'Not provided'))));
}

function ufx_api_growth_cookies() {
    ufx_check_nonce(); list($url,$res)=ufx_growth_head_response();$raw=wp_remote_retrieve_header($res,'set-cookie');$cookies=is_array($raw)?$raw:($raw?array($raw):array());$rows=array();
    foreach(array_slice($cookies,0,50) as $cookie){$parts=array_map('trim',explode(';',(string)$cookie));$pair=array_shift($parts);$name=strtok($pair,'=');$flags=array();foreach($parts as $part){$low=strtolower($part);if(in_array($low,array('secure','httponly','samesite=lax','samesite=strict','samesite=none'),true)||0===strpos($low,'max-age=')||0===strpos($low,'expires='))$flags[]=$part;}$rows[]=array('label'=>sanitize_key($name),'value'=>$flags?implode('; ',$flags):'No common security flags detected');}
    wp_send_json_success(array('results'=>$rows?$rows:array(array('label'=>'Initial response','value'=>'No Set-Cookie header detected')),'notice'=>'This checks only cookies in the initial server response; JavaScript and consent-triggered cookies require an interactive browser scan.'));
}

function ufx_api_growth_bulk_status() {
    ufx_check_nonce();$raw=isset($_POST['text'])?sanitize_textarea_field(wp_unslash($_POST['text'])):'';$lines=array_values(array_unique(array_filter(array_map('trim',preg_split('/\r?\n/',$raw)))));
    if(!$lines)wp_send_json_error(array('message'=>'Enter at least one public URL.'),400);$rows=array();
    foreach(array_slice($lines,0,10) as $line){$url=ufx_clean_url($line);if(!$url){$rows[]=array('label'=>alltools_text_slice($line,0,120),'value'=>'Invalid or non-public URL');continue;}$start=microtime(true);$res=ufx_safe_remote_head($url,array('timeout'=>6,'redirection'=>3,'user-agent'=>'UptimeFixer/3.2 Bulk'));$ms=(int)round((microtime(true)-$start)*1000);$rows[]=array('label'=>$url,'value'=>is_wp_error($res)?'Request failed':wp_remote_retrieve_response_code($res).' · '.$ms.' ms');}
    wp_send_json_success(array('results'=>$rows,'notice'=>'A maximum of 10 URLs is checked per run to protect server resources.'));
}

function ufx_api_growth_sitemap() {
    ufx_check_nonce();$url=ufx_growth_url_input('value');if(!$url)wp_send_json_error(array('message'=>'Enter a valid public sitemap URL.'),400);
    $res=ufx_safe_remote_get($url,array('timeout'=>15,'redirection'=>3,'limit_response_size'=>2097152,'user-agent'=>'UptimeFixer/3.0 Sitemap'));if(is_wp_error($res))wp_send_json_error(array('message'=>'The sitemap could not be downloaded.'),502);$body=wp_remote_retrieve_body($res);
    if(false!==stripos($body,'<!DOCTYPE'))wp_send_json_error(array('message'=>'Sitemaps containing a DOCTYPE declaration are rejected for security.'),400);
    if(!function_exists('simplexml_load_string'))wp_send_json_error(array('message'=>'This server needs the PHP SimpleXML extension for sitemap validation.'),503);libxml_use_internal_errors(true);$xml=simplexml_load_string($body,'SimpleXMLElement',LIBXML_NONET|LIBXML_NOCDATA);$errors=libxml_get_errors();libxml_clear_errors();if(false===$xml)wp_send_json_success(array('results'=>array(array('label'=>'XML status','value'=>'Invalid'),array('label'=>'First error','value'=>$errors?trim($errors[0]->message):'Could not parse XML'))));
    $root=$xml->getName();$locs=$xml->xpath('//*[local-name()="loc"]');$invalid=0;$sample=array_slice((array)$locs,0,500);foreach($sample as $loc){$value=trim((string)$loc);if(!filter_var($value,FILTER_VALIDATE_URL)||!preg_match('#^https?://#i',$value))$invalid++;}
    wp_send_json_success(array('results'=>array(array('label'=>'XML status','value'=>'Valid XML'),array('label'=>'Sitemap type','value'=>$root),array('label'=>'URL entries','value'=>count((array)$locs)),array('label'=>'Invalid URL entries in sample','value'=>$invalid.' of '.count($sample)),array('label'=>'Uncompressed size','value'=>size_format(strlen($body),1))),'notice'=>'URL syntax validation samples the first 500 entries to protect server resources.'));
}

function ufx_api_growth_schema() {
    ufx_check_nonce();$url=ufx_growth_url_input('value');if(!$url)wp_send_json_error(array('message'=>'Enter a valid public page URL.'),400);$res=ufx_safe_remote_get($url,array('timeout'=>15,'redirection'=>3,'limit_response_size'=>1048576,'user-agent'=>'UptimeFixer/3.0 Schema'));if(is_wp_error($res))wp_send_json_error(array('message'=>'The page could not be downloaded.'),502);$body=wp_remote_retrieve_body($res);$rows=array();$errors=0;
    if(preg_match_all('#<script[^>]+type=["\']application/ld\+json["\'][^>]*>(.*?)</script>#is',$body,$matches))foreach($matches[1] as $i=>$json){$data=json_decode(html_entity_decode(trim($json),ENT_QUOTES|ENT_HTML5),true);if(JSON_ERROR_NONE!==json_last_error()){$errors++;$rows[]=array('label'=>'Block '.($i+1),'value'=>'Invalid JSON: '.json_last_error_msg());continue;}$types=array();$walk=function($v)use(&$walk,&$types){if(!is_array($v))return;if(isset($v['@type']))$types=array_merge($types,(array)$v['@type']);foreach($v as $child)if(is_array($child))$walk($child);};$walk($data);$rows[]=array('label'=>'Block '.($i+1),'value'=>$types?'Valid JSON-LD · '.implode(', ',array_unique($types)):'Valid JSON-LD · no @type found');}
    if(!$rows)$rows[]=array('label'=>'JSON-LD','value'=>'No JSON-LD blocks found');wp_send_json_success(array('results'=>$rows,'notice'=>'This validates JSON syntax and reported types. Use Google Rich Results Test for Google-specific eligibility rules.'));
}

function ufx_api_growth_html_validate() {
    ufx_check_nonce();$url=ufx_growth_url_input('value');if(!$url)wp_send_json_error(array('message'=>'Enter a valid public page URL.'),400);$endpoint='https://validator.w3.org/nu/?out=json&doc='.rawurlencode($url);$res=wp_safe_remote_get($endpoint,array('timeout'=>20,'limit_response_size'=>1048576,'headers'=>array('Accept'=>'application/json'),'user-agent'=>'UptimeFixer/3.0 Validator'));if(is_wp_error($res))wp_send_json_error(array('message'=>'The validation service is unavailable.'),502);$data=json_decode(wp_remote_retrieve_body($res),true);$rows=array();foreach(array_slice(isset($data['messages'])?(array)$data['messages']:array(),0,50) as $m)$rows[]=array('label'=>isset($m['type'])?strtoupper($m['type']):'MESSAGE','value'=>(isset($m['lastLine'])?'Line '.$m['lastLine'].': ':'').(isset($m['message'])?$m['message']:'Validation message'));
    wp_send_json_success(array('results'=>$rows?$rows:array(array('label'=>'HTML validation','value'=>'No errors reported')),'notice'=>'Results are supplied by the public Nu HTML validation service.'));
}

function ufx_growth_pagespeed_request( $categories ) {
    $url=ufx_growth_url_input('value');if(!$url)wp_send_json_error(array('message'=>'Enter a valid public page URL.'),400);
    return array('url'=>$url,'data'=>ufx_pagespeed_data($url,'mobile',(array)$categories));
}

/** Basic HTML accessibility facts used only when Lighthouse is unavailable. */
function ufx_growth_accessibility_fallback_rows( $snapshot ) {
    $body=isset($snapshot['body'])?(string)$snapshot['body']:'';$images=array();$missing_alt=0;$h1=array();
    preg_match_all('#<img\b[^>]*>#i',$body,$images);foreach(isset($images[0])?$images[0]:array() as $tag)if(!preg_match('/\balt\s*=\s*(["\']).*?\1/is',$tag))$missing_alt++;
    preg_match_all('#<h1\b[^>]*>#i',$body,$h1);$has_lang=(bool)preg_match('/<html\b[^>]*\blang\s*=\s*["\'][^"\']+["\']/i',$body);$has_title=(bool)preg_match('/<title[^>]*>\s*[^<]+\s*<\/title>/is',$body);$has_viewport=(bool)preg_match('/<meta[^>]+name=["\']viewport["\']/i',$body);
    return array(
        array('label'=>'Audit mode','value'=>'Basic HTML accessibility fallback (not Lighthouse)'),
        array('label'=>'HTTP status','value'=>(string)$snapshot['code']),
        array('label'=>'HTML language','value'=>$has_lang?'Declared':'Missing'),
        array('label'=>'Page title','value'=>$has_title?'Found':'Missing'),
        array('label'=>'Mobile viewport','value'=>$has_viewport?'Found':'Missing'),
        array('label'=>'H1 headings','value'=>(string)count(isset($h1[0])?$h1[0]:array())),
        array('label'=>'Images without alt attribute','value'=>$missing_alt.' of '.count(isset($images[0])?$images[0]:array())),
    );
}

function ufx_api_growth_lighthouse() {
    ufx_check_nonce();$mode=sanitize_key(ufx_growth_post('mode',80));$only_accessibility='accessibility-checker'===$mode;$request=ufx_growth_pagespeed_request($only_accessibility?array('accessibility'):array('performance','accessibility','best-practices','seo'));$data=$request['data'];$rows=array();
    if(is_wp_error($data)){$snapshot=ufx_public_page_snapshot($request['url']);if(is_wp_error($snapshot))wp_send_json_error(array('message'=>$data->get_error_message().' The fallback page check also failed: '.$snapshot->get_error_message()),502);$rows=$only_accessibility?ufx_growth_accessibility_fallback_rows($snapshot):ufx_pagespeed_fallback_results($snapshot);$has_key=alltools_integration_secret('pagespeed_api_key');wp_send_json_success(array('results'=>$rows,'source'=>'server-fallback','notice'=>'Google Lighthouse is temporarily unavailable. A live basic page audit is shown instead; it does not invent Lighthouse or accessibility scores. '.($has_key?'The configured PageSpeed key may be rate-limited; try again later.':'Add a PageSpeed API key under Uptime Fixer → Theme Settings → API Integrations for more reliable lab reports.')));}
    $report=$data['lighthouseResult'];
    if($only_accessibility){$score=isset($report['categories']['accessibility']['score'])?(int)round($report['categories']['accessibility']['score']*100):0;$rows[]=array('label'=>'Accessibility score','value'=>$score.'/100');foreach(isset($report['audits'])?(array)$report['audits']:array() as $audit){if(count($rows)>=16)break;if(isset($audit['score'])&&is_numeric($audit['score'])&&(float)$audit['score']<1&&!empty($audit['title']))$rows[]=array('label'=>$audit['title'],'value'=>!empty($audit['displayValue'])?$audit['displayValue']:'Needs review');}}
    else foreach(array('performance'=>'Performance','accessibility'=>'Accessibility','best-practices'=>'Best practices','seo'=>'SEO') as $key=>$label){$score=isset($report['categories'][$key]['score'])?(int)round($report['categories'][$key]['score']*100):0;$rows[]=array('label'=>$label,'value'=>$score.'/100');}
    wp_send_json_success(array('results'=>$rows,'source'=>isset($data['_ufx_source'])?$data['_ufx_source']:'pagespeed','notice'=>isset($data['_ufx_notice'])?$data['_ufx_notice']:''));
}

function ufx_api_growth_page_size() {
    ufx_check_nonce();$url=ufx_growth_url_input('value');if(!$url)wp_send_json_error(array('message'=>'Enter a valid public page URL.'),400);$res=ufx_safe_remote_get($url,array('timeout'=>15,'redirection'=>3,'limit_response_size'=>2097152,'user-agent'=>'UptimeFixer/3.0 Page Size'));if(is_wp_error($res))wp_send_json_error(array('message'=>'The page could not be downloaded.'),502);$body=wp_remote_retrieve_body($res);preg_match_all('#<img\b#i',$body,$imgs);preg_match_all('#<script\b#i',$body,$scripts);preg_match_all('#<link[^>]+rel=["\']stylesheet["\']#i',$body,$styles);wp_send_json_success(array('results'=>array(array('label'=>'Downloaded HTML','value'=>size_format(strlen($body),1)),array('label'=>'Images referenced','value'=>count($imgs[0])),array('label'=>'Scripts referenced','value'=>count($scripts[0])),array('label'=>'Stylesheets referenced','value'=>count($styles[0]))),'notice'=>'This measures the initial HTML and reference counts, not the full browser transfer size.'));
}

function ufx_api_growth_ttfb() {
    ufx_check_nonce();$url=ufx_growth_url_input('value');if(!$url)wp_send_json_error(array('message'=>'Enter a valid public URL.'),400);$start=microtime(true);$res=ufx_safe_remote_get($url,array('timeout'=>15,'redirection'=>0,'limit_response_size'=>1,'user-agent'=>'UptimeFixer/3.0 TTFB'));$ms=(int)round((microtime(true)-$start)*1000);if(is_wp_error($res))wp_send_json_error(array('message'=>'The initial response failed.'),502);wp_send_json_success(array('results'=>array(array('label'=>'Approximate TTFB','value'=>$ms.' ms'),array('label'=>'HTTP status','value'=>wp_remote_retrieve_response_code($res)),array('label'=>'Server-Timing','value'=>wp_remote_retrieve_header($res,'server-timing')?:'Not provided')),'notice'=>'Measurement includes this server’s network latency and DNS/TLS setup.'));
}

function ufx_api_growth_email() {
    ufx_check_nonce();$domain=ufx_growth_domain_input('value',false);if(!$domain)wp_send_json_error(array('message'=>'Enter a valid domain.'),400);$hosts=array();$weights=array();getmxrr($domain,$hosts,$weights);$txt=ufx_dns_txt_values($domain);$spf=array_values(array_filter($txt,function($v){return 0===stripos($v,'v=spf1');}));$dmarc=ufx_dns_txt_values('_dmarc.'.$domain);$score=0;if($hosts)$score+=40;if($spf)$score+=30;if($dmarc)$score+=30;wp_send_json_success(array('results'=>array(array('label'=>'MX records','value'=>$hosts?count($hosts).' found':'Missing'),array('label'=>'SPF','value'=>$spf?$spf[0]:'Missing'),array('label'=>'DMARC','value'=>$dmarc?$dmarc[0]:'Missing'),array('label'=>'DNS readiness score','value'=>$score.'/100')),'notice'=>'This checks DNS readiness only; it does not send mail or guarantee inbox placement.'));
}

function ufx_growth_dataforseo( $path, $payload ) {
    $login=alltools_integration_secret('dataforseo_login');$password=alltools_integration_secret('dataforseo_password');if(!$login||!$password)wp_send_json_error(array('message'=>'DataForSEO is not configured. Add credentials under Uptime Fixer → Theme Settings → API Integrations.'),503);ufx_growth_paid_limit('dataforseo',50);$res=wp_safe_remote_post('https://api.dataforseo.com/v3/'.ltrim($path,'/'),array('timeout'=>35,'sslverify'=>true,'reject_unsafe_urls'=>true,'limit_response_size'=>2097152,'headers'=>array('Authorization'=>'Basic '.base64_encode($login.':'.$password),'Content-Type'=>'application/json'),'body'=>wp_json_encode(array($payload)),'user-agent'=>'UptimeFixer/3.0 SEO'));
    if(is_wp_error($res))wp_send_json_error(array('message'=>'The SEO data provider request failed.'),502);$data=json_decode(wp_remote_retrieve_body($res),true);if(!is_array($data))wp_send_json_error(array('message'=>'The SEO provider returned invalid data.'),502);if(isset($data['status_code'])&&20000!==(int)$data['status_code'])wp_send_json_error(array('message'=>isset($data['status_message'])?sanitize_text_field($data['status_message']):'SEO provider error.'),502);$task=isset($data['tasks'][0])?$data['tasks'][0]:array();if(isset($task['status_code'])&&20000!==(int)$task['status_code'])wp_send_json_error(array('message'=>isset($task['status_message'])?sanitize_text_field($task['status_message']):'SEO provider task failed.'),502);return isset($task['result'][0])?$task['result'][0]:array();
}

function ufx_growth_dfs_keyword_rows( $result, $max=30 ) {
    $rows=array();foreach(array_slice(isset($result['items'])?(array)$result['items']:array(),0,$max) as $item){$kd=isset($item['keyword_data'])?$item['keyword_data']:$item;$keyword=isset($kd['keyword'])?$kd['keyword']:(isset($item['keyword'])?$item['keyword']:'');$info=isset($kd['keyword_info'])?$kd['keyword_info']:array();$props=isset($kd['keyword_properties'])?$kd['keyword_properties']:array();if($keyword)$rows[]=array('label'=>$keyword,'value'=>'Volume: '.(isset($info['search_volume'])?$info['search_volume']:'—').' · Difficulty: '.(isset($props['keyword_difficulty'])?$props['keyword_difficulty']:'—').' · CPC: '.(isset($info['cpc'])?$info['cpc']:'—'));}return $rows;
}

function ufx_api_growth_seo() {
    ufx_check_nonce();$mode=sanitize_key(ufx_growth_post('mode',80));$value=ufx_growth_post('value',300);$second=ufx_growth_post('second',300);if(!$value)wp_send_json_error(array('message'=>'Enter a keyword or domain.'),400);$common=array('location_code'=>2840,'language_code'=>'en');$rows=array();
    if('keyword-generator'===$mode){$r=ufx_growth_dataforseo('dataforseo_labs/google/keyword_suggestions/live',array_merge($common,array('keyword'=>$value,'limit'=>40)));$rows=ufx_growth_dfs_keyword_rows($r,40);}
    elseif('keyword-difficulty-checker'===$mode){$r=ufx_growth_dataforseo('dataforseo_labs/google/keyword_overview/live',array_merge($common,array('keywords'=>array($value))));$rows=ufx_growth_dfs_keyword_rows($r,10);}
    elseif(in_array($mode,array('backlink-checker','domain-authority-checker'),true)){$domain=ufx_extra_domain($value,false);if(!$domain)wp_send_json_error(array('message'=>'Enter a valid domain.'),400);$r=ufx_growth_dataforseo('backlinks/summary/live',array('target'=>$domain,'include_subdomains'=>true));$rows=array(array('label'=>'Backlinks','value'=>isset($r['backlinks'])?$r['backlinks']:'—'),array('label'=>'Referring domains','value'=>isset($r['referring_domains'])?$r['referring_domains']:'—'),array('label'=>'Backlink rank','value'=>isset($r['rank'])?$r['rank']:'—'),array('label'=>'Spam score','value'=>isset($r['backlinks_spam_score'])?$r['backlinks_spam_score']:'—'));}
    elseif('keyword-rank-checker'===$mode){$domain=ufx_extra_domain($value,false);if(!$domain)wp_send_json_error(array('message'=>'Enter a valid domain.'),400);$r=ufx_growth_dataforseo('dataforseo_labs/google/ranked_keywords/live',array_merge($common,array('target'=>$domain,'limit'=>30)));foreach(array_slice(isset($r['items'])?(array)$r['items']:array(),0,30) as $item){$kd=isset($item['keyword_data'])?$item['keyword_data']:array();$serp=isset($item['ranked_serp_element']['serp_item'])?$item['ranked_serp_element']['serp_item']:array();if(!empty($kd['keyword']))$rows[]=array('label'=>$kd['keyword'],'value'=>'Position '.(isset($serp['rank_absolute'])?$serp['rank_absolute']:'—').' · Volume '.(isset($kd['keyword_info']['search_volume'])?$kd['keyword_info']['search_volume']:'—'));}}
    elseif('website-traffic-estimator'===$mode){$domain=ufx_extra_domain($value,false);if(!$domain)wp_send_json_error(array('message'=>'Enter a valid domain.'),400);$r=ufx_growth_dataforseo('dataforseo_labs/google/domain_rank_overview/live',array_merge($common,array('target'=>$domain)));$organic=isset($r['metrics']['organic'])?$r['metrics']['organic']:array();$paid=isset($r['metrics']['paid'])?$r['metrics']['paid']:array();$rows=array(array('label'=>'Estimated organic traffic','value'=>isset($organic['etv'])?round($organic['etv']):'—'),array('label'=>'Organic ranking keywords','value'=>isset($organic['count'])?$organic['count']:'—'),array('label'=>'Estimated paid traffic','value'=>isset($paid['etv'])?round($paid['etv']):'—'));}
    elseif('competitor-keyword-checker'===$mode){$a=ufx_extra_domain($value,false);$b=ufx_extra_domain($second,false);if(!$a||!$b)wp_send_json_error(array('message'=>'Enter two valid domains.'),400);$r=ufx_growth_dataforseo('dataforseo_labs/google/domain_intersection/live',array_merge($common,array('target1'=>$a,'target2'=>$b,'intersections'=>true,'limit'=>30)));$rows=ufx_growth_dfs_keyword_rows($r,30);}
    else wp_send_json_error(array('message'=>'Unsupported SEO data mode.'),400);
    wp_send_json_success(array('results'=>$rows?$rows:array(array('label'=>'Results','value'=>'No matching data was returned.')),'notice'=>'Commercial search metrics are estimates and may differ from first-party analytics.'));
}

function ufx_growth_openai_text( $prompt, $max_output=1200 ) {
    $key=alltools_integration_secret('openai_api_key');if(!$key)wp_send_json_error(array('message'=>'OpenAI is not configured. Add an API key under Uptime Fixer → Theme Settings → API Integrations.'),503);ufx_growth_paid_limit('openai-text',60);$body=array('model'=>apply_filters('alltools_openai_text_model','gpt-5-mini'),'input'=>$prompt,'max_output_tokens'=>$max_output);$res=wp_safe_remote_post('https://api.openai.com/v1/responses',array('timeout'=>45,'sslverify'=>true,'reject_unsafe_urls'=>true,'limit_response_size'=>1048576,'headers'=>array('Authorization'=>'Bearer '.$key,'Content-Type'=>'application/json'),'body'=>wp_json_encode($body),'user-agent'=>'UptimeFixer/3.2 AI'));if(is_wp_error($res))wp_send_json_error(array('message'=>'The AI request failed.'),502);$data=json_decode(wp_remote_retrieve_body($res),true);if(!is_array($data))wp_send_json_error(array('message'=>'The AI provider returned an invalid response.'),502);if(!empty($data['error']['message']))wp_send_json_error(array('message'=>sanitize_text_field($data['error']['message'])),502);$text='';foreach(isset($data['output'])?(array)$data['output']:array() as $item)foreach(isset($item['content'])?(array)$item['content']:array() as $content)if(isset($content['text']))$text.=$content['text'];if(!$text&&isset($data['output_text']))$text=$data['output_text'];$text=trim(wp_strip_all_tags($text));if(!$text)wp_send_json_error(array('message'=>'The AI provider returned no usable text.'),502);return $text;
}

function ufx_api_growth_ai_text() {
    ufx_check_nonce();$kind=sanitize_key(ufx_growth_post('kind',80));$text=isset($_POST['text'])?sanitize_textarea_field(wp_unslash($_POST['text'])):'';$text=alltools_text_slice(trim($text),0,30000);if(!$text)wp_send_json_error(array('message'=>'Enter source text or product facts.'),400);
    if('summary'===$kind||'pdf-summary'===$kind)$prompt="Summarize the following content accurately. Use a short overview followed by clear bullet points. Do not invent facts.\n\n".$text;
    elseif('product'===$kind)$prompt="Write an original, persuasive product description using only the supplied facts. Include a short headline, concise paragraph, and 5 benefit bullets. Avoid unsupported claims.\n\n".$text;
    elseif('visibility'===$kind)$prompt="Run a transparent single-model AI visibility probe. For the brand and topics below, explain whether this model recognizes the brand, what it associates with it, and explicitly state that this is not a cross-platform visibility score. Do not invent market-share data.\n\n".$text;
    else wp_send_json_error(array('message'=>'Unsupported AI mode.'),400);$answer=ufx_growth_openai_text($prompt,1400);wp_send_json_success(array('text'=>$answer,'notice'=>'AI output can contain mistakes; review before publishing.'));
}

function ufx_api_growth_ai_image() {
    ufx_check_nonce();$prompt=isset($_POST['text'])?sanitize_textarea_field(wp_unslash($_POST['text'])):'';$prompt=alltools_text_slice(trim($prompt),0,2000);if(!$prompt)wp_send_json_error(array('message'=>'Enter an image prompt.'),400);$key=alltools_integration_secret('openai_api_key');if(!$key)wp_send_json_error(array('message'=>'OpenAI is not configured. Add an API key under API Integrations.'),503);ufx_growth_paid_limit('openai-image',20);$res=wp_safe_remote_post('https://api.openai.com/v1/images/generations',array('timeout'=>90,'sslverify'=>true,'reject_unsafe_urls'=>true,'limit_response_size'=>8388608,'headers'=>array('Authorization'=>'Bearer '.$key,'Content-Type'=>'application/json'),'body'=>wp_json_encode(array('model'=>apply_filters('alltools_openai_image_model','gpt-image-2'),'prompt'=>$prompt,'size'=>'1024x1024','quality'=>'low')),'user-agent'=>'UptimeFixer/3.2 Image'));if(is_wp_error($res))wp_send_json_error(array('message'=>'Image generation failed.'),502);$data=json_decode(wp_remote_retrieve_body($res),true);if(!empty($data['error']['message']))wp_send_json_error(array('message'=>sanitize_text_field($data['error']['message'])),502);$b64=isset($data['data'][0]['b64_json'])?$data['data'][0]['b64_json']:'';if(!$b64)wp_send_json_error(array('message'=>'No image was returned.'),502);wp_send_json_success(array('image'=>'data:image/png;base64,'.$b64));
}

function ufx_growth_uploaded_audio() {
    if(empty($_FILES['file'])||!isset($_FILES['file']['tmp_name'])||!is_uploaded_file($_FILES['file']['tmp_name']))wp_send_json_error(array('message'=>'Choose an audio file.'),400);$file=$_FILES['file'];if(!empty($file['error'])||$file['size']>20*1024*1024)wp_send_json_error(array('message'=>'Audio must be smaller than 20 MB.'),400);$finfo=function_exists('finfo_open')?finfo_open(FILEINFO_MIME_TYPE):false;$mime=$finfo?finfo_file($finfo,$file['tmp_name']):'';if($finfo)finfo_close($finfo);$allowed=array('audio/mpeg','audio/wav','audio/x-wav','audio/mp4','audio/x-m4a','audio/webm','video/webm','audio/ogg');if(!in_array($mime,$allowed,true))wp_send_json_error(array('message'=>'Unsupported audio format.'),400);return array('tmp'=>$file['tmp_name'],'name'=>sanitize_file_name($file['name']),'mime'=>$mime,'size'=>(int)$file['size']);
}

function ufx_api_growth_transcription() {
    ufx_check_nonce();$file=ufx_growth_uploaded_audio();$key=alltools_integration_secret('openai_api_key');if(!$key)wp_send_json_error(array('message'=>'OpenAI is not configured. Add an API key under API Integrations.'),503);ufx_growth_paid_limit('openai-audio',25);$boundary='----UFX'.wp_generate_password(24,false,false);$content=file_get_contents($file['tmp']);$body="--{$boundary}\r\nContent-Disposition: form-data; name=\"model\"\r\n\r\n".apply_filters('alltools_openai_transcription_model','gpt-4o-mini-transcribe')."\r\n--{$boundary}\r\nContent-Disposition: form-data; name=\"file\"; filename=\"".$file['name']."\"\r\nContent-Type: ".$file['mime']."\r\n\r\n".$content."\r\n--{$boundary}--\r\n";unset($content);$res=wp_safe_remote_post('https://api.openai.com/v1/audio/transcriptions',array('timeout'=>120,'sslverify'=>true,'reject_unsafe_urls'=>true,'limit_response_size'=>2097152,'headers'=>array('Authorization'=>'Bearer '.$key,'Content-Type'=>'multipart/form-data; boundary='.$boundary),'body'=>$body,'user-agent'=>'UptimeFixer/3.2 Transcription'));unset($body);if(is_wp_error($res))wp_send_json_error(array('message'=>'Transcription request failed.'),502);$data=json_decode(wp_remote_retrieve_body($res),true);if(!empty($data['error']['message']))wp_send_json_error(array('message'=>sanitize_text_field($data['error']['message'])),502);$text=isset($data['text'])?sanitize_textarea_field($data['text']):'';if(!$text)wp_send_json_error(array('message'=>'The transcription provider returned no text.'),502);wp_send_json_success(array('text'=>$text));
}

function ufx_api_growth_speech() {
    ufx_check_nonce();$text=isset($_POST['text'])?sanitize_textarea_field(wp_unslash($_POST['text'])):'';$text=alltools_text_slice(trim($text),0,4000);if(!$text)wp_send_json_error(array('message'=>'Enter text for speech.'),400);$key=alltools_integration_secret('openai_api_key');if(!$key)wp_send_json_error(array('message'=>'OpenAI is not configured. Add an API key under API Integrations.'),503);ufx_growth_paid_limit('openai-speech',30);$res=wp_safe_remote_post('https://api.openai.com/v1/audio/speech',array('timeout'=>90,'sslverify'=>true,'reject_unsafe_urls'=>true,'limit_response_size'=>12582912,'headers'=>array('Authorization'=>'Bearer '.$key,'Content-Type'=>'application/json'),'body'=>wp_json_encode(array('model'=>apply_filters('alltools_openai_speech_model','gpt-4o-mini-tts'),'voice'=>'alloy','input'=>$text,'response_format'=>'mp3')),'user-agent'=>'UptimeFixer/3.2 Speech'));if(is_wp_error($res))wp_send_json_error(array('message'=>'Speech generation failed.'),502);$type=(string)wp_remote_retrieve_header($res,'content-type');if(false!==stripos($type,'json')){$data=json_decode(wp_remote_retrieve_body($res),true);wp_send_json_error(array('message'=>isset($data['error']['message'])?sanitize_text_field($data['error']['message']):'Speech provider error.'),502);}if(false===stripos($type,'audio/')&&false===stripos($type,'octet-stream'))wp_send_json_error(array('message'=>'The speech provider returned an unexpected response.'),502);wp_send_json_success(array('audio'=>'data:audio/mpeg;base64,'.base64_encode(wp_remote_retrieve_body($res))));
}
