<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/14
 * Time: 14:33
 * 创建材料组
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
    //检查是否存在pid
    $ans = $db->query("SELECT 1 FROM `project` WHERE `pid`={$pid}");
    if ($ans->num_rows === 0)
        throw new KBException(-100);
    //存在，插入
    $db->query("INSERT INTO `pgroup` (`pid`) VALUES ({$pid})");
    if ($db->sqlstate !== '00000')
        throw new KBException(-60);

    echo json_encode(['status' => 0, 'msg' => '', 'gid' => $db->insert_id]);

} catch (KBException $e) {
    echo json_encode(['status_code' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status_code' => -200, 'msg' => 'Unknow error']);
}