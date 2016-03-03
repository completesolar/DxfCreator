<?php namespace DXFWriter;
require_once __DIR__.'/vendor/autoload.php';

echo "<!DOCTYPE html><html><body><h1>Refresh to generate DXF File</h1></body></html>";
$cad = new CadMaker();
$pageOptions = [
        "xLength" => 17.0,
        "yLength" => 11.0,
        "marginBottom" => 0.03125,
        "marginLeft" => 0.03125,
        "marginTop" => 0.03125,
        "marginRight" => 0.03125,
        ];

$p1 = $cad->addPage(array_merge($pageOptions, ["name" => "Page 1"]));
$p2 = $cad->addPage(array_merge($pageOptions, ["name" => "Page 2"]));
$p3 = $cad->addPage(array_merge($pageOptions, ["name" => "Page 3"]));

$rectangleOptions = ["lineWeight" => 1.5, "lineColor" => "red"];

$cad->drawRectangle($p1, 0.0625, 0.0625, 16.875, 10.875);
$cad->drawRectangle($p1, 2, 2, 6, 6, ["lineWeight" => 1.0, "lineColor" => "blue"]);
$cad->drawRectangle($p1, 7, 2, 11, 6, ["lineWeight" => 1.0, "lineColor" => "green"]);

$dxf = new DxfConverter($cad);
$dxf->save('C:\Users\User\Documents\GitHub\DXFWriter\misc\demo.dxf');

//echo '<pre>';
// print_r($cad);
//$dxf->save('C:\Users\User\Documents\GitHub\DXFWriter\tests\dxf file examples\temp.dxf');

exit;
?>