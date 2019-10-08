<?php
/**
 * 上传评审材料
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
    if (!key_exists('pid', $_POST))
        throw new KBException(-100);
    if (!preg_match("/^\d*?$/AD", $_POST['pid']))
        throw new KBException(-100);
    if (!isset($_FILES))
        throw new KBException(-100);
    if (!key_exists('zip', $_FILES))
        //没有文件上传个毛
        throw new KBException(-100);

    //检查pid
    $pid = (int)$_POST['pid'];
    $ans = $db->query("SELECT 1 FROM `project` WHERE `pid`={$pid}");
    if ($ans->num_rows === 0)
        throw new KBException(-101);

    // 文件名过滤
    foreach (['/', '\\', ':', '*', '"', '<', '>', '|', '?'] as $val) {
        if (strpos($_FILES['zip']['name'], $val) !== false)
            throw new KBException(-50);
    }

    //保存文件名
    $db->query("UPDATE `project` SET `jug`='{$_FILES['zip']['name']}' WHERE `pid`={$pid}");
    if ($db->error || $db->affected_rows === 0)
        throw new KBException(-60);

    //保存文件，存储到 /<pid>jug
    if (!is_dir(FILE_DIR))
        throw new KBException(-107);
    if (disk_free_space(FILE_DIR) <= $_FILES['zip']['size'])
        throw new KBException(-104);
    if (!is_writable(FILE_DIR))
        throw new KBException(-105);
    if (!move_uploaded_file($_FILES['zip']['tmp_name'], FILE_DIR . "/{$pid}jug"))
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