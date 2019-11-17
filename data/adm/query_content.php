<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/14
 * Time: 14:50
 * 拉取项目下的申报材料列表
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
        !preg_match("/^\d*?$/AD", $_POST['pid']))
        throw new KBException(-100);

    $pid = (int)$_POST['pid'];
    //检查pid
    $ans = $db->query("SELECT 1 FROM `project` WHERE `pid`={$pid}");
    if ($ans->num_rows === 0)
        throw new KBException(-101);
    $data = [];
    //拉取
    $ans = $db->query("SELECT `name`,`cid`,`applicant`,`status`,`time`,`pdf_name` FROM `content` WHERE `pid`={$pid}");
    for ($i = $ans->num_rows; $i > 0; $i--) {
        $d = $ans->fetch_assoc();
        $d['cid'] = (int)$d['cid'];
        $d['status'] = (int)$d['status'];
        $d['pdf'] = $d['pdf_name'] ? true : false;
        unset($d['pdf_name']);
        $data[] = $d;
    }
    echo json_encode([
        'status' => 0,
        'msg' => '',
        'data' => $data
    ]);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}