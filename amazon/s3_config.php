<?php
// Bucket Name
$bucket="kingbnb";
if (!class_exists('S3'))require_once('S3.php');
			
//AWS access info
if (!defined('awsAccessKey')) define('awsAccessKey', 'AKIAJ4BAZ4KJDHHVRY5A');
if (!defined('awsSecretKey')) define('awsSecretKey', 'TYrU6IUbCnLvrcgHfVvnrOKtkfybDFD3k5ronRUD');
			
//instantiate the class
$s3 = new S3(awsAccessKey, awsSecretKey);

$s3->putBucket($bucket, S3::ACL_PUBLIC_READ);

?>