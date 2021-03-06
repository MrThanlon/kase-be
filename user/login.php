<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/1/21
 * Time: 15:46
 * 登录API
 */

try {
    require_once __DIR__ . '/../include/jwt.php';
    header('Content-type: application/json');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        //bad request
        throw new KBException(-100);
    if (!(key_exists('u', $_POST) && key_exists('p', $_POST)))
        throw new KBException(-100);
    if (preg_match("/^1[3|5|7|8]\d{9}$/AD", $_POST['u'])) {
        //手机号登录，申请人模式，使用tel字段查找
        $u = $db->escape_string($_POST['u']);
        $ans = $db->query(
            "SELECT `password`,`username`,`type`,`uid`,`version` FROM `user` WHERE `tel` = {$u} LIMIT 1");
        if ($ans->num_rows === 0)
            throw new KBException(-1);
        $res = $ans->fetch_row();
        //验证密码
        if ($res[0] !== hash('sha256', $db->escape_string($_POST['p']) . HASH_SALT))
            throw new KBException(-1);
        //登录成功
        //更新登录日期
        $db->query("UPDATE `user` SET `last_login`=CURRENT_TIMESTAMP WHERE `user`.`uid` = {$res[3]}");
        //生成token
        $jwt = ['u' => $res[1], 'uid' => (int)$res[3], 'type' => (int)$res[2], 'version' => (int)$res[4],
            'born' => time(), 'expire' => time() + EXPIRE];
        setcookie('token', jwt_encode($jwt), time() + EXPIRE,
            '/', DOMAIN, false, false);
        echo json_encode(['status' => 0, 'msg' => '']);
    } else {
        //其他人登录，使用username字段来查找
        $u = $db->escape_string($_POST['u']);
        $ans = $db->query(
            "SELECT `password`,`type`,`uid`,`version` FROM `user` WHERE `username` = '{$u}' LIMIT 1");
        if ($ans->num_rows === 0)
            throw new KBException(-1, "no user");
        $res = $ans->fetch_all()[0];
        if ($res[0] !== hash('sha256', $db->escape_string($_POST['p']) . HASH_SALT))
            throw new KBException(-1, "wrong password");
        //登录成功
        //更新登录日期
        $db->query("UPDATE `user` SET `last_login`=CURRENT_TIMESTAMP WHERE `user`.`uid` = {$res[2]}");
        //生成token
        $jwt = ['u' => $_POST['u'], 'uid' => (int)$res[2], 'type' => (int)$res[1], 'version' => (int)$res[3],
            'born' => time(), 'expire' => time() + EXPIRE];
        setcookie('token', jwt_encode($jwt), time() + EXPIRE, PATH, DOMAIN);
        echo json_encode(['status' => 0, 'msg' => '']);
    }

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}