<?php namespace DXFWriter;
require_once __DIR__.'/vendor/autoload.php';

echo "\n\n";

$cad = new CadMaker();
$p1 = $cad->addPage(["name" => "Layout1"]);
//$cad->drawRectangle($p1, 10, 10, 20, 20);
//$cad->drawRectangle($p1, 15, 15, 25, 25);

echo '<pre>';
// print_r($cad);
// exit;

$dxf = new DxfConverter($cad);
$dxf->save("C:\Users\User\Documents\GitHub\DXFWriter\misc\GeneratedOneLayout.dxf");
exit;
?>