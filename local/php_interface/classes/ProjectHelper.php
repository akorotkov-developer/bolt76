<?php
class ProjectHelper
{
    public static function getShieldClass($saleValue): string
    {
        $shieldType = '';

        if (!empty($saleValue)) {
            switch (mb_strtolower($saleValue)) {
                case 'распродажа':
                    $shieldType = 'b-shield-sale';
                    break;
                case 'новинка':
                    $shieldType = 'b-shield-new';
                    break;
                default:
                    $shieldType = 'b-shield-sale';
            }
        }

        return $shieldType;
    }
}