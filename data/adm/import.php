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
    $ans = $db->query("SELECT 1 FROM `project` WHERE `pid`={$pid} LIMIT 1");
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
                        $parser->parseFile($path . "/" . $app . "/" . $name . "/" . $file);
                    } catch (Exception $e) {
                        throw new KBException(-110, "Can not parse file,{$e->getMessage()}");
                    }
                    $applicant[$app][$name]['pdf'] = substr($file, 0, -4);
                    $pdf_sql = "'{$applicant[$app][$name]['pdf']}'";
                } elseif (substr($file, -4) === ".zip") {
                    if (!$z2->open($path . "/" . $app . "/" . $name . "/" . $file))
                        throw new KBException(-110, "Can not parse file,{$e->getMessage()}");
                    $z2->close();
                    $applicant[$app][$name]['zip'] = substr($file, 0, -4);
                    $zip_sql = "'{$applicant[$app][$name]['zip']}'";
                } else {
                    throw new KBException(-110, "Can not parse file, not pdf or zip");
                }
            }
            $sqls[] = "('{$name}',{$pid},'{$app}',1,{$jwt['uid']},{$pdf_sql},{$zip_sql})";
        }
    }

    $sql = implode(",", $sqls);
    $num = sizeof($sqls);
    // 插入
    $db->query("INSERT INTO `content` (`name`,`pid`,`applicant`,`status`,`uid`,`pdf_name`,`zip_name`) VALUES {$sql}");
    if ($db->affected_rows === 0)
        throw new KBException(-60);

    //保存文件
    if (!is_dir(FILE_DIR))
        throw new KBException(-107);
    //FIXME: 准确判断文件大小
    if (disk_free_space(FILE_DIR) <= $_FILES['zip']['size'])
        throw new KBException(-104);
    if (!is_writable(FILE_DIR))
        throw new KBException(-105);

    $cid = $db->insert_id;
    foreach ($applicant as $app => $names) {
        foreach ($names as $name => $file) {
            if (isset($file['pdf'])) {
                rename("{$path}/{$app}/{$name}/{$file['pdf']}.pdf", FILE_DIR . "/{$cid}p");
            }
            if (isset($file['zip'])) {
                rename("{$path}/{$app}/{$name}/{$file['zip']}.zip", FILE_DIR . "/{$cid}z");
            }
            $cid += 1;
        }
    }

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
        unlink($path);
    }
}