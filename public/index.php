<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../appFunctions/functions.php';

$app = AppFactory::create();
$app->setBasePath("/Proyectos/WSAppFELConnect/public"); // /myapp/api is the api folder (http://domain/myapp/api)

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});

$app->get('/certificarFel', function (Request $request, Response $response, array $args) {
    $data = json_decode($request->getBody(),true);

    if($data["id"] != '' && $data["endpoint"] != ''){


        //=======================CONFIGURACION======================
        $token_urlConf ="https://test.salesforce.com/services/oauth2/token";
        $paramsConf =
                    "grant_type=password"
                    . "&client_id=3MVG9ysJNY7CaIHn0buJJ0rfsXKFNLv5NtNXl8Yz5exVSbT8VfdJ4ddMhwpncbu6dyjsDJvyH3pj32XVIfm2V"
                    . "&client_secret=19C3E65304956C76A2354300277F447F2155D317D306C71719026EAE07CE3D73"
                    . "&username=admin@pymes.com.sandboxmig"
                    . "&password=s12345678";

        

        $respLogin = salesforceLogin($token_urlConf,$paramsConf);

        $response->getBody()->write("\nToken Salesforce: " . $respLogin["token"]);
        if($respLogin["token"] != ''){
            $reqFact = consultaRecord($data["id"], $respLogin["instance_url"], $respLogin["token"]);
            //$response->getBody()->write("\nRequest: " . $reqFact); 

            $certificaResp = CertificaFact($data["endpoint"],$reqFact);
            $response->getBody()->write("\nResponse: " . $certificaResp);

            if($certificaResp != ''){
                ActualizaRegistro($data["id"], $certificaResp,$respLogin["instance_url"], $respLogin["token"]);
            }
        }
    }

    return $response;
});

$app->run();