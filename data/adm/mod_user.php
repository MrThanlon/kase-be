<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/15
 * Time: 11:42
 * 分配评审到分区
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

    // FIXME: 检测账号是否为评审

    $gid = (int)$_POST['gid'];
    $u = $_POST['u'];
    // 检查是否已存在
    $ans = $db->query("SELECT 1 FROM `user-group` WHERE
                                 `uid`=(SELECT `uid` FROM `user` WHERE `username`='{$u}' AND `type`=2 LIMIT 1) AND
                                 `gid`={$gid} LIMIT 1");
    if ($ans->num_rows) {
        // 已存在，不用操作
        echo json_encode(['status' => 0, 'msg' => '']);
        exit;
    }

    // 检查是否在同一项目下
    $ans = $db->query("SELECT 1 FROM `user-project` WHERE
                                   `uid`=(SELECT `uid` FROM `user` WHERE `username`='{$u}' AND `type`=2 LIMIT 1) AND
                                   `pid`=(SELECT `pid` FROM `pgroup` WHERE `gid`={$gid} LIMIT 1) LIMIT 1");
    if ($ans->num_rows === 0)
        throw new KBException(-100, "Not in one project");

    // 插入
    $db->query("INSERT INTO `user-group` (`uid`,`gid`) SELECT `uid`,{$gid} FROM `user` WHERE `username`='{$u}' AND `type`=2 LIMIT 1");
    if ($db->affected_rows !== 1) {
        throw new KBException(-200,"Database might broken");
    }
    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}