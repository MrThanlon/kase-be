<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/17
 * Time: 11:48
 * 下载评分表
 */

try {
    require '../../include/jwt.php';
    if ($_SERVER['REQUEST_METHOD'] !== 'GET')
        //bad request
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    if ($jwt['type'] !== 2 || !key_exists('pid', $_GET) ||
        !preg_match("/^[1-9]\d*$/AD", $_GET['pid'])) //保证纯数字
        throw new KBException(-100);
    $pid = (int)$_GET['pid'];
    //检查pid，拉取总分
    $ans = $db->query("SELECT `total`,`total_only` FROM `project` WHERE `pid`={$pid}");
    if ($ans->num_rows === 0)
        throw new KBException(-101);
    $res = $ans->fetch_row();
    $total = (int)$res[0];
    $total_only = $res[1] === '1' ? true : false;
    //拉取所有question
    $ans = $db->query("SELECT `name`,`pqid`,`max` FROM `question` WHERE `pid`={$pid}");


} catch (KBException $e) {
    //echo json_encode(['status_code' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    //echo json_encode(['status_code' => -200, 'msg' => 'Unknow error']);
}