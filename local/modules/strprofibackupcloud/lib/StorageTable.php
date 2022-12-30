<?php

namespace StrprofiBackupCloud;

use Bitrix\Main;
use Bitrix\Main\Entity;

/**
 * Class StorageTable
 *
 * Класс для хранения переменных в БД и дальнейшего их использования
 *
 */
class StorageTable extends Entity\DataManager
{
    private static $connect = '';

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_strprofi_storage';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     * @throws Main\SystemException
     */
    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            new Entity\DatetimeField('TIMESTAMP_X', [
                'required' => true,
            ]),
            new Entity\TextField('DATA', [
                'required' => true,
                'serialized' => true,
            ]),
            new Entity\TextField('DISK_TYPE', [
                'required' => true,
                'serialized' => true,
            ]),
            new Entity\IntegerField('PERCENT', [

            ]),
        ];
    }

    /**
     * Записывает коннект к БД
     */
    private static function initConnect()
    {
        if (empty(self::$connect)) {
            self::$connect = Main\Application::getConnection();
        }
    }

    /**
     * @param array $arData
     * @return array|Entity\AddResult|Main\ORM\Data\AddResult
     */
    public static function add(array $arData)
    {
        $result = [];

        self::initConnect();
        if (
            self::$connect->isTableExists(self::getTableName())
        ) {
            self::prepareAdd($arData);
            $result = parent::add($arData);
        }
        return $result;
    }

    /**
     * Подготовка данных перед добавлением в таблицу
     * @param array $arData
     */
    private static function prepareAdd(array &$arData)
    {
        $arData['TIMESTAMP_X'] = new \Bitrix\Main\Type\DateTime();
        $arData['PERCENT'] = (int)$arData['PERCENT'];
    }

    /**
     * Возвращает массив с данными
     *
     * Обертка над стандартным getList, только возвращается массив вместо объекта
     *
     * @param array $parameters - массив с параметрами для стандартного getList
     * @param bool $bDelete - удалять ифнормацию после отдачи из БД (по умолчанию false - удалять)
     * @return array|null
     */
    public static function get(array $parameters = [], $bDelete = false)
    {
        if (\in_array('ID', $parameters['select'], true) === false) {
            $parameters['select'][] = 'ID';
        }
        $objItems = self::getList($parameters);
        while ($arItem = $objItems->fetch()) {
            if ($bDelete) {
                self::delete($arItem['ID']);
            }
            $arResult[] = $arItem;
        }
        return $arResult;
    }
}