<?php
    //error_reporting(E_ALL);    ini_set("display_errors", 1);
    require 'connect.php';

    function isValidUser($user){
        $pdo = pdoSqlConnect();
        $query = "SELECT exists(select * from userProfile_TB where userEmailId = ? and userPw = password(?)) as result";
        $st = $pdo->prepare($query);
        $st->execute([$user->userEmailId, $user->userPw]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st=null; $pdo = null;

        return intval($res[0]["result"]);
    }

    function getUserNickname($userId){
        $pdo = pdoSqlConnect();
        $query = "SELECT userNickname FROM userProfile_TB WHERE userEmailId = ?";
        $st = $pdo->prepare($query);
        $st->execute([$userId]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st=null; $pdo = null;

        return $res[0]['userNickname'];
    }

    function getEmail($phoneNumber){
        $pdo = pdoSqlConnect();
        $query = "SELECT userEmailId FROM userProfile_TB WHERE userPhoneNumber = ?";
        $st = $pdo->prepare($query);
        $st->execute([$phoneNumber]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st=null; $pdo = null;

        return $res[0]['userPhoneNumber'];
    }

    function SignUp($userdata){
        $pdo = pdoSqlConnect();
        $query = "INSERT into userProfile_TB values ( ?, password(?), ?,?, ? )";
        $date = getTodayByTimeStamp(); 
        $st = $pdo->prepare($query);
        $st->execute([$userdata->userEmailId, $userdata->userPw, $userdata->userNickname,$userdata->userPhoneNumber ,$date]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st=null; $pdo = null;
        return;
    }

    function isValidId($userId){
        $pdo = pdoSqlConnect();
        $query = "SELECT exists(select * from userProfile_TB where userEmailId = ?) as result";
        $st = $pdo->prepare($query);
        $st->execute([$userId]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st=null; $pdo = null;
        return intval($res[0]["result"]);
    }

    function isValidNickname($userNickname){
        $pdo = pdoSqlConnect();
        $query = "SELECT exists(select * from userProfile_TB where userNickname = ?) as result";
        $st = $pdo->prepare($query);
        $st->execute([$userNickname]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st=null; $pdo = null;
        return intval($res[0]["result"]);
    }

    function makeAuthLogs($phoneNumber,$authNum){
        $pdo = pdoSqlConnect();
        $query = "INSERT into authLog_TB values (?,?,?,?)";
        $st = $pdo->prepare($query);
        $now= getTodayByTimeStamp();
        $later= date("Y-m-d H:i:s",strtotime($now.'+5 minute'));
        $st->execute([$phoneNumber,$authNum,$now,$later]);
        $st=null; $pdo = null;
        return;
    }

    function isValidAuthNum($phoneNumber,$authNum){
        $pdo = pdoSqlConnect();
        $query = "SELECT exists(select * from authLog_TB where authMaxTime > now() and userPhoneNumber = ? and authNumber = ?) as result";
        $st = $pdo->prepare($query);
        $st->execute([$phoneNumber,$authNum]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st=null; $pdo = null;
        return intval($res[0]['result']);
    }

    function makeAuthPhoneNumber($phoneNumber){
        $pdo = pdoSqlConnect();
        $query = "INSERT into authPhoneNumber_TB values (?,?,?)";
        $st = $pdo->prepare($query);
        $now= getTodayByTimeStamp();
        $later= date("Y-m-d H:i:s",strtotime($now.'+10 minute'));
        $st->execute([$phoneNumber,$now,$later]);
        $st=null; $pdo = null;
        return;
    }

    function isValidPhoneNumber($phoneNumber){
        $pdo = pdoSqlConnect();
        $query = "SELECT exists(select * from authPhoneNumber_TB where userPhoneNumber = ?) as result";
        $st = $pdo->prepare($query);
        $st->execute([$phoneNumber]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st=null; $pdo = null;
        return intval($res[0]['result']);
    }

    function getRestrauntList($latitude,$longitude, $category){
        $pdo = pdoSqlConnect();
        $query = "SELECT l.restaurantNumber, restaurantName, mainMenu, now() between openTime and closeTime as status,
                (6371*acos(cos(radians(?))*cos(radians(i.latitude))*cos(radians(i.longitude)-radians(?))+sin(radians(?))*sin(radians(i.latitude)))) as distance from restaurantList_TB as l 
                inner join restaurantInfo_TB as i on l.restaurantNumber=i.restaurantNumber where category=? having distance <= 3.0 order by distance;";
        $st = $pdo->prepare($query);
        $st->execute([$latitude,$longitude,$latitude,$category]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st=null; $pdo = null;
        
        return $res;
    }

    function getRestrauntInfo($restaurantNum){
        $pdo = pdoSqlConnect();
        $query = "SELECT l.restaurantName,i.*,now() between openTime and closeTime as status from restaurantInfo_TB as i inner join 
                restaurantList_TB as l on i.restaurantNumber=l.restaurantNumber where l.restaurantNumber = ?";
        $st = $pdo->prepare($query);
        $st->execute([$restaurantNum]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st=null; $pdo = null;
        
        return $res[0];
    }

    function getMenu($restaurantNum){
        $pdo = pdoSqlConnect();
        $query = "SELECT menuNumber,menuName,price from menu_TB where restaurantNumber=?";
        $st = $pdo->prepare($query);
        $st->execute([$restaurantNum]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st=null; $pdo = null;
        
        return $res;
    }




    