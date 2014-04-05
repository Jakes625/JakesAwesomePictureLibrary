<?php
	require_once('core/types.php');
	
	define('HASH_FUNC', 'sha256');
	define('HASH_FILE_KEY', '49m7?FM>Ct-^5uqx');

	final class IMAGETYPE extends Enum{
		const GIF		=	1;
		const JPEG		=	2;
		const PNG		=	3;
		const SWF		=	4;
		const PSD		=	5;
		const BMP		=	6;
		const TIFF_II	=	7;
		const TIFF_MM	=	8;
		const JPC		=	9;
		const JP2		=	10;
		const JPX		=	11;
		const JB2		=	12;
		const SWC		=	13;
		const IFF		=	14;
		const WBMP		=	15;
		const XBM		=	16;
		const ICO		=	17;
	}
	
	/* HOW TO USE IMAGE MANAGER
		$imageManager = new ImageManager( "file" ); //where "file" = name of the form file element
		if($imageManager->upload( "upload/" ) === false) //upload( $directory, $filename )
			echo "Upload Failed.";
		else
			echo $imageManager->file_path; //end result filepath
	*/

	class ImageManager
	{
		public $form_name;
		public $file_path;
		
		private $hash;
		private $img_byte_limit;
		private $image_type;
		private $image_extension;
		private $valid_img_exts = array("gif", "jpeg", "jpg", "png"); //could allow more...
		
		public function __construct( $name, $limit = 400000 ){
			$this->form_name = $name;
			$this->img_byte_limit = $limit;
		}
		
		public function upload( $directory, $filename = null){
		
			$return_code = $this->prepare( $_FILES[$this->form_name] );
			
			if($return_code < 0) //bad error code - STOP!
				return false;
			
			if(!is_dir( $directory )) //no where to upload file
				return false;
				
			$filename_final = ($filename == null) ? $this->hash : $filename;	
			$this->file_path = "./" . $directory . $filename_final . "." . $this->image_extension;
			
			move_uploaded_file($_FILES[$this->form_name]["tmp_name"], $this->file_path);
			
			return true;
		}
		
		/* PREPARE RETURN CODES
			 0	= valid
			-1	= bad image type
			-2	= integrity mismatch
			-3	= file exceeded limit
			-4	= file corruption
		*/
		
		private function prepare($file){
			//file corruption: undefined, multiple, or invalid entries
			if( !isset($_FILES[$this->form_name]["error"]) || is_array($_FILES[$this->form_name]["error"]) )
				return -4;
			
			if( $_FILES[$this->form_name]["error"] == UPLOAD_ERR_NO_FILE )
				return -4;
		
			//check the extension and make sure it's in the whitelist.
			$this->image_extension = pathinfo($file["name"], PATHINFO_EXTENSION);
			if( !in_array( $this->image_extension, $this->valid_img_exts ) )
				return -1;
			
			//assign a valid image type, make sure it's valid
			$this->image_type = exif_imagetype( $file["tmp_name"] );
			if($this->image_type === false)
				return -1;
			
			//to do:
			//	compare $_FILES[$this->form_name]['type'] with $this->image_type to assert validity
			
			//check for validitity | make sure extension matches imagetype
			switch( $this->image_extension ){
				case "gif":
					if( $this->image_type != IMAGETYPE::GIF )
						return -2;
					break;
				case "jpeg":
				case "jpg":
					if( $this->image_type != IMAGETYPE::JPEG )
						return -2;
					break;
				case "png":
					if( $this->image_type != IMAGETYPE::PNG )
						return -2;
				break;
				default: //not a valid extension
					return -2;
			}
			
			//assert file size is not over it's requested limit.
			if( $file["size"] > $this->img_byte_limit )
				return -3;
			
			$this->hash = hash_hmac_file(HASH_FUNC, $file["tmp_name"], HASH_FILE_KEY);
			
			return 0;
		}
	}
	
	/* this class adds
		text and other
		elements to 
		an image
	*/
	class ImageEditor
	{
	
	}

?>
