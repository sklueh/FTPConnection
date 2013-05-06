<?php

/**
 * FTPConnection
 * 
 * @autor Sebastian Klüh (http://sklueh.de)
 * 
 * Example 1 - Single file upload:
 * $oFTP = new FTPConnection('sklueh.de', 'username', 'password');
 * var_dump($oFTP->uploadFile('testfile1.txt', 'testfile1.txt')); //true
 * 
 * Example 2 - Multiple file upload:
 * $oFTP = new FTPConnection('sklueh.de', 'username', 'password');
 * $aFiles = array('testfile1.txt', 'testfile2.txt', 'testfile3.txt');
 * var_dump($oFTP->uploadFiles($aFiles, '/my_dir/sub_dir')); //true
 * 
 * Example 3 - Recursive directory upload:
 * $oFTP = new FTPConnection('sklueh.de', 'username', 'password');
 * var_dump($oFTP->uploadDirectory('./example-dir1', '/')); //true
 * 
 */
class FTPConnection
{
	private $sHost;
	private $sUser;
	private $sPassword;
	private $rConnection;
	
	public function __construct($sHost, $sUser = "", $sPassword = "")
	{
		$this->sHost = $sHost;
		$this->sUser = $sUser;
		$this->sPassword = $sPassword;
		$this->connect();
	}
	
	public function __destruct()
	{
		$this->disconnect();
	}
		
	public function uploadFile($sSourcePath, $sTargetPath)
	{
		if(is_resource($this->rConnection) &&
		   file_exists($sSourcePath) &&
		   ftp_put($this->rConnection , $sTargetPath, $sSourcePath, FTP_ASCII) === true)
		{
			$this->setChmod($sTargetPath);
			return true;
		}
		return false;
	}
	
	public function uploadFiles($aSourcePaths, $sTargetPath)
	{
		$bSuccess = true;
		foreach((array) $aSourcePaths as $sSourcePath)
		{
			if($this->uploadFile($sSourcePath, $this->correctPath($sTargetPath).basename($sSourcePath)) !== true)
			$bSuccess = false;
		}
		return $bSuccess;
	}
	
	public function uploadDirectory($sSourcePath, $sTargetPath)
	{
		if(is_dir($sSourcePath))
		{
			if(dirname($sSourcePath) === ".") 
			$sSourcePath = "./".$sSourcePath;
			return $this->iterateDir($sSourcePath, $sTargetPath."/".$sSourcePath);
		}
		return false;
	}
	
	private function iterateDir($sSourcePath, $sTargetPath)
	{
		if(!$this->changeFTPDir($sTargetPath, $sSourcePath)) 
		return false;
	    foreach(new DirectoryIterator($sSourcePath) as $oItem)
	    {
	        if($oItem->isDir())
	        {
	            if(!$oItem->isDot())
				$this->iterateDir($oItem->getPathname(), ftp_pwd($this->rConnection)."/".$oItem->getFilename());
	            continue;
	        }

			if(!$this->uploadFile($oItem->getPathname(), $this->correctPath(ftp_pwd($this->rConnection)."/".$oItem->getFilename()))) 
			return false;
	    }
		return true;
	}
	
	private function setChmod($sTargetPath)
	{
		ftp_chmod($this->rConnection, 0755, $sTargetPath);
	}
	
	private function connect()
	{
		$this->rConnection = ftp_connect($this->sHost, 21);
		ftp_login($this->rConnection, $this->sUser, $this->sPassword);
	}
	
	private function disconnect()
	{
		if(is_resource($this->rConnection))
		ftp_close($this->rConnection);
	}
	
	private function changeFTPDir($sTargetPath, $sSourcePath)
	{		
		$sTargetPath = $this->cutSourceDirectory($sTargetPath, $sSourcePath);
		if(!@ftp_chdir($this->rConnection, $sTargetPath))
		{
			ftp_mkdir($this->rConnection, $sTargetPath);
			return ftp_chdir($this->rConnection, $sTargetPath);	
		}
		return true;
	}
	
	private function cutSourceDirectory($sTargetPath, $sSourcePath)
	{
		if(dirname($sSourcePath) != ".")
		$sTargetPath = str_replace(dirname($this->correctPath($sSourcePath)), "", $this->correctPath($sTargetPath));
		return $this->correctPath($sTargetPath);
	}
	
	private function correctPath($sTargetPath)
	{
		return str_replace("//./", "/", 
			   str_replace("./", "/", 
			   str_replace("//", "/", 
			   str_replace("\\", "/", "/".$sTargetPath."/"))));
	}
}