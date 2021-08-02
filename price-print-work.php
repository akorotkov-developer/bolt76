<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");

set_time_limit(0);

if (isset($_GET['clear_cache'])) {
    recursiveDelete('tmp');
    exit;
}

function compare($x, $y)
{
    $x = (array)$x;
    $y = (array)$y;
    //msg($x);
    //msg($y);
    //exit;
    //$a = preg_replace('(\.|x|х)', '', trim($x['Svertka']));
    //$b = preg_replace('(\.|x|х)', '', trim($y['Svertka']));
    $a = $x['Naimenovanie'];
    $b = $y['Naimenovanie'];
    //msg($a);
    //msg($b);
    //exit;
    if ($a == $b)
        return 0;
    else if ($a < $b)
        return -1;
    else
        return 1;
}

class pricePrint
{
    private $itemsForXml;

    public function getSectionsByIdArray()
    {
        if (empty($this->sectionsByIdArray)) {
            $xmlObjectArray = (array)$this->getSectionsXMLObject();
            $this->setSectionsArray($xmlObjectArray['category']);
        }
        return $this->sectionsByIdArray;
    }

    public function getTableHeaderHeight()
    {
        $borderBottom = $this->getSize('tdBorderBottom');
        $paddingTop = $this->getSize('rowPaddingTop');
        $paddingBottom = $this->getSize('rowPaddingBottom');
        $lineHeight = $this->getSize('rowLineHeight');
        $borderTop = $this->getSize('tableHeaderBorderTop');
        $headerHeight = $borderBottom + $borderTop + $paddingTop + $paddingBottom + $lineHeight; /* - 0.2*/


        return $headerHeight;
    }

    public function getPriceHTML()
    {
        //$this->setPriceHTML();
        return $this->priceHTML;
    }

    public function getElementsBySections()
    {
        if (empty($this->elementsBySections)) {
            $this->setElementsArray();
        }
        return $this->elementsBySections;
    }

    public function getElementsArray()
    {
        if (empty($this->elementsArray)) {
            $this->setElementsArray();
        }
        return $this->elementsArray;
    }

    public function getElementsXMLObjects()
    {
        if (empty($this->elementsXMLObjects)) {
            $this->setElementsXMlObjects();
        }
        return $this->elementsXMLObjects;
    }

    public function getSectionsArray()
    {
        if (empty($this->sectionsArray)) {
            $xmlObjectArray = (array)$this->getSectionsXMLObject();
            $this->setSectionsArray($xmlObjectArray['category']);
        }
        return $this->sectionsArray;
    }

    public function getSectionsXMLObject()
    {
        if (!$this->sectionsXMLObject)
            $this->setSectionsXMLObject();
        return $this->sectionsXMLObject;
    }


    public function set($settings = array())
    {
        return new pricePrint($settings);
    }

    private $heightCounter;
    private $priceHTML = 'ошибка формирования прайс-листа';
    private $elementsBySections = array();
    private $elementsArray = array();
    private $elementsXMLObjects = array();
    private $sectionsArray = array();
    private $sectionsByIdArray = array();
    private $sectionsXMLObject = false;
    private $settings;
    private $pagesCounter = 0;
    private $firstColumn = true;
    private $pageTitle = '';
    private $contentsArray = array();
    private $h2Counter = 0;
    private $h3Counter = 0;
    private $h4Counter = 0;
    private $rootSections;



    private function newColumnHTML($comment = 'новая колонка')
    {

        $this->heightCounterNull();
        $html = '';
        $html .= '</div></div><div class="secondColumn"><div class="columnContent">';
        $this->firstColumn = false;
        $size = $this->getHeaderSize(1, $this->pageTitle);
        $this->heightCounterPlus($size);
        return $html;
    }

    private function closeSectionBox($tableHeight, $section, $divider = true)
    {
        $html = '';


        $elements = $this->getElementsBySections();
        $sectionElements = $elements[$section['ID']];
        $elementsNumber = count($sectionElements);
        //$tableHeight = $this->getNextRowsHeight($section['ID'], $elementsNumber+1, 0);

        if ($tableHeight < 25) {
            $r = 25 - $tableHeight;
            $n = round($r / $this->getRowHeight(1));
            //$n = 25 - $tableHeight;
            //$r = $n%5.5;
            for ($i = 1; $i <= $n; $i++) {
                if ($this->getHeightCounter()>=$this->getSize('pageBreak')) {
                    break;
                }
                $html .= '<tr class="empty"><td colspan="5"><div class="tdContent">&nbsp;</div></td></tr>';
                $this->heightCounterPlus($this->getRowHeight(1));

            }
            //$html .= str_repeat('<tr class="empty"><td colspan="5"><div class="tdContent">&nbsp;</div></td></tr>', $n);

        }


        $html .= '</table>';
        //$html .= '</div>';
        if ($divider == true) {
            $height = $this->getSize('sectionBoxMarginBottom');

            $testData = ' data-this-height="' . $this->getSize('sectionBoxMarginBottom') . '" ';
            $testData .= ' data-height="' . ($this->getHeightCounter() + $height) . '" ';
            $testData .= ' data-added="' . $n . '" ';
            $testData .= ' data-row-height="' . $this->getRowHeight(1) . '" ';
            if (($this->getHeightCounter() + $height) <= $this->getSize('pageBreak'))
                $html .= '<div ' . $testData . '  class="sectionBoxMarginBottom"></div>';
            $this->heightCounterPlus($height);
        }
        $sectionImage = CFile::ResizeImageGet($section['PICTURE'], array('width' => 100, 'height' => 1000), BX_RESIZE_IMAGE_PROPORTIONAL, true);
        if ($sectionImage and $section['UF_TEMPLATE'] != 0) {
            if ($section['FORCE_BREAK_TABLE'] and $divider) {

            } else {
                $img = '<img style="" src="' . $sectionImage['src'] . '" data-img="' . $section['FORCE_BREAK_TABLE'] . ', ' . $divider . '" />';
                $html .= '<div data-table-height="' . $tableHeight . '" data-elements="' . $elementsNumber . '"  class="sectionImage">' . $img . '</div>';
            }

        }
        $html .= '</div>';
        //$html .= '<!-- закрывается раздел -->';
        $html .= "\n\n\n\n\n";
        //$this->heightCounter = $this->heightCounter - 2;

        return $html;
    }

    private function openSectionBox($sectionInfo)
    {
        $p = 'no-padding';
        if ($sectionInfo['UF_TEMPLATE'] != 0) {
            $p = 'padding';
        }

        $dataTest = '';
        $dataTest = ' data-section-id="' . $sectionInfo['ID'] . '" ';

        $html = "\n\n\n\n\n";
        //$html .= '<!-- открывается раздел -->';
        $html .= '<div ' . $dataTest . ' class="sectionBox ' . $p . '">';
        $html .= '<table>';
        //$html .= '<div class="tbl">';
        return $html;
    }

    private function getElementName($element)
    {
        $Svertka = trim($element['Svertka']);
        if (empty($Svertka) or $Svertka == '-') {
            $elementName = $element['Naimenovanie'];
        } else {
            $elementName = $element['Svertka'];
        }
        return $elementName;
    }

    private function getRowHeight($lines, $section = false)
    {

        $rowSize = $this->getSize('tdBorderBottom') + $this->getSize('rowPaddingTop') + $this->getSize('rowPaddingBottom');

        $lineSize = $this->getSize('rowLineHeight') * $lines;
        //$lineSize = $lineSize;
        if (isset($section['UF_TEMPLATE']) and  $section['UF_TEMPLATE'] == 0)
            $lineSize = $this->getSize('bigCellHeight');
        //$thisRowSize = $lineSize + $this->getSize('rowPaddingBottom') + $this->getSize('rowPaddingTop') + $borderBottom;
        $thisRowSize = $rowSize + $lineSize; /* - 0.2*/

        return $thisRowSize;
    }

    private function tableHeaderHTML($section)
    {
        //msg($section); exit;


        $headerHeight = $this->getTableHeaderHeight();

        //$headerHeight = $lineHeight + $paddingBottom + $paddingTop + $borderBottom;


        $dataInfo = '';
        $dataInfo .= ' data-this-height="' . $headerHeight . '" ';
        $dataInfo .= ' data-height="' . ($this->getHeightCounter() + $headerHeight) . '" ';

        $html = "\n";


        $html .= '<tr ' . $dataInfo . ' class="tableHeader">';
        if ($section['UF_TEMPLATE'] == 0) {
            $html .= '<td class="first name"><div class="tdContent"><img src="/img/eee.gif" class="bg" /><!--Картинка-->&nbsp;</div></td>';
        }
        $html .= '<td class="first name"><div class="tdContent"><img src="/img/eee.gif" class="bg" />Наименование</div></td>';
        $html .= '<td class="code"><div class="tdContent"><img src="/img/eee.gif" class="bg" />Арт.</div></td>';
        $html .= '<td class="pack"><div class="tdContent"><img src="/img/eee.gif" class="bg" />Упак.</div></td>';
        $html .= '<td class="unit"><div class="tdContent"><img src="/img/eee.gif" class="bg" />Ед.</div></td>';
        $html .= '<td class="price"><div class="tdContent"><img src="/img/eee.gif" class="bg" />Цена</div></td>';
        $html .= '</tr>';


        /*
        $html .= '<div ' . $dataInfo . ' class="tr">';
        $html .= '<div class="td name">Наименование</div>';
        $html .= '<div class="td code">Арт.</div>';
        $html .= '<div class="td pack">Упак.</div>';
        $html .= '<div class="td unit">Ед.</div>';
        $html .= '<div class="td price">Цена</div>';
        $html .= '</div>';
        */

        $this->heightCounterPlus($headerHeight);


        return $html;
    }


    private function getNextRowsHeight($sectionId, $rowsNumber, $elementKey)
    {
        $elementsBySections = $this->getElementsBySections();

        $elements = $elementsBySections[$sectionId];

        $slice = array_slice($elements, ($elementKey), $rowsNumber);

        $sections = $this->getSectionsByIdArray();


        //msg($slice);
        $nextSize = 0;
        foreach ($slice as $s) {
            $lines = $this->getLinesNumber($this->getElementName($s), $this->getSize('wrapElement'));
            //$size = $this->getRowHeight($lines['lines'], $sectionId);
            $size = $this->getRowHeight($lines['lines'], $sections[$sectionId]);
            $nextSize = $nextSize + $size;
        }
        //$nextSize = $this->getHeightCounter() + $nextSize;
        //exit;

        /*
        if ($sectionId==15889) {

            msg($elements);
            msg($slice);
            msg($nextSize);
            exit;
        }
        */


        return $nextSize;
    }


    private function itemsTable($section)
    {


        $sectionElements = $this->getSectionElements($section['ID']);
        $html = '';
        if (!empty($sectionElements)) {


            $html = $this->openSectionBox($section);
            $html .= $this->tableHeaderHTML($section);

            $tableHeight = 0;


            $elementsNumber = count($sectionElements);


            $elementsCounter = 0;


            usort($sectionElements, 'compare');
            //exit;


            foreach ($sectionElements as $elementKey => $element) {


                $getElementInfo = CIBlockElement::GetList(Array(), array("PROPERTY_ROWID" => $element['ID'], "IBLOCK_ID" => 1), false, false, array("ID", "NAME", "PREVIEW_PICTURE"));
                $count = $getElementInfo->SelectedRowsCount(); // количество полученных записей из таблицы
                $elementInfo = $getElementInfo->GetNext();


                $elementsCounter++;
                $elementName = $this->getElementName($element);
                $lines = $this->getLinesNumber($elementName, $this->getSize('wrapElement'));


                $rowHeight = $this->getRowHeight($lines['lines'], $section);

                ///$nextRowHeight = $this->getNextRowsHeight($section['ID'], 1, ($elementKey+1)) + $this->getHeightCounter();


                /*
                * Проверка - поместится ли следующая строка
                */
                if (($this->getHeightCounter() + $rowHeight) > ($this->getSize('pageBreak') /*- $this->getSize('pageBoxPaddingBottom')*/) /*and $elementsCounter != $elementsNumber*/) {
                    $section['FORCE_BREAK_TABLE'] = true;
                    $html .= $this->closeSectionBox($tableHeight, $section, false);
                    if ($this->firstColumn == true) {
                        $html .= $this->newColumnHTML();
                    } else {
                        $html .= $this->newPageHTML($section, 'новая страница во время таблицы');
                    }
                    $html .= $this->openSectionBox($section);
                    $html .= $this->tableHeaderHTML($section);
                }


                /*
                * Формирование строки
                */
                $testInfo = '';
                $testInfo .= ' data-this-height="' . ($rowHeight) . '" ';
                $testInfo .= ' data-height="' . ($this->getHeightCounter() + $rowHeight) . '" ';
                //$testInfo .= ' data-next-height="' . $nextRowHeight . '" ';
                $html .= "\n";
                $f = 'first';
                $html .= '<tr ' . $testInfo . '>';
                if ($section['UF_TEMPLATE'] == 0) {
                    //$img = '<div class="imgBox">';
                    $img = '';
                    $elementImage = CFile::ResizeImageGet($elementInfo['PREVIEW_PICTURE'], array('width' => 100, 'height' => 1000), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                    if ($elementImage) {
                        $img = '<img alt="" src="' . $elementImage['src'] . '" />';
                    } else {
                        $img .= '&nbsp;';
                    }
                    //$img .= '</div>';
                    $html .= '<td class="img first"><div class="imgBox"><div class="imgContent">' . $img . '</div></div></td>';
                    $f = '';
                }
                $html .= '<td class="' . $f . ' name"><div class="tdContent">' . $lines['text'] . '</div></td>';
                $html .= '<td class="code"><div class="tdContent">' . $element['Artikul'] . '</div></td>';
                $html .= '<td class="pack"><div class="tdContent">' . $element['VUpakovke'] . '</div></td>';
                $html .= '<td class="unit"><div class="tdContent">' . $element['EdIzmereniya'] . '</div></td>';
                $html .= '<td class="price"><div class="tdContent">' . $element['CZena2'] . '</div></td>';
                $html .= '</tr>';


                /*
                $html .= '<div ' . $testInfo . ' class="tr">';
                $html .= '<div class="td name">' . $lines['text'] . '</div>';
                $html .= '<div class="td code">' . $element['Artikul'] . '</div>';
                $html .= '<div class="td pack">' . $element['VUpakovke'] . '</div>';
                $html .= '<div class="td unit">' . $element['EdIzmereniya'] . '</div>';
                $html .= '<div class="td price">' . $element['CZena2'] . '</div>';
                $html .= '</div>';
                */

                $tableHeight = $tableHeight + $rowHeight;
                /*if ($section['UF_TEMPLATE'] == 0) {
                    $rowHeight = 22.5;
                }*/
                //if ($plusHeight == true)
                $this->heightCounterPlus($rowHeight);


            }
            $html .= $this->closeSectionBox($tableHeight, $section);
        }

        return $html;
    }


    public function setPriceHTML()
    {

        $sections = $this->getSectionsArray();
        $sectionsById = $this->getSectionsByIdArray();
        $this->setElementsArray();

        $this->pageTitle = $sections[0]['NAME'];

        $html = $this->newPageHTML($sections[0], 'открывается первая страница', 'first');
        $sectionsNumber = count($sections);
        $sectionsCounter = 0;
        //msg($this->rootSections);
        //msg($sectionsById);
        //msg($sections);
        //exit;
        $rootId = $sections[0]['ID'];
        $rootItemId = false;
        foreach ($sections as $sectionKey => $section) {

            $setContents = true;
            $sectionsCounter++;


            /*
             * Разрыв страницы или след колонка, если не помещается заголовок с первыми строками таблицы
             */
            $nextHeaderSize = $this->getHeaderSize($section['DEPTH_LEVEL'], $section['NAME']);
            $nRows = 5;
            if ($section['UF_TEMPLATE'] == 0) $nRows = 1;
            $nextRowsSize = $this->getNextRowsHeight($section['ID'], $nRows, 0) + $this->getTableHeaderHeight(); // первые несколько строк таблицы и заголовок таблицы
            $nextHeight = $nextHeaderSize + $nextRowsSize + $this->getHeightCounter();
            if (($nextHeight) > ($this->getSize('pageBreak'))) {
                if ($this->firstColumn == true) {
                    $html .= $this->newColumnHTML();
                } else {
                    if ($sectionsCounter != $sectionsNumber) {
                        //if (isset($ns['childs']) and isset($nKey) and $this->h2Counter==$ns['childs']) {
                        if (false) {

                        } else {
                            if ($section['DEPTH_LEVEL'] != 1)
                                $html .= $this->newPageHTML($sections[0], 'новая страница перед заголовоком level=' . $section['DEPTH_LEVEL'] . ' ' . $sections[$sectionKey]['NAME'] . ' nextLevel=' . $sections[$sectionKey + 1]['DEPTH_LEVEL'] . ' ');
                        }

                    }

                }

            }



            /*
             * Вывод заголовка
             */
            if ($section['DEPTH_LEVEL'] == 1) {
                $this->pageTitle = $section['NAME'];
                if ($sectionsCounter != 1) {
                    if ($sectionsCounter != $sectionsNumber) {
                        $html .= $this->newPageHTML($sections[0], 'новая страница перед заголовоком первого уровня ');
                        $this->h2Counter = 0;
                    }
                }
            } else {
                $elements = $this->getElementsBySections();
                //if (!empty($elements[$section['ID']])) {
                if (true) {
                    $html .= '<!-- $nextHeight="' . $nextHeight . '", $nextHeaderSize="' . $nextHeaderSize . '", $nextRowsSize="' . $nextRowsSize . '" -->';
                    $html .= $this->headerHTML($section);

                } else {
                    $setContents = false;
                }
            }



            //$html .= $this->itemsTable($section);



            /*
             * Вывод элементов
             */
            if ($section['DEPTH_LEVEL'] != 2) {
                $html .= $this->itemsTable($section);
            } else {
                $rootItemId = $section;
            }





            /*
             * Элементы лежащие в корне раздела второго уровня (без секции)
             */
            if (!isset($sections[$sectionKey + 1]) or (isset($sections[$sectionKey + 1]) and $sections[$sectionKey + 1]['DEPTH_LEVEL'] <= 2) and is_array($rootItemId)) {
                $nRows = 5;
                if ($rootItemId['UF_TEMPLATE'] == 0) $nRows = 1;
                $firstRowsHeight = $this->getNextRowsHeight($rootItemId['ID'], $nRows, 0) + $this->getTableHeaderHeight();
                if (($firstRowsHeight+$this->getHeightCounter())>$this->getSize('pageBreak')) {
                    if ($this->firstColumn == true) {
                        $html .= $this->newColumnHTML('новая колонка перед элементами в корне второго уровня (без секции)');
                    } else {
                        if ($sectionsCounter != $sectionsNumber) {
                            //if (isset($ns['childs']) and isset($nKey) and $this->h2Counter==$ns['childs']) {
                            if (false) {

                            } else {
                                if ($section['DEPTH_LEVEL'] != 1)
                                    $html .= $this->newPageHTML($sections[0], 'новая страница перед перед элементами в корне второго уровня (без секции)');
                            }

                        }

                    }
                }
                $html .= $this->itemsTable($rootItemId);
                $rootItemId = false;
            }




            /*
             * Формирование массива для содержания
             */
            if ($setContents)
                $this->setContentsArray($section);

        }
        //$html .= $this->pageNumberHTML();


        $html .= $this->newPageHTML($sections[0], 'закрывется последняя страница', 'last');

        $this->priceHTML = $html;
    }

    private function getSectionElements($sectionId)
    {
        //msg($this->elementsBySections); exit;
        return $this->elementsBySections[$sectionId];
    }

    private function pagesCounterPlus()
    {
        $newPagesCounter = $this->pagesCounter + 1;
        $this->pagesCounter = $newPagesCounter;
    }

    private function getPagesCounter()
    {
        return $this->pagesCounter;
    }

    private function getHeightCounter()
    {
        return $this->heightCounter;
    }

    private function getSize($code)
    {
        $sizes = $this->settings['sizes'];
        return $sizes[$code];
    }

    private function heightCounterNull()
    {
        $this->heightCounter = 0;
    }

    private function heightCounterPlus($size)
    {
        $newHeightCounter = $this->heightCounter + $size;
        $this->heightCounter = $newHeightCounter;
    }

    private function getLinesNumber($text, $characters)
    {
        $text = preg_replace('/\s+/', ' ', $text);
        $linesNumber = 1;
        if (strlen($text) > $characters) {
            $text = wrap_text($text, $characters, '<br/>');
            $linesNumber = substr_count($text, '<br/>') + 1;
        }
        return array('lines' => $linesNumber, 'text' => $text);
    }

    private function getHeaderSize($level, $name)
    {
        $lines = $this->getLinesNumber($name, $this->getSize('h' . $level . 'Break'));
        $marginBottom = $this->getSize('h' . $level . 'MarginBottom');

        $padding = $this->getSize('h' . $level . 'Padding');
        $size = ($this->getSize('h' . $level . 'LineHeight') * $lines['lines']) + $marginBottom + ($padding * 2);
        /*
        if ($name=='Дверная фурнитура') {
            msg($level);
            msg($name);
            msg($padding);
            msg($size);
            exit;
        }
        */
        return $size;
    }

    private function pageNumberHTML()
    {
        $html = '<div class="pageNumber">' . $this->getPagesCounter() . '</div>';
        return $html;
    }

    private function headerHTML($section)
    {


        $level = $section['DEPTH_LEVEL'];
        $name = $section['NAME'];
        $id = $section['ID'];

        $size = $this->getHeaderSize($level, $name);
        $mbHeight = $this->getSize('h' . $level . 'MarginBottom');
        $headerHeight = $this->getSize('h' . $level . 'LineHeight');
        $lines = $this->getLinesNumber($name, $this->getSize('h' . $level . 'Break'));

        $testInfo = 'data-height="' . $headerHeight . '" ';
        $testInfo .= 'data-lines="' . $lines['lines'] . '" ';
        $testInfo .= 'data-break-words="' . $this->getSize('h' . $level . 'Break') . '" ';


        $html = '<div data-this-height="' . $size . '" data-height="' . ($this->getHeightCounter() + $size) . '" data-section-id="' . $id . '"  class="h' . $level . 'Box">';
        if ($level == 2) {
            $this->h2Counter = $this->h2Counter + 1;
            $html .= '<div class="content">';
            $html .= '<img class="bg" alt="" src="/img/eee.gif" />';
            $html .= '<h' . $level . ' ' . $testInfo . '>' . $lines['text'] . '</h' . $level . '>';
            $html .= '</div>';
            $html .= '<div data-this-height="' . $mbHeight . '" class="h' . $level . 'MarginBottom headerMarginBottom"></div>';
            //$size = $size + ($this->getSize('h2Padding')*2);

        } else {
            $html .= '<h' . $level . ' ' . $testInfo . '>' . $lines['text'] . '</h' . $level . '>';
            $html .= '<div data-this-height="' . $mbHeight . '" class="h' . $level . 'MarginBottom headerMarginBottom"></div>';
        }

        $html .= '</div>';
        $this->heightCounterPlus($size);
        return $html;
    }

    private function newPageHTML($section, $comment = 'новая страница', $param = false)
    {
        $this->heightCounterNull();
        $sectionsById = $this->getSectionsByIdArray();
        $html = '';
        if ($param != 'first') {

            $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="clear"></div>';
            $html .= '</div>';
            $html .= $this->pageNumberHTML();
            $html .= '</div>';


            $html .= "\n\n\n\n\n\n";
            $html .= '<!-- ' . $comment . ' -->';
            $html .= "\n\n\n\n\n\n";
        }
        if ($param != 'last') {
            $html .= '<div class="pageBox">';


            $size = $this->getHeaderSize(1, $this->pageTitle);
            $html .= '<div data-this-height="' . $size . '" data-height="' . $this->getHeightCounter() . '" class="h1Box">';
            $html .= '<div class="tblBox">';
           /* $html .= '<img class="bg" src="/img/white.gif" alt="" style="border: solid 1px #ff7920;">';*/
            $html .= '<div class="tbl" style="border: solid 1px #ff7920; page-break-before: always;">';
            $html .= '<div class="tr">';
            $html .= '<div class="td header"><h1 data-level="">' . $this->pageTitle . '</h1></div>';
            $html .= '<div class="td w1 logo"><img class="logo" src="/img/logo.png" alt=""></div>';
            $html .= '<div class="td w1 contacts">strprofi.ru<br/>(4852) 58-04-45</div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="h1MarginBottom headerMarginBottom"></div>';
            $html .= '</div>';


            $this->heightCounterPlus($size);

            $html .= '<div class="columns">';
            $html .= '<div class="firstColumn">';
            $html .= '<div class="columnContent">';
            $this->pagesCounterPlus();
            $this->firstColumn = true;
        }


        return $html;
    }


    private function getNextHeadersSize($currentHeaderIndex, $headersNumber = 1)
    {
        $sections = $this->getSectionsArray();
        $slice = array_slice($sections, ($currentHeaderIndex + 1), $headersNumber);
        $nextHeadersSize = 0;
        foreach ($slice as $s) {
            $headerSize = $this->getHeaderSize($s['DEPTH_LEVEL'], $s['NAME']);
            $nextHeadersSize = $nextHeadersSize + $headerSize;
        }
        return $nextHeadersSize;
    }


    private function setElementsArray()
    {
        $elementsArray = array();
        $elementsBySections = array();
        $elementsObjects = $this->getElementsXMLObjects();
        foreach ($elementsObjects as $key => $object) {
            if (!empty($object->item)) {
                foreach ($object->item as $item) {
                    $elementsArray[] = (array)$item;
                    $elementsBySections[$key][] = (array)$item;
                }
            }
        }

        $this->elementsArray = $elementsArray;
        $this->elementsBySections = $elementsBySections;
    }

    private function setElementsXMlObjects()
    {
        //Создадим все фалики xml с товрами
        $this->getItemsForXml();

        $elementsXMLObjects = array();
        $sections = $this->getSectionsArray();

        foreach ($sections as $section) {
            $loadXML = simplexml_load_file('tmp/section-' . $section['ID'] . '.xml');

            $elementsXMLObjects[$section['ID']] = $loadXML;
        }

        $this->elementsXMLObjects = $elementsXMLObjects;
    }

    private function setSectionsArray($from, $depthLevel = 0)
    {

        $depthLevel++;
        if (isset($from->ID)) {
            $from = array($from);
        }

        $from = (array)$from;
        $cc = 0;
        foreach ($from as $f) {

            $arFilter = Array('IBLOCK_ID' => 1, 'UF_ROWID' => (int)$f->ID);
            $db_list = CIBlockSection::GetList(Array(), $arFilter, false, Array("UF_*"));
            $count = $db_list->SelectedRowsCount(); // количество полученных записей из таблицы


            if ($count == 1) {

                $ar_result = $db_list->GetNext();
                $na = array();
                $sectionName = trim($ar_result['NAME']);

                $sectionName = preg_replace('/(^\.*\s*[0-9]*\s*)/', '', $sectionName);
                $na['NAME'] = $sectionName;
                $na['ID'] = (int)$f->ID;
                $na['UF_TEMPLATE'] = $ar_result['UF_TEMPLATE'];
                $na['PICTURE'] = $ar_result['PICTURE'];
                $na['DEPTH_LEVEL'] = $ar_result['DEPTH_LEVEL'];
                if ($na['DEPTH_LEVEL'] == 1) {
                    $na['KEY'] = $cc;

                }


                if (!isset($f->childs)) {
                    $na['RIGHT_MARGIN'] = 2;
                    $na['LEFT_MARGIN'] = 1;
                } else {
                    $na['RIGHT_MARGIN'] = 20;
                    $na['LEFT_MARGIN'] = 10;
                }

                if (isset($f->childs)) {
                    $xmlArray = (array)$f->childs;
                    $na['childs'] = count($xmlArray['category']);
                }
                if (isset($na['KEY']))
                    $this->rootSections[] = $na;
                $this->sectionsArray[] = $na;
                $this->sectionsByIdArray[$na['ID']] = $na;
                if (isset($f->childs)) {
                    //$xmlArray = (array)$f->childs;

                    $this->setSectionsArray($xmlArray['category'], $depthLevel);
                }
                $cc++;
            }

        }

    }

    private function setContentsArray($section)
    {
        if (!isset($this->contentsArray[$section['ID']]) and $section['DEPTH_LEVEL'] < 3) {
            $section['PAGE'] = $this->getPagesCounter();
            $this->contentsArray[$section['ID']] = $section;
        }
    }

    public function getContentsHTML()
    {
        $html = '<div id="contents">';
        $html .= '<h1>Содержание</h1>';
        $height = 15;
        $contentsArray = $this->contentsArray;
        //msg($contentsArray); exit;
        foreach ($contentsArray as $i) {
            $height = $height + 5;

            $html .= '<div class="tbl" data-height="' . $height . '"><div class="tr"><div class="name td level-' . $i['DEPTH_LEVEL'] . '">' . $i['NAME'] . '</div><div class="td page">' . $i['PAGE'] . '</div></div></div>';
            if ($height + 5 > 265) {
                $height = 0;
                $html .= '<div class="pageBreakAfter"></div>';
            }
        }
        $html .= '</div>';
        return $html;
    }

    private function setSectionsXMLObject()
    {
        $xml = simplexml_load_file("tmp/cats2.xml");

        $this->sectionsXMLObject = $xml;
    }


    private function __construct($settings)
    {
        $this->settings = $settings;
    }

    private function getItemsForXml() {
        CModule::IncludeModule("iblock");

        $sections = $this->getSectionsArray();

        $arRezultSections = array_column($sections, 'ID');

        $arFilter = [
            'ACTIVE' => 'Y',
            'IBLOCK_ID' => 1,
            'UF_ROWID' => $arRezultSections
        ];
        $arSelect = ['IBLOCK_ID', 'ID', 'NAME', 'DEPTH_LEVEL', 'IBLOCK_SECTION_ID', 'PICTURE', 'UF_ROWID'];
        $arOrder = ['DEPTH_LEVEL' => 'ASC', 'SORT' => 'ASC'];
        $rsSections = CIBlockSection::GetList($arOrder, $arFilter, false, $arSelect);

        $arSectionIds = [];
        while ($arSection = $rsSections->GetNext()) {
            $arSectionIds[$arSection['UF_ROWID']] = $arSection['ID'];
        }

        //Сделаем выборку всех товаров полученных секциях
        $arItems = [];

        $arFilter = [
            'IBLOCK_ID' => 1,
            'PROPERTY_SHOW_IN_PRICE' => [1],
            'SECTION_ID' => $arSectionIds,
            /*'INCLUDE_SUBSECTIONS' => 'Y'*/
        ];
        $dbRes = CIBlockElement::GetList(
            ['SORT"=>"ASC'],
            $arFilter,
            false,
            false,
            [
                'ID',
                'NAME',
                'PROPERTY_NOMNOMER',
                'PROPERTY_SHOW_IN_PRICE',
                'IBLOCK_SECTION_ID',
                'PREVIEW_PICTURE',
                'PROPERTY_ARTICUL',
                'PROPERTY_ROWID',
                'PROPERTY_NOMNOMER',
                'PROPERTY_NomenklaturaGeog',
                'PROPERTY_VES',
                'PROPERTY_UPAKOVKA',
                'PROPERTY_UPAKOVKA2',
                'PROPERTY_NAIMENOVANIE',
                'PREVIEW_PICTURE',
                'PROPERTY_PRICE',
                'PRICE_OPT',
                'PRICE_OPT2',
                'PROPERTY_UNITS',
                'IBLOCK_SECTION_ID'
            ]
        );
        while($arRez = $dbRes->Fetch())
        {
            $arItems[] = $arRez;
        }

        //Теперь составим xml файлики из полученныех данных
        //TODO искуственно ограничение на количество товаров
        $sectArraya = [17304, 17304, 3566, 7532];
        foreach ($arSectionIds as $keyRowId => $sectionItem) {
            //TODO искуственно ограничение на количество товаров
  /*          if (!in_array($keyRowId, $sectArraya)) {
                continue;
            }*/

            ob_start();

            /*header('Content-Type: text/xml');*/
            echo '<?xml version="1.0" encoding="utf-8"?> ';?>

            <items>
                <?php
                foreach ($arItems as $item) {
                    if (!$item['PROPERTY_ARTICUL_VALUE']) {
                        continue;
                    }

                    if ($item['IBLOCK_SECTION_ID'] != $sectionItem) {
                        continue;
                    }
                    ?>

                    <item>
                        <ID><?=$item['PROPERTY_ROWID_VALUE'];?></ID>
                        <NomNomer><?=$item['PROPERTY_PROPERTY_NOMNOMER_VALUE'];?></NomNomer>
                        <NomenklaturaGeog><?=$item['PROPERTY_NomenklaturaGeog_VALUE'];?></NomenklaturaGeog>
                        <Ves><?=$item['PROPERTY_VES_VALUE'];?></Ves>
                        <VUpakovke><?=$item['PROPERTY_UPAKOVKA_VALUE'];?></VUpakovke>
                        <VUpakovke2><?=$item['PROPERTY_UPAKOVKA_VALUE'];?></VUpakovke2>
                        <Ostatok><?=$item['PROPERTY_OSTATOK_VALUE'];?></Ostatok>
                        <Artikul><?=$item['PROPERTY_ARTICUL_VALUE'];?></Artikul>
                        <Naimenovanie><?=$item['PROPERTY_NAIMENOVANIE_VALUE'];?></Naimenovanie>
                        <Foto><?=$item['PREVIEW_PICTURE'];?></Foto>
                        <CZena1><?=$item['PROPERTY_PRICE_VALUE'];?></CZena1>
                        <CZena2><?=$item['PRICE_OPT_VALUE'];?></CZena2>
                        <CZena3><?=$item['PRICE_OPT2_VALUE'];?></CZena3>
                        <EdIzmereniya><?=$item['PROPERTY_UNITS_VALUE'];?></EdIzmereniya>
                        <?php
                        if ($item['Opisanie']) {
                            ?><Opisanie><?=$item['Opisanie'];?></Opisanie>
                        <?php }?>
                    </item>
                <?php } ?>
            </items>

            <?php $xmlContent = ob_get_clean();

            $filename = '/tmp/section-' . $keyRowId . '.xml';
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . $filename, $xmlContent);
        }

        return $arItems;
    }

    public function cacheDate(){
        $filename = 'tmp/cats2.xml';
        $result = date('d.m.Y');
        if (file_exists($filename)) {
            $result = date("d.m.Y", filemtime($filename));
        }
        return $result;
    }

}





$sizes = array(
    'h1FontSize' => 10,
    'h2FontSize' => 12,
    'h3FontSize' => 10,
    'h4FontSize' => 9,
    'h1Break' => 24,
    'h2Break' => 29,
    'h3Break' => 36,
    'h4Break' => 45,
    'h1Padding' => 0,
    'h2Padding' => 2,
    'h3Padding' => 0,
    'h4Padding' => 0,
    'h1MarginBottom' => 5,
    'h2MarginBottom' => 5,
    'h3MarginBottom' => 5,
    'h4MarginBottom' => 5,
    'h1LineHeight' => 15,
    'h2LineHeight' => 5,
    'h3LineHeight' => 5,
    'h4LineHeight' => 5,
    'pageBoxWidth' => '184mm',
    'pageBoxPaddingBottom' => 5,
    'pageHeight' => 260,
    'pageBreak' => 273.5,
    'bodyFontSize' => '6pt',
    'pageNumberLineHeight' => 5,
    'sectionBoxMarginBottom' => 5,
    'tdBorderBottom' => 0.5,
    'tableHeaderBorderTop' => 0.5,
    'rowPaddingTop' => 1,
    'rowPaddingBottom' => 1,
    'rowLineHeight' => 3,
    'wrapElement' => 21,
    'bigCellHeight' => 20
);

$pricePrint = pricePrint::set(array('cache' => true, 'sizes' => $sizes));

$firstList = '<div id="first-list">

<div class="priceDate">'.(date("d.m.Y")/*$pricePrint->cachdivDate()*/).'</div>
<div class="logoBox"><img alt="" src="/img/big-logo.png" /></div>

<div class="titleName">Прайс лист</div>
<div class="titlePhones">+7 (4852) 58-04-45<br/>+7 (4852) 58-04-46</div>
<div class="titleWeb">mail@strprofi.ru<br/>www.strprofi.ru</div>
<div class="desc">
<p>Уважаемые партнеры реализована возможность выполнять заказы
через  сайт strprofi.ru, для этого не требуется специальных навыков
необходимо набрать корзину (по принципу интернет магазина) и
оформить заказ.</p>
<p>При заказе через сайт</p>
<ul>
<li>
экономится время на составлении заказа
</li>
<li>исключаются возможные ошибки и разночтения</li>
<li>Вы получаете актуальную информацию по ассортименту, ценам и текущим складским остаткам</li>

<li>Вы получаете дополнительную скидку 2%</li>
</ul>
</div>

</div>
<div class="pageBreakAfter"></div>';


?>

<!doctype html>
<html>
<head>
    <title>Прайс-лист</title>
    <!--<link href='http://fonts.googleapis.com/css?family=Ubuntu+Mono&subset=latin,cyrillic' rel='stylesheet'
          type='text/css'>-->
    <link href='https://fonts.googleapis.com/css?family=PT+Mono&subset=latin,cyrillic-ext,latin-ext,cyrillic' rel='stylesheet' type='text/css'>
    <? require_once 'pricePrintCss.php'; ?>
</head>
<body>
<?

$pricePrint->setPriceHTML();
echo $firstList;
echo '<div class="pageBreakAfter">&nbsp;</div>';


echo $pricePrint->getPriceHTML();
echo $pricePrint->getContentsHTML();

?>

</body>
</html>