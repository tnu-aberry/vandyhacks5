<?php
function getAllUbers($startPoint, $endPoint) {
  global $config;

  $uberStartPoint = explode(",", $startPoint);
  $uberEndPoint = explode(",", $endPoint);

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "https://api.uber.com/v1.2/estimates/price?start_latitude=" . $uberStartPoint[0] . "&start_longitude=" . $uberStartPoint[1] . "&end_latitude=" . $uberEndPoint[0] . "&end_longitude=" . $uberEndPoint[1]);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch,CURLOPT_HTTPHEADER, array('Authorization: Token ' . $config['UBER_SERVER_TOKEN']));
  $uberApiReturn = curl_exec($ch);
  curl_close($ch);

  if(strpos($uberApiReturn, 'distance_exceeded')) {
    $results = [];
    return $results;
  }

  $uberApiReturn = json_decode($uberApiReturn, true);
  $results = [];
  foreach($uberApiReturn['prices'] as $transport) {

    // Standardise formats
    $transportName = 'Uber ' . $transport['localized_display_name'];
    $transportPrice = $transport['high_estimate'] + $transport['low_estimate'] / 2;

    if($transportPrice !== 0) { // If a service isn't available, Uber says the price is 0
      $results[] = array('name' => $transportName, 'distance' => $transport['distance'], 'currency' => $transport['currency_code'], 'price' => $transportPrice, 'time' => $transport['duration']);
    }
  }
  return $results;
}
