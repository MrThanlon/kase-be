<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/11
 * Time: 15:25
 * 上传ZIP压缩格式的附件
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
    if (!key_exists('cid', $_POST))
        throw new KBException(-100);
    if (!isset($_FILES))
        throw new KBException(-100);
    if (!key_exists('zip', $_FILES))
        //没有文件上传个毛
        throw new KBException(-100);
    if (!preg_match('/\.zip$/iD', $_FILES['zip']['name']))
        //文件名不规范
        throw new KBException(-50);
    foreach (['/', '\\', ':', '*', '"', '<', '>', '|', '?'] as $val) {
        if (strpos($_FILES['zip']['name'], $val) !== false)
            throw new KBException(-50);
    }

    //查找是否存在cid
    $cid = $_POST['cid'];
    if (!preg_match("/^\d*?$/AD", $cid))
        throw new KBException(-100);
    $ans = $db->query("SELECT `zip_name`,`pid`,`status` FROM `content` WHERE `cid`={$cid} LIMIT 1");
    if ($ans->num_rows === 0)
        throw new KBException(-103);
    $res = $ans->fetch_row();
    if ($res[0] !== null)
        throw new KBException(-100);
    $status = (int)$res[2];

    // 检查时间
    $pid = (int)$res[1];
    $ans = $db->query("SELECT 1 FROM `project` WHERE
                              `pid`={$pid} AND
                              `start`<=CURRENT_TIMESTAMP AND
                              `end`>=CURRENT_TIMESTAMP");
    if ($ans->num_rows === 0)
        throw new KBException(-103, "Time exceeded");

    //检查zip文件是否正常
    $zip = new ZipArchive;
    if ($zip->open($_FILES['zip']['tmp_name']) !== true)
        throw new KBException(-109);
    $zip->close();

    //更新zip_name
    $name = $db->escape_string(substr($_FILES['zip']['name'], 0, -4));
    $db->query("UPDATE `content` SET `zip_name`='{$name}',`status`=0 WHERE `cid`={$cid}");
    //保存文件
    if (!is_dir(FILE_DIR))
        throw new KBException(-107);
    if (disk_free_space(FILE_DIR) <= $_FILES['zip']['size'])
        throw new KBException(-104);
    if (!is_writable(FILE_DIR))
        throw new KBException(-105);
    if (!move_uploaded_file($_FILES['zip']['tmp_name'], FILE_DIR . "/{$cid}z"))
        throw new KBException(-106);
    echo json_encode([
        'status' => 0,
        'msg' => ''
    ]);

} catch (KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}