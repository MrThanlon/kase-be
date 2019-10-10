<?php
/**
 * 上传申报材料
 */

try {
    require_once __DIR__ . '/../../include/jwt.php';
    require_once __DIR__ . '/../../vendor/autoload.php';

    header('Content-type: application/json');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        //bad request
        throw new KBException(-100);
    if (!key_exists('token', $_COOKIE))
        throw new KBException(-10);
    $jwt = jwt_decode($_COOKIE['token']);
    if ($jwt['type'] !== 3 ||
        !key_exists('pid', $_POST) ||
        !preg_match("/^\d*?$/AD", $_POST['pid']) ||
        !isset($_FILES) ||
        !key_exists('zip', $_FILES))
        throw new KBException(-100);

    //检查pid
    $pid = (int)$_POST['pid'];
    $ans = $db->query("SELECT 1 FROM `project` WHERE
                              `pid`={$pid} AND
                              `start`<=CURRENT_TIMESTAMP AND
                              `end`>=CURRENT_TIMESTAMP");
    if ($ans->num_rows === 0)
        throw new KBException(-101);

    //读取zip
    $zip = new ZipArchive;
    if ($zip->open($_FILES['zip']['tmp_name']) !== true)
        throw new KBException(-109);

    //解压
    $path = tempnam(sys_get_temp_dir(), '') . "zip";
    mkdir($path);
    $zip->extractTo($path);
    $zip->close();

    //解析
    $sqls = [];
    $parser = new \Smalot\PdfParser\Parser();
    $z2 = new ZipArchive;
    $applicant = [];
    // 过滤 . 和 ..
    foreach (array_filter(scandir($path), function ($v) {
        return !($v === "." || $v === "..");
    }) as $app) {
        // 解析申请人
        $applicant[$app] = [];

        foreach (array_filter(scandir($path . "/" . $app), function ($v) {
            return !($v === "." || $v === "..");
        }) as $name) {
            // 解析项目
            $files = array_filter(scandir($path . "/" . $app . "/" . $name), function ($v) {
                return !($v === "." || $v === "..");
            });
            // 最多两个文件，至少一个文件
            if (sizeof($files) !== 1 && sizeof($files) !== 2)
                throw new KBException(-110, "Can not parse file");

            $pdf_sql = "NULL";
            $zip_sql = "NULL";

            foreach ($files as $file) {
                // 解析文件
                if (substr($file, -4) === ".pdf") {
                    try {
                        $parser->parseFile($path . "/" . $app . "/" . $name);
                    } catch (Exception $e) {
                        throw new KBException(-110, "Can not parse file");
                    }
                    $applicant[$app][$name]['pdf'] = $file;
                    $pdf_sql = "'{$file}'";
                } elseif (substr($file, -4) === ".zip") {
                    if (!$z2->open($path . "/" . $app . "/" . $name))
                        throw new KBException(-110, "Can not parse file");
                    $z2->close();
                    $applicant[$app][$name]['zip'] = $file;
                    $zip_sql = "'{$file}'";
                } else {
                    throw new KBException(-110, "Can not parse file");
                }
            }
            $sqls[] = "('{$name}',{$pid},'{$app}',1,{$jwt['uid']},{$pdf_sql},{$zip_sql})";
        }
    }

    $sql = implode(",", $sqls);
    // 插入
    $db->query("INSERT INTO `content` (`name`,`pid`,`applicant`,`status`,`uid`,`pdf_name`,`zip_name`) VALUES {$sql}");
    if ($db->affected_rows === 0)
        throw new KBException(-60);

    echo json_encode([
        'status' => 0,
        'msg' => ''
    ]);

} catch
(KBException $e) {
    echo json_encode(['status' => $e->getCode(), 'msg' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => -200, 'msg' => 'Unknow error']);
} finally {
    if (isset($path)) {
        function del($p)
        {
            if (is_dir($p)) {
                foreach (scandir($p) as $v) {
                    del($v);
                }
            } else
                unlink($p);
        }

        del($path);
    }
}