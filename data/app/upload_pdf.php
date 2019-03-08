<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/11
 * Time: 13:15
 * 上传pdf
 */

try {
    header('Content-type: application/json');
    require '../../include/jwt.php';
    require '../../vendor/autoload.php';
    $parser = new \Smalot\PdfParser\Parser();

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
    if (!key_exists('pdf', $_FILES))
        //没有文件上传个毛
        throw new KBException(-100);
    if (!preg_match('/\.pdf$/iD', $_FILES['pdf']['name']))
        //文件名不规范
        throw new KBException(-50);
    foreach (['/', '\\', ':', '*', '"', '<', '>', '|', '?'] as $val) {
        if (strpos($_FILES['pdf']['name'], $val) !== false)
            throw new KBException(-50);
    }
    //查找是否存在cid
    $cid = $_POST['cid'];
    if (!preg_match("/^\d*?$/AD", $cid))
        throw new KBException(-100);
    $ans = $db->query("SELECT `pdf_name` FROM `content` WHERE `cid`={$cid} LIMIT 1");
    if ($ans->num_rows === 0)
        throw new KBException(-103);
    $res = $ans->fetch_row();
    if ($res[0] !== '')
        throw new KBException(-100);
    //检查pdf文件是否正常
    try {
        $pdf = $parser->parseFile($_FILES['pdf']['tmp_name']);
    } catch (Exception $e) {
        throw new KBException(-108);
    }
    //更新pdf_name
    $name = $db->escape_string(substr($_FILES['pdf']['name'], 0, -4));
    $ans = $db->query("UPDATE `content` SET `pdf_name`='{$name}' WHERE `cid`={$cid}");
    //保存文件
    if (!is_dir(FILE_DIR))
        throw new KBException(-107);
    if (disk_free_space(FILE_DIR) <= $_FILES['pdf']['size'])
        throw new KBException(-104);
    if (!is_writable(FILE_DIR))
        throw new KBException(-105);
    if (!move_uploaded_file($_FILES['pdf']['tmp_name'], FILE_DIR . "/{$cid}p"))
        throw new KBException(-106);
    echo json_encode([
        'status_code' => 0,
        'msg' => ''
    ]);

} catch (KBException $e) {
    echo json_encode(['status_code' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status_code' => -200, 'msg' => 'Unknow error']);
}