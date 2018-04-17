<?php

return [
    'class' => 'yii\redis\Connection',
    'hostname' => $SYSTEM_CONFIG['SYSTEM_REDIS_HOST'],
    'port' => $SYSTEM_CONFIG['SYSTEM_REDIS_PORT'],
    //'password' => $SYSTEM_CONFIG['SYSTEM_REDIS_PASS'],
    'database' => $SYSTEM_CONFIG['SYSTEM_REDIS_DATABASE']
];