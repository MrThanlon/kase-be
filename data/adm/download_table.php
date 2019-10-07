<?php
/**
 * 上传评审材料
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


} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}