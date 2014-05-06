<?php

namespace svenanders\tools;

require (__DIR__).'/src/SarPoTwigExtractor/SarPoTwigExtractor.php';
if(empty($argv[1])) die('Cannot run this module without any arguments. 
(Argument should be name of Application folder(s), fi:
php Module.php Application
');
$extractor=new SarPoTwigExtractor((__DIR__)."/../".$argv[1]);
$traverse=$extractor->parseModule();

