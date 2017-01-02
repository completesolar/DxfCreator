<?php namespace DxfCreator\Dxf;

use DxfCreator\Drawing\Drawing;
use DxfCreator\Drawing\Drawable;
use DxfCreator\Drawing\Ellipse;
use DxfCreator\Drawing\Entity;
use DxfCreator\Drawing\File;
use DxfCreator\Drawing\Image;
use DxfCreator\Drawing\MText;
use DxfCreator\Drawing\Page;
use DxfCreator\Drawing\Pdf;
use DxfCreator\Drawing\Polygon;
use DxfCreator\Drawing\Block;
use DxfCreator\Drawing\Text;
use DxfCreator\Drawing\CustomBlockDefinition;
use DxfCreator\Drawing\Layer;
use DxfCreator\Drawing\Viewport;

/**
 *
 * @author Jake Strang
 *
 * Developer Notes:
 * - This class has poor naming. The term Block is overused and can mean several things.
 * - Memory runs out fast when importing external files. That part should be redesigned to save memory
 *  (ie. each BLOCK could be managed individually).
 *
 */

class DxfConverter
{
    private $drawing;
    private $header;
    private $tables;
    private $blocks;
    private $entities;
    private $objects;
    private $handseed;
    private $blockDefinitions;
    private $blockRecords;
    private $imageDictionary;
    private $handles;
    private $viewportCounter;
    private $styles;
    private $layers;
    private $ltypes;
    private $dimStyles;

    public function __construct(Drawing $drawing)
    {
        if (count($drawing->pages) == 0){
            $drawing->addPage("Layout 1");
        }

        $this->drawing = $drawing;
    }

    public function save($filePath)
    {
        $this->setUp();
        $this->extractContent();
        $this->writeToFile($filePath);
    }

    private function setExternalBlockDefinitions($file, $names)
    {
        $matches = [];
        preg_match("/0(\r)?\nBLOCK(\r)?\n/", $file, $matches, PREG_OFFSET_CAPTURE);
        $partialFile = substr($file, $matches[0][1]);
        preg_match("/0(\r)?\nENDSEC/", $partialFile, $matches, PREG_OFFSET_CAPTURE);
        $blocksSection = substr($partialFile, 0, $matches[0][1]);
        $blocksSection = str_replace("\r\n", "\n", $blocksSection);
        $blocksSection = str_replace("\n", "\r\n", $blocksSection);
        $blocks = explode("0\r\nBLOCK\r\n", $blocksSection);
        unset($blocks[0]);
        $stylesToAdd = [];
        foreach ($blocks as $block){
            $blockArray = explode("\r\n", trim($block));
            $dxfBlock = new DxfBlock();
            $dxfBlock->addArray($blockArray, true);
            $name = $this->getDxfObjectName($dxfBlock);
            $basePoint = $this->getBlockBasePoint($dxfBlock);

            // Dimension objects reference special blocks, that have names starting with "*D"
            if (($names == [] || array_search($name, $names) !== false || substr($name, 0, 2) === "*D") && !preg_match("/^\*(Model|Paper)_Space[0-9]*$/", $name)){
                $blockRecordHandle = $this->getNewHandle();
                $content = $this->updateBlockContent($dxfBlock, $blockRecordHandle);
                $this->blockRecords[$name] = new DxfBlockRecord($name, $blockRecordHandle);
                $this->blockDefinitions[] = new DxfBlockDefinition($name, $basePoint, $content, $this->getNewHandle(), $this->getNewHandle(), $blockRecordHandle);
            }
        }
    }

    private function setExternalTable($tableName, $file)
    {
        $matches = [];
        preg_match("/0(\r)?\n$tableName(\r)?\n/", $file, $matches, PREG_OFFSET_CAPTURE);
        $partialFile = substr($file, $matches[0][1]);
        preg_match("/0(\r)?\nENDTAB/", $partialFile, $matches, PREG_OFFSET_CAPTURE);
        $table = substr($partialFile, 0, $matches[0][1]);
        $table = str_replace("\r\n", "\n", $table);
        $table = str_replace("\n", "\r\n", $table);
        $entriesAsStrings = explode("0\r\n$tableName\r\n", $table);
        unset($entriesAsStrings[0]);

        foreach ($entriesAsStrings as $entryAsString){
            $entryAsArray = explode("\r\n", $entryAsString);
            unset($entryAsArray[count($entryAsArray)-1]);

            $dxfEntry = new DxfBlock();
            $dxfEntry->addArray($entryAsArray, true);
            $name = $this->getDxfObjectName($dxfEntry);

            switch($tableName){
                case "LAYER":
                    if (!isset($this->layers[$name])){
                        $this->setLayerObject($dxfEntry, $name);
                    }
                    break;
                case "LTYPE":
                    if (!isset($this->ltypes[$name])){
                        $this->setLtypeObject($dxfEntry, $name);
                    }
                    break;
                case "STYLE":
                    if (!isset($this->styles[$name])){
                        $this->setStyleObject($dxfEntry, $name);
                    }
                    break;
                case "DIMSTYLE":
                    if (!isset($this->styles[$name])){
                        $this->setDimStyleObject($dxfEntry, $name);
                    }
                default:
                    break;
            }
        }
    }

    private function setLtypeObject($ltype, $name)
    {
        if (isset($this->ltypes[$name])){
            return;
        }

        $ltypeObject = new LineType($name);
        foreach ($ltype->getBody() as $value){
            switch ($value[0]){
                case 3:
                    $ltypeObject->description = $value[1];
                    break;
                case 40:
                    $ltypeObject->patternLength = $value[1];
                    break;
                case 49:
                    $ltypeObject->lengths[] = $value[1];
                    break;
                case 70:
                    $ltypeObject->flags = $value[1];
                    break;
                case 72:
                    $ltypeObject->alignmentCode = $value[1];
                    break;
                case 73:
                    $ltypeObject->lineTypeElementCount = $value[1];
                    break;
                default:
                    break;
            }
        }

        $this->ltypes[$name] = $ltypeObject;
    }

    private function setLayerObject($layer, $name)
    {
        if (isset($this->layers[$name])){
            return;
        }

        $dxfLayer = new DxfLayer($name);
        foreach ($layer->getBody() as $value){
            switch ($value[0]){
                case 6:
                    $dxfLayer->lineTypeName = $value[1];
                    break;
                case 62:
                    $dxfLayer->color = $value[1];
                    break;
                case 70:
                    $dxfLayer->flags = $value[1];
                    break;
                case 290:
                    $dxfLayer->plottingFlag = $value[1];
                    break;
                case 370:
                    $dxfLayer->lineWeight = $value[1];
                    break;
                default:
                    break;
            }
        }

        $this->layers[$name] = $dxfLayer;
    }

    private function setDimStyleObject($dxfDimStyle, $name)
    {
        if (isset($this->dimStyles[$name])){
            return;
        }

        $dimstyle = new DimStyle($name);
        foreach ($dxfDimStyle->getBody() as $value){
            switch (true){
                case $value[0] == 70:
                    $dimstyle->flags = $value[1];
                    break;
                case $value[0] == 2:
                    break;
                case $value[0] == 100:
                    break;
                case $value[0] == 102:
                    break;
                case $value[0] == 105:
                    break;
                case $value[0] == 330:
                    break;
                case $value[0] == 360:
                    break;
                case $value[0] <= 289:
                    $dimstyle->dimAttributes[$value[0]] = $value[1];
                    break;
                default:
                    break;
            }
        }

        $this->dimStyles[$name] = $dimstyle;
    }

    private function setStyleObject($dxfStyle, $styleName)
    {
        if (isset($this->styles[$styleName])){
            return;
        }

        $style = new Style($styleName);
        foreach ($dxfStyle->getBody() as $value){
            switch ($value[0]){
                case 3:
                    $style->fontFile = $value[1];
                    break;
                case 4:
                    $style->bigFontFile = $value[1];
                    break;
                case 40:
                    $style->height = $value[1];
                    break;
                case 41:
                    $style->widthFactor = $value[1];
                    break;
                case 42:
                    $style->lastHeightUsed = $value[1];
                    break;
                case 50:
                    $style->obliqueAngle = $value[1];
                    break;
                case 70:
                    $style->flags = $value[1];
                    break;
                case 71:
                    $style->orientationFlag = $value[1];
                    break;
                case 1071:
                    $style->extraFormatInfo = $value[1];
                    break;
                default:
                    break;
            }
        }

        $this->styles[$styleName] = $style;
    }

    private function setCustomBlockDefinitions()
    {
        foreach ($this->drawing->customBlockDefinitions as $block){
            $blockRecordHandle = $this->getNewHandle();
            $this->blockRecords[$block->name] = new DxfBlockRecord($block->name, $blockRecordHandle);

            $dxfShapes = new DxfBlock();
            foreach ($block->shapes as $shape){
                // There are going to be issues if you include a hatch with cutouts in a CustomBlockDefinition.
                // TODO: Cutout logic will need to be redesigned.
                // Images/PDFs cannot be included in blocks for now.
                // What happens when a custom block has a custom block inside it?
                $dxfShapes->addBlock($this->getEntityBlock($shape, $this->getNewHandle(), $blockRecordHandle, null, null));
            }

            $this->blockDefinitions[] = new DxfBlockDefinition($block->name, $block->basePoint, $dxfShapes, $this->getNewHandle(), $this->getNewHandle(), $blockRecordHandle);
        }
    }

    private function getDxfObjectName($dxfObject)
    {
        foreach ($dxfObject->getBody() as $value){
            if ($value[0] == 2){
               return $value[1];
            }
        }
    }

    private function getBlockBasePoint($dxfBlock)
    {
        $x = 0;
        $y = 0;
        foreach ($dxfBlock as $code => $value){

            if ($code == 0){
                break;
            }
            if ($code == 10){
                $x = $value;
                break;
            }
        }

        foreach ($dxfBlock as $code => $value){

            if ($code == 0){
                break;
            }
            if ($code == 20){
                $y = $value;
                break;
            }
        }

        return [$x, $y];
    }

    private function updateBlockContent($dxfBlock, $blockRecordHandle)
    {
        $inContent = false;
        $removed = false;
        $content = new DxfBlock();
        $entity = null;
        for ($i = 0; $i < count($dxfBlock->body); $i++){

            $removed = false;
            switch ($dxfBlock->body[$i][0]){
                case 0:
                    $inContent = true;
                    if ($dxfBlock->body[$i][1] == "ENDBLK"){
                        break 2;
                    }
                    $entity = $dxfBlock->body[$i][1];
                    break;
                case 5:
                    if ($inContent){
                        $dxfBlock->body[$i][1] = $this->getNewHandle();
                    }
                    break;
                case 330:
                    $dxfBlock->body[$i][1] = $blockRecordHandle;
                    break;
                default:
                    break;
            }

            if ($inContent && !$removed){
                $content->add($dxfBlock->body[$i][0], $dxfBlock->body[$i][1]);
            }
        }

        return $content;
    }

    private function writeToFile($filePath)
    {
        $dxfFile = new DxfBlock();
        $dxfFile->addBlock($this->header);
        $dxfFile->addBlock($this->tables);
        $dxfFile->addBlock($this->blocks);
        $dxfFile->addBlock($this->entities);
        $dxfFile->addBlock($this->objects);
        $dxfFile->add(0, "EOF");

        $file = $dxfFile->toString();
        $fileArray = explode("\n", $file);
        $utf8File = "";

        // Content from imported blocks is already in UTF8. This was the only way I could get special characters
        // to work consistently.
        foreach ($fileArray as $line){
           $utf8File .= iconv(mb_detect_encoding($line, mb_detect_order(), true), "UTF-8", $line) . "\n";
        }

        $stream = fopen($filePath, "w");
        fwrite($stream, $utf8File);
        fclose($stream);
    }

    private function handseedVariable()
    {
        $handseedVariable = new DxfBlock();
        $handseedVariable->add(9, '$HANDSEED');
        $handseedVariable->add(5, $this->getNewHandle());
        return $handseedVariable;
    }

    private function extractContent()
    {
        $blockRecordTable = $this->createBlockRecordTable();
        $blockRecordTable->addBlock($this->createBlockRecord("*Model_Space", $this->handles["modelBlockRecord"], $this->handles["modelLayout"]));
        $layouts = new DxfBlock();

        $this->extractModelSpaceContent();

        for ($pageNum = 0; $pageNum < count($this->drawing->pages); $pageNum++){
            $this->extractPageContent($pageNum, $blockRecordTable, $layouts);
        }

        $layouts->addBlock($this->createModelObject());

        foreach ($this->blockRecords as $record){
            $blockRecordTable->addBlock($record->toBlock());
        }

        $this->tables->addBlock($blockRecordTable);
        $this->objects->addBlock($this->imageDictionary);
        $this->objects->addBlock($this->layoutDictionary);
        $this->objects->addBlock($layouts);
        $this->header->addBlock($this->handseedVariable());
    }

    private function extractModelSpaceContent()
    {
        $placeHolder = "";
        foreach ($this->drawing->modelSpace->content as $entity){
            $this->extractEntity("model", $entity, $this->handles["modelBlockRecord"], $placeHolder);
        }
    }

    private function extractPageContent($page, &$blockRecordTable, &$layouts)
    {
        $marginBottom = $this->drawing->pages[$page]->marginBottom;
        $marginLeft = $this->drawing->pages[$page]->marginLeft;
        $marginRight = $this->drawing->pages[$page]->marginRight;
        $marginTop = $this->drawing->pages[$page]->marginTop;
        $name = $this->drawing->pages[$page]->name;
        $xLength = $this->drawing->pages[$page]->xLength;
        $yLength = $this->drawing->pages[$page]->yLength;
        $blockRecordHandle = $this->getNewHandle();
        $layoutHandle = $this->getNewHandle();

        if ($page == 0){
            $id = "*Paper_Space";
        } else {
            $id = "*Paper_Space" . ($page - 1);
        }

        $blockRecordTable->addBlock($this->createBlockRecord($id, $blockRecordHandle, $layoutHandle));
        $layoutBlock = $this->createLayoutBlock($id, $blockRecordHandle);
        $this->layoutDictionary->addBlock($this->createLayoutForDictionary($name, $layoutHandle));
        $layouts->addBlock($this->createLayoutObject($blockRecordHandle, $layoutHandle, $marginLeft,
                $marginBottom, $marginRight, $marginTop, $xLength, $yLength, $name, $page));

        if (!empty($this->drawing->pages[$page]->content)){
            foreach ($this->drawing->pages[$page]->content as $entity){
                $this->extractEntity($page, $entity, $blockRecordHandle, $layoutBlock);
            }
        }

        $this->blocks->addBlock($layoutBlock);
    }

    private function extractEntity($page, $entity, $blockRecordHandle, &$layoutBlock)
    {
        $entityHandle = $this->getNewHandle();

        if (is_a($entity, "DxfCreator\Drawing\File")){
            $definitionHandle = $this->getNewHandle();
            $this->objects->addBlock($this->getDefinition($entity, $entityHandle, $definitionHandle));

            if (is_a($entity, "DxfCreator\Drawing\Image")){
                $this->imageDictionary->addBlock($this->createImageForDictionary(pathinfo($entity->filepath, PATHINFO_FILENAME), $definitionHandle));
            }
        } else {
            $definitionHandle = null;
        }

        if (is_a($entity, "DxfCreator\Drawing\Block")){
            $this->blockRecords[$entity->name]->addRef($entityHandle);
        }


        if ($page == 0 || $page === "model"){
            $this->entities->addBlock($this->getEntityBlock($entity, $entityHandle, $blockRecordHandle, $definitionHandle, $page));
        } else {
            $layoutBlock->addBlock($this->getEntityBlock($entity, $entityHandle, $blockRecordHandle, $definitionHandle, $page));
        }
    }

    private function setUp()
    {
        $this->handseed = "100";
        $this->handles = [];
        $this->handles["modelBlock"] = $this->getNewHandle();
        $this->handles["modelBlockRecord"] = $this->getNewHandle();
        $this->handles["modelDictionary"] = $this->getNewHandle();
        $this->handles["modelLayout"] = $this->getNewHandle();
        $this->handles["acdbPlaceHolder"] = $this->getNewHandle();
        $this->viewportCounter = 2;
        $this->initializeSections();
        $this->blockDefinitions = [];
        $this->blockRecords = [];
        $this->setUpHeader();
        $this->initializeTableEntries();
        $this->importExternalData();
        $this->setUpTables();
        $this->setUpObjects();
        $this->blocks->addBlock($this->createLayoutBlock("*Model_Space", $this->handles["modelBlockRecord"]));
        $this->setCustomBlockDefinitions();

        foreach ($this->blockDefinitions as $definition){
            $this->blocks->addBlock($definition->toBlock());
        }

    }

    private function importExternalData()
    {
        foreach ($this->drawing->blockDefinitionFiles as $fileObject){
            $file = file_get_contents($fileObject->filepath);
            $this->setExternalBlockDefinitions($file, $fileObject->names);
            $this->setExternalTable("LAYER", $file);
            $this->setExternalTable("STYLE", $file);
            $this->setExternalTable("LTYPE", $file);
            $this->setExternalTable("DIMSTYLE", $file);
        }
    }

    private function initializeTableEntries()
    {
        $this->setDefaultStyles();
        $this->setDefaultLtypes();
        $this->setDefaultLayers();
        $this->setDefaultDimStyles();
        $this->setUserDefinedLayers();
    }

    private function setDefaultDimStyles()
    {
        $this->dimStyles = [];
        $this->dimStyles["Standard"] = new DimStyle("Standard");
    }

    private function setDefaultStyles()
    {
        $this->styles = [];
        $this->styles["Standard"] = new Style("Standard", "arial.ttf");
    }

    private function setDefaultLtypes()
    {
        $this->ltypes = [];
        $defaultLtypes = [
                ["ByBlock", "", ""],
                ["ByLayer", "", ""],
                ["Continuous", "Solid line", ""],
                ["ACAD_ISO02W100", "ISO dash __ __ __ __ __ __ __ __ __ __ __ __ __", "_"],
                ["ACAD_ISO03W100", "ISO dash space __    __    __    __    __    __", "_ "],
                ["ACAD_ISO07W100", "ISO dot . . . . . . . . . . . . . . . . . . . .", "."],
                ["ACAD_ISO10W100", "ISO dash dot __ . __ . __ . __ . __ . __ . __ .", "_."],
                ["ACAD_ISO11W100", "ISO double-dash dot __ __ . __ __ . __ __ . __", "__."]
        ];

        foreach ($defaultLtypes as $defaultLtype){
            // This code was redesigned in a hurry using existing code fragments, thus it does its job in a very round-about way.
            $dxfLtype = $this->getLtypeDxfBlockFromPattern($defaultLtype[0], $defaultLtype[1], $defaultLtype[2]);
            $ltype = new LineType($this->getDxfObjectName($dxfLtype));
            foreach ($dxfLtype->getBody() as $value){
                switch ($value[1]){
                    case 70:
                        $ltype->flags = $value[1];
                        break;
                    case 3:
                        $ltype->description = $value[1];
                        break;
                    case 72:
                        $ltype->alignmentCode = $value[1];
                        break;
                    case 73:
                        $ltype->lineTypeElementCount = $value[1];
                        break;
                    case 40:
                        $ltype->patternLength = $value[1];
                        break;
                    case 49:
                        $ltype->lengths[] = $value[1];
                        break;
                    default:
                        break;
                }
            }

            $this->ltypes[$ltype->name] = $ltype;
        }
    }

    private function setDefaultLayers()
    {
        $this->layers = [];
        $this->layers[0] = new DxfLayer(0);
    }

    private function setUserDefinedLayers()
    {
        foreach ($this->drawing->layers as $userDefinedLayer){
            $layer = new DxfLayer($userDefinedLayer->name);
            $layer->color = $userDefinedLayer->lineColor;
            $layer->lineTypeName = $userDefinedLayer->lineType;
            $layer->lineWeight = $userDefinedLayer->lineWeight*100;
            $this->layers[$layer->name] = $layer;
        }
    }

    private function setUpObjects()
    {
        $this->handles["namedObjectDictionary"] = $this->getNewHandle();
        $this->handles["ACAD_GROUP"] = $this->getNewHandle();
        $this->handles["ACAD_LAYOUT"] = $this->getNewHandle();
        $this->handles["ACAD_IMAGE_DICT"] = $this->getNewHandle();
        $this->handles["ACAD_IMAGE_VARS"] = $this->getNewHandle();
        $this->handles["ACAD_PLOTSTYLENAME"] = $this->getNewHandle();

        $this->objects->addBlock($this->createNamedObjectDictionary());
        $this->objects->addBlock($this->createOtherObjects());
        $this->imageDictionary = $this->createImageDictionary();
        $this->layoutDictionary = $this->createLayoutDictionary();
    }

    private function getLayerAsDxfBlock(DxfLayer $layer, $handle)
    {
        $dxfBlock = new DxfBlock();
        $dxfBlock->add(0, "LAYER");
        $dxfBlock->add(5, $handle);
        $dxfBlock->add(330, $this->handles["layerTable"]);
        $dxfBlock->add(100, "AcDbSymbolTableRecord");
        $dxfBlock->add(100, "AcDbLayerTableRecord");
        $dxfBlock->add(2, $layer->name);
        $dxfBlock->add(70, $layer->flags);
        $dxfBlock->add(62, $layer->color);
        $dxfBlock->add(6, $layer->lineTypeName);

        if (!is_null($layer->plottingFlag)){
            $dxfBlock->add(290, $layer->plottingFlag);
        }

        $dxfBlock->add(370, $layer->lineWeight);
        $dxfBlock->add(390, $this->handles["acdbPlaceHolder"]);

        return $dxfBlock;
    }

    private function setUpTables()
    {
        $this->handles["appidTable"] = $this->getNewHandle();
        $this->handles["ltypeTable"] = $this->getNewHandle();
        $this->handles["vportTable"] = $this->getNewHandle();
        $this->handles["styleTable"] = $this->getNewHandle();
        $this->handles["layerTable"] = $this->getNewHandle();
        $this->handles["dimStyleTable"] = $this->getNewHandle();

        $vportTable = $this->createTable("VPORT", $this->getNewHandle(), 1);
        $vportTable->addBlock($this->getVport());
        $ltypeTable = $this->createTable("LTYPE", $this->handles["ltypeTable"], count($this->ltypes));
        $layerTable = $this->createTable("LAYER", $this->handles["layerTable"], count($this->layers));
        $dimStyleStandardHandle = $this->getNewHandle();
        $dimStyleTable = $this->createTable("DIMSTYLE", $this->handles["dimStyleTable"], count($this->dimStyles));
        $dimStyleTable->add(100, "AcDbDimStyleTable");
        $dimStyleTable->add(71, 2);
        $dimStyleTable->add(340, $dimStyleStandardHandle);

        $styleTable = $this->createTable("STYLE", $this->handles["styleTable"], 2);

        foreach ($this->ltypes as $ltype){
            $ltypeTable->addBlock($this->getLtypeAsDxfBlock($ltype, $this->getNewHandle()));
        }

        foreach ($this->layers as $layer){
            $layerTable->addBlock($this->getLayerAsDxfBlock($layer, $this->getNewHandle()));
        }

        foreach ($this->styles as $style){
            $styleTable->addBlock($this->getStyleAsDxfBlock($style, $this->getNewHandle()));
        }

        foreach ($this->dimStyles as $name => $dimStyle){
            if ($name === "Standard"){
                $dimStyleTable->addBlock($this->getDimStyleAsDxfBlock($dimStyle, $dimStyleStandardHandle));
            } else {
                $dimStyleTable->addBlock($this->getDimStyleAsDxfBlock($dimStyle, $this->getNewHandle()));
            }
        }

        $viewTable = $this->createTable("VIEW", $this->getNewHandle(), 2);
        $ucsTable = $this->createTable("UCS", $this->getNewHandle(), 0);
        $appidTable = $this->createTable("APPID", $this->handles["appidTable"], 8);
        $appidTable->addBlock($this->getAppid("ACAD", $this->getNewHandle()));
        $appidTable->addBlock($this->getAppid("AcadAnnoPO", $this->getNewHandle()));
        $appidTable->addBlock($this->getAppid("AcadAnnotative", $this->getNewHandle()));
        $appidTable->addBlock($this->getAppid("ACAD_DSTYLE_DIMJAG", $this->getNewHandle()));
        $appidTable->addBlock($this->getAppid("ACAD_DSTYLE_DIMTALN", $this->getNewHandle()));
        $appidTable->addBlock($this->getAppid("ACAD_MLEADERVER", $this->getNewHandle()));
        $appidTable->addBlock($this->getAppid("ACAD_NAV_VCDISPLAY", $this->getNewHandle()));
        $appidTable->addBlock($this->getAppid("ACAD_PSEXT", $this->getNewHandle()));
        $appidTable->addBlock($this->getAppid("GradientColor1ACI", $this->getNewHandle()));
        $appidTable->addBlock($this->getAppid("GradientColor2ACI", $this->getNewHandle()));

        $this->tables->addBlock($vportTable);
        $this->tables->addBlock($ltypeTable);
        $this->tables->addBlock($layerTable);
        $this->tables->addBlock($styleTable);
        $this->tables->addBlock($viewTable);
        $this->tables->addBlock($ucsTable);
        $this->tables->addBlock($appidTable);
        $this->tables->addBlock($dimStyleTable);
    }

    private function getStyleAsDxfBlock(Style $style, $handle)
    {
        $dxfStyle = new DxfBlock();
        $dxfStyle->add(0, "STYLE");
        $dxfStyle->add(5, $handle);
        $dxfStyle->add(330, $this->handles["styleTable"]);
        $dxfStyle->add(100, "AcDbSymbolTableRecord");
        $dxfStyle->add(100, "AcDbTextStyleTableRecord");
        $dxfStyle->add(2, $style->name);
        $dxfStyle->add(70, $style->flags);
        $dxfStyle->add(40, $style->height);
        $dxfStyle->add(41, $style->widthFactor);
        $dxfStyle->add(50, $style->obliqueAngle);
        $dxfStyle->add(71, $style->orientationFlag);
        $dxfStyle->add(42, $style->lastHeightUsed);
        $dxfStyle->add(3, $style->fontFile);
        $dxfStyle->add(4, $style->bigFontFile);
        if(!is_null($style->extraFormatInfo)){
            $dxfStyle->add(1071, $style->extraFormatInfo);
        }
        return $dxfStyle;
    }

    private function getDimStyleAsDxfBlock(DimStyle $dimStyle, $handle)
    {
        $dxfDimStyle = new DxfBlock();
        $dxfDimStyle->add(0, "DIMSTYLE");
        $dxfDimStyle->add(105, $handle);
        $dxfDimStyle->add(330, $this->handles["dimStyleTable"]);
        $dxfDimStyle->add(100, "AcDbSymbolTableRecord");
        $dxfDimStyle->add(100, "AcDbDimStyleTableRecord");
        $dxfDimStyle->add(2, $dimStyle->name);
        $dxfDimStyle->add(70, $dimStyle->flags);

        foreach ($dimStyle->dimAttributes as $key => $attribute){
            $dxfDimStyle->add($key, $attribute);
        }

        return $dxfDimStyle;
    }

    private function initializeSections()
    {
        $this->header = $this->makeSection("HEADER");
        $this->tables = $this->makeSection("TABLES");
        $this->blocks = $this->makeSection("BLOCKS");
        $this->entities = $this->makeSection("ENTITIES");
        $this->objects = $this->makeSection("OBJECTS");
    }

    private function getNewHandle()
    {
        $this->handseed = strtoupper(dechex(hexdec($this->handseed) + 1));
        return $this->handseed;
    }

    private function getDefinition(File $file, $entityHandle, $definitionHandle)
    {
        $definition = new DxfBlock();

        switch ($file->type)
        {
            case "IMAGE":
                $definition->addBlock($this->getImageDefinition($file, $definitionHandle));
                break;
            case "PDFUNDERLAY":
                $definition->addBlock($this->getPdfDefinition($file, $entityHandle, $definitionHandle));
                break;
            default:
                throw new Exception('Class File. Type: ' . $file->type . ' not recognized.');
                break;
        }

        return $definition;
    }

    private function getImageDefinition(Image $image, $definitionHandle)
    {
        $imageDefinition = new DxfBlock();
        $imageDefinition->add(0, "IMAGEDEF");
        $imageDefinition->add(5, $definitionHandle);
        $imageDefinition->add(100, "AcDbRasterImageDef");
        $imageDefinition->add(90, 0);
        $imageDefinition->add(1, $image->filepath);
        $imageDefinition->add(10, $image->widthPx);
        $imageDefinition->add(20, $image->heightPx);
        $imageDefinition->add(280, 1);
        $imageDefinition->add(281, 2);

        return $imageDefinition;
    }

    private function getPdfDefinition(Pdf $pdf, $entityHandle, $definitionHandle)
    {
        $pdfDefinition = new DxfBlock();
        $pdfDefinition->add(0, "PDFDEFINITION");
        $pdfDefinition->add(5, $definitionHandle);
        $pdfDefinition->add(102, "{ACAD_REACTORS");
        $pdfDefinition->add(330, $entityHandle);
        $pdfDefinition->add(102, "}");
        $pdfDefinition->add(100, "AcDbUnderlayDefinition");
        $pdfDefinition->add(1, $pdf->filepath);
        $pdfDefinition->add(2, $pdf->page);

        return $pdfDefinition;
    }

    private function getPdfUnderlay(Pdf $pdf, $defintionHandle)
    {
        $pdfEntity = new DxfBlock();
        $pdfEntity->add(100, "AcDbUnderlayReference");
        $pdfEntity->add(340, $defintionHandle);
        $pdfEntity->add(10, $pdf->position[0]);
        $pdfEntity->add(20, $pdf->position[1]);
        $pdfEntity->add(30, 0.0);
        $pdfEntity->add(41, $pdf->scaleFactor);
        $pdfEntity->add(42, $pdf->scaleFactor);
        $pdfEntity->add(43, 1.0);
        $pdfEntity->add(281, 100);
        $pdfEntity->add(282, 0);

        return $pdfEntity;
    }

    private function getImage(Image $image, $definitionHandle)
    {
        $dxfImage = new DxfBlock();
        $dxfImage->add(100, "AcDbRasterImage");
        $dxfImage->add(90, 0);
        $dxfImage->add(10, $image->position[0] + $image->rotationPoint[0] - $image->rotationPoint[0]*cos(deg2rad($image->angle))
                + $image->rotationPoint[1]*sin(deg2rad($image->angle)));
        $dxfImage->add(20, $image->position[1] + $image->rotationPoint[1] - $image->rotationPoint[1]*cos(deg2rad($image->angle))
                - $image->rotationPoint[0]*sin(deg2rad($image->angle)));
        $dxfImage->add(30, "0.0");
        $dxfImage->add(11, ($image->widthInch / $image->widthPx) * cos(deg2rad($image->angle)));
        $dxfImage->add(21, ($image->widthInch / $image->widthPx) * sin(deg2rad($image->angle)));
        $dxfImage->add(31, "0.0");
        $dxfImage->add(12, ($image->widthInch / $image->widthPx) * cos(deg2rad($image->angle + 90)));
        $dxfImage->add(22, ($image->widthInch / $image->widthPx) * sin(deg2rad($image->angle + 90)));
        $dxfImage->add(32, "0.0");
        $dxfImage->add(13, $image->widthPx);
        $dxfImage->add(23, $image->heightPx);
        $dxfImage->add(340, $definitionHandle);
        $dxfImage->add(70, 7);
        $dxfImage->add(280, 0);
        $dxfImage->add(281, 50);
        $dxfImage->add(282, 50);
        $dxfImage->add(283, 0);
        $dxfImage->add(290, 0);
        $dxfImage->add(71, 1);
        $dxfImage->add(91, 2);
        $dxfImage->add(14, -0.5);
        $dxfImage->add(24, -0.5);
        $dxfImage->add(14, $image->widthPx - 0.5);
        $dxfImage->add(24, $image->heightPx - 0.5);

        return $dxfImage;
    }

    private function getEntityBlock(Entity $entity, $entityHandle, $layoutBlockRecordHandle, $definitionHandle, $page)
    {
        $dxfEntity = new DxfBlock();

        if ($entity->type == "LWPOLYLINE" && $entity->fillColor !== "NONE"){
            $dxfEntity->addBlock($this->getHatch($entity, $layoutBlockRecordHandle, $page));
        }

        $dxfEntity->add(0, $entity->type);
        $dxfEntity->add(5, $entityHandle);
        $dxfEntity->add(330, $layoutBlockRecordHandle);
        $dxfEntity->add(100, "AcDbEntity");

        if ($page !== "model"){
            $dxfEntity->add(67, 1);
        }

        $dxfEntity->add(8, $entity->layer);

        if(is_a($entity, "DxfCreator\Drawing\Drawable")){
            if ($entity->lineType !== ""){
                $dxfEntity->add(6, $entity->lineType);
            }
            if ($entity->lineColor !== ""){
                $dxfEntity->add(62, $entity->lineColor);
            }
            if ($entity->lineWeight !== ""){
                $dxfEntity->add(370, intval($entity->lineWeight*100));
            }
        }

        switch ($entity->type){
            case "LWPOLYLINE":
                $dxfEntity->addBlock($this->getPolygon($entity));
                break;
            case "TEXT":
                $dxfEntity->addBlock($this->getText($entity));
                break;
            case "MTEXT":
                $dxfEntity->addBlock($this->getMText($entity));
                break;
            case "IMAGE":
                $dxfEntity->addBlock($this->getImage($entity, $definitionHandle));
                break;
            case "PDFUNDERLAY":
                $dxfEntity->addBlock($this->getPdfUnderlay($entity, $definitionHandle));
                break;
            case "INSERT":
                $dxfEntity->addBlock($this->getInsert($entity));
                break;
            case "ELLIPSE":
                $dxfEntity->addBlock($this->getEllipse($entity));
                break;
            case "VIEWPORT":
                $dxfEntity->addBlock($this->getViewport($entity));
                break;
            default:
                throw new \Exception('Class Entity. Type: ' . $entity->type . ' not recognized.');
                break;
        }

        return $dxfEntity;
    }

    private function getViewport(Viewport $viewport)
    {
        $dxfViewport = new DxfBlock();
        $dxfViewport->add(100, "AcDbViewport");
        $dxfViewport->add(10, $viewport->center[0]);
        $dxfViewport->add(20, $viewport->center[1]);
        $dxfViewport->add(30, "0.0");
        $dxfViewport->add(40, $viewport->paperWidth);
        $dxfViewport->add(41, $viewport->paperHeight);
        $dxfViewport->add(68, $this->viewportCounter);
        $dxfViewport->add(69, $this->viewportCounter);
        $dxfViewport->add(12, $viewport->viewCenterPoint[0]);
        $dxfViewport->add(22, $viewport->viewCenterPoint[1]);
        $dxfViewport->add(13, "0.0");
        $dxfViewport->add(23, "0.0");
        $dxfViewport->add(14, 0.5);
        $dxfViewport->add(24, 0.5);
        $dxfViewport->add(15, 0.5);
        $dxfViewport->add(25, 0.5);
        $dxfViewport->add(16, "0.0");
        $dxfViewport->add(26, "0.0");
        $dxfViewport->add(36, "1.0");
        $dxfViewport->add(17, "0.0");
        $dxfViewport->add(27, "0.0");
        $dxfViewport->add(37, "0.0");
        $dxfViewport->add(42, "50.0");
        $dxfViewport->add(43, "0.0");
        $dxfViewport->add(44, "0.0");
        $dxfViewport->add(45, $viewport->viewHeight);
        $dxfViewport->add(50, "0.0");
        $dxfViewport->add(51, "0.0");
        $dxfViewport->add(72, 1000);

        foreach ($viewport->frozenLayers as $layer){
            if (isset($this->layers[$layer])){
                $dxfViewport->add(331, $this->layers[$layer]->handle);
            }
        }

        $dxfViewport->add(90, 819296);
        $dxfViewport->add(1, "");
        $dxfViewport->add(281, 0);
        $dxfViewport->add(71, 1);
        $dxfViewport->add(74, 0);
        $dxfViewport->add(110, "0.0");
        $dxfViewport->add(120, "0.0");
        $dxfViewport->add(130, "0.0");
        $dxfViewport->add(111, "1.0");
        $dxfViewport->add(121, "0.0");
        $dxfViewport->add(131, "0.0");
        $dxfViewport->add(112, "0.0");
        $dxfViewport->add(122, "1.0");
        $dxfViewport->add(132, "0.0");
        $dxfViewport->add(79, 0);
        $dxfViewport->add(146, "0.0");
        $dxfViewport->add(170, 0);
        $dxfViewport->add(61, 5);
        $dxfViewport->add(292, 1);
        $dxfViewport->add(282, 1);
        $dxfViewport->add(141, "0.0");
        $dxfViewport->add(142, "0.0");
        $dxfViewport->add(63, 250);
        $dxfViewport->add(421, 3355443);

        $this->viewportCounter++;
        return $dxfViewport;
    }

    private function getHatch(Polygon $polygon, $blockRecordHandle, $page)
    {
        $solid = $polygon->fillType == "SOLID";

        $hatch = new DxfBlock();
        $hatch->add(0, "HATCH");
        $hatch->add(5, $this->getNewHandle());
        $hatch->add(330, $blockRecordHandle);
        $hatch->add(100, "AcDbEntity");

        if ($page !== "model"){
            $hatch->add(67, 1);
        }
        $hatch->add(8, 0);
        $hatch->add(62, $polygon->fillColor);

        if (!$solid){
            $hatch->add(370, intval($polygon->fillWeight*100));
        }

        $hatch->add(100, "AcDbHatch");
        $hatch->add(10, "0.0");
        $hatch->add(20, "0.0");
        $hatch->add(30, "0.0");
        $hatch->add(210, "0.0");
        $hatch->add(220, "0.0");
        $hatch->add(230, "1.0");
        $hatch->add(2, $polygon->fillType);
        if ($solid){
            $hatch->add(70, 1);
        } else {
            $hatch->add(70, 0);
        }

        $hatch->add(71, 0);
        $paths = 1;

        if (isset($polygon->cutouts)){
            $paths += count($polygon->cutouts);
        }

        $hatch->add(91, $paths);
        $hatch->add(92, 7);
        $hatch->add(72, 0);
        $hatch->add(73, 1);
        $hatch->add(93, count($polygon->points));

        foreach ($polygon->points as $point){
            $hatch->add(10, $point[0]);
            $hatch->add(20, $point[1]);
        }

        $hatch->add(97, 0);

        if (isset($polygon->cutouts)){
            for ($i = 0; $i < count($polygon->cutouts); $i++){
                if ($page === "model"){
                    $cutout = $this->drawing->modelSpace->content[$polygon->cutouts[$i]];
                } else {
                    $cutout = $this->drawing->pages[$page]->content[$polygon->cutouts[$i]];
                }

                if (!is_a($cutout, "DxfCreator\Drawing\Polygon")){
                    throw new \Exception("Fill cutout must be class Polygon.");
                }

                $hatch->add(92, 22);
                $hatch->add(72, 0);
                $hatch->add(73, 1);
                $hatch->add(93, count($cutout->points));

                foreach ($cutout->points as $point){
                    $hatch->add(10, $point[0]);
                    $hatch->add(20, $point[1]);
                }

                $hatch->add(97, 0);
            }
        }

        $hatch->add(75, 1);
        $hatch->add(76, 1);

        if (!$solid){
            $hatch->add(52, 0);
            $hatch->add(41, "1.0");
            $hatch->add(77, 0);

            switch (strtoupper($polygon->fillType)){
                case "ANSI31":
                    $hatch->addBlock($this->getAnsi31FillPattern($polygon->fillScale));
                    break;
                default:
                    throw new \Exception("Fill type not recognized");
                    break;
            }
        }

        $hatch->add(47, 0.007);
        $hatch->add(98, 1);
        $hatch->add(10, "0.0");
        $hatch->add(20, "0.0");

        return $hatch;
    }

    private function getAnsi31FillPattern($fillScale)
    {
        $pattern = new DxfBlock();
        $pattern->add(78, 1);
        $pattern->add(53, 45.0);
        $pattern->add(43, "0.0");
        $pattern->add(44, "0.0");
        $pattern->add(45, -$fillScale * 0.1);
        $pattern->add(46, $fillScale * 0.1);
        $pattern->add(79, 0);

        return $pattern;
    }

    private function getEllipse(Ellipse $ellipse)
    {
        $dxfEllipse = new DxfBlock();
        $dxfEllipse->add(100, "AcDbEllipse");
        $dxfEllipse->add(10, $ellipse->center[0]);
        $dxfEllipse->add(20, $ellipse->center[1]);
        $dxfEllipse->add(30, "0.0");
        $dxfEllipse->add(11, $ellipse->xRadius);
        $dxfEllipse->add(21, "0.0");
        $dxfEllipse->add(31, "0.0");
        $dxfEllipse->add(40, "1.0");

        return $dxfEllipse;
    }

    private function getInsert(Block $block)
    {
        $dxfInsert = new DxfBlock();
        $dxfInsert->add(100, "AcDbBlockReference");
        $dxfInsert->add(2, $block->name);
        $dxfInsert->add(10, $block->position[0]);
        $dxfInsert->add(20, $block->position[1]);
        $dxfInsert->add(30, "0.0");
        $dxfInsert->add(41, $block->scale);
        $dxfInsert->add(42, $block->scale);
        $dxfInsert->add(50, $block->angle);

        return $dxfInsert;
    }

    private function getText(Text $text)
    {
        $dxfText = new DxfBlock();
        $dxfText->add(100, "AcDbText");
        $dxfText->add(10, "0.0");
        $dxfText->add(20, "0.0");
        $dxfText->add(30, "0.0");
        $dxfText->add(40, $text->lineHeight);
        $dxfText->add(1, $text->text);
        $dxfText->add(50, $text->angle);
        $dxfText->add(7, "Standard");
        $dxfText->add(72, $text->horizontalAlignment);
        $dxfText->add(11, $text->position[0]);
        $dxfText->add(21, $text->position[1]);
        $dxfText->add(31, "0.0");
        $dxfText->add(100, "AcDbText");
        $dxfText->add(73, $text->verticalAlignment);

        return $dxfText;
    }

    private function getMText(MText $mText)
    {
        $dxfMText = new DxfBlock();
        $dxfMText->add(100, "AcDbMText");
        $dxfMText->add(10, $mText->position[0]);
        $dxfMText->add(20, $mText->position[1]);
        $dxfMText->add(30, "0.0");
        $dxfMText->add(40, $mText->lineHeight);
        $dxfMText->add(41, $mText->width);
        $dxfMText->add(46, "0.0");
        $dxfMText->add(71, $mText->alignment);
        $dxfMText->add(72, 1);
        $dxfMText->addBlock($this->formatMTextString($mText));
        $dxfMText->add(73, 1);
        $dxfMText->add(44, 1.0);

        return $dxfMText;
    }

    private function formatMTextString(MText $mText)
    {
        $text = $mText->text;
        $text = str_replace("\r\n", '\P', $text);
        $text = str_replace("\r", '\P', $text);
        $text = str_replace("\n", '\P', $text);
        $text = str_replace("\t", '^I', $text);

        $bold = $mText->bold? 1 : 0;
        $italic = $mText->italic? 1 : 0;
        $underlineBegin = $mText->underline? '\L' : '';
        $underlineEnd = $mText->underline? '\l' : '';
        $formatTag = '\f' . $mText->font . '|b' . $bold . '|i' . $italic . '|c0|p0;';
        $text = $this->smartReplace('\-', '\fSymbol|b0|i0|c2|p18;' . "· " . $formatTag, $text);
        $textString =  '\A1;{' . $formatTag . $underlineBegin . $text . $underlineEnd . '}';

        // Strings in a dxf file must be divided at exactly 250 characters
        $chunks = str_split($textString, 250);

        $dxfString = new DxfBlock();

        for ($chunk = 0; $chunk < count($chunks); $chunk++){
            if ($chunk == count($chunks) - 1){
               $dxfString->add(1, $chunks[$chunk]);
            } else {
                $dxfString->add(3, $chunks[$chunk]);
            }
        }

        return $dxfString;
    }

    private function smartReplace($searchString, $replaceString, $text)
    {
        $searchString = preg_quote($searchString);
        // Replace 'searchString' with 'replaceString' if it is preceded by an odd number of backslashes (after PHP escaping and Regex escaping).
        return preg_replace('/(^|[^\\\\])(\\\\\\\\)*\\K' . $searchString . '/', $replaceString, $text);
    }

    private function getPolygon(Polygon $polygon)
    {
        $dxfPolygon = new DxfBlock();
        $dxfPolygon->add(100, "AcDbPolyline");
        $dxfPolygon->add(90, count($polygon->points));
        $dxfPolygon->add(70, $polygon->closed ? 1 : 0);
        $dxfPolygon->add(43, 0.0);

        foreach ($polygon->points as $point){
            $dxfPolygon->add(10, $point[0]);
            $dxfPolygon->add(20, $point[1]);
        }

        return $dxfPolygon;
    }

    private function createDictionary($handle, $pointer)
    {
        $dictionary = new DxfBlock();
        $dictionary->add(0, "DICTIONARY");
        $dictionary->add(5, $handle);
        $dictionary->add(330, $pointer);
        $dictionary->add(100, "AcDbDictionary");
        $dictionary->add(280, 1);
        $dictionary->add(281, 1);
        return $dictionary;
    }

    private function createOtherObjects()
    {

        $objects = new DxfBlock();
        $objects->addBlock($this->createDictionary($this->handles["modelDictionary"], $this->handles["modelBlockRecord"]));
        $objects->add(0, "DICTIONARY");
        $objects->add(5, $this->handles["ACAD_GROUP"]);
        $objects->add(102, "{ACAD_REACTORS");
        $objects->add(330, $this->handles["namedObjectDictionary"]);
        $objects->add(102, "}");
        $objects->add(330, $this->handles["namedObjectDictionary"]);
        $objects->add(100, "AcDbDictionary");
        $objects->add(281, 1);
        $objects->add(0, "ACDBDICTIONARYWDFLT");
        $objects->add(5, $this->handles["ACAD_PLOTSTYLENAME"]);
        $objects->add(102, "{ACAD_REACTORS");
        $objects->add(330, $this->handles["namedObjectDictionary"]);
        $objects->add(102, "}");
        $objects->add(330, $this->handles["namedObjectDictionary"]);
        $objects->add(100, "AcDbDictionary");
        $objects->add(281, 1);
        $objects->add(3, "Normal");
        $objects->add(350, $this->handles["acdbPlaceHolder"]);
        $objects->add(100, "AcDbDictionaryWithDefault");
        $objects->add(340, $this->handles["acdbPlaceHolder"]);

        $objects->addBlock($this->createRasterVariables());
        $objects->add(0, "ACDBPLACEHOLDER");
        $objects->add(5, $this->handles["acdbPlaceHolder"]);
        $objects->add(102, "{ACAD_REACTORS");
        $objects->add(330, $this->handles["ACAD_PLOTSTYLENAME"]);
        $objects->add(102, "}");
        $objects->add(330, $this->handles["ACAD_PLOTSTYLENAME"]);

        return $objects;
    }

    private function createRasterVariables()
    {
        $object = new DxfBlock();
        $object->add(0, "RASTERVARIABLES");
        $object->add(5, $this->handles["ACAD_IMAGE_VARS"]);
        $object->add(102, "{ACAD_REACTORS");
        $object->add(330, $this->handles["namedObjectDictionary"]);
        $object->add(102, "}");
        $object->add(330, $this->handles["namedObjectDictionary"]);
        $object->add(100, "AcDbRasterVariables");
        $object->add(90, 0);
        $object->add(70, 0);
        $object->add(71, 1);
        $object->add(72, 5);

        return $object;
    }

    private function getDimstyleTable()
    {
        $tableHandle = $this->getNewHandle();
        $dimStyleEntryHandle = $this->getNewHandle();
        $preBody = new DxfBlock();
        $preBody->add(0, "TABLE");
        $preBody->add(2, "DIMSTYLE");
        $preBody->add(5, $tableHandle);
        $preBody->add(330, 0);
        $preBody->add(100, "AcDbSymbolTable");
        $preBody->add(70, 2);
        $preBody->add(100, "AcDbDimStyleTable");
        $preBody->add(71, 2);
        $preBody->add(340, $dimStyleEntryHandle);
        $postBody = new DxfBlock();
        $postBody->add(0, "ENDTAB");
        $dimstyleTable = new DxfContainer($preBody, $postBody);

        $dimstyle = new DxfBlock();
        $dimstyle->add(0, "DIMSTYLE");
        $dimstyle->add(105, $dimStyleEntryHandle);
        $dimstyle->add(330, $tableHandle);
        $dimstyle->add(100, "AcDbSymbolTableRecord");
        $dimstyle->add(100, "AcDbDimStyleTableRecord");
        $dimstyle->add(2, "Standard");
        $dimstyle->add(70, 0);
        $dimstyle->add(340, $this->getNewHandle());

        $dimstyleTable->addBlock($dimstyle);
        return $dimstyleTable;
    }

    private function getAppid($name, $handle)
    {
        $appid = new DxfBlock();
        $appid->add(0, "APPID");
        $appid->add(5, $handle);
        $appid->add(330, $this->handles["appidTable"]);
        $appid->add(100, "AcDbSymbolTableRecord");
        $appid->add(100, "AcDbRegAppTableRecord");
        $appid->add(2, $name);
        $appid->add(70, 0);
        return $appid;
    }

    private function getLayerTable()
    {
        $layerTableHandle = $this->getNewHandle();

        $preBody = new DxfBlock();
        $preBody->add(0, "TABLE");
        $preBody->add(2, "LAYER");
        $preBody->add(5, $layerTableHandle);
        $preBody->add(330, 0);
        $preBody->add(100, "AcDbSymbolTable");
        $preBody->add(70, count($this->drawing->layers)+1);
        $postBody = new DxfBlock();
        $postBody->add(0, "ENDTAB");
        $layerTable = new DxfContainer($preBody, $postBody);

        $layerDxf = new DxfBlock();
        $layerDxf->add(0, "LAYER");
        $layerDxf->add(5, $this->getNewHandle());
        $layerDxf->add(330, $layerTableHandle);
        $layerDxf->add(100, "AcDbSymbolTableRecord");
        $layerDxf->add(100, "AcDbLayerTableRecord");
        $layerDxf->add(2, 0);
        $layerDxf->add(70, 0);
        $layerDxf->add(62, 7);
        $layerDxf->add(6, "Continuous");
        $layerDxf->add(370, -3);
        $layerDxf->add(390, $this->handles["acdbPlaceHolder"]);
        $layerDxf->add(348, 0);
        $layerTable->addBlock($layerDxf);

        foreach ($this->layers as $layer){
            $layerDxf = new DxfBlock();
            $layerDxf->add(0, "LAYER");
            $layerDxf->add(5, $layer->handle);
            $layerDxf->add(330, $layerTableHandle);
            $layerDxf->add(100, "AcDbSymbolTableRecord");
            $layerDxf->add(100, "AcDbLayerTableRecord");
            $layerDxf->add(2, $layer->name);
            $layerDxf->add(70, $layer->flags);
            $layerDxf->add(62, $layer->color);
            $layerDxf->add(6, $layer->lineTypeName);

            if (is_null($layer->plottingFlag)){
                $layerDxf->add(290, $layer->plottingFlag);
            }

            $layerDxf->add(370, $layer->lineWeight);
            $layerDxf->add(390, $this->handles["acdbPlaceHolder"]);
            $layerTable->addBlock($layerDxf);
        }

        return $layerTable;
    }

    private function getLtypeAsDxfBlock(LineType $ltype, $handle)
    {
        $dxfLtype = new DxfBlock();
        $dxfLtype->add(0, "LTYPE");
        $dxfLtype->add(5, $handle);
        $dxfLtype->add(330, $this->handles["ltypeTable"]);
        $dxfLtype->add(100, "AcDbSymbolTableRecord");
        $dxfLtype->add(100, "AcDbLinetypeTableRecord");
        $dxfLtype->add(2, $ltype->name);
        $dxfLtype->add(70, $ltype->flags);
        $dxfLtype->add(3, $ltype->description);
        $dxfLtype->add(72, $ltype->alignmentCode);
        $dxfLtype->add(73, $ltype->lineTypeElementCount);
        $dxfLtype->add(40, $ltype->patternLength);

        foreach ($ltype->lengths as $length){
            $dxfLtype->add(49, $length);
            $dxfLtype->add(74, 0);
        }

        return $dxfLtype;
    }

    private function getLtypeDxfBlockFromPattern($name, $description, $pattern, $handle = "")
    {
        $ltype = new DxfBlock();
        $ltype->add(0, "LTYPE");
        $ltype->add(5, $handle);
        $ltype->add(330, "");
        $ltype->add(100, "AcDbSymbolTableRecord");
        $ltype->add(100, "AcDbLinetypeTableRecord");
        $ltype->add(2, $name);
        $ltype->add(70, 0);
        $ltype->add(3, $description);
        $ltype->add(72, 65);
        $ltype->add(73, 0);
        $ltype->addBlock($this->getLineTypePattern($pattern));

        return $ltype;
    }

    private function getLineTypePattern($pattern)
    {
        $patternBlock = new DxfBlock();

        if ($pattern == ""){
            $patternBlock->add(73, 0);
            $patternBlock->add(40, "0.0");
            return $patternBlock;
        }

        $chars = str_split($pattern);
        $length = 0;
        $numElements = 0;
        $symbolLengths = [];

        foreach ($chars as $char){
            switch($char){
                case '_':
                    $length += 15.0;
                    $numElements += 2;
                    break;
                case '.':
                    $length += 3.0;
                    $numElements += 2;
                    break;
                case ' ':
                    $length += 15.0;
                    break;
                default:
                    throw new \Exception("Unrecognized symbol in linetype pattern");
                    break;
            }
        }

        $patternBlock->add(73, $numElements);
        $patternBlock->add(40, $length);
        $lastWasSpace = false;
        $firstPass = true;
        foreach ($chars as $char){

            switch($char){
            case '_':
                if (!($firstPass || $lastWasSpace)){
                    $patternBlock->add(49, -3.0);
                    $patternBlock->add(74, 0);
                }
                $patternBlock->add(49, 12.0);
                $patternBlock->add(74, 0);
                $lastWasSpace = false;
                break;
            case '.':
                if (!($firstPass || $lastWasSpace)){
                    $patternBlock->add(49, -3.0);
                    $patternBlock->add(74, 0);
                }
                $patternBlock->add(49, 0.0);
                $patternBlock->add(74, 0);
                $lastWasSpace = false;
                break;
            case ' ':
                $patternBlock->add(49, -18.0);
                $patternBlock->add(74, 0);
                $lastWasSpace = true;
                break;
            default:
                throw new \Exception("Unrecognized symbol in linetype pattern");
                break;
            }

            $firstPass = false;
        }

        if (!$lastWasSpace){
            $patternBlock->add(49, -3.0);
            $patternBlock->add(74, 0);
        }

        return $patternBlock;
    }

    private function getVport()
    {
        $vport = new DxfBlock();
        $vport->add(0, "VPORT");
        $vport->add(5, $this->getNewHandle());
        $vport->add(330, $this->handles["vportTable"]);
        $vport->add(100, "AcDbSymbolTableRecord");
        $vport->add(100, "AcDbViewportTableRecord");
        $vport->add(2, "*Active");
        $vport->add(70, 0);
        $vport->add(10, "0.0");
        $vport->add(20, "0.0");
        $vport->add(11, "1.0");
        $vport->add(21, "1.0");
        $vport->add(12, 23.05009701445532);
        $vport->add(22, 11.22268240045883);
        $vport->add(13, "0.0");
        $vport->add(23, "0.0");
        $vport->add(14, 0.5);
        $vport->add(24, 0.5);
        $vport->add(15, 0.5);
        $vport->add(25, 0.5);
        $vport->add(16, "0.0");
        $vport->add(26, "0.0");
        $vport->add(36, "1.0");
        $vport->add(17, "0.0");
        $vport->add(27, "0.0");
        $vport->add(37, "0.0");
        $vport->add(40, 24.41255520304833);
        $vport->add(41, 1.978801843317972);
        $vport->add(42, "50.0");
        $vport->add(43, "0.0");
        $vport->add(44, "0.0");
        $vport->add(50, "0.0");
        $vport->add(51, "0.0");
        $vport->add(71, 0);
        $vport->add(72, 1000);
        $vport->add(73, 1);
        $vport->add(74, 3);
        $vport->add(75, 0);
        $vport->add(76, 1);
        $vport->add(77, 0);
        $vport->add(78, 0);
        $vport->add(281, 0);
        $vport->add(65, 1);
        $vport->add(110, "0.0");
        $vport->add(120, "0.0");
        $vport->add(130, "0.0");
        $vport->add(111, "1.0");
        $vport->add(121, "0.0");
        $vport->add(131, "0.0");
        $vport->add(112, "0.0");
        $vport->add(122, "1.0");
        $vport->add(132, "0.0");
        $vport->add(79, 0);
        $vport->add(146, "0.0");
        $vport->add(60, 3);
        $vport->add(61, 5);
        $vport->add(292, 1);
        $vport->add(282, 1);
        $vport->add(141, "0.0");
        $vport->add(142, "0.0");
        $vport->add(63, 250);
        $vport->add(421, 3355443);
        $vport->add(1001, "ACAD_NAV_VCDISPLAY");
        $vport->add(1070, 3);
        return $vport;
    }

    private function createModelObject()
    {
        $layout = new DxfBlock();
        $layout->add(0, "LAYOUT");
        $layout->add(5, $this->handles["modelLayout"]);
        $layout->add(102, "{ACAD_REACTORS");
        $layout->add(330, $this->handles["ACAD_LAYOUT"]);
        $layout->add(102, "}");
        $layout->add(330, $this->handles["ACAD_LAYOUT"]);
        $layout->add(100, "AcDbPlotSettings");
        $layout->add(1, "");
        $layout->add(2, "none_device");
        $layout->add(4, "ANSI_A_(8.50_x_11.00_Inches)");
        $layout->add(6, "");
        $layout->add(40, 6.35);
        $layout->add(41, 19.05);
        $layout->add(42, 6.35);
        $layout->add(43, 19.05);
        $layout->add(44, 215.9);
        $layout->add(45, 279.4);
        $layout->add(46, "0.0");
        $layout->add(47, "0.0");
        $layout->add(48, "0.0");
        $layout->add(49, "0.0");
        $layout->add(140, "0.0");
        $layout->add(141, "0.0");
        $layout->add(142, "1.0");
        $layout->add(143, 2.584895464708373);
        $layout->add(70, 11952);
        $layout->add(72, 0);
        $layout->add(73, 1);
        $layout->add(74, 0);
        $layout->add(7, "");
        $layout->add(75, 0);
        $layout->add(147, 0.3868628397755418);
        $layout->add(76, 0);
        $layout->add(77, 2);
        $layout->add(78, 300);
        $layout->add(148, "0.0");
        $layout->add(149, "0.0");
        $layout->add(100, "AcDbLayout");
        $layout->add(1, "Model");
        $layout->add(70, 1);
        $layout->add(71, 0);
        $layout->add(10, "0.0");
        $layout->add(20, "0.0");
        $layout->add(11, "12.0");
        $layout->add(21, "9.0");
        $layout->add(12, "0.0");
        $layout->add(22, "0.0");
        $layout->add(32, "0.0");
        $layout->add(14, "0.0");
        $layout->add(24, "0.0");
        $layout->add(34, "0.0");
        $layout->add(15, "0.0");
        $layout->add(25, "0.0");
        $layout->add(35, "0.0");
        $layout->add(146, "0.0");
        $layout->add(13, "0.0");
        $layout->add(23, "0.0");
        $layout->add(33, "0.0");
        $layout->add(16, "1.0");
        $layout->add(26, "0.0");
        $layout->add(36, "0.0");
        $layout->add(17, "0.0");
        $layout->add(27, "1.0");
        $layout->add(37, "0.0");
        $layout->add(76, 0);
        $layout->add(330, $this->handles["modelBlockRecord"]);
        $layout->add(331, $this->handles["vportTable"]);
        return $layout;
    }

    private function createLayoutObject($blockRecordHandle, $layoutHandle, $marginL, $marginB, $marginR, $marginT,
            $width, $height, $name, $pageNum)
    {
        $layout = new DxfBlock();
        $layout->add(0, "LAYOUT");
        $layout->add(5, $layoutHandle);
        $layout->add(102, "{ACAD_REACTORS");
        $layout->add(330, $this->handles["ACAD_LAYOUT"]);
        $layout->add(102, "}");
        $layout->add(330, $this->handles["ACAD_LAYOUT"]);
        $layout->add(100, "AcDbPlotSettings");
        $layout->add(1, "");
        $layout->add(2, "");
        $layout->add(4, "");
        $layout->add(6, "");
        $layout->add(40, $marginL * 25.4);
        $layout->add(41, $marginB * 25.4);
        $layout->add(42, $marginR * 25.4);
        $layout->add(43, $marginT * 25.4);
        $layout->add(44, $width * 25.4);
        $layout->add(45, $height * 25.4);
        $layout->add(46, "0.0");
        $layout->add(47, "0.0");
        $layout->add(48, "0.0");
        $layout->add(49, "0.0");
        $layout->add(140, "0.0");
        $layout->add(141, "0.0");
        $layout->add(142, "1.0");
        $layout->add(143, "1.0");
        $layout->add(70, 688);
        $layout->add(72, 0);
        $layout->add(73, 0);
        $layout->add(74, 5);
        $layout->add(7, "");
        $layout->add(75, 16);
        $layout->add(147, "1.0");
        $layout->add(76, 0);
        $layout->add(77, 2);
        $layout->add(78, 300);
        $layout->add(148, "0.0");
        $layout->add(149, "0.0");
        $layout->add(100, "AcDbLayout");
        $layout->add(1, $name);
        $layout->add(70, 1);
        $layout->add(71, $pageNum + 1);
        $layout->add(10, "0.0");
        $layout->add(20, "0.0");
        $layout->add(11, $width);
        $layout->add(21, $height);
        $layout->add(12, "0.0");
        $layout->add(22, "0.0");
        $layout->add(32, "0.0");
        $layout->add(14, "0.0");
        $layout->add(24, "0.0");
        $layout->add(34, "0.0");
        $layout->add(15, "0.0");
        $layout->add(25, "0.0");
        $layout->add(35, "0.0");
        $layout->add(146, "0.0");
        $layout->add(13, "0.0");
        $layout->add(23, "0.0");
        $layout->add(33, "0.0");
        $layout->add(16, "1.0");
        $layout->add(26, "0.0");
        $layout->add(36, "0.0");
        $layout->add(17, "0.0");
        $layout->add(27, "1.0");
        $layout->add(37, "0.0");
        $layout->add(76, 0);
        $layout->add(330, $blockRecordHandle);
        return $layout;
    }

    private function createLayoutForDictionary($name, $layoutHandle)
    {
        $layout = new DxfBlock();
        $layout->add(3, $name);
        $layout->add(350, $layoutHandle);
        return $layout;
    }

    private function createImageForDictionary($name, $imageDefinitionHandle)
    {
        $image = new DxfBlock();
        $image->add(3, $name);
        $image->add(350, $imageDefinitionHandle);
        return $image;
    }

    private function createLayoutBlock($name, $blockRecordHandle)
    {
        $preBlock = new DxfBlock();
        $preBlock->add(0, "BLOCK");
        $preBlock->add(5, $this->getNewHandle());
        $preBlock->add(330, $blockRecordHandle);
        $preBlock->add(100, "AcDbEntity");
        if ($name !== "*Model_Space"){
            $preBlock->add(67, 1);
        }
        $preBlock->add(8, 0);
        $preBlock->add(100, "AcDbBlockBegin");
        $preBlock->add(2, $name);
        $preBlock->add(70, 0);
        $preBlock->add(10, "0.0");
        $preBlock->add(20, "0.0");
        $preBlock->add(30, "0.0");
        $preBlock->add(3, $name);
        $preBlock->add(1, "");
        $postBlock = new DxfBlock();
        $postBlock->add(0, "ENDBLK");
        $postBlock->add(5, $this->getNewHandle());
        $postBlock->add(330, $blockRecordHandle);
        $postBlock->add(100, "AcDbEntity");
        if ($name !== "*Model_Space"){
            $postBlock->add(67, 1);
        }
        $postBlock->add(8, 0);
        $postBlock->add(100, "AcDbBlockEnd");

        return new DxfContainer($preBlock, $postBlock);
    }

    private function createBlockRecord($name, $blockRecordHandle, $layoutHandle)
    {
        $record = new DxfBlock();
        $record->add(0, "BLOCK_RECORD");
        $record->add(5, $blockRecordHandle);

        if ($name === "*Model_Space"){
            $record->add(102, "{ACAD_XDICTIONARY");
            $record->add(360, $this->handles["modelDictionary"]);
            $record->add(102, "}");
        }

        $record->add(330, 1);
        $record->add(100, "AcDbSymbolTableRecord");
        $record->add(100, "AcDbBlockTableRecord");
        $record->add(2, $name);
        $record->add(340, $layoutHandle);
        $record->add(70, 0);
        $record->add(280, 1);
        $record->add(281, 0);
        return $record;
    }

    private function createNamedObjectDictionary()
    {
        $dictionary = new DxfBlock();
        $dictionary->add(0, "DICTIONARY");
        $dictionary->add(5, $this->handles["namedObjectDictionary"]);
        $dictionary->add(330, "0");
        $dictionary->add(100, "AcDbDictionary");
        $dictionary->add(281,1);
        $dictionary->add(3, "ACAD_GROUP");
        $dictionary->add(350, $this->handles["ACAD_GROUP"]);
        $dictionary->add(3, "ACAD_LAYOUT");
        $dictionary->add(350, $this->handles["ACAD_LAYOUT"]);
        $dictionary->add(3, "ACAD_IMAGE_DICT");
        $dictionary->add(350, $this->handles["ACAD_IMAGE_DICT"]);
        $dictionary->add(3, "ACAD_IMAGE_VARS");
        $dictionary->add(350, $this->handles["ACAD_IMAGE_VARS"]);
        $dictionary->add(3, "ACAD_PLOTSTYLENAME");
        $dictionary->add(350, $this->handles["ACAD_PLOTSTYLENAME"]);

        return $dictionary;
    }

    private function createLayoutDictionary()
    {
        $preBody = new DxfBlock();
        $preBody->add(0, "DICTIONARY");
        $preBody->add(5, $this->handles["ACAD_LAYOUT"]);
        $preBody->add(102, "{ACAD_REACTORS");
        $preBody->add(330, $this->handles["namedObjectDictionary"]);
        $preBody->add(102, "}");
        $preBody->add(330, $this->handles["namedObjectDictionary"]);
        $preBody->add(100, "AcDbDictionary");
        $preBody->add(281,1);

        $layoutDictionary = new DxfContainer($preBody);
        $layoutDictionary->addBlock($this->createLayoutForDictionary("Model", $this->handles["modelLayout"]));

        return $layoutDictionary;
    }

    private function createImageDictionary()
    {
        $preBody = new DxfBlock();
        $preBody->add(0, "DICTIONARY");
        $preBody->add(5, $this->handles["ACAD_IMAGE_DICT"]);
        $preBody->add(102, "{ACAD_REACTORS");
        $preBody->add(330, $this->handles["namedObjectDictionary"]);
        $preBody->add(102, "}");
        $preBody->add(330, $this->handles["namedObjectDictionary"]);
        $preBody->add(100, "AcDbDictionary");
        $preBody->add(281,1);

        return new DxfContainer($preBody);
    }

    private function createTable($title, $handle, $maxEntries)
    {
        $preBody = new DxfBlock();
        $preBody->add(0, "TABLE");
        $preBody->add(2, $title);
        $preBody->add(5, $handle);
        $preBody->add(330, 0);
        $preBody->add(100, "AcDbSymbolTable");
        $preBody->add(70, $maxEntries);
        $postBody = new DxfBlock();
        $postBody->add(0, "ENDTAB");
        return new DxfContainer($preBody, $postBody);
    }

    private function createBlockRecordTable()
    {
        return $this->createTable("BLOCK_RECORD", 1, count($this->drawing->pages) + count($this->blockRecords));
    }

    private function makeSection($title)
    {
        $pre = new DxfBlock();
        $pre->add(0, "SECTION");
        $pre->add(2, $title);
        $post = new DxfBlock();
        $post->add(0, "ENDSEC");
        return new DxfContainer($pre, $post);
    }

    private function setUpHeader()
    {
        $variables = $this->setVariables();
        $this->header->addBlock($this->variablesToDxf($variables));
    }

    private function setVariables()
    {
        // AC1027 represnets AutoCAD 2013
        // EXTMAX and EXTMIN definitely do nothing useful here.
        $variables = [
                '$ACADVER' => [1 => "AC1027"],
                '$INSBASE' => [10 => "0.0", 20 => "0.0", 30 => "0.0"],
                '$EXTMIN' => [
                        10 => "1.000000000000000E+20",
                        20 => "1.000000000000000E+20",
                        30 => "1.000000000000000E+20"],
                '$EXTMAX' => [
                        10 => "-1.000000000000000E+20",
                        20 => "-1.000000000000000E+20",
                        30 => "-1.000000000000000E+20"],
                '$LTSCALE' => [40 => "0.03"],
                '$LUNITS' => [70 => 4],
        ];

        return $variables;
    }

    private function variablesToDxf($variables)
    {
        $body = new DxfBlock();
        foreach ($variables as $variable => $content){
            $body->add(9, $variable);
            foreach ($content as $code => $value){
                $body->add($code, $value);
            }
        }
        return $body;
    }
}
