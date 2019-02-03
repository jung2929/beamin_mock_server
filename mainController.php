<?php
    // error_reporting(E_ALL); ini_set("display_errors", 1);
    require 'function.php';
    $res = (Object)Array();
    header('Content-Type: json');
    $req = json_decode(file_get_contents("php://input"));
    $bannerimage = 7;
    try {
        addAccessLogs($accessLogs, $req);
        switch ($handler) {
            case "index":
                $res->code = 200;
                $res->isSuccess = true;
                $res->message = "인덱스 페이지";
                $index=0;
                while($index<$bannerimage){
                    $res->data->banner[$index]='http://bucoco.kr/images/banner_image/'.($index+1).'.png';
                    $index++;
                }
                echo json_encode($res,JSON_NUMERIC_CHECK);
                //echo "RESTFUL API INDEX PAGE";
                break;

            case "test":
                $now= getTodayByTimeStamp();
                $later= date("Y-m-d H:i:s",strtotime($now.'-1 minute'));
                $res->now=$now;
                $res->later=$later; 
                $res->result=$now<$later;
                echo json_encode($res,JSON_NUMERIC_CHECK);
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

	        case "sendSms":
                $phoneNumber=$req->phoneNumber;
                $authNum=getAuth();
                if(!preg_match('/01([0|1|6|7|8|9]?)-?([0-9]{3,4})-?([0-9]{4})$/',$phoneNumber)){
                    $res->code = 400;
                    $res->isSuccess = false;
                    $res->message = "올바른 전화번호가 아님";
                    addErrorLogs($errorLogs, $res, $req);
                    echo json_encode($res,JSON_NUMERIC_CHECK);
                    return;
                }
                $res->code = 200;
                $res->isSuccess = true;
                $res->message = "발송 성공";
                //$res->data->authNumber=$authNum;
                require_once './webtizen_sms/apitool/now_sms_send.php';
                
                break;
            /*
            * API No. 1
            * API Name : 핸드폰 인증번호 발송 API
            * 마지막 수정 날짜 : 19.02.03
            */

            case "smsAuth":
                $phoneNumber=$req->userPhoneNumber;
                $authNum=$req->authNum;
                if(!preg_match('/01([0|1|6|7|8|9]?)-?([0-9]{3,4})-?([0-9]{4})$/',$phoneNumber)){
                    $res->code = 400;
                    $res->isSuccess = false;
                    $res->message = "올바른 전화번호가 아님";
                    addErrorLogs($errorLogs, $res, $req);
                    echo json_encode($res,JSON_NUMERIC_CHECK);
                    return;
                }                
                if(!isValidAuthNum($phoneNumber,$authNum)){
                    $res->code = 400;
                    $res->isSuccess = false;
                    $res->message = "인증 실패";
                    addErrorLogs($errorLogs, $res, $req);
                    echo json_encode($res,JSON_NUMERIC_CHECK);
                    return;
                }
                makeAuthPhoneNumber($phoneNumber);
                $res->code = 200;
                $res->isSuccess = true;
                $res->message = "인증 성공";
                echo json_encode($res,JSON_NUMERIC_CHECK);

                break;

            /*
            * API No. 2
            * API Name : 인증번호 확인 API
            * 마지막 수정 날짜 : 19.02.03
            */

            case "login":
                if(isValidUser($req)){
                    http_response_code(200);
                    $userEmailId= $req->userEmailId;
                    $userPw= $req->userPw;
                    $jwt=getJWToken($userEmailId, $userPw , JWT_SECRET_KEY);
                    $res->code = 200;
                    $res->isSuccess = true;
                    $res->message = "로그인 성공";
                    $res->data->userEmailId=$userEmailId;
                    $res->data->userNickname=getUserNickname($userEmailId);
                    $res->data->jwt = $jwt;
                }
                else{
                    http_response_code(400);
                    $res->code = 400;
                    $res->isSuccess = false;
                    $res->message = "올바른 아이디나 패스워드가 아님";
                    addErrorLogs($errorLogs, $res, $req);
                }
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            /*
            * API No. 3
            * API Name : 로그인 API
            * 마지막 수정 날짜 : 19.02.03
            */

            case "signUp":
                if(!preg_match('/01([0|1|6|7|8|9]?)-?([0-9]{3,4})-?([0-9]{4})$/',$phoneNumber)){
                    $res->code = 400;
                    $res->isSuccess = false;
                    $res->message = "올바른 전화번호가 아님";
                    addErrorLogs($errorLogs, $res, $req);
                    echo json_encode($res,JSON_NUMERIC_CHECK);
                    return;
                }    
                if(!isValidPhoneNumber($req->phoneNumber)){
                    $res->code = 400;
                    $res->isSuccess = false;
                    $res->message = "인증되지 않은 전화번호";
                    addErrorLogs($errorLogs, $res, $req);
                    echo json_encode($res,JSON_NUMERIC_CHECK);
                    return;
                }
                if(!filter_var($req->userEmailId,FILTER_VALIDATE_EMAIL)){
                    http_response_code(400);
                    $res->code = 400;
                    $res->isSuccess=false;
                    $res->message = "올바른 이메일 형식이 아님";
                    addErrorLogs($errorLogs, $res, $req);
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                if(isValidId($req->userEmailId)){
                    http_response_code(400);
                    $res->code = 400;
                    $res->isSuccess=false;
                    $res->message = "이메일 중복";
                    addErrorLogs($errorLogs, $res, $req);
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                if(!preg_match('/^.*(?=^.{8,15}$)(?=.*\d)(?=.*[a-zA-Z])(?=.*[!@#$%^&+=]).*$/',$req->userPw)){
                    http_response_code(400);
                    $res->code = 400;
                    $res->isSuccess=false;
                    $res->message = "비밀번호는 8~15자, 영어 숫자 특수문자 조합 필수";
                    addErrorLogs($errorLogs, $res, $req);
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                if($req->userNickname[0] == ' ' || mb_strlen($req->userNickname,'utf-8')<1 || mb_strlen($req->userNickname,'utf-8')>10){
                    http_response_code(400);
                    $res->code = 400;
                    $res->isSuccess=false;
                    $res->message = "닉네임은 1~10자의 문자열";
                    addErrorLogs($errorLogs, $res, $req);
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                if(isValidNickname($req->userNickname)){
                    http_response_code(400);
                    $res->code = 400;
                    $res->isSuccess=false;
                    $res->message = "닉네임 중복";
                    addErrorLogs($errorLogs, $res, $req);                    
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                
                http_response_code(200);
                $res->code = 200;
                $res->isSuccess=true;
                $res->message = "회원가입 성공";
                Signin($req);
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
                
            /*
            * API No. 4
            * API Name : 회원가입 API
            * 마지막 수정 날짜 : 19.02.03
            */

    } catch (Exception $e) {

        return getSQLErrorException($errorLogs, $e, $req);
    }