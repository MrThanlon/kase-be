<?php
/**
 * 删除课题分区
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
    if (!key_exists('gid', $_POST))
        throw new KBException(-100);
    if (!preg_match("/^\d*?$/AD", $_POST['gid']))
        throw new KBException(-100);

    $gid = (int)$_POST['gid'];
    $ans = $db->query("SELECT 1 FROM `pgroup` WHERE `gid`={$gid}");
    if ($ans->num_rows === 0)
        throw new KBException(-112);

    $db->query("DELETE FROM `pgroup` WHERE `gid`={$gid} LIMIT 1");
    $db->query("DELETE FROM `user-group` WHERE `gid`={$gid}");
    $db->query("DELETE FROM `content-group` WHERE `gid`={$gid}");

    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}