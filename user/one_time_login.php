<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/24
 * Time: 21:37
 * 无密码登录
 */

try {
    require_once __DIR__ . '/../include/jwt.php';
    require_once __DIR__ . '/../include/sms.php';
    header('Content-type: application/json');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
        !key_exists('token', $_POST) ||
        !key_exists('u', $_POST) ||
        //!key_exists('password', $_POST) ||
        !preg_match("/^[0-9]\d{5}$/AD", $_POST['token']) || //匹配6位短码
        !preg_match("/^1[3|5|7|8]\d{9}$/AD", $_POST['u'])) //匹配手机号
        //bad request
        throw new KBException(-100);
    if (!key_exists('sms_token', $_COOKIE))
        throw new KBException(-12);

    //验证码校验
    sms_check($_COOKIE['sms_token'], $_POST['u'], $_POST['token']);

    //提取密码
    $hash = false;
    if (key_exists('password', $_POST)) {
        $password = $db->escape_string($_POST['password']);
        $hash = hash('sha256', $password . HASH_SALT);
    }

    //查询用户信息
    $ans = $db->query("SELECT `uid`,`version`,`type` FROM `user` WHERE `username`='{$_POST['u']}' AND (`type`=1 OR `type`=0) LIMIT 1");
    if ($ans->num_rows === 0)
        throw new KBException(-200, "System error, failed to query user");//讲道理不应该
    $res = $ans->fetch_row();
    $uid = (int)$res[0];
    $version = (int)$res[1];
    $type = (int)$res[2];

    //更新last_login
    $db->query("UPDATE `user` SET `last_login`=CURRENT_TIMESTAMP,`type`=1" .
        $hash === false ? '' : ",`password`='{$hash}'" .
        " WHERE `uid`={$uid} LIMIT 1"
    );
    //登录成功
    $jwt = [
        'u' => $_POST['u'],
        'uid' => $uid,
        'type' => 1,
        'version' => $version,
        'expire' => time() + EXPIRE,
        'born' => time()
    ];
    setcookie('token', jwt_encode($jwt), time() + EXPIRE, PATH, DOMAIN);
    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}