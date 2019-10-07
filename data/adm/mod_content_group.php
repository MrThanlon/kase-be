<?php
/**
 * 从分区移除课题
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
        !key_exists('cid', $_POST) ||
        !preg_match("/^\d*?$/AD", $_POST['cid']))
        throw new KBException(-100);

    $gid = (int)$_POST['gid'];
    $cid = (int)$_POST['cid'];
    //检查是否存在关联
    $ans = $db->query("SELECT `id` FROM `content-group` WHERE `cid`={$cid} AND `gid`={$gid} LIMIT 1");
    if ($ans->num_rows === 0)
        throw new KBException(-100, "Not linking");

    //删除
    $db->query("DELETE FROM `content-group` WHERE `cid`={$cid} AND `gid`={$gid} LIMIT 1");
    // FIXME: 检查是否真的去除了
    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}