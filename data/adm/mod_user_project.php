<?php
/**
 * 分配评审到项目
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
        !key_exists('pid', $_POST) ||
        !preg_match("/^\d*?$/AD", $_POST['gid']) ||
        !key_exists('u', $_POST))
        throw new KBException(-100);

    $pid = (int)$_POST['pid'];
    $u = $_POST['u'];

    // FIXME: 检测账号是否为评审

    // 检查是否已存在
    $ans = $db->query("SELECT 1 FROM `user-project` WHERE
                                   `uid`=(SELECT `uid` FROM `user` WHERE `username`='{$u}' AND `type`=2 LIMIT 1) AND
                                   `pid`={$pid}");
    if ($ans->num_rows) {
        echo json_encode(['status' => 0, 'msg' => '']);
        exit;
    }

    // 插入
    $db->query("INSERT INTO `user-project` (`uid`,`pid`) SELECT `uid`,{$pid} FROM `user` WHERE `username`='{$u}' AND `type`=2 LIMIT 1");
    if ($db->affected_rows !== 1) {
        throw new KBException(-200, "Database might broken");
    }
    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}