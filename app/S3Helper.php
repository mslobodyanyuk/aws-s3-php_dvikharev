<?php

namespace App;

use Aws\S3\S3Client;

class S3Helper{
	
	const ACL_PUBLIC_READ = 'public-read';
	
	const BUCKET_FILE_FOLDER = 'store';
	
	public static function generateDestinationFilename(string $fileSrc)
	{
		return sprintf( "%s/%s/%s",
						static::BUCKET_FILE_FOLDER,
						substr(md5($fileSrc), 0,2),
						basename($fileSrc)		
		);	
	}
	
	public function uploadFile($fileSrc)
	{
		$client = new S3Client([
			'version' => 'latest',
			'region' => getenv('S3_REGION'),
			'credentials' => [
				'key' => getenv('AWS_APPKEY'),
				'secret' => getenv('AWS_SECRET'),
			],			
		]);
		
		$source = fopen($fileSrc, 'rb');
		$result = $client->upload(
			getenv('S3_BUCKET'),
			static::generateDestinationFilename($fileSrc),
			$source,
			static::ACL_PUBLIC_READ
		);
		
		if(substr(@$result["@metadata"]["statusCode"], 0, 2) !== '20'){
			throw new \Exception('Error: File upload failed.');
		}

		if(empty($result['ObjectURL'])){
			throw new \Exception('Error: File uploaded, but no ObjectURL found.');
		}		
				
		return (string) $result['ObjectURL'];		
	}	
}
