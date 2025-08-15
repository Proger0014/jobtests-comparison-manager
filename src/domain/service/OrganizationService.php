<?php

namespace ComparisonManager\domain\service;

use ComparisonManager\common\models\Organization;

class OrganizationService
{
    /**
     * @return array<Organization>
     */
    public function getAll(): array {
        return Organization::find()->all();
    }
}