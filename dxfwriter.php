<?php

/**
 * @author Alessandro Vernassa <speleoalex@gmail.com> http://speleoalex.altervista.org
 * @copyright Copyright (c) 2013
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */
define("DXF_COLOR_BYLAYER", 0);
define("DXF_COLOR_BLACK", 250);
define("DXF_COLOR_RED", 1);
define("DXF_COLOR_YELLOW", 2);
define("DXF_COLOR_GREEN", 3);
define("DXF_COLOR_CYAN", 4);
define("DXF_COLOR_BLUE", 5);
define("DXF_COLOR_MAGENTA", 6);
define("DXF_COLOR_WHITE", 7);
define("DXF_COLOR_GRAY", 8);
define("DXF_COLOR_lightGRAY", 9);

class DXFLayer {

    /**
     * 
     */
    function DXFLayer() {
        $this->name = "0";
        $this->color = "0";
        $this->linetype = "CONTINUOUS";
    }

}

/**
 * 
 * 
 * example: 
 * $shape = new DXF();
 * $shape->addLayer("mylayer",DXF_COLOR_RED);
 * $shape->addLine(10, 10, 0, 10, 300, 0, "mylayer");
 * 
 * $dxfstring = $shape->getString();
 * 
 * $shape->SaveFile("myFile.dxf");
 * 
 */
class DXF {

    /**
     * 
     */
    function DXF() {
        $this->strDXF = "";
        $this->layers = array();
        $this->ShapeString = "";
    }

    /**
     * 
     * @param string $name
     * @param string $color
     * @param string $linetype
     */
    function addLayer($name = "0", $color = DXF_COLOR_WHITE, $linetype = "CONTINUOUS") {
        $tmp = new DXFLayer();
        $tmp->name = $name;
        $tmp->color = $color;
        $tmp->linetype = $linetype;
        $this->layers[] = $tmp;
    }

    /**
     * 
     * @return type
     */
    function getString() {
        $strDXF = "";
        $strDXF .= $this->getHeaderString();
        $strDXF .= $this->getBodyString();
        return $strDXF;
    }

    /**
     * 
     * @return string
     */
    function getHeaderString() {
        $strDXF = "";
        $strDXF .= "0\nSECTION\n  2\nHEADER\n  9\n\$ACADVER\n  1\nAC1006\n  0\nENDSEC\n  0\n";
        //layers:
        $strDXF .= "SECTION\n  2\nTABLES\n  0\nTABLE\n2\n";
        $strDXF .= $this->getLayersString();
        $strDXF .= "ENDTAB\n 0\nENDSEC\n";
        return $strDXF;
    }

    /**
     * 
     * @return string
     */
    function getBodyString() {
        $strDXF = "";
        $strDXF .= "0\nSECTION\n2\nENTITIES\n0\n";
        $strDXF .= $this->ShapeString;
        $strDXF .= "ENDSEC\n0\nEOF\n";
        return $strDXF;
    }

    /**
     * 
     * @return string
     */
    function getLayersString() {
        $strDXF = "";
        $strDXF .= "LAYER\n  0\n";
        $count = 1;
        foreach ($this->layers as $layer) {
            $strDXF .= "LAYER\n 2\n" . $layer->name . "\n 70\n 64\n 62\n " . $layer->color . "\n 6\n" . $layer->linetype . "\n 0\n";
            $count++;
        }
        return $strDXF;
    }

    /**
     * 
     * @param float $x
     * @param float $y
     * @param float $z
     * @param float $x2
     * @param float $y2
     * @param float $z2
     * @param string $layerName
     * @return string
     */
    function addLine($x, $y, $z, $x2, $y2, $z2, $layerName = "0") {
        $str = "LINE\n" .
                "8" . "\n" .
                $layerName . "\n" .
                "10" . "\n" .
                $x . "\n" .
                "20" . "\n" .
                $y . "\n" .
                "30" . "\n" .
                $z . "\n" .
                "11" . "\n" .
                $x2 . "\n" .
                "21" . "\n" .
                $y2 . "\n" .
                "31" . "\n" .
                $z2 . "\n0\n";
        $this->ShapeString .= $str;
        return $str;
    }

    /**
     * 
     * @param float $x
     * @param float $y
     * @param float $z
     * @param string $text
     * @param float $size
     * @param string $layerName
     * @return string
     */
    function addText($x, $y, $z, $text, $size, $layerName = "0") {
        $str = "TEXT\n" .
                "8" . "\n" .
                $layerName . "\n" .
                " 10\n" .
                $x . "\n" .
                " 20\n" .
                $y . "\n" .
                " 30\n" .
                $z . "\n" .
                " 40\n" .
                $size . "\n" .
                "  1\n" .
                $text . "\n" .
                "  0\n";
        $this->ShapeString .= $str;
        return $str;
    }

    /**
     * 
     * @param float $x
     * @param float $y
     * @param float $z
     * @param float $radius
     * @param string $layerName
     * @return string
     */
    function addCircle($x, $y, $z, $radius, $layerName = "0") {
        $str = "CIRCLE\n" .
                "8" . "\n" .
                $layerName . "\n" .
                "10" . "\n" .
                $x . "\n" .
                "20" . "\n" .
                $y . "\n" .
                "30" . "\n" .
                $z . "\n" .
                "40" . "\n" .
                $radius . "\n" .
                "0\n";
        $this->ShapeString .= $str;
        return $str;
    }

    /**
     * 
     * @param type $filename
     */
    function SaveFile($filename) {
        while (false !== ob_get_clean()
        );
        header("Content-Type: image/vnd.dxf");
        header("Content-Disposition: inline; filename=$filename");
        echo $this->getString();
        die();
    }

}
?>
