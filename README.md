# DXFWriter

This package allows PHP programs to dynamically create .dxf files (ie. CAD drawings). 
The pacakage is intended for creating multi-page design documents which would eventually be printed. The documents might include tables, text, images, and simple 2D vector drawings. The .dxf files are compatible with AutoCAD, and the package was tested with AutoCAD 2016.
This pacakge is NOT designed to work with the Model Space, or to create complex models. Think paper. It's mainly for making layouts.

## Basic Usage:

Build your design with the CadMaker class:
```
$design = new CadMaker();
$page1 = $design->addPage("Page 1");
$design->drawRectangle($page1, 0.0, 0.0, 3.0, 4.0);
```
Then use the DxfConverter class to save your design:
```
$dxf = new DxfConverter($design);
$dxf->save('C:\Users\John\myCadDesign.dxf');
```
Then open your design in AutoCAD to edit it, or print it.
