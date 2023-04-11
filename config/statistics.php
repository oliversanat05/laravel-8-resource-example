<?php

/* Global statistics Constants */

return [
    'vMapStatus'=>[
        0,
        1,
        2
    ],
    'vMapConstant'=>[
        'Pending' => 0,
        'InProgress' => 1,
        'Completed' => 2
    ],

    'valueDefaultTitle' => 'New Level 1 Activity',
    'editorMaxLength' => 100000,

    'coachingReadiness' => 7,

    'callMaxAssignEnd' => '45',
    'parentPageId' => 0,
    'delegateUserType' => '5',
    'encqualifierType' => '8',

    'preferenceType' => [
        18, 19, 20
    ],
    'frequency' => 0,

    'defaultLevel' => [
        'level1' => 'Value',
        'level2' => 'kpi',
        'level3' => 'strategy',
        'level4' => 'project',
        'level5' => 'criticalActivity',
    ],

    'trackingLevelKpi' => [
        'id' => 'kpi',
        'title' => 'Level 2'
    ],
    'trackingLevelStr' => [
        'id' => 'strategy',
        'title' => 'Level 3'
    ],
    'trackingLevelPro' => [
        'id' => 'project',
        'title' => 'Level 4'
    ]
    ,'trackingLevelCa' => [
        'id' => 'criticalActivity',
        'title' => 'Level 5'
    ],

    'cPreference' => 4,
    'statusPending'=>[
        'id'=>0,
        'title'=>'Pending'
    ],
    'statusInProgress'=>[
        'id'=>1,
        'title'=>'in Progress'
    ],
    'statusPendingProgress'=>[
        'id'=>3,
        'title'=>'Pending and In Progress'
    ],
    'statusCompleted'=>[
        'id'=>2,
        'title'=>'Completed'
    ],
    'statusAll'=>[
        'id'=>'',
        'title'=>'All'
    ],
    'statusBoth' => [0,1],
    'startHere' => [
        'coachingReadiness' => '1',
        'sweetSpotAnalysis' => '2',
        'coreDisciplines' => '3',
        'idealClient' => '4',

    ],
];
