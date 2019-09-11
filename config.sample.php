<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 2019/1/21
 * Time: 15:46
 * 一般配置
 */
//服务器相关
//域名
define('DOMAIN', 'localhost');
//网站路径，根目录为'/'
define('PATH', '/');
//cookie有效期，单位：秒
define('EXPIRE', 2592000);
//文件存储路径
define('FILE_DIR', __DIR__ . "/storage");
//跨域
define('CORS', 'http://localhost:8080');

//短信相关，使用腾讯云SMS，暂时未可用
//短信有效期，单位：秒
define('SMS_PERIOD', 600);
//SDK AppID
define('SMS_APPID', 9700);
//App Key
define('SMS_APPKEY', 'App key');
//短信验证码模板ID，只需要一个参数，示例：[评审平台]验证码为{1}，请于10分钟内使用。
define('SMS_TPLID_SCODE', 1234);
//短信通知模板ID，需要三个参数，示例：[评审平台]您的课题：[{1}]已{2}，请打开https://example.com/kase/#/pannel/article/{3}查看。
define('SMS_TPLID_NOTICE', 5678);
//短信登录模板ID，需要1个参数，也就是token，示例：[评审平台]您正在登录评审平台，点此打开https://ch34k.xyz/kase/#/token/{1}
define('SMS_TPLID_URL', 6666);

//数据库相关
//数据库主机
define('DB_HOST', 'p:127.0.0.1');
//数据库账户
define('DB_USER', 'kase');
//数据库密码
define('DB_PASS', 'kase-password');
//数据库名
define('DB_NAME', 'kase');
//数据库端口号，默认3306
define('DB_PORT', 3306);

//用户相关
//密码哈希盐
define('HASH_SALT', 'asjfhejwnkdjcw');
//HMAC签名密码
define('SECRET', 'MasterZYF--sahjfb vdnwmef');
//超级管理员用户名
define('ADMIN_USERNAME', 'root');
//超级管理员密码
define('ADMIN_PASSWORD', 'hello');
