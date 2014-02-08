<?PHP
/*--------------------------------------------------------------------------------
   Archivo: image_handler.class.php
   Sistema: Sitio web mekano4
   Descripcion: Manejo de todo tipo de imagenes. 
   Ultima actualizacion: 10/10/2006
--------------------------------------------------------------------------------*/

class ImageHandler{

	var $TipoImg;		/// El tipo de imagen, como puede ser jpg, gif, png, etc.
	var $ImgResource;	/// El recurso de imagen	

	function ImageHandler(){
		$this->TipoImg = '';
		$this->ImgResource = null;
	}
	
	/// Configura la clase para manejar una imagen dado el nombre de archivo
	/// Mas rapida porque solo soporta JPEG, JPG, GIF y PNG.
	/// Ideal para cuando se sabe que tipo de imagen se va a manejar
	 
	function open_image_with_extension($file){
		// Extraer extension
		$extension = strrchr($file, '.');
		$extension = strtolower($extension);
	
		switch($extension) {
			case '.jpg':
				$this->TipoImg = 'jpg';
			case '.jpeg':
				$this->ImgResource = @imagecreatefromjpeg($file);
				if ($this->TipoImg != 'jpg')
					$this->TipoImg = 'jpeg';
				break;
			case '.gif':
				$this->ImgResource = @imagecreatefromgif($file);
				$this->TipoImg = 'gif';
				break;
			case '.png': 
				$this->ImgResource = @imagecreatefrompng($file);
				
			default:
				$this->ImgResource = false;
				break;
		}
	}
	
	/// Configura la clase para manejar una imagen dado el nombre de archivo.
	/// Examina exhaustivamente hasta encontrar el tipo de imagen del archivo.
	/// Ideal para cuando NO se sabe que tipo de imagen se va a manejar
	
	function open_image ($file){
		$this->ImgResource = false;
		
		# JPEG:
		$this->ImgResource = imagecreatefromjpeg($file);
		if ($this->ImgResource !== false){ 
			$this->TipoImg = 'jpeg';
			return 0;
		}
		
		/*
		# GIF:
		$this->ImgResource = @imagecreatefromgif($file);
		if ($this->ImgResource !== false){
			$this->TipoImg = 'gif';
			return 0;
		}	
	
		# PNG:
		$this->ImgResource = @imagecreatefrompng($file);
		if ($this->ImgResource !== false){
			$this->TipoImg = 'png';
			return 0;
		}
	
		
		# GD2 File:
		$this->ImgResource = @imagecreatefromgd2($file);
		if ($this->ImgResource !== false){
			$this->TipoImg = 'gd2';
			return 0;
		}	
	
		# WBMP:
		$this->ImgResource = @imagecreatefromwbmp($file);
		if ($this->ImgResource !== false){
			$this->TipoImg = 'wbmp';
			return 0;
		}	
	
		# XBM:
		$this->ImgResource = @imagecreatefromxbm($file);
		if ($this->ImgResource !== false){
			$this->TipoImg = 'xbm';
			return 0;
		}	
	
		# XPM:
		$this->ImgResource = @imagecreatefromxpm($file);
		if ($this->ImgResource !== false){
			$this->TipoImg = 'xpm';
			return 0;
		}	
	
		# Try and load from string:
		$contents = file_get_contents($file);
		if($contents != FALSE){
			$this->ImgResource = @imagecreatefromstring($contents);
			if ($this->ImgResource !== false){
				// Extraer extension
				$extension = strrchr($file, '.');
				$extension = strtolower($extension);
				$this->TipoImg = str_replace('.','',$extension);
				return 0;
			}
		}
		*/
		
		return 1; // Error
	}
	
	/// Retorna el puntero recurso de imagen que se esta manejando
	function getImgResource(){
		return $this->ImgResource;
	}
	
	/// Retorna la extension de la imagen que se esta manejando
	function getImgFileType(){
		return $this->$TipoImg;
	}
	
	function get_image_width(){
		return imagesx($this->ImgResource);	
	}
	
	function get_image_height(){
		return imagesy($this->ImgResource);	
	}
	
	///  Se resizea la imagen a los parametros pasados
	function resize_image($width,$height){
		$largo  = imagesx($this->ImgResource);
		$ancho = imagesy($this->ImgResource);
		
		# Create a new temporary image
		$tmp_img = imagecreatetruecolor($width, $height);
		# Copy and resize old image into new image
		imagecopyresampled($tmp_img, $this->ImgResource, 0, 0, 0, 0,
				$width, $height, $largo, $ancho);
		imagedestroy($this->ImgResource);
		$this->ImgResource = $tmp_img;
	}
	
	function cropImage($nw, $nh) {
		$w = imagesx($this->ImgResource);
		$h = imagesy($this->ImgResource);
		
		$dimg = imagecreatetruecolor($nw, $nh);
		$wm = $w/$nw;
		$hm = $h/$nh;
		$h_height = $nh/2;
		$w_height = $nw/2;

		if($w> $h) {
			$adjusted_width = $w / $hm;
			$half_width = $adjusted_width / 2;
			$int_width = $half_width - $w_height;
			imagecopyresampled($dimg, $this->ImgResource, -$int_width, 0, 0, 0, $adjusted_width, $nh, $w, $h);
		} 
		elseif(($w <$h) || ($w == $h)) {
			$adjusted_height = $h / $wm;
			$half_height = $adjusted_height / 2;
			$int_height = $half_height - $h_height;
			imagecopyresampled($dimg,$this->ImgResource,0,-$int_height,0,0,$nw,$adjusted_height,$w,$h);
		} 
		else {
			imagecopyresampled($dimg,$this->ImgResource,0,0,0,0,$nw,$nh,$w,$h);
		}
		
		imagedestroy($this->ImgResource);
		$this->ImgResource = $dimg;
	}
	
	/** Ajusta la imagen a los parametros si la imagen es mayor, 
	*	sino la deja como estaba. Esta es la que hay que usar cuando
	*	no queremos que la imagen se deteriore si es menor al tamaño
	*	maximo.
	*/	 
	function fit_image($maxWidth,$maxHeight){
		$width  = imagesx($this->ImgResource);
		$height = imagesy($this->ImgResource);

		$scale = min($maxWidth/$width, $maxHeight/$height);
		//  Si la imagen es mayor al tamaño permitido se achica
		if ($scale < 1) {
			$new_width = floor($scale*$width);
			$new_height = floor($scale*$height);
		    $this->resize_image($new_width,$new_height);
		}
	}
	
	/// Guarda en el disco la imagen con el nombre $filename
	function image_to_file($filename){
		$php_version = (float) PHP_VERSION;
		// Va a sobreescribir
		if (file_exists($filename))
			@unlink($filename);
		
		switch($this->TipoImg){
			case 'jpg':
			case 'jpeg':
				imagejpeg($this->ImgResource,$filename);
				break;
			case 'gif':
				imagegif($this->ImgResource,$filename);
				break;
			case 'png':
				imagepng($this->ImgResource, $filename);
				break;
			case 'gd':
				imagegd($this->ImgResource, $filename);
				break;
			case 'gd2':
				imagegd2($this->ImgResource, $filename);
				break;
			case 'wbmp':
				imagewbmp($this->ImgResource, $filename);
				break;
			//  No se soporta la creacion de imagenes xbm, xpm, para PHP < 5.0
			case 'xbm':
				if ($php_version >= 5.0)
					imagexbm($this->ImgResource, $filename);
				break;
		}
	}
}

?>