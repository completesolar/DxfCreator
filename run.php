<?php namespace DXFWriter;
require_once __DIR__.'/vendor/autoload.php';
echo "<!DOCTYPE html><html><body><h1>Refresh to generate DXF File</h1></body></html>";

$cad = new CadMaker();
        $p1 = $cad->addPage("Page 1");

        $cad->drawRectangle($p1, 1, 1, 3, 3, ["lineWeight" => 0.1]);
        $cad->drawRectangle($p1, 2, 2, 4, 4, ["lineColor" => "red", "lineWeight" => 0.25]);
        $cad->drawRectangle($p1, 3, 3, 5, 5, ["lineColor" => "yellow", "lineWeight" => 0.5]);
        $cad->drawRectangle($p1, 4, 4, 6, 6, ["lineColor" => "green", "lineWeight" => 0.75]);
        $cad->drawRectangle($p1, 5, 5, 7, 7, ["lineColor" => 4, "lineWeight" => 1.0]);
        $cad->drawRectangle($p1, 6, 6, 8, 8, ["lineColor" => 5, "lineWeight" => 1.5]);
        $cad->drawRectangle($p1, 7, 7, 9, 9, ["lineColor" => 6, "lineWeight" => 2.0]);
        $cad->drawRectangle($p1, 8, 8, 10, 10, ["lineColor" => 7, "lineWeight" => 2.5]);

        $dxf = new DxfConverter($cad);
        $dxf->save('tests/dxf file examples/testRectangleOptions.dxf');


// $cad->drawRectangle($p1, 0.0625, 0.0625, 16.875, 10.875, ["lineColor" => "red"]);
// $cad->drawRectangle($p1, 2, 2, 6, 6, ["lineColor" => "red"]);
// $cad->drawRectangle($p1, 7, 2, 11, 6, ["lineColor" => "red"]);

// $cad->drawRectangle($p2, 0.0625, 0.0625, 16.875, 10.875, ["lineColor" => "green"]);
// $cad->drawRectangle($p2, 2, 2, 6, 6, ["lineColor" => "green"]);
// $cad->drawRectangle($p2, 7, 2, 11, 6, ["lineColor" => "green"]);

// $cad->drawRectangle($p3, 0.0625, 0.0625, 16.875, 10.875, ["lineColor" => "blue"]);
// $cad->drawRectangle($p3, 2, 2, 6, 6, ["lineColor" => "blue"]);
// $cad->drawRectangle($p3, 7, 2, 11, 6, ["lineColor" => "blue"]);

//$dxf = new DxfConverter($cad);
//$dxf->save('C:\Users\User\Documents\GitHub\DXFWriter\misc\test.dxf');

exit;
?>