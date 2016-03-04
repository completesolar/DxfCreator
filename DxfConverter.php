<?php namespace DXFWriter;

class DxfConverter
{
    private $cad;
    private $header;
    private $tables;
    private $blocks;
    private $entities;
    private $objects;
    private $handseed;

    public function getNewHandle()
    {
        $this->handseed = strtoupper(dechex(hexdec($this->handseed) + 1));
        return $this->handseed;
    }

    public function __construct(CadMaker $newCad)
    {
        $this->cad = $newCad;
        $this->header = $this->makeSection("HEADER");
        $this->tables = $this->makeSection("TABLES");
        $this->blocks = $this->makeSection("BLOCKS");
        $this->entities = $this->makeSection("ENTITIES");
        $this->objects = $this->makeSection("OBJECTS");
        $this->handseed = "210";
        $this->setUpHeader($this->cad);

        // One time stuff for all pages:

        $vportTable = $this->createTable("VPORT", 8, 1);
        $vportTable->addBlock($this->getVport());
        $ltypeTable = $this->createTable("LTYPE", 5, 1);
        $ltypeTable->addBlock($this->getLtype("ByBlock", "", 14));
        $ltypeTable->addBlock($this->getLtype("ByLayer", "", 15));
        $ltypeTable->addBlock($this->getLtype("Continuous", "Solid line", 16));
        $layerTable = $this->getLayerTable();
        $styleTable = $this->createTable("STYLE", 3, 2);
        $viewTable = $this->createTable("VIEW", 6, 2);
        $ucsTable = $this->createTable("UCS", 7, 0);
        $appidTable = $this->createTable("APPID", 9, 8);
        $appidTable->addBlock($this->getAppid("ACAD", 12));
        $appidTable->addBlock($this->getAppid("AcadAnnoPO", "DD"));
        $appidTable->addBlock($this->getAppid("AcadAnnotative", "DE"));
        $appidTable->addBlock($this->getAppid("ACAD_DSTYLE_DIMJAG", "DF"));
        $appidTable->addBlock($this->getAppid("ACAD_DSTYLE_DIMTALN", "E0"));
        $appidTable->addBlock($this->getAppid("ACAD_MLEADERVER", 107));
        $appidTable->addBlock($this->getAppid("ACAD_NAV_VCDISPLAY", "1A6"));
        $appidTable->addBlock($this->getAppid("ACAD_PSEXT", "1F6"));
        $dimstyleTable = $this->getDimstyleTable();

        $this->tables->addBlock($vportTable);
        $this->tables->addBlock($ltypeTable);
        $this->tables->addBlock($layerTable);
        $this->tables->addBlock($styleTable);
        $this->tables->addBlock($viewTable);
        $this->tables->addBlock($ucsTable);
        $this->tables->addBlock($appidTable);
        $this->tables->addBlock($dimstyleTable);

        $this->objects->addBlock($this->createNamedObjectDictionary());
        $this->objects->addBlock($this->createOtherDictionaries());
        $layoutDictionary = $this->createLayoutDictionary();


        //Add model space
        $blockRecordTable = $this->createBlockRecordTable();
        $blockRecordTable->addBlock($this->createBlockRecord("*Model_Space", "1F", 22));
        $this->blocks->addBlock($this->createLayoutBlock("*Model_Space", "1F"));

        $layouts = new DxfBlock();

        $pageNum = 0;
        for ($pageNum = 0; $pageNum < count($this->cad->pages); $pageNum++){

            $marginBottom = $this->cad->pages[$pageNum]->marginBottom;
            $marginLeft = $this->cad->pages[$pageNum]->marginLeft;
            $marginRight = $this->cad->pages[$pageNum]->marginRight;
            $marginTop = $this->cad->pages[$pageNum]->marginTop;
            $name = $this->cad->pages[$pageNum]->name;
            $xLength = $this->cad->pages[$pageNum]->xLength;
            $yLength = $this->cad->pages[$pageNum]->yLength;
            $blockRecordHandle = $this->getNewHandle();
            $layoutHandle = $this->getNewHandle();

            if ($pageNum == 0){
                $id = "*Paper_Space";
            } else {
                $id = "*Paper_Space" . ($pageNum - 1);
            }

            //echo "ID = ". $id;

            $blockRecordTable->addBlock($this->createBlockRecord($id, $blockRecordHandle, $layoutHandle));
            $layoutBlock = $this->createLayoutBlock($id, $blockRecordHandle);
            //$this->blocks->addBlock($this->createLayoutBlock($id, $blockRecordHandle));
            $layoutDictionary->addBlock($this->createLayoutForDictionary($name, $layoutHandle));
            $layouts->addBlock($this->createLayoutObject(
                    $blockRecordHandle,
                    $layoutHandle,
                    $marginLeft,
                    $marginBottom,
                    $marginRight,
                    $marginTop,
                    $xLength,
                    $yLength,
                    $name,
                    $pageNum));

            if (!empty($this->cad->pages[$pageNum]->content)){
                foreach ($this->cad->pages[$pageNum]->content as $shape){
                    if ($pageNum == 0){
                        $this->entities->addBlock($this->getShape($shape, $blockRecordHandle));
                    } else {
                        $layoutBlock->addBlock($this->getShape($shape, $blockRecordHandle));
                    }

                }
            }

            $this->blocks->addBlock($layoutBlock);
        }

        $layoutDictionary->addBlock($this->createLayoutForDictionary("Model", 22));
        $layouts->addBlock($this->createModelObject());

        $this->tables->addBlock($blockRecordTable);
        $this->objects->addBlock($layoutDictionary);
        $this->objects->addBlock($layouts);
    }

    public function getShape(Shape $shape, $layoutBlockRecordHandle)
    {
        $dxfShape = new DxfBlock();
        $dxfShape->add(0, "LWPOLYLINE");
        $dxfShape->add(5, $this->getNewHandle());
        $dxfShape->add(330, $layoutBlockRecordHandle);
        $dxfShape->add(100, "AcDbEntity");
        $dxfShape->add(67, 1);
        $dxfShape->add(8, 0);
        $dxfShape->add(6, "Continuous");
        $dxfShape->add(62, $shape->lineColor);
        //$dxfShape->add(370, 200);
        //$dxfShape->add(370, min($shape->lineWeight*100, 200));
        $dxfShape->add(370, intval($shape->lineWeight*100));
        // Options for shape probably go here in group codes found under Common Entity Group Codes
        $dxfShape->add(100, "AcDbPolyline");
        $dxfShape->add(90, count($shape->points));
        $dxfShape->add(70, $shape->type == "line" ? 0 : 1);
        $dxfShape->add(43, 0.0);
        //$dxfShape->add(39, $shape->lineWeight);

        foreach ($shape->points as $point){
            $dxfShape->add(10, $point[0]);
            $dxfShape->add(20, $point[1]);
        }

        return $dxfShape;
    }

    public function createDictionary($handle, $pointer)
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

    public function createOtherDictionaries()
    {
        $dictionaries = new DxfBlock();
        $dictionaries->addBlock($this->createDictionary("15D", "1F"));
        $dictionaries->addBlock($this->createDictionary("1FE", "1FD"));
        $dictionaries->add(3, "ASDK_XREC_ANNOTATION_SCALE_INFO");
        $dictionaries->add(360, "1FF");
        $dictionaries->addBlock($this->createDictionary("202", "201"));
        $dictionaries->add(3, "ASDK_XREC_ANNOTATION_SCALE_INFO");
        $dictionaries->add(360, 203);
        $dictionaries->add(0, "DICTIONARY");
        $dictionaries->add(5, "D");
        $dictionaries->add(102, "{ACAD_REACTORS");
        $dictionaries->add(330, "C");
        $dictionaries->add(102, "}");
        $dictionaries->add(330, "C");
        $dictionaries->add(100, "AcDbDictionary");
        $dictionaries->add(281, 1);
        return $dictionaries;
    }

    public function getDimstyleTable()
    {
        $preBody = new DxfBlock();
        $preBody->add(0, "TABLE");
        $preBody->add(2, "DIMSTYLE");
        $preBody->add(5, "A");
        $preBody->add(330, 0);
        $preBody->add(100, "AcDbSymbolTable");
        $preBody->add(70, 2);
        $preBody->add(100, "AcDbDimStyleTable");
        $preBody->add(71, 2);
        $preBody->add(340, 27);
        $postBody = new DxfBlock();
        $postBody->add(0, "ENDTAB");
        $dimstyleTable = new DxfContainer($preBody, $postBody);

        $dimstyle = new DxfBlock();
        $dimstyle->add(0, "DIMSTYLE");
        $dimstyle->add(105, 27);
        $dimstyle->add(330, "A");
        $dimstyle->add(100, "AcDbSymbolTableRecord");
        $dimstyle->add(100, "AcDbDimStyleTableRecord");
        $dimstyle->add(2, "Standard");
        $dimstyle->add(70, 0);
        $dimstyle->add(340, 11);

        $dimstyleTable->addBlock($dimstyle);
        return $dimstyleTable;
    }

    public function getAppid($name, $handle)
    {
        $appid = new DxfBlock();
        $appid->add(0, "APPID");
        $appid->add(5, $handle);
        $appid->add(330, 9);
        $appid->add(100, "AcDbSymbolTableRecord");
        $appid->add(100, "AcDbRegAppTableRecord");
        $appid->add(2, $name);
        $appid->add(70, 0);
        return $appid;
    }

    public function getLayerTable()
    {
        $preBody = new DxfBlock();
        $preBody->add(0, "TABLE");
        $preBody->add(2, "LAYER");
        $preBody->add(5, 2);
        $preBody->add(102, "{ACAD_XDICTIONARY");
        $preBody->add(360, "18E");
        $preBody->add(102, "}");
        $preBody->add(330, 0);
        $preBody->add(100, "AcDbSymbolTable");
        $preBody->add(70, 1);
        $postBody = new DxfBlock();
        $postBody->add(0, "ENDTAB");
        $layerTable = new DxfContainer($preBody, $postBody);

        $layer = new DxfBlock();
        $layer->add(0, "LAYER");
        $layer->add(5, 10);
        $layer->add(102, "{ACAD_XDICTIONARY");
        $layer->add(360, "E6");
        $layer->add(102, "}");
        $layer->add(330, 2);
        $layer->add(100, "AcDbSymbolTableRecord");
        $layer->add(100, "AcDbLayerTableRecord");
        $layer->add(2, 0);
        $layer->add(70, 0);
        $layer->add(62, 7);
        $layer->add(6, "Continuous");
        $layer->add(370, -3);
        $layer->add(390, "F");
        $layer->add(347, 98);
        $layer->add(348, 0);
        $layerTable->addBlock($layer);
        return $layerTable;
    }


    public function getLtype($name, $description, $handle)
    {
        $ltype = new DxfBlock();
        $ltype->add(0, "LTYPE");
        $ltype->add(5, $handle);
        $ltype->add(330, 5);
        $ltype->add(100, "AcDbSymbolTableRecord");
        $ltype->add(100, "AcDbLinetypeTableRecord");
        $ltype->add(2, $name);
        $ltype->add(70, 0);
        $ltype->add(3, $description);
        $ltype->add(72, 65);
        $ltype->add(73, 0);
        $ltype->add(40, "0.0");
        return $ltype;
    }

    public function getVport()
    {
        $vport = new DxfBlock();
        $vport->add(0, "VPORT");
        $vport->add(5, 94);
        $vport->add(330, 8);
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
        $vport->add(348, "9F");
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

    public function save($filePath)
    {
        // Add handseed to header

        $this->header->add(9, '$HANDSEED');
        $this->header->add(5, $this->getNewHandle());

        $file = new DxfBlock();
        $file->addBlock($this->header);
        $file->addBlock($this->tables);
        $file->addBlock($this->blocks);
        $file->addBlock($this->entities);
        $file->addBlock($this->objects);
        $file->add(0, "EOF");

        //echo $file->toString();

        file_put_contents($filePath, $file->toString());

        // BLOCK_RECORD TABLE code 70 = #pages - 1
        // one block record for each page, and one for the model space
        // code 5 = handle (ex. 1EB)
        // code 330 = soft pointer (ex. 1)
        // 100 = AcDbSymbolTableRecord
        // 100 = AcDbBlockTableRecord
        // code 2 = *Paper_Space# (where # is the page number - 2)
        // code 340, pointer to layout object (ex. 1EC)
        // 70 = number of inserts (ex. 0)
        // 280 = block explodability (ex. 1)
        // 281 = block scalability (ex. 0)

        // Add a BLOCK in BLOCKS section:
        // 5 = handle, (ex. 1ED)
        // 330 = soft pointer id, same as handle in block record (ex. 1EB)
        // 100 = AcDbEntity
        // 8 = Layer name
        // 100 = AcDbBlockBegin
        // 2 = same as 2 in block record (ex *Paper_Space1)
        // 70 = flag indicating things, set to 0
        // 10,20,30 = basepoints in x,y,z
        // 3 = block name again (ex *Paper_Space1)
        // 1 = xref path name, who knows, leave blank

        // 0 = ENDBLK
        // 5 = handle (ex. 1EE)
        // 330 = soft-pointer, same as in block/block_record (ex. 1EB)
        // 100 = AcDbEntity
        // 8 = leyer name
        // 100 = AcDbBlockEnd

        // In Objects Table
        // Add all pages to one dictionary:
        // 0 = DICTIONARY
        // 5 = handle, (ex. 1A)
        // 102 = {ACAD_REACTORS
        // 330 = C , soft pointer to owner dictionary
        // 102 = }
        // 330 = C , soft pointer to owner object
        // 100 = AcDbDictionary
        // 281 = duplicate record options, set to 1
        // 3 = actual name of page (ex. S0.0 Cover Sheet)
        // 350 = soft owner id, same as 340 in block record (ex 1EC)
        // REPEAT 3 and 350 for each page, and for ModelSpace

        // Add a layout object for each page
        // 0 = LAYOUT
        // 5 = handle, same as 340 in block record and 350 in dictionary (ex 1EC)
        // 102 = {ACAD_REACTORS
        // 330 = handle of dictionary (ex. 1A)
        // 102 = }
        // 330 = handle of dictionary (ex. 1A)
        // 100 = AcDbPlotSettings
        // List of plot settings:
        // 1 = page setup name, leave blank
        // 2 = name of printer, (ex. Kyocera TASKalfa 3050ci XPS)
        // 4 = paper size, not sure how to format it
        // 6 = plot view name, not sure what it means
        // 40 = left margin size (mm)
        // 41 = bottom margin size
        // 42 = right margin size
        // 43 = top margin size
        // 44 = physical paper width (mm)
        // 45 = physical paper height
        // 46 = x value of origin offset (mm)
        // 47 = y value of origin offset (mm)
        // 48 = x value of lower left plot window corner
        // 49 = y value of upper right plot window corner
        // 140 = x value of lower left plot window corner
        // 141 = y value of upper right plot window corner
        // 142 = Custom print scale: real world units
        // 143 = Custom printscale: drawing units
        // 70 = plot layout flag (ex. 688, no idea what it means)
        // 72 = plot paper units (ex. 0 , for inches)
        // 73 = plot rotation (ex. 0)
        // 74 = plot type ie. what portion to display (currently 5, but maybe play around with this)
        // 7 = current style sheet (leave blank)
        // 75 = standard scale type (ex 16 , aka 1:1)
        // 147 = float that represents scale again (ex. 1.0)
        // 76 = ShadePlot mode (ex. 0)
        // 77 = ShadePlot resolution level (ex. 2 , normal)
        // 78 = ShadePlot custom DPI (ex. 300)
        // 148 = Paper image origin, x value (ex. 0.0)
        // 149 = Paper image origin, y value (ex. 0.0)

        // 100 = AcDbLayout
        // 1 = Layout name (ex. S0.0 Cover Sheet)
        // 70 = some sort of flag (ex. 1)
        // 71 = tab order in autocad, after model space (ex. 3)
        // 10 = minimum limits of layout, x value (ex. 0.0)
        // 20 = min limits of layout, y value (ex 0.0)
        // 11 = max limits, x value (ex. 9.0)
        // 21 = max limits, y value (ex. 12.0)
        // 12 = insertion base point, x value (ex. 0.0)
        // 22 = insertion base point, y value (ex. 0.0)
        // 32 = insertion base point, z value (ex. 0.0)
        // 14 = min extents for layout, x value (ex. 0.0)
        // 24 = min extents, y valuie (ex. 0.0)
        // 34 = min extents, z value (ex. 0.0)
        // 15 = max extents, x value (ex. 0.0)
        // 25 = max extents, y value (ex. 0.0)
        // 35 = max extents, z value (ex. 0.0)
        // 146 = elevation (ex. 0.0)
        // 13 = UCS origin, x value (ex. 0.0)
        // 23 = UCS origin, y value (ex. 0.0)
        // 33 = UCS origin, z value (ex. 0.0)
        // 16 = UCS x-axis, x value (ex 1.0)
        // 26 = UCS x-axis, y value (ex 0.0)
        // 36 = UCS x-axis, z value (ex. 0.0)
        // 17 = UCS y-axis, x value (ex. 0.0)
        // 27 = UCS y-axis, y value (ex 1.0)
        // 37 = UCS y-axis, z value (ex. 0.0)
        // 76 = Orthographic type of UCS (ex. 0 , non-orthographic)
        // 330 = handle to layout's paper space block table record (ex. 1EB)
    }


    public function createModelObject()
    {
        $layout = new DxfBlock();
        $layout->add(0, "LAYOUT");
        $layout->add(5, 22);
        $layout->add(102, "{ACAD_REACTORS");
        $layout->add(330, "1A");
        $layout->add(102, "}");
        $layout->add(330, "1A");
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
        $layout->add(46, "0.0"); //play around with all these
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
        $layout->add(330, "1F");
        $layout->add(331, "94");
        return $layout;
    }

    public function createLayoutObject($blockRecordHandle, $layoutHandle, $marginL, $marginB, $marginR, $marginT,
            $width, $height, $name, $pageNum)
    {
        $layout = new DxfBlock();
        $layout->add(0, "LAYOUT");
        $layout->add(5, $layoutHandle);
        $layout->add(102, "{ACAD_REACTORS");
        $layout->add(330, "1A");
        $layout->add(102, "}");
        $layout->add(330, "1A");
        $layout->add(100, "AcDbPlotSettings");
        $layout->add(1, "");
        $layout->add(2, "Kyocera TASKalfa 3050ci XPS"); // don't hardcode this
        $layout->add(4, "");
        $layout->add(6, "");
        $layout->add(40, $marginL * 25.4);
        $layout->add(41, $marginB * 25.4);
        $layout->add(42, $marginR * 25.4);
        $layout->add(43, $marginT * 25.4);
        $layout->add(44, $width * 25.4);
        $layout->add(45, $height * 25.4);
        $layout->add(46, "0.0"); //play around with all these
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

    public function createLayoutForDictionary($name, $layoutHandle)
    {
        $layout = new DxfBlock();
        $layout->add(3, $name);
        $layout->add(350, $layoutHandle);
        return $layout;
    }

    public function createLayoutBlock($name, $blockRecordHandle)
    {
        $preBlock = new DxfBlock();
        $preBlock->add(0, "BLOCK");
        $preBlock->add(5, $this->getNewHandle());
        $preBlock->add(330, $blockRecordHandle);
        $preBlock->add(100, "AcDbEntity");
        if ($name != "*Model_Space"){
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
        if ($name != "*Model_Space"){
            $postBlock->add(67, 1);
        }
        $postBlock->add(8, 0);
        $postBlock->add(100, "AcDbBlockEnd");

        return new DxfContainer($preBlock, $postBlock);
    }

    public function createBlockRecord($name, $blockRecordHandle, $layoutHandle)
    {
        $record = new DxfBlock();
        $record->add(0, "BLOCK_RECORD");
        $record->add(5, $blockRecordHandle);

        if ($name == "*Model_Space"){
            $record->add(102, "{ACAD_XDICTIONARY");
            $record->add(360, "15D");
            $record->add(102, "}");
        }

        $record->add(330, 1); // This probably references something
        $record->add(100, "AcDbSymbolTableRecord");
        $record->add(100, "AcDbBlockTableRecord");
        $record->add(2, $name);
        $record->add(340, $layoutHandle);
        $record->add(70, 0); // Inserts is hardcoded for now
        $record->add(280, 1);
        $record->add(281, 0);
        return $record;
    }

    public function createNamedObjectDictionary()
    {
        $dictionary = new DxfBlock();
        $dictionary->add(0, "DICTIONARY");
        $dictionary->add(5, "C");
        $dictionary->add(330, "0");
        $dictionary->add(100, "AcDbDictionary");
        $dictionary->add(281,1);
        $dictionary->add(3, "ACAD_GROUP");
        $dictionary->add(350, "D");
        $dictionary->add(3, "ACAD_LAYOUT");
        $dictionary->add(350, "1A");

        return $dictionary;
    }

    public function createLayoutDictionary()
    {
        $preBody = new DxfBlock();
        $preBody->add(0, "DICTIONARY");
        $preBody->add(5, "1A");
        $preBody->add(102, "{ACAD_REACTORS");
        $preBody->add(330, "C");
        $preBody->add(102, "}");
        $preBody->add(330, "C");
        $preBody->add(100, "AcDbDictionary");
        $preBody->add(281,1);

        return new DxfContainer($preBody);
    }

    public function createTable($title, $handle, $maxEntries)
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

    public function createBlockRecordTable()
    {
        //$maxPages = count($this->cad->pages) < 2 ? 0 : count($this->cad->pages) - 2;
        return $this->createTable("BLOCK_RECORD", 1, count($this->cad->pages));
    }

    public function makeSection($title)
    {
        $pre = new DxfBlock();
        $pre->add(0, "SECTION");
        $pre->add(2, $title);
        $post = new DxfBlock();
        $post->add(0, "ENDSEC");
        return new DxfContainer($pre, $post);
    }

    public function setUpHeader(CadMaker $cad)
    {
        $variables = $this->setVariables($cad);
        $this->header->addBlock($this->variablesToDxf($variables));
    }

    public function setVariables(CadMaker $cad)
    {
        // For now just hardcoded
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
        ];

        return $variables;
    }

    public function variablesToDxf($variables)
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