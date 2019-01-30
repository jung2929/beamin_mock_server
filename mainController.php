<?php
    // error_reporting(E_ALL); ini_set("display_errors", 1);
    require 'function.php';
    $res = (Object)Array();
    header('Content-Type: json');
    $req = json_decode(file_get_contents("php://input"));

    try {
        addAccessLogs($accessLogs, $req);
        switch ($handler) {
            case "index":
                echo "RESTFUL API INDEX PAGE";
                break;
            case "test":
                $first="남";
                $last= "윤호";
                $name=$first.$last;

                echo $name;
                break;

            case "ACCESS_LOGS":
                header('Content-Type: text/html; charset=UTF-8');
                getLogs("./logs/access.log");
                break;
            case "ERROR_LOGS":
                header('Content-Type: text/html; charset=UTF-8');
                getLogs("./logs/errors.log");
                break;
            /*
            * API No. 0
            * API Name : 테스트 API
            * 마지막 수정 날짜 : 18.08.16
            */

            case "login":
                if(isValidUser($req)==1){
                    http_response_code(200);
                    $userId= $req->userEmailId;
                    $userPw= $req->userPw;
                    $jwt=getJWToken($userId, $userPw , JWT_SECRET_KEY);
                    $res->code = 200;
                    $res->isSuccess = true;
                    $res->message = "로그인 성공";
                    $res->data->userEmailId=$userId;
                    $res->data->userNickname=getUserNickname($userId);
                    $res->data->jwt = $jwt;
                }
                else{
                    http_response_code(400);
                    $res->code = 400;
                    $res->isSuccess = false;
                    $res->message = "올바른 아이디나 패스워드가 아닙니다";
                }
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            case "signUp":
                if(mb_strlen($req->userEmailId,'utf-8')<1){
                    http_response_code(400);
                    $res->code = 400;
                    $res->isSuccess=false;

                    $res->message = "올바른 아이디 형식이 아님";
                }
                elseif(isValidId($req->userEmailId)!=0){
                    http_response_code(400);
                    $res->code = 400;
                    $res->isSuccess=false;

                    $res->message = "아이디 중복";
                }
                elseif(mb_strlen($req->userPw,'utf-8')<1||mb_strlen($req->userPw,'utf-8')>15){
                    http_response_code(400);
                    $res->code = 400;
                    $res->isSuccess=false;

                    $res->message = "비밀번호는 1~15자의 문자열";
                }
                elseif(mb_strlen($req->userNickname,'utf-8')<1||mb_strlen($req->userNickname,'utf-8')>10){
                    http_response_code(400);
                    $res->code = 400;
                    $res->isSuccess=false;

                    $res->message = "닉네임은 1~10자의 문자열";
                }
                elseif(isValidNickname($req->userNickname)){
                    http_response_code(400);
                    $res->code = 400;
                    $res->isSuccess=false;
                    $res->message = "닉네임 중복";
                }
                else{
                    http_response_code(200);
                    $res->code = 200;
                    $res->isSuccess=true;
                    $res->message = "회원가입 성공";
                    Signin($req);
                }
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
                
        }

    } catch (Exception $e) {

        return getSQLErrorException($errorLogs, $e, $req);
    }