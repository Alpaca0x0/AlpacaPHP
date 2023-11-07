<?php
class Image{
	private $image;

	function __construct($binary){ 
		$this->image = $binary;
	}

	function get($what){
		$what = strtolower(trim($what));
		switch ($what) {
			case 'info':
				return getimagesizefromstring($this->image);

			break; case 'bin': case 'binary':
				return $this->image;
	
			break;case 'byte': case 'bytes':
				return strlen($this->image);

			break;case 'width':
				return $this->get('info')[0];

			break;case 'height':
				return $this->get('info')[1];
			
			break;case 'mime':
				return $this->get('info')['mime'];
			
			break;default:
				return 'error';
			break;
		}
	}

	function is($what){
		$what = strtolower(trim($what));
		switch ($what) {
			case 'image': case 'picture':
				try {
					$ret = $this->get('info');
				} catch (\Throwable $th) {
					return false;
				}
				return $ret ? true : false;
			break;case '':
				//
			break;default:
				return false;
			break;
		}
	}

	static function httpToImage($tmp_name){
		if(is_array($tmp_name)){ return false; }
		if(empty($tmp_name) || !file_exists($tmp_name) || !is_uploaded_file($tmp_name)){
			return false;
		}else{
			return new Image(file_get_contents($tmp_name));
		}
	}

}


// if($imageByte > $maxSize){ die('File is too big, must be smaller then '.$maxSize); }
//         if(!$imageSize){ die('Not a image or file is too big, must be smaller then '.$iniMaxSize); }
//         if(!in_array($imageMime, ['image/jpeg', 'image/png',])){ die('<br>this format is not allow'); }
//         if($imageSize[0] !== $imageSize[1] || $imageSize[0]!=320){ die('Must be square'); }

//         echo '<img src="data:image/png;base64, '.$imageBase64.'">';