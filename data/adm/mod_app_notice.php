<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/17
 * Time: 11:28
 * 修改通知
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
    if ($jwt['type'] !== 3 || !key_exists('content', $_POST))
        throw new KBException(-100);
    $content = $db->escape_string($_POST['content']);
    $ans = $db->query("SELECT `value` FROM `normal` WHERE `nid`=1 LIMIT 1");
    if ($ans->num_rows === 0) {
        $db->query("INSERT INTO `normal` (`nid`,`value`) VALUES (1,'{$content}')");
    }
    else {
        $db->query("UPDATE `normal` SET `value`='{$content}' WHERE `nid`=1 LIMIT 1");
    }
    if ($db->sqlstate !== '00000')
        throw new KBException(-60);
    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}