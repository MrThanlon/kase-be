<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/14
 * Time: 14:50
 * 拉取项目下的申报材料列表
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
    if ($jwt['type'] !== 3 || !key_exists('pid', $_POST) || !preg_match("/^\d*?$/AD", $_POST['pid']))
        throw new KBException(-100);
    $pid = $_POST['pid'];
    //检查pid
    $ans = $db->query("SELECT 1 FROM `project` WHERE `pid`={$pid}");
    if ($ans->num_rows === 0)
        throw new KBException(-101);
    $data = [];
    //拉取
    $ans = $db->query("SELECT `name`,`cid`,`applicant`,`uid`,`status` FROM `content` WHERE `pid`={$pid}");
    for ($i = $ans->num_rows; $i > 0; $i--) {
        $data[] = $ans->fetch_assoc();
    }
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