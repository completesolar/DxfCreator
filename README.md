# DxfCreator

DxfCreator lets you dynamically create CAD drawings for AutoCAD and perhaps other programs.  
You can work with layouts, layers, viewports, blocks, basic shapes, text, and import images and pdf files. There are no 3D capabilities, and many other AutoCAD features are not present. DxfCreator's specialty is to allow you to mass produce designs using the data in your database.  
Tested with AutoCAD LT 2016.

## Basic Usage:

Build your design with the Drawing class:
```
$design = new Drawing();
$page1 = $design->addPage("Page 1");
$design->drawRectangle($page1, 0.0, 0.0, 3.0, 4.0);
```
Then use the DxfConverter class to save your design:
```
$dxf = new DxfConverter($design);
$dxf->save('C:\Users\John\myCadDesign.dxf');
```
Then open your design in AutoCAD to edit it, or print it.

See the project wiki for more details and documentation.
