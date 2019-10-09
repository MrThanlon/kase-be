<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/2/4
 * Time: 11:13
 * 下载申报材料
 */

try {
    require_once __DIR__ . '/../../include/jwt.php';
    if ($_SERVER['REQUEST_METHOD'] !== 'GET')
        //bad request
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    if ($jwt['type'] !== 1 ||
        !key_exists('pid', $_GET) ||
        !preg_match("/^\d*?$/AD", $_GET['pid']))
        throw new KBException(-100);

    $pid = (int)$_GET['pid'];
    $ans = $db->query("SELECT `app` FROM `project` WHERE `pid`={$pid} LIMIT 1");
    if ($ans->num_rows == 0)
        throw new KBException(-101);

    $name = $ans->fetch_row()[0];
    if (!$name)
        throw new KBException(-100, "No file");

    //清除缓冲区
    ob_clean();

    //读取文件名
    $path = FILE_DIR . "/{$pid}app";
    if (!is_file($path) || !is_readable($path))
        //无法读取文件
        throw new KBException(-110, $path);
    $f = fopen($path, 'rb');
    if ($f === false)
        throw new KBException(-110, $path);
    //文件类型是二进制流，设置为utf8编码（支持中文文件名称）
    header('Content-type:application/octet-stream; charset=utf-8');
    header("Content-Transfer-Encoding: binary");
    header("Accept-Ranges: bytes");
    //文件大小
    header("Content-Length: " . filesize($path));
    //触发浏览器文件下载功能，讲道理文件名应该urlencode，然而并不是
    header("Content-Disposition:attachment;filename=\"{$name}.pdf\"");
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