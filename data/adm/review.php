<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/14
 * Time: 15:02
 * 审核
 */

try {
    require_once __DIR__ . '/../../include/jwt.php';
    require_once __DIR__ . '/../../include/sms.php';
    header('Content-type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        //bad request
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    if ($jwt['type'] !== 3 || !key_exists('cid', $_POST) || !preg_match("/^\d*?$/AD", $_POST['cid']) ||
        !key_exists('result', $_POST) || ($_POST['result'] !== '1' && $_POST['result'] !== '2'))
        throw new KBException(-100);
    //查找cid
    $cid = $_POST['cid'];
    $ans = $db->query("SELECT `status`,`name`,`uid`,`pid` FROM `content` WHERE `cid`={$cid}");
    if ($ans->num_rows === 0)
        throw new KBException(-103);
    $res = $ans->fetch_row();

    // 允许重复审核
    //if ($res[0] !== '0')
    //    throw new KBException(-111);

    //执行审核
    $status = (int)$_POST['result'];
    $name = $res[1];
    $uid = (int)$res[2];
    $pid = (int)$res[3];
    $db->query("UPDATE `content` SET `status`={$status} WHERE `cid`={$cid}");
    if ($db->sqlstate !== '00000')
        throw new KBException(-60);

    //TODO:短信通知
    $ans = $db->query("SELECT `username` FROM `user` WHERE `uid`={$uid} LIMIT 1");
    $res = $ans->fetch_row();
    //snotice($status === 1, $cid, $name, $res[0]);

    //响应
    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}