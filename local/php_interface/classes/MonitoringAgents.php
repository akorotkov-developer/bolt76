<?php

use Bitrix\Main\Mail\Event;

class MonitoringAgents
{
    /**
     * Функция для проверки отслеживаемых агентов
     * @return string
     */
    public static function run(): string
    {
        // Получаем агенты, которые необходимо мониторить
        $aAgentIds = [11453, 13172];

        $aDeActiveAgents = [];
        foreach ($aAgentIds as $iAgentId) {
            $oDbRes = CAgent::GetById($iAgentId);
            if ($aRes = $oDbRes->fetch()) {
                if ($aRes['ACTIVE'] == 'N') {
                    $aDeActiveAgents[] = $aRes['NAME'];
                }
            }
        }

        if (count($aDeActiveAgents) > 0) {
            foreach ($aDeActiveAgents as $agent) {
                Event::send([
                    'EVENT_NAME' => 'CHECK_AGENTS',
                    'LID' => 's1',
                    'C_FIELDS' => [
                        'AGENT_NAME' => $agent
                    ],
                ]);
            }
        }

        return '\\' . __METHOD__ . '();';
    }
}

