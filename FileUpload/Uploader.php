<?php
/**
 * @example 
    // list of valid extensions, ex. array("jpeg", "xml", "bmp")
    $allowedExtensions = array();
    // max file size in bytes
    $sizeLimit = 10 * 1024 * 1024;

    $uploader = new FileUpload_Uploader( $allowedExtensions, $sizeLimit );
    $result = $uploader->handleUpload('uploads/');
    // to pass data through iframe you will need to encode all html tags
    echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
 * @url https://raw.github.com/valums/file-uploader/master/server/php.php
 */

class FileUpload_Uploader
{
    private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;

    function __construct( array $allowedExtensions = array(), $sizeLimit = 10485760 )
    {
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        
        $this->checkServerSettings();       

        if( isset( $_GET['qqfile'] ) )
            $this->file = new FileUpload_Xhr();
        elseif( isset( $_FILES['qqfile'] ) )
            $this->file = new FileUpload_Form();
        else
            $this->file = false; 
    }
    
    private function checkServerSettings()
    {
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        
        if( ( $postSize < $this->sizeLimit ) 
            || ( $uploadSize < $this->sizeLimit ) )
        {
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");    
        }        
    }
    
    private function toBytes( $str )
    {
        $val = trim( $str );
        $last = strtolower( $str[ strlen( $str ) - 1 ] );

        switch( $last )
        {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }

        return $val;
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload( $uploadDirectory, $filename = false, $replaceOldFile = FALSE )
    {
        if( !is_writable( $uploadDirectory ) )
            return array('error' => "Server error. Upload directory isn't writable.");
        
        if( !$this->file )
            return array('error' => 'No files were uploaded.');
        
        $size = $this->file->getSize();
        
        if( $size == 0 )
            return array('error' => 'File is empty');
        
        if( $size > $this->sizeLimit )
            return array('error' => 'File is too large');
        
        $realPathinfo = pathinfo($this->file->getName());
        $realFilename = $realPathinfo['filename'];
        $realExt = $realPathinfo['extension'];

        if( !$filename )
        {
            $pathinfo = pathinfo($this->file->getName());
            $filename = $pathinfo['filename'];
            $ext = '.' . $pathinfo['extension'];
        }
        else
        {
            $_fileinfo = pathinfo($filename);
            $filename = $_fileinfo['filename'];

            if( isset( $_fileinfo['extension'] ) )
                $ext = '.' . $_fileinfo['extension'];
            else
                $ext = null;
        }

        if( $this->allowedExtensions 
            && !in_array( strtolower( $realExt ), $this->allowedExtensions ) )
        {
            $these = implode( ', ', $this->allowedExtensions );
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        
        if( !$replaceOldFile )
        {
            /// don't overwrite previous files that were uploaded
            while( file_exists( $uploadDirectory . $filename . $ext ) )
                $filename .= rand(10, 99);
        }
        
        if( $this->file->save( $uploadDirectory . $filename . $ext ) )
        {
            chmod( $uploadDirectory . $filename . $ext, 0777 );
            return array('success'=>true);
        }
        else
            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
    } 
}