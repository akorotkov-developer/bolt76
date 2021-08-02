<?php


error_reporting(E_ALL);

ini_set("display_errors", 1);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Title");

$Razdel = (int)$_GET['cat'];
if (!$Razdel) die("Не задан раздел");

$lines = file('../catalog.txt');
$items = [];
$i = 0;

if($lines===FALSE) 
{
	die('!! file open error !!');
}
else
{
	//foreach ($lines as $line_num => $line) 
	//{
		$a = explode($line,';');
		if($a[0]=='л')
		if($a[2]==$Razdel)
		{
			$items[$i]['ROWID'] = $a[1];
        	$items[$i]['NomNomer'] = $a[6];
        	$items[$i]['NomenklaturaGeog'] = '';
        	$items[$i]['KodOKEI'] = '';
        	$items[$i]['Ves'] = $a[8];
        	$items[$i]['VUpakovke1'] = $a[12];
        	$items[$i]['VUpakovke2'] = '';
			$items[$i]['Ostatok'] = $a[13];
        	$items[$i]['VRezerve'] = '';
			$items[$i]['KodUpakovki'] = '';
        	$items[$i]['KratnostOtpuska'] = '';
        	$items[$i]['PorNomer'] = i;
        	$items[$i]['Artikul'] = $a[5];
	    	$items[$i]['Svertka'] = $a[4];
	    	$items[$i]['Naimenovanie'] = $a[3];
			$items[$i]['Foto'] = './img'+$a[1]+'.jpg';
        	$items[$i]['CZena1'] = $a[9];
        	$items[$i]['CZena2'] = $a[10];
        	$items[$i]['CZena3'] = $a[11];
        	$items[$i]['EdIzmereniya'] = $a[7];
        	$items[$i]['Opisanie'] = '';
			$i++;

		}
		//echo "Строка #<b>{$line_num}</b> : " . htmlspecialchars($line) . "<br />\n";
	//}
    foreach($lines as $line)
    {
            $line = iconv("windows-1251","utf-8",$line);
            $a = explode(';',$line);
            //echo count($a).' ';
            //var_dump($a);
            if(count($a)==17)
            if($a[2]==$Razdel)
            {

                $items[$i]['ROWID'] = $a[1];
                $items[$i]['NomNomer'] = $a[6];
                $items[$i]['NomenklaturaGeog'] = '';
                $items[$i]['KodOKEI'] = '';
                $items[$i]['Ves'] = $a[8];
                $items[$i]['VUpakovke1'] = $a[12];
                $items[$i]['VUpakovke2'] = '';
                $items[$i]['Ostatok'] = $a[13];
                $items[$i]['VRezerve'] = '';
                $items[$i]['KodUpakovki'] = '';
                $items[$i]['KratnostOtpuska'] = '';
                $items[$i]['PorNomer'] = i;
                $items[$i]['Artikul'] = $a[5];
                $items[$i]['Svertka'] = $a[4];
                $items[$i]['Naimenovanie'] = $a[3];
                $items[$i]['Foto'] = './img/'.$a[1].'.jpg';
                $items[$i]['CZena1'] = $a[9];
                $items[$i]['CZena2'] = $a[10];
                $items[$i]['CZena3'] = $a[11];
                $items[$i]['EdIzmereniya'] = $a[7];
                $items[$i]['Opisanie'] = '';

                $i++;
            }
    }

}

?>

<items>
<?php
    $n = 1;
	foreach($items as $item) {
        //var_dump($item);
        ?><item><?
        ?><ID><?=$item['ROWID'];?></ID><?
        ?><NomNomer><?=$item['NomNomer'];?></NomNomer><?
        ?><NomenklaturaGeog><?=$item['NomenklaturaGeog'];?></NomenklaturaGeog><?
        ?><KodOKEI><?=$item['KodOKEI'];?></KodOKEI><?
        ?><Ves><?=$item['Ves'];?></Ves><?
        ?><VUpakovke><?=$package[$item['ROWID']][0]['VUpakovke'];?></VUpakovke><?
        ?><VUpakovke2><?=$package[$item['ROWID']][1]['VUpakovke'];?></VUpakovke2><?
        ?><Ostatok><?=$ostatok[$item['ROWID']];?></Ostatok><?
        ?><VRezerve>0</VRezerve><?
        ?><KodUpakovki><?=$item['KodUpakovki'];?></KodUpakovki><?
        ?><KratnostOtpuska><?=$item['KratnostOtpuska'];?></KratnostOtpuska><?
        ?><PorNomer><?=(10 * $n++);?></PorNomer><?
        ?><Artikul><?=$item['Artikul'];?></Artikul><?
	    ?><Svertka><?=$item['Svertka'];?></Svertka><?
	    ?><Naimenovanie><?=$item['Naimenovanie'];?></Naimenovanie><?
        ?><Foto><?=$item['Foto'];?></Foto><?
        ?><CZena1><?=$item['CZena1'];?></CZena1><?
        ?><CZena2><?=$item['CZena2'];?></CZena2><?
        ?><CZena3><?=$item['CZena3'];?></CZena3><?
        ?><EdIzmereniya><?=$item['EdIzmereniya'];?></EdIzmereniya><?
        if ($item['Opisanie']) {
            ?><Opisanie><?=$item['Opisanie'];?></Opisanie><?
            if ($descs[$item['Opisanie']]) {
                $desc = $descs[$item['Opisanie']];
                //$desc = utf8_encode($desc);
                //*
                $desc = str_replace("
","\n",$desc);
                $desc = str_replace("
 ","",$desc);
                $desc = str_replace(" ","",$desc);
                $desc = trim($desc);
                $desc = iconv("windows-1251","utf-8",$desc);
                //*/
                //$desc = utf8_decode($desc);
                //$desc = preg_replace('/[^(\w\s\d)]*/','', $desc);
	            $desc = htmlspecialchars($desc);
                if ($desc) print '<desc>'.htmlspecialchars($desc).'</desc> ';
            }

        }
        ?></item><?
        $n++;
    }
    ?></items>