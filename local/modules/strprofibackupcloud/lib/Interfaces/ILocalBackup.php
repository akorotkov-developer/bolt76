<?php

namespace StrprofiBackupCloud\Interfaces;

interface ILocalBackup
{
    /**
     * Получение списка локальных копий
     * @return array
     */
    public function getLocalBackups(): array;

    /**
     * Получение информации о резервной копии
     * @param array $data
     * @return array
     */
    public function getBackUpInfo(array $data): array;

    /**
     * Удаляет backUp с сервера
     * @param array $data
     * @return bool
     */
    public function delete(array $data): bool;
}