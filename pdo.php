<?php
//    error_reporting(E_ALL);
//    ini_set("display_errors", 1);
    require 'connect.php';

    function isValidUser($user){
        $pdo = pdoSqlConnect();
        $query = "select exists(select * from userProfile_TB where userEmailId = ? and userPw = password(?)) as result";
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

    function isValidId($userId){
        $pdo = pdoSqlConnect();
        $query = "select exists(select * from userProfile_TB where userEmailId = ?) as result";
        $st = $pdo->prepare($query);
        $st->execute([$userId]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st=null; $pdo = null;
        return intval($res[0]["result"]);
    }

    function isValidNickname($userNickname){
        $pdo = pdoSqlConnect();
        $query = "select exists(select * from userProfile_TB where userNickname = ?) as result";
        $st = $pdo->prepare($query);
        $st->execute([$userNickname]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st=null; $pdo = null;
        return intval($res[0]["result"]);
    }

    function Signin($userdata){
        $pdo = pdoSqlConnect();
        $query = "insert into userProfile_TB (userEmailId,userPw,userNickname,SignUpTimeStamp)values ( ?, password(?), ?, ? )";
        $date = getTodayByTimeStamp(); 
        $st = $pdo->prepare($query);
        $st->execute([$userdata->userEmailId, $userdata->userPw, $userdata->userNickname, $date]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st=null; $pdo = null;
        return;
    }
