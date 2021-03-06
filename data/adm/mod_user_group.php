<?php
/**
 * 从分区移除评审
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
    if ($jwt['type'] !== 3 ||
        !key_exists('gid', $_POST) ||
        !preg_match("/^\d*?$/AD", $_POST['gid']) ||
        !key_exists('u', $_POST))
        throw new KBException(-100);

    $gid = (int)$_POST['gid'];
    $u = $_POST['u'];

    // 检查是否存在
    $ans = $db->query("SELECT 1 FROM `user-group` WHERE
                                 `uid`=(SELECT `uid` FROM `user` WHERE `username`='{$u}' AND `type`=2 LIMIT 1) AND
                                 `gid`={$gid} LIMIT 1");
    if ($ans->num_rows === 0)
        throw new KBException(-100, "Not linking");

    // 删除
    $db->query("DELETE FROM `user-group` WHERE
                                 `uid`=(SELECT `uid` FROM `user` WHERE `username`='{$u}' AND `type`=2 LIMIT 1) AND
                                 `gid`={$gid} LIMIT 1");

    echo json_encode(['status' => 0, 'msg' => '']);


} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}