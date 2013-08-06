FTPConnection
=============

A simple class for uploading files or directories via ftp.

**Example 1 - Single file upload:**
```php
$oFTP = new FTPConnection('sklueh.de', 'username', 'password');
var_dump($oFTP->uploadFile('testfile1.txt', 'testfile1.txt')); //true
´´´

**Example 2 - Multiple file upload:**
```php
$oFTP = new FTPConnection('sklueh.de', 'username', 'password');
$aFiles = array('testfile1.txt', 'testfile2.txt', 'testfile3.txt');
var_dump($oFTP->uploadFiles($aFiles, '/my_dir/sub_dir')); //true
´´´

**Example 3 - Recursive directory upload:**
```php
$oFTP = new FTPConnection('sklueh.de', 'username', 'password');
var_dump($oFTP->uploadDirectory('./example-dir1', '/')); //true
```
