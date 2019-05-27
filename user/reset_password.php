<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/24
 * Time: 21:35
 * 重设密码
 */

require_once __DIR__ . '/../include/jwt.php';
try {
    header('Content-type: application/json');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !key_exists('p', $_POST) || !key_exists('op', $_POST))
        //bad request
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    $op = hash('sha256', $_POST['op'] . HASH_SALT);
    //检测原密码
    $ans = $db->query("SELECT `password` FROM `user` WHERE `uid`={$jwt['uid']}");
    if ($ans->num_rows === 0)
        throw new KBException(-200);
    $res = $ans->fetch_row();
    if ($res[0] !== $op) {
        throw new KBException(-1);
    }

    $p = hash('sha256', $_POST['p'] . HASH_SALT);
    //新用户更新type
    $jwt['type'] === 0 ?
        $db->query("UPDATE `user` SET `type`=1,`password`='{$p}',`version`=`version`+1 WHERE `username`={$jwt['u']} LIMIT 1") :
        $db->query("UPDATE `user` SET `password`='{$p}',`version`=`version`+1 WHERE `username`={$jwt['u']} LIMIT 1");
    if ($db->sqlstate !== '00000')
        throw new KBException(-60);
    //删除cookie
    setcookie('token', '', time() - 3600, PATH, DOMAIN);
    //响应
    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}