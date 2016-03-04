<?php namespace DXFWriter;
require_once __DIR__.'/vendor/autoload.php';

// Example of how to use this package

$myDocument = new CadMaker();

$pageOptions = [
        "xLength" => 11.0,
        "yLength" => 8.5,
        "marginBottom" => 0.5,
        "marginLeft" => 0.5,
        "marginRight" => 0.5,
        "marginTop" => 0.5,
];

$page1 = $myDocument->addPage("Page 1", $pageOptions);

$rectangleOptions = ["lineWeight" => 1.0];
$myDocument->drawRectangle($page1, 0, 0, 10, 7.5, $rectangleOptions);
$myDocument->drawRectangle($page1, 0, 7.5, 7.5, 6.5, $rectangleOptions);
$myDocument->drawRectangle($page1, 0, 0, 7.5, 0.5, $rectangleOptions);
$myDocument->drawRectangle($page1, 7.5, 7.5, 10, 0, $rectangleOptions);

$page2 = $myDocument->addPage("Page 2", $pageOptions);

$myDocument->drawRectangle($page2, 0, 7.5, 10, 7, $rectangleOptions);
$myDocument->drawRectangle($page2, 0, 0, 3, 7, $rectangleOptions);
$myDocument->drawRectangle($page2, 3, 0, 7, 7, $rectangleOptions);
$myDocument->drawRectangle($page2, 7, 0, 10, 7, $rectangleOptions);

$dxf = new DxfConverter($myDocument);
$dxf->save('C:\Users\John\myDocument.dxf');

?>
