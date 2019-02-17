<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/1/26
 * Time: 11:15
 * 拉取申请材料的列表
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
    if ($jwt['type'] !== 1)
        throw new KBException(-100);
    $ans = $db->query(
        "SELECT `name`,`cid`,`pid`,`applicant`,`status` FROM `content` WHERE `uid`={$jwt['uid']}");
    $data = [];
    for ($i = $ans->num_rows; $i > 0; $i--) {
        $d = $ans->fetch_assoc();
        $d['cid'] = (int)$d['cid'];
        $d['pid'] = (int)$d['pid'];
        $d['status'] = (int)$d['status'];
        $data[] = $d;
    }
    //响应
    echo json_encode([
        'status_code' => 0,
        'msg' => '',
        'data' => $data
    ]);

} catch (KBException $e) {
    echo json_encode(['status_code' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status_code' => -200, 'msg' => 'Unknow error']);
}

