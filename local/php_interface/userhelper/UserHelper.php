<?php

/**
 * Класс для работы с пользователями
 */
class UserHelper
{
    /**
     * Получить группу пользователей для определения цены
     */
    public static function getPriceUserGroup()
    {
        global $USER;
        $arCurUserGroups = $USER->GetUserGroupArray();

        $dbRez = CGroup::GetList($by = 'c_sort', $order = 'asc', ['STRING_ID' => 'OPT_2|OPT_3']);
        while ($arRes = $dbRez->Fetch()) {
            $arUsersGroups[$arRes['ID']] = $arRes['STRING_ID'];
        }

        foreach ($arUsersGroups as $key => $userGroupCode) {
            if (in_array($key, $arCurUserGroups)) {
                $curUserGroupCode = $userGroupCode;
            }
        }

        return $curUserGroupCode;
    }
}