<?php

/**
 * Renomeado.
 *
 * <code>
 * Por no bootstrap:
 * protected function _initActionHelpers() {
 *		Zend_Controller_Action_HelperBroker::addPrefix('CORE_Controller_Action_Helper');
 * }
 * </code>
 *
 * @author Alisson Chiquitto<chiquitto@chiquitto.com.br>
 * @see http://zfextend.googlecode.com/svn-history/r79/trunk/Site/private/ZFKiller/Controller/Action/Helper/Download.php
 */
class CORE_Controller_Action_Helper_Download extends Zend_Controller_Action_Helper_Abstract {

	/**
	 *
	 * @param string $file Local do arquivo
	 * @param string $filename Nome do arquivo
	 * @see http://davidwalsh.name/php-force-download
	 */
	public function download($file, $filename)
	{
		if( !file_exists($file) )
		{
			throw new Exception('Arquivo para download não encontrado. Arquivo: ' . $file);
		}
		// get the file mime type using the file extension
		switch (strtolower(substr(strrchr($file, '.'), 1)))
		{
			case 'pdf': $mime = 'application/pdf';
				break;
			case 'zip': $mime = 'application/zip';
				break;
			case 'jpeg':
			case 'jpg': $mime = 'image/jpg';
				break;
			default: $mime = 'application/force-download';
		}

		header('Pragma: public');   // required
		header('Expires: 0');	// no cache
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($file)) . ' GMT');
		header('Cache-Control: private', false);
		header('Content-Type: ' . $mime);
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . filesize($file));  // provide file size
		header('Connection: close');
		readfile($file);	// push it out
		exit();
	}

	/**
     * direct(): Perform helper when called as
     * $this->_helper->redirector($file, $filename)
     *
     * @param string $file Local do arquivo
	 * @param string $filename Nome do arquivo
     * @return void
     */
    public function direct($file, $filename)
    {
        $this->download($file, $filename);
    }
}