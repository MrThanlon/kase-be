<?php
/**
 * 错误处理
 */
require_once __DIR__ . '/log.php';

class KBException extends Exception
{
    private static $msg = [
        -1 => 'login failed',
        -10 => 'Expired token',
        -12 => 'Expired short token',
        -20 => 'Message failed',
        -30 => 'Phone number does not exist',
        -40 => 'Repeat registration',
        -50 => 'File is not standardized',
        -100 => 'Bad request',
        -101 => 'Wrong pid',
        -102 => 'Unexpeted symbol',
        -103 => 'Wrong cid',
        -104 => 'Disk space is not enough',
        -105 => 'Permision denied : FILE_DIR is not writable',
        -106 => 'Unable to save file',
        -107 => 'Wrong configuration: FILE_DIR is not a directory',
        -108 => 'Unknow PDF file format',
        -109 => 'Unknow ZIP file format',
        -110 => 'Unable to read file',
        -111 => 'content has been reviewed',
        -112 => 'Wrong gid',
        -113 => 'score is too high',
        -114 => 'Wrong qid',
        -115 => 'Unacceptable table',
        -200 => 'Unknow error'
    ];

    public function __construct($code = -200, $message = "", Throwable $previous = null)
    {
        if (!$message)
            $message = KBException::$msg[$code];
        errlog($code, "[{$this->getLine()}]{$message}");
        parent::__construct($message, $code, $previous);
    }
}