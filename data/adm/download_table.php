<?php
/**
 * 下载评审的打分表，文件名为<用户名>-<实际文件名>
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
        throw new KBException(-100);
    if (!key_exists('u', $_GET))
        throw new KBException(-100);

    $u = $_GET['u'];
    $ans = $db->query("SELECT `name`,`uid` FROM `tables` WHERE
                                  `uid`=(SELECT `uid` FROM `user` WHERE `username`='{$u}' AND `type`=2 LIMIT 1) LIMIT 1");
    if ($ans->num_rows === 0)
        throw new KBException(-41);
    $res = $ans->fetch_row();
    $name = $u . "-" . $res[0];

    //清除缓冲区
    ob_clean();

    //文件下载
    $path = FILE_DIR . "/{$res[1]}t";
    if (!is_file($path) || !is_readable($path))
        //无法读取文件
        throw new KBException(-110);
    $f = fopen($path, 'rb');
    if ($f === false)
        throw new KBException(-110);
    //文件类型是二进制流，设置为utf8编码（支持中文文件名称）
    header('Content-type:application/octet-stream; charset=utf-8');
    header("Content-Transfer-Encoding: binary");
    header("Accept-Ranges: bytes");
    //文件大小
    header("Content-Length: " . filesize($path));
    //触发浏览器文件下载功能，讲道理文件名应该urlencode，然而并不是
    header("Content-Disposition:attachment;filename=\"{$name}\"");
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