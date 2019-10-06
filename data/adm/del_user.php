<?php
/**
 * 删除评审账号
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
    if ($jwt['type'] !== 3)
        throw new KBException(-100);
    if (!key_exists('u', $_POST))
        throw new KBException(-100);

    $u = $_POST['u'];
    $ans = $db->query("SELECT `uid` FROM `user` WHERE `username`='{$u}' LIMIT 1");
    if ($ans->num_rows === 0)
        throw new KBException(-41);
    $uid = $ans->fetch_array()[0];

    $db->query("DELETE FROM `user` WHERE `username`='{$u}' LIMIT 1");
    $db->query("DELETE FROM `score` WHERE `uid`={$uid}");
    $db->query("DELETE FROM `user-project` WHERE `uid`={$uid}");
    $db->query("DELETE FROM `user-group` WHERE `uid`={$uid}");

    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}