<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/15
 * Time: 11:42
 * 分配审核到分区
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
    if ($jwt['type'] !== 3 || !key_exists('gid', $_POST) || !preg_match("/^\d*?$/AD", $_POST['gid']) ||
        !key_exists('cid', $_POST) || !preg_match("/^\d*?$/AD", $_POST['cid']))
        throw new KBException(-100);
    $gid = (int)$_POST['gid'];
    $cid = (int)$_POST['cid'];


} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}