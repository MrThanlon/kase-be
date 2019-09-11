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
        //bad request
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    if ($jwt['type'] !== 3)
        throw new KBException(-100);
    if (!key_exists('pid', $_POST) || !preg_match("/^[1-9]\d*$/AD", $_POST['pid']) ||
        !key_exists('name', $_POST) || !key_exists('max', $_POST) ||
        !preg_match("/^[1-9]\d*$/AD", $_POST['max']))
        throw new KBException(-100);
    $pid = (int)$_POST['pid'];
    $name = $db->escape_string($_POST['name']);
    $comment = key_exists('comment', $_POST) ? $db->escape_string($_POST['comment']) : '';
    $max = (int)$_POST['max'];
    //检查pid
    $ans = $db->query("SELECT `total` FROM `project` WHERE `pid`={$pid}");
    if ($ans->num_rows === 0)
        throw new KBException(-101);
    $res = $ans->fetch_row();
    $total = (int)$res[0];
    //检查分值是否超过total
    $ans = $db->query("SELECT `max` FROM `question` WHERE `pid`={$pid}");
    $current = 0;
    //pqid需要从1开始，0表示只打总分
    $pqid = $ans->num_rows + 1;
    $res = $ans->fetch_all();
    foreach ($res as $val) {
        $current += (int)$val[0];
    }
    if ($max + $current > $total)
        throw new KBException(-113);
    //没超过，可以插入
    $db->query("INSERT INTO `question` (`name`,`comment`,`pqid`,`max`,`pid`) VALUES ('{$name}','{$comment}',{$pqid},{$max},{$pid})");
    if ($db->sqlstate !== '00000')
        throw new KBException(-60);
    echo json_encode(['status' => 0, 'msg' => '', 'qid' => $db->insert_id, 'pqid' => $pqid]);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}