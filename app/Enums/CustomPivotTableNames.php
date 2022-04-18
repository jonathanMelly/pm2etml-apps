<?php

namespace App\Enums;

enum CustomPivotTableNames:string
{
    case CONTRACT_GROUP_MEMBER = 'contract_worker';
    case CONTRACT_USER ='contract_client';
    case GROUP_USER = 'group_members';
}
