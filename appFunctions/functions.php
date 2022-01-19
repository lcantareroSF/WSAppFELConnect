<?php
function salesforceLogin($token_url, $params){
    $access_token = '';
    $instance_url = '';

    session_start();
    $curl = curl_init($token_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    $json_response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ( $status != 200 )
            {
                die("Error: call to token URL $token_url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
            }
    curl_close($curl);
    $response = json_decode($json_response, true);
    $access_token = $response['access_token'];
    $instance_url= $response['instance_url'];
    
    $_SESSION['access_token'] = $access_token;
    $_SESSION['instance_url'] = $instance_url;

    if($access_token != ''){
        print("Sesion iniciada.");
        return array("token"=>$access_token,
                     "instance_url"=>$instance_url);
    }else{
        print("No se pudo iniciar sesion." . $instance_url);
        return array("token"=>'',
                     "instance_url"=>'');
    }
}

function CertificaFact($url, $request) {
    $headers = [
        'Method: POST',
        'Connection: Keep-Alive',
        'User-Agent: PHP-SOAP-CURL',
        'Content-Type: text/xml'];

    $defaults = array(
        CURLOPT_POST => 1,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_URL => $url,
        CURLOPT_FRESH_CONNECT => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FORBID_REUSE => 1,
        CURLOPT_TIMEOUT => 4,
        CURLOPT_POSTFIELDS => $request
    );

    $ch = curl_init();
    curl_setopt_array($ch, $defaults);
    $resFinal = '';
    if( ! $result = curl_exec($ch))
    {
        $resFinal = trigger_error(curl_error($ch));
        print("<br><br>Error de comunicacion con servicio de facturacion.<br> $resFinal");
    }else{   
        $resFinal = $result;
        print("<br><br>Comunicacion con servicio de facturacion correcta.");
    }

    curl_close($ch);
    return $resFinal;
}

function ActualizaRegistro($id, $responseCertFEL, $instance_url, $access_token){
    $access_token = $_SESSION['access_token'];
	$instance_url = $_SESSION['instance_url'];


	$url = "$instance_url/services/data/v54.0/sobjects/Ventas__c/".$id;
	$content = json_encode(array("Response_WS__c" => $responseCertFEL));
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER,array("Authorization: Bearer $access_token","Content-type: application/json"));
	curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
	curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ( $status != 201 and $status != 204 )
		{
			die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
		}
		echo "<br><br>Actualizacion de registro exitosa.<br/><br/>";
		curl_close($curl);
	$response = json_decode($json_response, true);
	
	echo "Response $response<br/><br/>";
}

function consultaRecord($id, $instance_url , $access_token){
    session_start();
		$access_token = $_SESSION['access_token'];
		$instance_url = $_SESSION['instance_url'];
	

		$url = $instance_url."/services/data/v54.0/sobjects/Ventas__c/".$id;
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER,array("Authorization: Bearer $access_token"));
		$json_response = curl_exec($curl);
		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if ( $status != 200 )
				{
					die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
				}
			echo "<br><br>Consulta de registro exitosa<br/><br/>";
			curl_close($curl);
			$response = json_decode($json_response, true);
        return $response['Reques_WS__c'];
}