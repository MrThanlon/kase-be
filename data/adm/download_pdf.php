<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/14
 * Time: 13:59
 * 下载PDF
 */

try {
    require_once __DIR__ . '/../../include/jwt.php';
    if ($_SERVER['REQUEST_METHOD'] !== 'GET')
        //bad request
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    if ($jwt['type'] !== 3)
        throw new KBException(-100, "Wrong user type: {$jwt['type']}.");
    if (!key_exists('cid', $_GET) ||
        !preg_match("/^[1-9]\d*$/AD", $_GET['cid']))
        throw new KBException(-100);

    $cid = (int)$_GET['cid'];
    $ans = $db->query("SELECT `pdf_name` FROM `content` WHERE `cid`={$cid} LIMIT 1");
    if ($ans->num_rows === 0)
        throw new KBException(-103);
    $name = $ans->fetch_row()[0];
    $name = urlencode($name);
    //清除缓冲区
    ob_clean();
    //读取文件
    $path = FILE_DIR . "/{$cid}p";
    if (!is_file($path) || !is_readable($path))
        //无法读取文件
        throw new KBException(-110);
    $f = fopen($path, 'rb');
    if ($f === false)
        throw new KBException(-110);
    //文件类型是二进制流，设置为utf8编码（支持中文文件名称）
    header("Access-Control-Expose-Headers: Content-Disposition");
    header('Content-type:application/octet-stream; charset=utf-8');
    header("Content-Transfer-Encoding: binary");
    header("Accept-Ranges: bytes");
    //文件大小
    header("Content-Length: " . filesize($path));
    //触发浏览器文件下载功能，讲道理文件名应该urlencode，然而并不是
    header("Content-Disposition:attachment;filename*=UTF-8''\"{$name}.pdf\"");
    //循环读取文件内容，并输出
    while (!feof($f)) {
        //从文件指针 handle 读取最多 length 个字节（每次输出10k）
        echo fread($f, 10240);
    }
    //关闭文件流
    fclose($f);

} catch (KBException $e) {
    //echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    //echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
}