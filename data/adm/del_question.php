<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/17
 * Time: 14:55
 * 删除评分内容
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
    if (!key_exists('qid', $_POST) || !preg_match("/^[1-9]\d*$/AD", $_POST['qid']))
        throw new KBException(-100);
    $qid = $_POST['qid'];
    $ans = $db->query("SELECT 1 FROM `question` WHERE `qid`={$qid} LIMIT 1");
    if ($ans->num_rows === 0)
        throw new KBException(-114);
    $db->query("DELETE FROM `question` WHERE `qid`={$qid} LIMIT 1");
    if ($db->sqlstate !== '00000')
        throw new KBException(-60);
    echo json_encode(['status' => 0, 'msg' => '']);
} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}