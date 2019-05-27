<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/24
 * Time: 15:29
 * 登出
 */

require_once __DIR__ . '/../include/jwt.php';
try {
    header('Content-type: application/json');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        //bad request
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    //登出操作
    //数据库版本号+1
    $db->query("UPDATE `user` SET `version`=`version`+1 WHERE `uid`={$jwt['uid']} LIMIT 1");
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
