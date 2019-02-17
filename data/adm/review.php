<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/14
 * Time: 15:02
 * 审核
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
    if ($jwt['type'] !== 3 || !key_exists('cid', $_POST) || !preg_match("/^\d*?$/AD", $_POST['cid']) ||
        !key_exists('result', $_POST) || ($_POST['result'] !== '1' && $_POST['result'] !== '2'))
        throw new KBException(-100);
    //查找cid
    $cid = $_POST['cid'];
    $ans = $db->query("SELECT `status` FROM `content` WHERE `cid`={$cid}");
    if ($ans->num_rows === 0)
        throw new KBException(-103);
    $res = $ans->fetch_row();
    if ($res[0] !== 0)
        throw new KBException(-111);
    $status = $_POST['result'] === 1 ? 1 : -1;
    $db->query("UPDATE `content` SET `status`={$status} WHERE `cid`={$cid}");
    if ($db->sqlstate !== '00000')
        throw new KBException(-60);
    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status_code' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status_code' => -200, 'msg' => 'Unknow error']);
}