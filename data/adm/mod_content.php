<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/15
 * Time: 11:42
 * 分配材料到材料组
 */

try {
    header('Content-type: application/json');
    require '../../include/jwt.php';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        //bad request
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    if ($jwt['type'] !== 3 || !key_exists('gid', $_POST) || !preg_match("/^\d*?$/AD", $_POST['gid']) ||
        !key_exists('cid', $_POST) || !preg_match("/^\d*?$/AD", $_POST['cid']))
        throw new KBException(-100);
    $gid = (int)$_POST['gid'];
    $cid = (int)$_POST['cid'];
    //检查cid，材料需要被审核通过了才能分配
    $ans = $db->query("SELECT `status`,`pid` FROM `content` WHERE `cid`={$cid}");
    if ($ans->num_rows === 0)
        throw new KBException(-103);
    $res = $ans->fetch_row();
    if ($res[0] !== '1')
        throw new KBException(-103);
    $pid = (int)$res[1];
    //检查gid和pid
    $ans = $db->query("SELECT `pid` FROM `pgroup` WHERE `gid`={$gid}");
    if ($ans->num_rows === 0)
        throw new KBException(-103);
    $res = $ans->fetch_row();
    if ((int)$res[0] !== $pid)
        throw new KBException(-112);
    //检查是否已存在关联，存在的话就没事了
    $ans = $db->query("SELECT `id` FROM `content-group` WHERE `cid`={$cid} AND `gid`={$gid}");
    if ($ans->num_rows !== 0)
        echo json_encode(['status' => 0, 'msg' => '']);
    //插入
    $db->query("INSERT INTO `content-group` (`cid`,`gid`) VALUES ({$cid},{$gid})");
    if ($db->sqlstate !== '00000')
        throw new KBException(-60);
    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status_code' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status_code' => -200, 'msg' => 'Unknow error']);
}