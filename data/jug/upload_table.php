<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/17
 * Time: 15:17
 * 上传打分表，解析表中数据
 * update: 2019/10/7 修改上传逻辑，不解析数据
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
    if ($jwt['type'] !== 2)
        throw new KBException(-100);
    if (!key_exists('pid', $_POST) ||
        !preg_match("/^[1-9]\d*$/AD", $_POST['pid']) ||
        !key_exists('file', $_FILES))
        throw new KBException(-100);

    // 文件名过滤
    foreach (['/', '\\', ':', '*', '"', '<', '>', '|', '?'] as $val) {
        if (strpos($_FILES['file']['name'], $val) !== false)
            throw new KBException(-50);
    }

    $pid = (int)$_POST['pid'];
    //检查pid关联
    $ans = $db->query("SELECT 1 FROM `user-project` WHERE `pid`={$pid} AND `uid`={$jwt['uid']} LIMIT 1");
    if ($ans->num_rows === 0)
        throw new KBException(-101);

    //更新数据库
    //允许重复上传覆盖
    $name = $db->escape_string($_FILES['file']['name']);
    $ans = $db->query("SELECT 1 FROM `tables` WHERE `uid`={$jwt['uid']} LIMIT 1");
    if ($ans->num_rows === 0) {
        $db->query("INSERT INTO `tables` (`name`,`uid`) VALUES ('{$name}',{$jwt['uid']})");
        if ($db->affected_rows === 0)
            throw new KBException(-60, $db->error);
    } else {
        $db->query("UPDATE `tables` SET `name`='{$name}',`time`=CURRENT_TIMESTAMP WHERE `uid`={$jwt['uid']} LIMIT 1");
    }
    if ($db->error)
        throw new KBException(-60, $db->error);

    //保存文件到/<uid>t
    if (!is_dir(FILE_DIR))
        throw new KBException(-107);
    if (disk_free_space(FILE_DIR) <= $_FILES['file']['size'])
        throw new KBException(-104);
    if (!is_writable(FILE_DIR))
        throw new KBException(-105);
    if (!move_uploaded_file($_FILES['file']['tmp_name'], FILE_DIR . "/{$jwt['uid']}t"))
        throw new KBException(-106);

    //响应
    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}