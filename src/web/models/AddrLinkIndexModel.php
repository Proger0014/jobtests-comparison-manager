<?php

namespace ComparisonManager\web\models;

use ComparisonManager\common\models\Organization;
use ComparisonManager\common\models\Page;
use yii\base\Model;

class AddrLinkIndexModel extends Model
{
    // query params
    public int $page;
    public int $threshold;
    public string $status;
    public ?int $orgId;
    public ?string $q;
    public int $pageSize;

    // other
    /** @var array<Organization> */
    public array $organizations;

    public Page $gridModel;

    public function safeAttributes(): array
    {
        return [
            'page',
            'threshold',
            'status',
            'orgId',
            'q',
            'pageSize'
        ];
    }
}