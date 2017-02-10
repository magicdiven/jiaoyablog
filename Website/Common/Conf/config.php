<?php
$systemConfig = include('system_config.php');

$appConfig = array (
    //每页显示数
    'PAGE_LIST_ROWS' => 10,
    // 开启布局
//    'LAYOUT_ON' => true,
//    'LAYOUT_NAME' => 'BlogCommon/bloglayout',
);

return array_merge($systemConfig, $appConfig);