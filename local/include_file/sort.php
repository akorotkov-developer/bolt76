<?php
use Bitrix\Main\Application;

$request = Application::getInstance()->getContext()->getRequest();
$sortGetParam = $request->get('catalog_sort');

$sortValues = [
    'default' => 'По умолчанию',
    'price_asc' => 'Подешевле',
    'price_desc' => 'Подороже',
    'name' => 'По названию'
];
?>
<div class="b-sort-top">
    <span class="sort-ass"> Сортировать:</span>

    <select id="sort-select">
        <?php foreach ($sortValues as $code => $value) {?>
            <option value="<?= $code?>" <?= ($code == $sortGetParam) ? 'selected' : ''?>><?= $value?></option>
        <?php } ?>
    </select>

    <script>
        $('#sort-select').change(function() {
            console.log($(this).val(), '$(this).val()!!!');
            console.log($(this).val(), '$(this).val()');

            var baseUrl = window.location.protocol + '//' + window.location.host + window.location.pathname;
            window.location.href = baseUrl + '?catalog_sort=' + $(this).val();
        });
    </script>
</div>