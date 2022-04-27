<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
move_uploaded_file($_FILES['file']['tmp_name'], $_SERVER["DOCUMENT_ROOT"] . '/upload/tmp/' . $_FILES['file']['name']);
if (file_exists($_SERVER["DOCUMENT_ROOT"] . '/upload/tmp/' . $_FILES['file']['name'])) {
    $arFile = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"] . '/upload/tmp/' . $_FILES['file']['name']);
    $iFileId = CFile::SaveFile($arFile, 'vars');

    echo $iFileId;
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");