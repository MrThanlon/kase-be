<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/17
 * Time: 14:34
 * 添加评分内容
 */

try {
    require_once __DIR__ . '/../../include/jwt.php';
    header('Content-type: application/json');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    if ($jwt['type'] !== 3)
        throw new KBException(-100);
    if (!key_exists('pid', $_POST) ||
        !preg_match("/^[1-9]\d*$/AD", $_POST['pid']) ||
        !key_exists('name', $_POST) ||
        !key_exists('max', $_POST) ||
        !preg_match("/^[1-9]\d*$/AD", $_POST['max']))
        throw new KBException(-100);
    $pid = (int)$_POST['pid'];
    $name = $db->escape_string($_POST['name']);
    $comment = key_exists('comment', $_POST) ? $db->escape_string($_POST['comment']) : '';
    $max = (int)$_POST['max'];
    //检查pid
    $ans = $db->query("SELECT 1 FROM `project` WHERE `pid`={$pid} LIMIT 1");
    if ($ans->num_rows === 0)
        throw new KBException(-101);
    //计算pqid
    $ans = $db->query("SELECT 1 FROM `question` WHERE `pid`={$pid}");
    $pqid = $ans->num_rows + 1;
    //插入
    $db->query("INSERT INTO `question` (`name`,`comment`,`pqid`,`max`,`pid`) VALUES ('{$name}','{$comment}',{$pqid},{$max},{$pid})");
    if ($db->sqlstate !== '00000')
        throw new KBException(-60);
    echo json_encode(['status' => 0, 'msg' => '', 'qid' => $db->insert_id, 'pqid' => $pqid]);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}