<?php


function e($string){
	if (file_exists("/tmp/break.php"))
	{
			echo 'breakpoint exists at /tmp/break.php. remove file then start again';
			die;
	}
	file_put_contents("/tmp/file.log",$string."\n",FILE_APPEND);
	echo $string.'<br>';
}




e('loading items from file to memory');
$lines = file('catalog.txt');
$items = [];
$i = 0;

if($lines===FALSE)
{
	die('!! file open error !!');
}
else
{
foreach($lines as $line)
{
		$line = iconv("windows-1251","utf-8",$line);
		$a = explode(';',$line);
		//echo count($a).' ';
		//var_dump($a);
				//echo $line;

					//echo '<br>'.(count($a)).'<br>';
					#if($a[2]==$Razdel)

		#if(count($a)==19)
		if(count($a)>=20)
		{
			$items[$a[2]][$i]['ID'] = $a[1];
			$items[$a[2]][$i]['NomNomer'] = $a[6];
			$items[$a[2]][$i]['NomenklaturaGeog'] = '';
			$items[$a[2]][$i]['KodOKEI'] = '';
			$items[$a[2]][$i]['Ves'] = $a[8];
			$items[$a[2]][$i]['VUpakovke'] = $a[12];
			$items[$a[2]][$i]['VUpakovke2'] = $a[13];
			$items[$a[2]][$i]['Ostatok'] = $a[14];
			$items[$a[2]][$i]['VRezerve'] = $a[15];
			$items[$a[2]][$i]['KodUpakovki'] = '';
			$items[$a[2]][$i]['KratnostOtpuska'] = '';
			$items[$a[2]][$i]['PorNomer'] = $i;
			$items[$a[2]][$i]['Artikul'] = $a[5];
			$items[$a[2]][$i]['Svertka'] = $a[4];
			$items[$a[2]][$i]['Naimenovanie'] = $a[3];
			$items[$a[2]][$i]['Foto'] = $a[18]; #$host.'/import/img/'.$a[18].'.jpg';
			$items[$a[2]][$i]['CZena1'] = $a[9];
			$items[$a[2]][$i]['CZena2'] = $a[10];
			$items[$a[2]][$i]['CZena3'] = $a[11];
			$items[$a[2]][$i]['EdIzmereniya'] = $a[7];
			$items[$a[2]][$i]['desc'] = str_replace('<br>',"\n",str_replace('<br><br>',"\n",$a[19]));

			$i++;
		}
					else
					{
					{
						#foreach($a as $k=>$v) e($k.' '.$v);
						e("line $i is less than 20 columns (really ".count($a)."):<br> $line ");
					}
}
}



}
e($i.' items loaded from catalog.txt');
}

?>