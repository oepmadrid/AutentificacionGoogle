<?php
session_start();
require('../config.php');
//https://developers.google.com/+/web/signin/server-side-flow
//https://developers.google.com/accounts/docs/OAuth2WebServer#handlingtheresponse
//https://github.com/googleplus/gplus-quickstart-php
//https://developers.google.com/accounts/docs/OpenIDConnect#createxsrftoken
if(isset($_GET['code'])) {
    $code = $_GET['code'];
	$stateRequested = " ";
	if(isset($_GET['state']))
		$stateRequested = $_GET['state'];
		
	
	
	/*
	 *Nos aseguramos  que no hay falsificación de petición , y que el usuario
   	 *que envia  esta solicitud de conexión es el usuario que se suponía.
	*/
	echo "Este es el estado autogenerado".$_SESSION['state'];
	echo "<br>";
	echo "Este es el estado segun google".$stateRequested;
	echo "<br>";
	
		if($_SESSION['state']!= $stateRequested)
			die('Invalid state parameter');
	
	$post = array(
        "code" => $code,
        "client_id" => $oauth2_client_id,
        "client_secret" => $oauth2_secret,
        "redirect_uri" => $oauth2_redirect,
        "grant_type" => "authorization_code"
    );
//Creamos un http query
$postText = http_build_query($post);

$url = "https://accounts.google.com/o/oauth2/token";
//Iniciamos la sesion de curl
$ch = curl_init();
//asignamos la url
curl_setopt($ch, CURLOPT_URL, $url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postText); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$result = curl_exec($ch);
//cerramos la sesion curl
curl_close($ch);
//decodificamos el string con formato json
$data = json_decode($result);

$id_token = $data->id_token;
$access_token = $data->access_token;

//url que nos da la informacion del usuario en formato json.
$url_id_token = "https://www.googleapis.com/oauth2/v1/tokeninfo?id_token=".$id_token;


//Ahora obtengo los datos del usuario !!! por fin....
$json = file_get_contents($url_id_token);
$obj = json_decode($json);

	$issuer =  $obj->issuer;
	$issued_to = $obj->issued_to;
	$audience = $obj->audience;
	$user_id = $obj->user_id;
	$expires_in = $obj->expires_in;
	$issued_at =  $obj->issued_at;
	$email = $obj->email;
	$email_verified =  $obj->email_verified;
	

echo "<br>";
echo "<br>";


//$accessToken seria el valor que necesitamos.
echo "Access Token: ".$access_token;
echo "<br>";
echo "<br>";
echo "Id Token: ".$id_token;
echo "<br><br>";
echo "Email: ".$email; 


echo "<br><br>"
echo "Con esta informacion ya puedes crear un usuario para que interactue con tu aplicacion web..." 

/*		Ahora nos aseguramos que los datos sean correctos		*/

//asegurarnos que el token obtenido es para nuestra aplicacion.
if( $audience != $oauth2_client_id)
	return new Response("Id cliente invalido",401);


//guardamos el token en la sesion si lo vamos a utilziar luego.

}


?>
