<?php

ob_start('ob_gzhandler');
header('Content-type: text/css; charset: UTF-8');
header('Cache-Control: must-revalidate');
header('Expires: '.gmdate('D, d M Y H:i:s',time()+3600).' GMT');

include_once 'datatables/datatables-bs4/css/dataTables.bootstrap4.min.css';
include_once 'datatables/datatables-responsive/css/responsive.bootstrap4.min.css';

?>