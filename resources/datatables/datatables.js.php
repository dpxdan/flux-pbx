<?php

ob_start('ob_gzhandler');
header('Content-type: text/javascript; charset: UTF-8');
header('Cache-Control: must-revalidate');
header('Expires: '.gmdate('D, d M Y H:i:s',time()+3600).' GMT');

include_once 'datatables/datatables/jquery.dataTables.min.js';
include_once 'datatables/datatables-bs4/js/dataTables.bootstrap4.min.js';
include_once 'datatables/datatables-responsive/js/responsive.bootstrap4.min.js';
include_once 'datatables/datatables-responsive/js/dataTables.responsive.min.js';


?>