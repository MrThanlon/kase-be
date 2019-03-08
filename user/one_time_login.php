<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/24
 * Time: 21:37
 * 无密码登录
 */

require_once __DIR__ . '../include/jwt.php';
try {
    header('Content-type: application/json');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
        !key_exists('token', $_POST) || !key_exists('u', $_POST) ||
        !preg_match("/^[0-9]\d{5}$/AD", $_POST['token']) || //匹配6位短码
        !preg_match("/^1[3|5|7|8]\d{9}$/AD", $_POST['u'])) //匹配手机号
        //bad request
        throw new KBException(-100);
    //查找数据库
    $ans = $db->query("SELECT `token`,`expire`,`valid`,`tid` FROM `token` WHERE `username`={$_POST['u']} ORDER BY `tid` DESC LIMIT 1");
    if ($ans->num_rows === 0)
        throw new KBException(-30);
    $res = $ans->fetch_row();
    //关于表结构，valid,1.未使用，2.已使用
    if ($res[0] !== hash('sha256', $_POST['token'] . HASH_SALT) || (int)$res[1] > time() || $res[2] !== '1')
        throw new KBException(-12);
    //正常，更新token，设置cookie并响应
    $db->query("UPDATE `token` SET `valid`=2 WHERE `tid`={$res[3]} LIMIT 1");
    if ($db->sqlstate != '00000')
        throw new KBException(-60);
    //查询用户信息
    $ans = $db->query("SELECT `uid`,`version`,`type` FROM `user` WHERE `username`={$_POST['u']} LIMIT 1");
    if ($ans->num_rows === 0)
        throw new KBException(-60);//讲道理不应该
    $res = $ans->fetch_row();
    $uid = (int)$res[0];
    $ver = (int)$res[1];
    //更新last_login
    $db->query("UPDATE `user` SET `last_login`=CURRENT_TIMESTAMP WHERE `uid`={$uid} LIMIT 1");
    $jwt = [
        'u' => $_POST['u'],
        'uid' => $uid,
        'type' => (int)$res[2],
        'version' => $ver,
        'expire' => time() + EXPIRE,
        'born' => time()
    ];
    setcookie('token', jwt_encode($jwt), time() + EXPIRE, PATH, DOMAIN);
    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status_code' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status_code' => -200, 'msg' => 'Unknow error']);
}