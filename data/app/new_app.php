<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/4
 * Time: 11:23
 * 创建新申请
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
    if ($jwt['type'] !== 1)
        throw new KBException(-100);
    //检测请求参数
    $keys = ['name', 'pid', 'applicant'];
    foreach ($keys as $val) {
        if (!key_exists($val, $_POST))
            throw new KBException(-100);
    }
    //检测是否存在此pid，pid为纯数字
    if (!preg_match("/^\d*?$/AD", $_POST['pid']))
        throw new KBException(-101);
    $ans = $db->query("SELECT 1 FROM `project` WHERE
                              `pid`={$_POST['pid']} AND
                              `start`<=CURRENT_TIMESTAMP AND
                              `end`>=CURRENT_TIMESTAMP");
    if ($ans->num_rows === 0)
        throw new KBException(-101);

    //插入content
    //SQL过滤
    $name = $db->escape_string($_POST['name']);
    $applicant = $db->escape_string($_POST['applicant']);
    //HTML标签检测
    if (strpos($name, '<') !== false || strpos($applicant, '<') !== false)
        throw new KBException(-102);
    //插入
    $db->query("INSERT INTO `content` (`name`,`pid`,`applicant`,`status`,`uid`)" .
        " VALUES ('{$name}',{$_POST['pid']},'{$applicant}',0,{$jwt['uid']})");
    //响应
    if ($db->sqlstate !== '00000')
        //插入失败
        throw new KBException(-60);
    $cid = $db->insert_id;
    //更新
    $db->query("UPDATE `project` SET `contents`=`contents`+1 WHERE `pid`={$_POST['pid']} LIMIT 1");
    echo json_encode([
        'status' => 0,
        'msg' => '',
        'cid' => $cid
    ]);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}