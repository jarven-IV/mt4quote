<?php
return array(
    //'配置项'=>'配置值'
    /* 数据库设置 */
    'DB_TYPE' => 'mysql', // 数据库类型

    'DB_HOST' => '127.0.0.1', // 服务器地址
    'DB_NAME' => 'mt4quote5', // 数据库名
    'DB_USER' => 'mt4quote5', // 用户名
    'DB_PWD' => 'aAn2K4a3sG4iG5B7', // 密码
    'DB_PORT' => 3306, // 端口

    'URL_MODEL' => 2,
    'DB_FIELDTYPE_CHECK' => false, // 是否进行字段类型检查
    'DB_FIELDS_CACHE' => true, // 启用字段缓存
    'DB_CHARSET' => 'utf8', // 数据库编码默认采用utf8
    'MODULE_ALLOW_LIST'=>array('Home','Api','Admin'),
    'DEFAULT_MODULE' => 'Home'
);