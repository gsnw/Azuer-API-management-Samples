<?php
function send($url, $header = array(), $post = array())
{
	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $url);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
	if(!empty($header))
	{
		curl_setopt($c, CURLOPT_HTTPHEADER, $header);
	}

	if(!empty($post))
	{
		curl_setopt($c, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($c, CURLOPT_POSTFIELDS, $post);
	}

	$data = curl_exec($c);
	curl_close($c);
	return $data;
}

# Variable
include_once(__DIR__."/config.inc.php");
$headerRequest = array();
$postRequest = array();

# Token Request
$url = sprintf("https://login.microsoftonline.com/%s/oauth2/token", $config['subscription']);

$postRequest['grant_type'] = 'client_credentials';
$postRequest['client_id'] = $config['client_id'];
$postRequest['client_secret'] = $config['client_secret'];
$postRequest['resource'] = 'https://management.azure.com/';

$jsonTokenResponse = json_decode(send($url, $headerRequest, $postRequest), true);

# Billing Data
$url = sprintf("https://management.azure.com/subscriptions/%s/providers/Microsoft.CostManagement/query?api-version=2019-11-01", $config['subscription_id']);

$headerRequest[] = 'Content-Type: application/json';
$headerRequest[] = 'Accept: application/json';
$headerRequest[] = 'Authorization: Bearer '.$jsonTokenResponse['access_token'];

$jsonData['type'] = 'Usage';
$jsonData['timeframe'] = 'TheLastMonth';
#$jsonData['dataset'] = array('granularity' => 'Daily');
$jsonData['dataset']['granularity'] = 'Daily';
$jsonData['dataset']['aggregation']['totalCost']['name'] = 'PreTaxCost';
$jsonData['dataset']['aggregation']['totalCost']['function'] = 'Sum';
# $jsonData['dataset'] = array('aggregation' => array('totalCost' => array('name' => 'PreTaxCost', 'function' => 'Sum')));
$postRequest = json_encode($jsonData);


$jsonDataResponse = json_decode(send($url, $headerRequest, $postRequest));
print_r($jsonDataResponse);
?>
