<?php
require_once 'config.php';

function get_desc( $db, $desc_id)
{
//printf($desc_id);		 
	$desc_str='';
	$desc_hex='';	
//printf('Foto='.$desc_id);	
	$query = $db->query( 'SELECT data FROM ITEMS_ WHERE ROWID='.$desc_id );
	if( ( $rec = $db->fetch_assoc( $query ) ) )
			{
			if (strlen( $rec[ 'data' ] ) > 0)
				{
				$desc_hex=$rec[ 'data' ];
//				$desc_hex=substr($desc_hex, 2, strlen($desc_hex)-2); 
//printf($desc_hex);
//				while(strlen($desc_hex)>0)
//					{
//					$desc_str=$desc_str.chr(base_convert(substr($desc_hex, 0, 2) , 16, 10));
//					$desc_hex=substr($desc_hex, 2, strlen($desc_hex)-2); 
//					}
				}
			}
	return $desc_hex;
}	

function get_image_name( $db, $foto_id, $size )
{
	global $NO_IMAGE;
	global $TMP_IMG_DIR;
	global $IMG_SIZE_COUNT;
	global $IMG;
	global $IMG_QUALITY;
	if  (!$foto_id) 
		return $IMG[ $size ][ 'X' ];		
	if( $db && $foto_id && $size > 0 && $size <= $IMG_SIZE_COUNT )
	{
		$filename = $TMP_IMG_DIR.'/'.$IMG[ $size ][ 'N' ].$foto_id.'.jpeg';
		if( !is_dir( $TMP_IMG_DIR ) )
			mkdir( $TMP_IMG_DIR );
//		if( !file_exists( $filename ) || ( time() - filemtime( $filename ) > 86400 ) || filesize( $filename )==0 )
		if( !file_exists( $filename ) || ( time() - filemtime( $filename ) > 1 ) || filesize( $filename )==0 )
		{
			$query = $db->query( 'SELECT data FROM ITEMS_ WHERE ROWID='.$foto_id );
			$db->set_field_len( $query, 'data', 0x100000 ); // 1мб, максимальный размер картинки
			if( ( $rec = $db->fetch_assoc( $query ) ) )
			{
				if (strlen( $rec[ 'data' ] ) < 5)
					{
					return $IMG[ $size ][ 'X' ];
					}
				if( extension_loaded('gd') && strlen( $rec[ 'data' ] ) > 0 )
				 {
					$im = imagecreatefromstring( $rec[ 'data' ] );
					
					$imW = imagesx( $im );
					$imH = imagesy( $im );
					$imMax=max($imW,$imH);
					$k_img=$IMG[ $size ][ 'W' ]/max($imW,$imH);
					$im2W = $imW * $k_img;
					$im2H = $imH * $k_img;

					$im2 = imagecreatetruecolor( $im2W, $im2H );
					$white = imagecolorallocate($im2, 255, 255, 255);
					imagefill( $im2, 0, 0, $white );
					imagecolordeallocate( $im2, $white );
					if( imagecopyresized( $im2, $im, 0, 0, 0, 0, $im2W, $im2H, $imW, $imH ) )
						imagejpeg( $im2, $filename, $IMG_QUALITY );
					imagedestroy( $im2 );
					imagedestroy( $im );
					return $filename;


/*					for( $i = 1; $i <= $IMG_SIZE_COUNT; ++$i )
					{
						$img_max_w  = $IMG[ $i ][ 'W' ];
						$img_max_h = $IMG[ $i ][ 'H' ];
						$cur_filename = $TMP_IMG_DIR.'/'.$IMG[ $i ][ 'N' ].$foto_id.'.jpeg';
						
						if( $imH * $img_max_w > $imW * $img_max_h )
						{
							$im2H = $img_max_h;
							$im2W = $imW * $img_max_h / $imH;
						}
						else
						{
							$im2W = $img_max_w;
							$im2H = $imH * $img_max_w / $imW;
						}

						$im2 = imagecreatetruecolor( $im2W, $im2H );
						$white = imagecolorallocate($im2, 255, 255, 255);
						imagefill( $im2, 0, 0, $white );
						imagecolordeallocate( $im2, $white );
						if( imagecopyresized( $im2, $im, 0, 0, 0, 0, $im2W, $im2H, $imW, $imH ) )
							imagejpeg( $im2, $cur_filename, $IMG_QUALITY );
						imagedestroy( $im2 );
					 }
					imagedestroy( $im );
					return $filename;
*/
				  }

/*				for( $i = 1; $i <= $IMG_SIZE_COUNT; ++$i )
				{
					$cur_filename = $TMP_IMG_DIR.'/'.$IMG[ $i ][ 'N' ].$foto_id.'.jpeg';
					$file = fopen( $cur_filename, 'wb' );
					fwrite( $file, $rec[ 'data' ] );
					fclose( $file );
				}
				if( strlen( $rec[ 'data' ] ) > 0 )
					return $filename;
*/
			}// 			if( ( $rec = $db->fetch_assoc( $query ) ) )
		}
		else
		// артинка недавно обновлена, новую из базы не достаем
			if( filesize( $filename ) > 0 )
				return $filename;
	}
	if( $size > 0 && $size <= $IMG_SIZE_COUNT )
		return $IMG[ $size ][ 'X' ];
	return $NO_IMAGE;
}
?>