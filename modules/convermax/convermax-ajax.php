<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/convermax.php');

//Context::getContext()->controller->php_self = 'category';
$convermax = new Convermax();
echo $convermax->ajaxCall();