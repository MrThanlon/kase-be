<?php
/**
 * 上传申报材料
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
        !preg_match("/^\d*?$/AD", $_POST['pid']) ||
        !isset($_FILES) ||
        !key_exists('zip', $_FILES))
        throw new KBException(-100);

    //检查pid
    $pid = (int)$_POST['pid'];
    $ans = $db->query("SELECT 1 FROM `project` WHERE
                              `pid`={$pid} AND
                              `start`=<CURRENT_TIMESTAMP AND
                              `end`>=CURRENT_TIMESTAMP");
    if ($ans->num_rows === 0)
        throw new KBException(-101);

    //读取zip
    $zip = new ZipArchive;
    if ($zip->open($_FILES['zip']['tmp_name']) !== true)
        throw new KBException(-109);


    echo json_encode([
        'status' => 0,
        'msg' => ''
    ]);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}