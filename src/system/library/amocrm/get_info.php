<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/amocrm.php';

use system\library\amocrm\amoCRM;

$amoCRM = new amoCRM($config['amoCRM']);
$amoCRM->getInfo();