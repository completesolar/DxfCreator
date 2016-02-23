<?php
/**
 * @author Alessandro Vernassa 
 */
ob_start();
require_once 'dxfwriter.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>DXF Writer</title>
    </head>
    <body>
        <pre>CODE:
        $shape = new DXF();
        $shape->addLayer("mylayer", DXF_COLOR_RED);
        $shape->addLayer("text", DXF_COLOR_BLUE);
        $shape->addText(12, 110, 0, "HELLO WORLD", 5, "text");
        $shape->addCircle(50, 100, 0, 50, "mylayer");
        $shape->addLine(0, 100, 0, 100, 100, 0, "mylayer");
        </pre>
        <a href="?download">download dxf</a><br />
        <?php
        $shape = new DXF();
        $shape->addLayer("mylayer", DXF_COLOR_RED);
        $shape->addLayer("text", DXF_COLOR_BLUE);
        $shape->addText(12, 110, 0, "HELLO WORLD", 5, "text");
        $shape->addCircle(50, 100, 0, 50, "mylayer");
        $shape->addLine(0, 100, 0, 100, 100, 0, "mylayer");
        if (isset($_GET['download']))
        {
            $shape->SaveFile("myFile.dxf");        
        }
        $dxfstring = $shape->getString();
        
        echo "<h2>dxf string:</h2><pre>$dxfstring</pre>";
        ?>
    </body>
</html>
