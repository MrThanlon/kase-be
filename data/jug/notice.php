<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/12
 * Time: 11:27
 * 拉取通知信息
 */

try {
    require_once __DIR__ . '/../../include/jwt.php';
    header('Content-type: application/json');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        //bad request
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    if ($jwt['type'] !== 2)
        throw new KBException(-100);
    $ans = $db->query("SELECT `value` FROM `normal` WHERE `nid`=2 LIMIT 1");
    $notice = '';
    if ($ans->num_rows === 0) {
        //没有数据
        $db->query("INSERT INTO `normal` (`nid`,`value`) VALUES (2,'')");
    } else {
        $notice = ($ans->fetch_row())[0];
    }
    echo json_encode([
        'status' => 0,
        'msg' => '',
        'content' => $notice
    ]);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}