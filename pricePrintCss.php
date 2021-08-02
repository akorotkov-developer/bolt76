<style type="text/css" media="all">

html, body {
    padding: 0;
    margin: 0;
    /*font-family: Arial, sans-serif;*/
    /*font-family: 'Ubuntu Mono', sans-serif;*/
    font-family: 'PT Mono', sans-serif;
}

td {
    padding: 0;
}

body {
    font-size: <?=$sizes['bodyFontSize']?>;
}

h1, h2, h3, h4 {
    padding: 0;
    margin: 0;
    font-weight: bold;
}

h1 {
    /*line-height: <?=$sizes['h1LineHeight']?>mm;*/
    font-size: <?=$sizes['h1FontSize']?>pt;

}

h2 {
    line-height: <?=$sizes['h2LineHeight']?>mm;
    font-size: <?=$sizes['h2FontSize']?>pt;

}

h3 {
    line-height: <?=$sizes['h3LineHeight']?>mm;
    font-size: <?=$sizes['h3FontSize']?>pt;

}

h4 {
    line-height: <?=$sizes['h4LineHeight']?>mm;
    font-size: <?=$sizes['h4FontSize']?>pt;
}

.w1 {
    width: 1%;
}
.h1Box {
    position: relative;
    z-index: 5;
}
.h1Box .td {
    vertical-align: middle;
    height: <?=$sizes['h1LineHeight']?>mm;
}

.h1Box .header {
    padding-right: 5mm;
    padding-left: 5mm;
    font-size: 14pt;
}

.h1Box .logo {
    padding-right: 5mm;
}

.h1Box .contacts {
    padding-right: 5mm;
    white-space: nowrap;
    font-size: 12pt;
}

.h1Box .tblBox {
    position: relative;
}

.h2Box .content{
    position: relative;
    padding: <?=$sizes['h2Padding']?>mm;
}



.h1MarginBottom {
    height: <?=$sizes['h1MarginBottom']?>mm;
    line-height: <?=$sizes['h1MarginBottom']?>mm;
}

.h2MarginBottom {
    height: <?=$sizes['h2MarginBottom']?>mm;
    line-height: <?=$sizes['h2MarginBottom']?>mm;
}

.h3MarginBottom {
    height: <?=$sizes['h3MarginBottom']?>mm;
    line-height: <?=$sizes['h3MarginBottom']?>mm;
}

.h4MarginBottom {
    height: <?=$sizes['h4MarginBottom']?>mm;
    line-height: <?=$sizes['h4MarginBottom']?>mm;
}

.headerMarginBottom {
    font-size: 0;
    line-height: 0;
}

.sectionBoxMarginBottom {
    height: <?=$sizes['sectionBoxMarginBottom']?>mm;
    line-height: <?=$sizes['sectionBoxMarginBottom']?>mm;
}

.sectionBox {

    position: relative;
}

.sectionBox.padding {
    padding-left: 21mm;
}

.sectionImage {
    position: absolute;
    left: 0;
    top: <?=$pricePrint->getTableHeaderHeight()?>mm;
    width: 19mm;
}

.sectionImage img {
    max-width: 100%;
    display: block;
    margin: auto;
    max-height: 25mm;
}
td.img{
    width: 1%;
    vertical-align: middle;
}
.imgBox {
    height: 20mm;

    text-align: center;
    overflow: hidden;
    display: table-cell;
    vertical-align: middle;
}

.imgBox .imgContent{
    width: 21mm;
    display: inline-block;
}

td.img img {
    display: inline-block;
    position: relative;
    margin-top: 1mm;
    max-height: 17mm;
    max-width: 20mm;
}

.pageBox {
    width: <?=$sizes['pageBoxWidth']?>;
    margin: auto;
    page-break-after: always;
    height: <?=$sizes['pageHeight']?>mm;
    position: relative;
    /* border-bottom: 1mm solid black;
 border-top: 1mm solid black;*/
    padding-bottom: <?=$sizes['pageBoxPaddingBottom']?>mm;
}

.pageNumber {
    position: absolute;
    line-height: <?=$sizes['pageNumberLineHeight']?>mm;
    bottom: -20px;
}

table {
    border-collapse: collapse;
    empty-cells: show;
    border-spacing: 0;
    width: 100%;
    border: 0 none;
}

td.code,
td.pack,
td.price {

    white-space: nowrap;
}
td.unit{

    white-space: nowrap;
}



.tbl {
    display: table;
}

.tr {
    display: table-row;

}

.td {
    display: table-cell;

}

.td.code,
.td.pack,
.td.unit,
.td.price {
    width: 10%;
}

td.price {
    text-align: right;
}
td.name{
    width: 33mm;
}

td {
    border-bottom: <?=$sizes['tdBorderBottom']?>mm #777 solid;
    border-right: <?=$sizes['tdBorderBottom']?>mm #777 solid;
    margin: 0;
    padding: 0;
    /*height: 5mm;*/
    line-height: <?=$sizes['rowLineHeight']?>mm;
}

.tdContent {
    position: relative;
    padding: <?=$sizes['rowPaddingTop']?>mm 1mm <?=$sizes['rowPaddingBottom']?>mm 1mm;
}

.empty td {
    border-bottom-color: #fff;
    border-right-color: #fff;
}

td.first {
    border-left: <?=$sizes['tdBorderBottom']?>mm #000 solid;
}

tr.tableHeader td {
    border-top: <?=$sizes['tableHeaderBorderTop']?>mm #000 solid;
    line-height: <?=$sizes['rowLineHeight']?>mm;
}

.firstColumn {
    float: left;
    width: 50%;

}

.bg {
    width: 100%;
    height: 100%;
    position: absolute;
    left: 0;
    top: 0;
    z-index: -1;
}

.secondColumn {

    margin-left: 50%;

}

.firstColumn .columnContent {
    padding-right: 2mm;
}

.secondColumn .columnContent {
    padding-left: 2mm;
}

.clear {
    float: none;
    clear: both;
    font-size: 0;
    line-height: 0;
}

.pageBox div {
    /*display: none;*/
}

.pageBox .pageNumber {
    /*display: block;*/
}

#contents {
    page-break-after: always;
    width: <?=$sizes['pageBoxWidth']?>;
    margin: auto;

}

#first-list {
    width: <?=$sizes['pageBoxWidth']?>;
    margin: auto;
    font-size: 10pt;
}

.pageBreakAfter {
    page-break-after: always;
    font-size: 0;
    line-height: 0;
}

#contents h1 {
    line-height: 10mm;
    margin-bottom: 5mm;
}

#contents .tbl {
    width: 100%;
    border-bottom: 0.5mm #000 dashed;
    font-size: 10pt;
    line-height: 5mm;
}

#contents .td.page {
    width: 10%;
    text-align: right;
}

#contents .level-2 {
    padding-left: 1cm;
}

.logoBox {
    padding-top: 0mm;
    margin-bottom: 70mm;
}

.titleName {
    font-size: 30pt;
    font-weight: bold;
    letter-spacing: 2px;
    font-family: Tahoma;
    text-transform: uppercase;
    text-align: center;
    margin: 0 0 120mm 0;
}

.priceDate{
    float: right;
    font-weight: bold;
    font-family: Tahoma;
    margin: 50px 0 0 0;
}
.titlePhones {
    float: left;
    font-weight: bold;
    font-family: Tahoma;
    margin: 0 0 0 5mm;
}
.titleWeb {
    float: right;
    font-weight: bold;
    font-family: Tahoma;
}
.desc {
    display: none;
}
@media print {
    a[href]:after {
        content: none !important;
    }

    .pagebreak:before {
        content: counter(page);
        counter-increment: page;
        text-align: center;
        font: bold 15px/15px Tahoma;
        margin: 10px 0 0 0;
        border-top: 1px solid #333;
        display: block;
        width: 100%;
        padding: 5px 0 0 0;
    }
}
</style>