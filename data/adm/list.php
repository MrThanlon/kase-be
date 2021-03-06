<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/14
 * Time: 13:59
 * 拉取项目列表，全部的
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
        throw new KBException(-100, "Wrong user type: {$jwt['type']}.");
    $ans = $db->query("SELECT `name`,`pid`,`groups`,`contents`,UNIX_TIMESTAMP(`start`),UNIX_TIMESTAMP(`end`) FROM `project`");
    $data = [];
    for ($i = $ans->num_rows; $i > 0; $i--) {
        $d = $ans->fetch_assoc();
        $d['pid'] = (int)$d['pid'];
        $d['groups'] = (int)$d['groups'];
        $d['contents'] = (int)$d['contents'];
        $d['start'] = (int)$d['UNIX_TIMESTAMP(`start`)'];
        $d['end'] = (int)$d['UNIX_TIMESTAMP(`end`)'];
        unset($d['UNIX_TIMESTAMP(`start`)']);
        unset($d['UNIX_TIMESTAMP(`end`)']);
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