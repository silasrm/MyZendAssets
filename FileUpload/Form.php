<?php
/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 * @url https://raw.github.com/valums/file-uploader/master/server/php.php
 */

class FileUpload_Form
{
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save( $path )
    {
        if( !move_uploaded_file( $_FILES['qqfile']['tmp_name'], $path ) )
        {
            return false;
        }

        return true;
    }

    function getName()
    {
        return $_FILES['qqfile']['name'];
    }

    function getSize()
    {
        return $_FILES['qqfile']['size'];
    }
}