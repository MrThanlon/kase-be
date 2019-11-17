<?php
// 批量下载，使用JSON上传数组

try {
    require_once __DIR__ . '/../../include/jwt.php';

    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    if ($jwt['type'] !== 3)
        throw new KBException(-100);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
        $_SERVER['CONTENT_TYPE'] === 'application/json') {
        // 从json解析数据
        $postjson = file_get_contents("php://input");
        $data = json_decode($postjson, false);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // 从URL参数解析数据
        $url = parse_url($_SERVER['REQUEST_URI']);
        $query = explode('&', $url['query']);
        $data = array_reduce($query, function ($pre, $cur) {
            if (substr($cur, 0, 5) === "user=") {
                $pre[] = substr($cur, 5);
                return $pre;
            } else
                return $pre;
        }, []);
    } else
        throw new KBException(-100);

    // 检查是否上传评分表
    $num = sizeof($data);
    $sql = '\'' . implode('\',\'', $data) . '\'';
    $ans = $db->query("SELECT `name`,`uid` FROM `tables` WHERE `uid` IN
                                  (SELECT `uid` FROM `user` WHERE `username` IN ({$sql}))");
    if ($ans->num_rows !== $num)
        throw new KBException(-100);
    $tn = $ans->fetch_all();

    // 拉取username-uid
    $ans = $db->query("SELECT `username`,`uid` FROM `user` WHERE `username` IN ({$sql})");
    $u = $ans->fetch_all();
    $usernames = array_reduce($u, function ($pre, $cur) {
        $pre[(int)$cur[1]] = $cur[0];
        return $pre;
    }, []);

    //检查文件大小
    $size = 0;
    foreach ($tn as $val) {
        if (!is_readable(FILE_DIR . "/{$val[1]}t"))
            throw new KBException(-200, "File is not readable");

        $size += filesize(FILE_DIR . "/{$val[1]}t");
    }
    $path = tempnam('', '');
    if (disk_free_space($path) <= $size) {
        unlink($path);
        throw new KBException(-104);
    }

    // 打包数据
    $zip = new ZipArchive;
    $zip->open($path, ZipArchive::CREATE);
    foreach ($tn as $val) {
        if (!$zip->addFile(FILE_DIR . "/{$val[1]}t", $usernames[$val[1]] . "-" . $val[0])) {
            $zip->close();
            unlink($path);
            throw new KBException(-200, "Failed to zip {$usernames[$val[1]]} file, name: {$val[0]}");
        }
    }
    $zip->close();

    //清除缓冲区
    ob_clean();

    //文件下载
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
    header("Content-Disposition:attachment;filename=\"tables.zip\"");
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