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
    $pid = (int)$_POST['pid'];
    //检查pid关联
    $ans = $db->query("SELECT 1 FROM `user-project` WHERE `pid`={$pid} AND `uid`={$jwt['uid']} LIMIT 1");
    if ($ans->num_rows === 0)
        throw new KBException(-101);

    //更新数据库
    $ans = $db->query("SELECT 1 FROM `tables` WHERE `uid`={$jwt['uid']} LIMIT 1");
    if ($ans->num_rows !== 0)
        throw new KBException(-115, "Uploaded.");
    $name = $db->escape_string($_FILES['zip']['name']);
    $db->query("INSERT INTO `tables` (`name`,`uid`) VALUES ('{$name}',{$jwt['uid']})");
    if ($db->sqlstate !== '00000')
        throw new KBException(-60);

    //保存文件到/<uid>t
    if (!is_dir(FILE_DIR))
        throw new KBException(-107);
    if (disk_free_space(FILE_DIR) <= $_FILES['zip']['size'])
        throw new KBException(-104);
    if (!is_writable(FILE_DIR))
        throw new KBException(-105);
    if (!move_uploaded_file($_FILES['zip']['tmp_name'], FILE_DIR . "/{$jwt['uid']}t"))
        throw new KBException(-106);

    //响应
    echo json_encode(['status' => 0, 'msg' => '']);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}