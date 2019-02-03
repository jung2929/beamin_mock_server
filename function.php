<?php
//    error_reporting(E_ALL);
//    ini_set("display_errors", 1);


    use Firebase\JWT\JWT;
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use Google\Auth\ApplicationDefaultCredentials;
    use GuzzleHttp\Client;
    use GuzzleHttp\HandlerStack;


    function getSQLErrorException($errorLogs, $e, $req){
        $res = (Object) Array();
        http_response_code(500);
        $res->code = 500;
        $res->message = "SQL Exception -> " . $e->getTraceAsString();
        echo json_encode($res);

        addErrorLogs($errorLogs, $res, $req);
    }

    function isValidHeader($jwt, $key){

        try{
            $data = getDataByJWToken($jwt, $key);
            return isValidUser($data);
        }catch(Exception $e){
            return false;
        }
    }


    function getTodayByTimeStamp(){
        return date("Y-m-d H:i:s");
    }

    function getJWToken($userId, $userPw, $secretKey){
        $data = array(
            'date' => (string)getTodayByTimeStamp(),
            'userId' => (string)$userId,
            'userPw' => (string)$userPw
        );

        return $jwt = JWT::encode($data, $secretKey);
    }

    function getDataByJWToken($jwt, $secretKey){
        $decoded = JWT::decode($jwt, $secretKey, array('HS256'));
        return $decoded;
    }

    function addAccessLogs($accessLogs, $body){

        if(isset($_SERVER['HTTP_X_ACCESS_TOKEN']))
            $logData["JWT"] = getDataByJWToken($_SERVER['HTTP_X_ACCESS_TOKEN'], JWT_SECRET_KEY);
        $logData["GET"] = $_GET;
        $logData["BODY"] = $body;
        $logData["REQUEST_METHOD"] = $_SERVER["REQUEST_METHOD"];
        $logData["REQUEST_URI"] = $_SERVER["REQUEST_URI"];
//    $logData["SERVER_SOFTWARE"] = $_SERVER["SERVER_SOFTWARE"];
        $logData["REMOTE_ADDR"] = $_SERVER["REMOTE_ADDR"];
        $logData["HTTP_USER_AGENT"] = $_SERVER["HTTP_USER_AGENT"];
        $accessLogs->addInfo(json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    }

    function addErrorLogs($errorLogs, $res, $body){
        if(isset($_SERVER['HTTP_X_ACCESS_TOKEN']))
            $req["JWT"] = getDataByJWToken($_SERVER['HTTP_X_ACCESS_TOKEN'], JWT_SECRET_KEY);
        $req["GET"] = $_GET;
        $req["BODY"] = $body;
        $req["REQUEST_METHOD"] = $_SERVER["REQUEST_METHOD"];
        $req["REQUEST_URI"] = $_SERVER["REQUEST_URI"];
//    $req["SERVER_SOFTWARE"] = $_SERVER["SERVER_SOFTWARE"];
        $req["REMOTE_ADDR"] = $_SERVER["REMOTE_ADDR"];
        $req["HTTP_USER_AGENT"] = $_SERVER["HTTP_USER_AGENT"];

        $logData["REQUEST"] = $req;
        $logData["RESPONSE"] = $res;

        $errorLogs->addError(json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

//        sendDebugEmail("Error : " . $req["REQUEST_METHOD"] . " " . $req["REQUEST_URI"] , "<pre>" . json_encode($logData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "</pre>");
    }

    function getAuth(){
        $num=mt_rand(0,9999);
        $authNum=str_pad($num,4,'0',STR_PAD_LEFT);
        return $authNum;
    }

    function getLogs($path){
        $fp = fopen($path, "r" , FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$fp) echo "error";

        while (!feof($fp)) {
            $str = fgets($fp, 10000);
            $arr[] = $str;
        }
        for ($i = sizeof($arr) - 1; $i >= 0; $i--) {
            echo $arr[$i] . "<br>";
        }
//        fpassthru($fp);
        fclose($fp);
    }

