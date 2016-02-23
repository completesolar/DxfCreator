<?php namespace DXFWriter;

interface DocumentInterface
{
    /**
     * Converts the CAD object into a file at the specified location,
     * or to the previously saved location if no path given.
     * 
     * @parameters string $filePath
     * 
     * @returns null
     */
    public function save($filePath);
    
    
}
