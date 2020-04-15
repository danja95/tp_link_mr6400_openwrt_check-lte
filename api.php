<?php

/** CONFIG **/
$username = "admin";
$password = "admin";
$url = "http://192.168.2.1:8080";
/** END CONFIG **/


header("Content-Type: application/json");

/*
    Viele Menschen sind an diesem Code gestorben.
    */

require __DIR__ . '/vendor/autoload.php';

use \Curl\Curl;

$nonce = null; $token = null;

$nonceRequest = new Curl();
$nonceRequest->post($url . "/cgi-bin/qcmap_auth", json_encode([
    "module" => "authenticator",
    "action" => 0
]));

if(!$nonceRequest->error) {
    $nonce = json_decode($nonceRequest->response)->nonce;
    
    $tokenRequest = new Curl();
    $tokenRequest->post($url . "/cgi-bin/qcmap_auth", json_encode([
        "module" => "authenticator",
        "action" => 1,
        "digest" => md5(implode(":",[$username,$password,$nonce]))
    ]));
    
    if(!$tokenRequest->error) {
        
        $token = json_decode($tokenRequest->response)->token;
        
        $statusRequest = new Curl();
        $statusRequest->post($url . "/cgi-bin/qcmap_web_cgi", json_encode([
            "module" => "status",
            "action" => 0,
            "token" => $token
        ]));
        
        if(!$statusRequest->error) {
            $reqRes = json_decode($statusRequest->response);
            
            $reqRes->apiError = false;
        
            print_r(json_encode($reqRes, JSON_PRETTY_PRINT));
            
        } else {
            die(json_encode(["apiError" => "status_request"]));
        }
        
    } else {
        die(json_encode(["apiError" => "token_request"]));
    }
    
    
} else {
    die(json_encode(["apiError" => "nonce_request"]));
}
 
