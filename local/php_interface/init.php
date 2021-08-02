<?php
CJSCore::Init(array("jquery"));

/**Подключение PHP mailer*/
require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/phpmailer/Exception.php';
require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/phpmailer/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
/**************************/

function deleteDir($path)
{
    return is_file($path) ?
        @unlink($path) :
        array_map(__FUNCTION__, glob($path.'/*')) == @rmdir($path);
}

function recursiveDelete($str){
    if(is_file($str)){
        return @unlink($str);
    }
    elseif(is_dir($str)){
        $scan = glob(rtrim($str,'/').'/*');
        foreach($scan as $index=>$path){
            recursiveDelete($path);
        }
        //return @rmdir($str);
        return true;
    }
}
function wrap_text($text, $line_width = 9)
{
    //msg($line_width);
    $result = preg_replace("/\<br\/\>/", "<br/><br/>", $text);
    $result = preg_replace('/(.{1,' . $line_width . '})(\s+|$)/su', "\\1<br/>", $result);
    $result = preg_replace('/\<br\/\>$/', '', $result);
    return $result;
}


function translitIt($str)
{
    $tr = array(
        "А" => "A", "Б" => "B", "В" => "V", "Г" => "G",
        "Д" => "D", "Е" => "E", "Ж" => "J", "З" => "Z", "И" => "I",
        "Й" => "Y", "К" => "K", "Л" => "L", "М" => "M", "Н" => "N",
        "О" => "O", "П" => "P", "Р" => "R", "С" => "S", "Т" => "T",
        "У" => "U", "Ф" => "F", "Х" => "H", "Ц" => "TS", "Ч" => "CH",
        "Ш" => "SH", "Щ" => "SCH", "Ъ" => "", "Ы" => "YI", "Ь" => "",
        "Э" => "E", "Ю" => "YU", "Я" => "YA", "а" => "a", "б" => "b",
        "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ж" => "j",
        "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l",
        "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
        "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
        "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "y",
        "ы" => "yi", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya"
    );
    return strtr($str, $tr);
}

function smart_wordwrap($string, $width = 75, $break = "\n")
{
    $string = translitIt($string);

    $result = wordwrap($string, $width, $break);

    return $result;
}


function Mywordwrap($str, $width, $break)
{
    $return = '';
    $br_width = mb_strlen($break, 'UTF-8');
    for ($i = 0, $count = 0; $i < mb_strlen($str, 'UTF-8'); $i++, $count++) {
        if (mb_substr($str, $i, $br_width, 'UTF-8') == $break) {
            $count = 0;
            $return .= mb_substr($str, $i, $br_width, 'UTF-8');
            $i += $br_width - 1;
        }

        if ($count > $width) {
            $return .= $break;
            $count = 0;
        }

        $return .= mb_substr($str, $i, 1, 'UTF-8');
    }

    return $return;
}


function utf8_wordwrap($str, $width, $break = "\n") // wordwrap() with utf-8 support
{
    //$str = iconv(mb_detect_encoding($str, mb_detect_order(), true), "UTF-8", $str);
    $str = preg_split('/([\x20\r\n\t]++|\xc2\xa0)/sSX', $str, -1, PREG_SPLIT_NO_EMPTY);
    $len = 0;
    $return = '';
    foreach ($str as $val) {
        $val .= ' ';
        $tmp = mb_strlen($val, 'utf-8');
        $len += $tmp;
        if ($len >= $width) {
            $return .= $break . $val;
            $len = $tmp;
        } else
            $return .= $val;
    }
    return $return;
}

function msg($o)
{
    global $USER;
    if ($USER->IsAdmin()) {
        echo '<pre>' . print_r($o, true) . '</pre>';
    }
}

;


function coolPrice($price)
{
    return number_format($price, 2, ".", " ");
}

function getCountTips()
{
    $tips = Array();
    if (CModule::IncludeModule("iblock")) {
        $arSelect = Array("ID", "NAME", "PROPERTY_COUNTS", "IBLOCK");
        $arFilter = Array("IBLOCK_ID" => 2, "ACTIVE" => "Y");
        $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
        while ($ob = $res->GetNext()) {
            $tips[$ob["NAME"]] = $ob["PROPERTY_COUNTS_VALUE"];
        }
    }
    return $tips;
}


function pismo($email, $subject, $text, $from = "mail@strprofi.ru", $ReplyTo = "", $fromName = "", $file = false, $filename = "file.xml")
{
    if (is_array($text)) $text = implode("<br/>", $text);
    //*
    $ReplyTo = $ReplyTo ? $ReplyTo : $from;
    $fromName = $fromName ? $fromName : "Компания «СтройProfi»";

    $text = "<table style='border:none;border:0;width:100%;'><tr>
	<td style='text-align: left;width:50%;'><a href='http://www.strprofi.ru' style='outline:none;border:0;'><img src='http://strprofi.ru/img/logo.png' style='outline:none;border:0;' alt='СтройProfi'></a></td>
	<td style='text-align: right;width:50%;'><b>(4852) 58-04-45</b><br/>mail@strprofi.ru</td></tr></table>
	<div style='height:1px;line-height: 1px;background: #000;margin: 10px 0 10px 0;'></div>"
        . $text .
        "<div style='height:1px;line-height: 1px;background: #000;margin: 10px 0 10px 0;'></div>" .
        "<b>Компания «СтройProfi»</b><br/>
150044, г.Ярославль, Ленинградский пр-т. 33, офис 501<br/>
<table style='color: #555;font-size:11px;border:0;margin-top:10px;'>
<tr>
<td style='vertical-align:top;padding: 0 10px 0 0;'><b>Режим работы офиса:</b></td>
<td>Понедельник-Пятница: 9.00 - 18.00</td>
</tr>
<tr>
<td style='vertical-align:top;padding: 0 10px 0 0;'><b>Режим работы магазина:</b></td>
<td>Понедельник-Пятница: 9.00 - 18.00<br/>Суббота: 10.00 - 16.00</td>
</tr><tr>
<td style='vertical-align:top;padding: 0 10px 0 0;'><b>Телефон / Факс:</b></td>
<td>+7 (4852) 58-04-45<br/>+7 (4852) 58-04-46</td>
</tr></table>";

    $mail = new PHPMailer;
    try {
        $mail->CharSet = 'UTF-8';
        $mail->IsHTML(true);
        $mail->setFrom($from, $fromName);
        $mail->addAddress($email);
        $mail->Subject =  $subject;
        $mail->msgHTML($text);

        if ($file) {
            $mail->addAttachment($file);
        }

        $mail->send();
    } catch (Exception $e) {
        echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
    }
}

function getUrl($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_exec($ch);
    curl_close($ch);
    unset($ch);
}

function sklon($n, $forms)
{
    return $n % 10 == 1 && $n % 100 != 11 ? $forms[0] : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20) ? $forms[1] : $forms[2]);
}
