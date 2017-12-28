<?php

return array(
    //网站配置信息
   // 'URL_MODEL'=>2,
    'xmpat' => 'D:\phpStudy\WWW', //上传视频文件根目录
    'URL' => 'http://localhost/', //网站根URL
    'COOKIE_SALT' => 'xm123', //设置cookie加密密钥
    //备份配置
    'DB_PATH_NAME' => 'dilian',        //备份目录名称,主要是为了创建备份目录
    'DB_PATH' => './db/',     //数据库备份路径必须以 / 结尾；
    'DB_PART' => '20971520',  //该值用于限制压缩后的分卷最大长度。单位：B；建议设置20M
    'DB_COMPRESS' => '1',         //压缩备份文件需要PHP环境支持gzopen,gzwrite函数        0:不压缩 1:启用压缩
    'DB_LEVEL' => '9',         //压缩级别   1:普通   4:一般   9:最高
    //扩展配置文件
    'LOAD_EXT_CONFIG' => 'db',
    'Library'=>'D:\phpStudy\WWW\ThinkPHP\Library',//方便调用jwt
);