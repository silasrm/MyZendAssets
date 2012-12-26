<?php

/**
 * This class is a imlementation of FTP protocol
 * for simple use in PHP scripts
 *
 * @author Silas Ribas <silasrm@gmail.com>
 * @author Daniel Martuszewski <daniel10a@o2.pl>

 *
 */
class CORE_Ftp
{
	/**
	 * FTP login
	 * @var string
	 */
	private $username;

	/**
	 * FTP password
	 * @var string
	 */
	private $password;

	/**
	 * FTP host without ftp:// prefix
	 * @var type
	 */
	private $host;

	/**
	 * FTP port
	 * @var int
	 */
	private $port = 21;

	/**
	 * Timeout for all subsequent network operations
	 * @var int
	 */
	private $timeout = 90;

	/**
	 * Determinate if use secure connection
	 * @var bool
	 */
	private $secure = false;

	/**
	 * URI setted by user
	 * @var string
	 */
	private $uri;

	/**
	 * Path to directory within go after login
	 * @var string
	 */
	private $path;

	/**
	 * Determinate if any error has uccured
	 * @var bool
	 */
	private $isError = false;

	/**
	 * Determinate if login went without errors
	 * @var bool
	 */
	private $loginOk;

	/**
	 * All logs
	 * @var array
	 */
	private $messages;

	/**
	 * Connection mode
	 * @var bool
	 */
	private $passiveMode = false;

	/**
	 * Connection to FTP server handler
	 * @var resource
	 */
	private $connectionHandler;

	/**
	 * Files to upload in format $files['remoteFile'] => 'localFile'
	 * @var array
	 */
	private $files = array();

	/**
	 * Extensions of ASCII files
	 * @var array
	 */
	private $asciiExtensions = array('txt', 'csv');

	/**
	 * Language
	 * @var string
	 */
	public $lang = 'eng';

	/**
	 * Array with log messages
	 * @var array
	 */
	public $localization = array(
		'eng' => array(
			'failedConnection' => 'FTP connection has failed!',
			'failedLogin' => 'Failed login to %s for user %s!',
			'successLogin' => 'Connected to %s for user %s',
			'fileNotExists' => 'File %s does not exist.',
			'successUpload' => 'File %s uploaded as %s',
			'failedUpload' => 'Failed uploading file "%s"!',
			'successMkdir' => 'Directory %s created',
			'failedMkdir' => 'Failed creating directory "%s"!',
			'currentDir' => 'Current directory %s',
			'failedChangingDir' => 'Failed changing directory to %s',
			'removeDir' => 'Directory %s removed',
			'failedRemovingDir' => 'Failed removing directory %s',
			'removeFile' => 'File %s removed',
			'failedRemovingFile' => 'Failed removing file %s',
			'exec' => 'Exec: %s',
			'failedExec' => 'Failed to exec: %s',
			'chmod' => 'Change mode of file %s to %d',
			'failedChmod' => 'Failed changing mode of file %s to %d',
			'passive' => 'Switch to passive mode',
			'active' => 'Switch to active mode',
			'failedMode' => 'Failed changing mode',
			'successRename' => 'Rename %s to %s',
			'failedRenaming' => 'Failed renaming file %s to %s',
			'successDownload' => 'Downloaded %s to %s',
			'failedDownloading' => 'Failed downloading %s',
			'connectionClose' => 'Connection closed',
			'failedClosing' => 'Failed closing connection'
		)
	);

	public function __construct( $host = null, $port = null, $useSSL = false, $timeout = null, $pasv = null )
	{
		if( !is_null($host) )
		{
			$this->setHost($host);
		}

		if( !is_null($port) )
		{
			$this->setPort($port);
		}

		if( $useSSL )
		{
			$this->setSecure($useSSL);
		}

		if( !is_null($timeout) )
		{
			$this->setTimeout($timeout);
		}

		if( !is_null($pasv) )
		{
			$this->pasv($pasv);
		}
	}

	/**
	 * FTP username
	 * @param string $username
	 * @return CORE_Ftp
	 */
	public function setUsername($username)
	{
		$this->username = $username;

		return $this;
	}

	/**
	 * FTP password
	 * @param string $password
	 * @return CORE_Ftp
	 */
	public function setPassword($password)
	{
		$this->password = $password;

		return $this;
	}

	/**
	 * FTP IP address
	 * @param string $host
	 * @return CORE_Ftp
	 */
	public function setHost($host)
	{
		$this->host = $host;

		return $this;
	}

	/**
	 * FTP server port
	 * @param int $port
	 * @return CORE_Ftp
	 */
	public function setPort($port)
	{
		$this->port = $port;

		return $this;
	}

	/**
	 * Connection timeout
	 * @param int $timeout
	 * @return CORE_Ftp
	 */
	public function setTimeout($timeout)
	{
		$this->timeout = $timeout;

		return $this;
	}

	/**
	 * Extract all data from URI and set in the class properties
	 * @param string $uri
	 * @return CORE_Ftp
	 */
	public function setUri($uri)
	{
		$this->uri = $uri;

		// Split FTP URI into:
		// $match[0] = ftp://username:password@sld.domain.tld/path1/path2/
		// $match[1] = username
		// $match[2] = password
		// $match[3] = sld.domain.tld
		// $match[4] = /path1/path2/
		preg_match('/ftp:\/\/(.*?):(.*?)@(.*?)(\/.*)/i', $uri, $match);
		$this->username = $match[1];
		$this->password = $match[2];
		$this->host = $match[3];
		$this->path = $match[4];

		return $this;
	}

	/**
	 * Return all log messages
	 * @return array
	 */
	public function getMessages()
	{
		return $this->messages;
	}

	/**
	 * Return last log message
	 * @return string
	 */
	public function getMessage()
	{
		return $this->messages[count($this->messages) - 1];
	}

	/**
	 * Return translation from $localization array
	 * @param string $logName
	 * @return string
	 */
	protected function getLog($logName)
	{
		if( isset($this->localization[$this->lang][$logName]) )
		{
			return $this->localization[$this->lang][$logName];
		}
		elseif( isset($this->localization['eng'][$logName]) )
		{
			return $this->localization['eng'][$logName];
		}

		return '';
	}

	/**
	 * Connect and login to the FTP server.
	 * @return mixed CORE_Ftp or CORE_Ftp_Exception
	 */
	public function connect()
	{
		if( $this->secure )
		{
			$this->connectionHandler = ftp_ssl_connect($this->host, $this->port, $this->timeout);
		}
		else
		{
			$this->connectionHandler = ftp_connect($this->host, $this->port, $this->timeout);
		}

		if( (!$this->connectionHandler ) )
		{
			$this->logMessage($this->getLog('failedConnection'));

			throw new CORE_Ftp_Exception($this->getLog('failedConnection'), 1);
		}

		$loginRes = ftp_login($this->connectionHandler, $this->username, $this->password);

		if( (!$loginRes ) )
		{
			$this->logMessage(sprintf($this->getLog('failedLogin'), $this->host, $this->username), true);

			throw new CORE_Ftp_Exception(sprintf($this->getLog('failedLogin'), $this->host, $this->username), 2);
		}
		else
		{
			$this->logMessage(sprintf($this->getLog('successLogin'), $this->host, $this->username));
			$this->loginOk = true;
		}

		$this->chdir($this->path);

		return $this;
	}

	/**
	 * Log all messages into property
	 * @param string $message
	 * @param bool $isError optional
	 * @return CORE_Ftp
	 */
	protected function logMessage($message, $isError = null)
	{
		$this->messages[] = sprintf('%s: %s', date('Y-m-d H:i:s'), $message);
		if( $isError )
		{
			$this->isError = true;
		}

		return $this;
	}

	/**
	 * Show if error was occured
	 * @return bool
	 */
	public function isError()
	{
		return $this->isError;
	}

	/**
	 * Turn on using secure (SSL) connection.
	 * Use it before connect();
	 * @param bool $secure
	 * @return CORE_Ftp
	 */
	public function setSecure($secure)
	{
		$this->secure = $secure;

		return $this;
	}

	/**
	 * Long list of files on the sarver
	 * @param string $directory optional
	 * @param bool $recursive optional
	 * @return mixed Array of filenames or false
	 */
	public function rawlist($directory = null, $recursive = true)
	{
		if( !$directory ) {
			$directory = $this->path;
		}

		return ftp_rawlist($this->connectionHandler, $directory, $recursive);
	}

	/**
	 * Alias tp rawlist()
	 */
	public function lsl($directory = null, $recursive = true)
	{
		return $this->rawlist($directory, $recursive);
	}

	/**
	 * List of files on the FTP server
	 * @param type $directory
	 * @return mixed Array of filenames or false
	 */
	public function nlist($directory = null)
	{
		if( !$directory )
		{
			$directory = $this->path;
		}

		return ftp_nlist($this->connectionHandler, $directory);
	}

	/**
	 * Alias to nlist()
	 */
	public function ls($directory = null)
	{
		return $this->nlist($directory);
	}

	/**
	 * Add file to stack of files to upload
	 * @param string $localFile
	 * @param string $remoteFile
	 * @return mixed CORE_Ftp or CORE_Ftp_Exception
	 */
	public function addFile($localFile, $remoteFile = null)
	{
		if( !file_exists($localFile) )
		{
			$this->logMessage(sprintf($this->getLog('fileNotExists'), $localFile), true);

			throw new CORE_Ftp_Exception(sprintf($this->getLog('fileNotExists'), $localFile), 3);
		}

		if( !$remoteFile )
		{
			$remoteFile = basename($localFile);
		}

		$this->files[$remoteFile] = $localFile;

		return $this;
	}

	/**
	 * Set ASCII files extensions
	 * (use to determinate filetype during upload)
	 * @param array $extensions
	 * @return CORE_Ftp
	 */
	public function setAsciiExtensions($extensions)
	{
		$this->asciiExtensions = $extensions;

		return $this;
	}

	/**
	 * Add extension to ASCII file extensions
	 * (use to determinate filetype during upload)
	 * @param string $extension
	 * @return CORE_Ftp
	 */
	public function addAsciiExtension($extension)
	{
		$this->asciiExtensions[] = $extension;

		return $this;
	}

	/**
	 * Determinate if file is ASCII file. Based on
	 * filename extension and $asciiExtensions property.
	 * @param string $filename
	 * @return bool
	 */
	protected function isAsciiFile($filename)
	{
		$arr = explode('.', $filename);
		if( in_array(array_pop($arr), $this->asciiExtensions) )
		{
			return true;
		}

		return false;
	}

	/**
	 * Upload files from $files property
	 * @param string $localFile
	 * @param string $remoteFile
	 * @return mixed CORE_Ftp or CORE_Ftp_Exception
	 */
	public function put($localFile = null, $remoteFile = null)
	{
		if( $localFile )
		{
			$this->addFile($localFile, $remoteFile);
		}

		foreach( $this->files as $remote => $local )
		{
			if( $this->isAsciiFile($local) )
			{
				$mode = FTP_ASCII;
			}
			else
			{
				$mode = FTP_BINARY;
			}

			if( ftp_put($this->connectionHandler, $remote, $local, $mode) )
			{
				$this->logMessage(sprintf($this->getLog('successUpload'), $local, $remote));
			}
			else
			{
				$this->logMessage(sprintf($this->getLog('failedUpload'), $local), true);
				throw new CORE_Ftp_Exception(sprintf($this->getLog('failedUpload'), $local), 4);
			}
		}

		return $this;
	}

	/**
	 * Alias to put
	 */
	public function upload($localFile = null, $remoteFile = null)
	{
		$this->put($localFile, $remoteFile);

		return $this;
	}

	/**
	 * Make dir $directory on FTP
	 * @param string $directory
	 * @return mixed CORE_Ftp or CORE_Ftp_Exception
	 */
	public function mkdir($directory)
	{
		if( ftp_mkdir($this->connectionHandler, $directory) )
		{
			$this->logMessage(sprintf($this->getLog('successMkdir'), $directory));

			return $this;
		}

		$this->logMessage(sprintf($this->getLog('failedMkdir'), $directory));

		throw new CORE_Ftp_Exception(sprintf($this->getLog('failedMkdir'), $directory), 5);
	}

	/**
	 * Change current directory on FTP server
	 * @param string $path
	 * @return mixed CORE_Ftp or CORE_Ftp_Exception
	 */
	public function chdir($path)
	{
		if( ftp_chdir($this->connectionHandler, $path) )
		{
			$this->path = $this->pwd();
			$this->logMessage(sprintf($this->getLog('currentDir'), $this->path));

			return $this;
		}

		$this->logMessage(sprintf($this->getLog('failedChangingDir'), $path), true);

		throw new CORE_Ftp_Exception(sprintf($this->getLog('failedChangingDir'), $path), 6);
	}

	/**
	 * Alias to chdir
	 */
	public function cd($path)
	{
		return $this->chdir($path);
	}

	/**
	 * Return current path
	 * @return string
	 */
	public function pwd()
	{
		return ftp_pwd($this->connectionHandler);
	}

	/**
	 * Remove directory from FTP
	 * @param string $directory This must be either an absolute or relative path to an empty directory.
	 * @return mixed CORE_Ftp or CORE_Ftp_Exception
	 */
	public function rmdir($directory)
	{
		if( ftp_rmdir($this->connectionHandler, $directory) )
		{
			$this->logMessage(sprintf($this->getLog('removeDir'), $directory));

			return $this;
		}

		$this->logMessage(sprintf($this->getLog('failedRemovingDir'), $directory), true);

		throw new CORE_Ftp_Exception(sprintf($this->getLog('failedRemovingDir'), $directory), 7);
	}

	/**
	 * Remove file from FTP
	 * @param string $file
	 * @return mixed CORE_Ftp or CORE_Ftp_Exception
	 */
	public function delete($file)
	{
		if( ftp_delete($this->connectionHandler, $file) )
		{
			$this->logMessage(sprintf($this->getLog('removeFile'), $file));

			return $this;
		}

		$this->logMessage(sprintf($this->getLog('failedRemovingFile'), $file), true);

		throw new CORE_Ftp_Exception(sprintf($this->getLog('failedRemovingFile'), $file), 8);
	}

	/**
	 * Execute command on the FTP server
	 * @param string $command
	 * @return mixed Result of command or CORE_Ftp_Exception
	 */
	public function exec($command)
	{
		$result = ftp_exec($this->connectionHandler, $command);
		if( $result )
		{
			$this->logMessage(sprintf($this->getLog('exec'), $command));

			return $result;
		}

		$this->logMessage(sprintf($this->getLog('failedExec'), $command), true);

		throw new CORE_Ftp_Exception(sprintf($this->getLog('failedExec'), $command), 9);
	}

	/**
	 * Set permissions on the specified remote file.
	 * @param int $mode New permissions. Must be octal value.
	 * @param string $filename
	 * @return mixed CORE_Ftp or CORE_Ftp_Exception
	 */
	public function chmod($mode, $filename)
	{
		if( ftp_chmod($this->connectionHandler, $mode, $filename) )
		{
			$this->logMessage(sprintf($this->getLog('chmod'), $filename, decoct($mode)));

			return $this;
		}

		$this->logMessage(sprintf($this->getLog('failedChmod'), $filename, decoct($mode)));

		throw new CORE_Ftp_Exception(sprintf($this->getLog('failedChmod'), $filename, decoct($mode)), 10);
	}

	/**
	 * Last modify time to file. Does not work with directories!
	 * @param string $filename
	 * @return mixed Last access time to $filename or false
	 */
	public function mdtm($filename)
	{
		$result = ftp_mdtm($this->connectionHandler, $filename);
		if( $result === -1 )
		{
			return false;
		}

		return $result;
	}

	/**
	 * Switch on/off passive mode
	 * @param bool $mode optional Default true
	 * @return mixed CORE_Ftp or CORE_Ftp_Exception
	 */
	public function pasv($mode = true)
	{
		if( ftp_pasv($this->connectionHandler, $mode) )
		{
			$log = $mode ? 'passive' : 'active';
			$this->logMessage($this->getLog($log));
			$this->passiveMode = true;

			return $this;
		}

		$this->logMessage($this->getLog('failedMode'), true);

		throw new CORE_Ftp_Exception($this->getLog('failedMode'), 11);
	}

	/**
	 * Rename file or dictionary on the FTP server
	 * @param string $oldname
	 * @param string $newname
	 * @return mixed CORE_Ftp or CORE_Ftp_Exception
	 */
	public function rename($oldname, $newname)
	{
		if( ftp_rename($this->connectionHandler, $oldname, $newname) )
		{
			$this->logMessage(sprintf($this->getLog('successRename'), $oldname, $newname));

			return $this;
		}

		$this->logMessage(sprintf($this->getLog('failedRenaming'), $oldname, $newname), true);

		throw new CORE_Ftp_Exception(sprintf($this->getLog('failedRenaming'), $oldname, $newname), 12);
	}

	/**
	 * Return filesize in chosen unit
	 * @param string $filename
	 * @param string $unit optional Default is B (bytes). Can be 'b', 'Kb', 'B', 'KB', 'Mb', 'MB', 'Gb', 'GB'
	 * @return mixed Size of file or false
	 */
	public function size($filename, $unit = 'B')
	{
		$bytes = ftp_size($this->connectionHandler, $filename);
		if( $bytes === -1 )
		{
			return false;
		}

		switch ($unit)
		{
			case 'b':
				$size = $bytes * 8;
				break;
			case 'Kb':
				$size = $bytes / 8 / 1024;
				break;
			case 'KB':
				$size = $bytes / 1024;
				break;
			case 'Mb':
				$size = $bytes * 8 / 1024 / 1024;
				break;
			case 'MB':
				$size = $bytes / 1024 / 1024;
				break;
			case 'Gb':
				$size = $bytes * 8 / 1024 / 1024 / 1024;
				break;
			case 'GB':
				$size = $bytes / 1024 / 1024 / 1024;
				break;
			default:
				$size = $bytes;
				break;
		}

		return $size;
	}

	/**
	 * Returns the system type identifier of the remote FTP server.
	 * @return mixed Remote system type or false
	 */
	public function systype()
	{
		return ftp_systype($this->connectionHandler);
	}

	/**
	 * Downloads file from the FTP server
	 * @param string $remoteFile
	 * @param string $localFile
	 * @return mixed CORE_Ftp or CORE_Ftp_Exception
	 */
	public function get($remoteFile, $localFile = null)
	{
		if( !$localFile )
		{
			$localFile = basename($remoteFile);
		}

		if( $this->isAsciiFile($localFile) )
		{
			$mode = FTP_ASCII;
		}
		else
		{
			$mode = FTP_BINARY;
		}

		if( ftp_get($this->connectionHandler, $localFile, $remoteFile, $mode) )
		{
			$this->logMessage(sprintf($this->getLog('successDownload'), $remoteFile, $localFile));

			return $this;
		}

		$this->logMessage(sprintf($this->getLog('failedDownloading'), $remoteFile));

		throw new CORE_Ftp_Exception(sprintf($this->getLog('failedDownloading'), $remoteFile), 13);
	}

	/**
	 * Alias to get()
	 */
	public function download($remoteFile, $localFile = null)
	{
		return $this->get($remoteFile, $localFile);
	}

	/**
	 * Close connection with FTP.
	 * @return mixed CORE_Ftp or CORE_Ftp_Exception
	 */
	public function close()
	{
		if( ftp_close($this->connectionHandler) )
		{
			$this->logMessage($this->getLog('connectionClosed'));
			return $this;
		}

		$this->logMessage($this->getLog('failedClosing'), true);
		throw new CORE_Ftp_Exception($this->getLog('failedClosing'), 14);
	}

	/**
	 * Checks if file or directory exists.
	 * @param  string
	 * @return bool
	 */
	public function fileExists($file)
	{
		return is_array($this->ls($file));
	}

	/**
	 * Checks if directory exists.
	 * @param  string
	 * @return bool
	 */
	public function isDir($dir)
	{
		$current = $this->pwd();

		try
		{
			$res = false;
			if( $this->chdir($dir) )
			{
				$res = true;
			}
		}
		catch(CORE_Ftp_Exception $e)
		{
			$res = false;
		}

		$this->chdir($current);
		return $res;
	}

	/**
	 * Recursive creates directories.
	 * @param  string
	 * @return CORE_Ftp
	 */
	public function mkDirRecursive($dir)
	{
		$parts = explode('/', $dir);
		$path = '';

		while (!empty($parts))
		{
			$path .= array_shift($parts);
			try
			{
				if ($path !== '') $this->mkdir($path);
			}
			catch (CORE_Ftp_Exception $e)
			{
				if (!$this->isDir($path))
				{
					throw new CORE_Ftp_Exception(sprintf($this->getLog('failedMkdir'), $path));
				}
			}

			$path .= '/';
		}

		return $this;
	}

	/**
	 * Recursive deletes path.
	 * @param  string
	 * @return CORE_Ftp
	 */
	public function deleteRecursive($path)
	{
		if (!$this->delete($path))
		{
			foreach ((array) $this->nlist($path) as $file)
			{
				if ($file !== '.' && $file !== '..')
				{
					$this->deleteRecursive(strpos($file, '/') === FALSE ? "$path/$file" : $file);
				}
			}

			$this->rmdir($path);
		}

		$this;
	}
}