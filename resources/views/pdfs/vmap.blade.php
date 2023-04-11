<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <style>
        table td,
        table th {
            border: 1px solid black;
        }

        .container {
            margin-top: 20px;
        }

        .container table{
            clear: both;
            width: 750px;
        }

        .value-style {
            background: #4ABAEA;
            color: white;
            /* width: 10% !important; */
        }

        .kpi-style {
            background: #4AA675;
            color: white;
        }

        .kpi-style td:first-child {
            padding-left: 30px;
        }

        .strategy-style {
            background: #EF6136;
            color: white;
        }

        .project-style {
            background: #BD732E;
            color: white;
        }

        .critical-activity-style {
            background: #F9CA47;
            color: white;
        }

        .critical-activity-style td:first-child{
            padding-left: 120px;
        }
        .project-style td:first-child{
            padding-left: 90px;
        }
        .strategy-style td:first-child{
            padding-left: 60px;
        }
        .kpi-style td:first-child{
            padding-left: 30px;
        }

        @page { margin: 170px 30px; }
        #header { position: fixed; left: 0px; top: -120px; right: 0px; height: 110px; }
        table  tr{
            width: 700px;
        }

        .description{
            width: 700px !important;
        }

        .vission-mission{
            float: left;
            width: 350px;
            height: 160px;
        }
        .vission-mission-desc{
            margin-top: 5px;
            border-radius: 2px;
            border: solid 2px #ccc;
            height: 130px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>

@php
    use App\Services\VMapHelperServices\VMapHelpers;
@endphp

<body>
    <div id="header">
        <div style="height: 100px;">
            <div style="float: left;width: 45%;"><img src="{{ public_path('images/logo.png') }}" alt=""
                    srcset=""></div>
            <div style="float: left;width: 45%;text-align: right;"><span style="font-weight: 500;">V-Map:
                    {{ $data['formTitle'] }} <br />
                    User: {{ $data['user']['name'] }}
                </span>

            </div>
        </div>
    </div>

    <div class="container page-break">
        @if ($data)
            <div class="vission-mission" style="margin-right: 50px;"><span class="visson-misson-title"><label
                        style="font-weight: 500;">Mission<label></span>
                <div class="vission-mission-desc">
                    {{ $data['missionStatement'] }}
                </div>
            </div>

            <div class="vission-mission "><span class="visson-misson-title"><label
                        style="font-weight: 500;">Vission</label></span>
                <div class="vission-mission-desc">
                    {{ $data['visionStatement'] }}
                </div>
            </div>

            <table>

                @if (isset($result->checkType) && $result->checkType == 3)
                    <h1>Check Type 3</h1>
                @else
                    @if (isset($data['values']))
                        @foreach ($data['values'] as $valueKey => $value)
                            <tr class="value-style">
                                <td>{{ $value['valueTitle'] }}</td>
                                <td>
                                    {{ $data['activity_title']['valueTitle'] ?? 'Value' }}
                                </td>
                                <td>
                                    @if ($value['statusId'] == 0)
                                        Pending
                                    @elseif($value['statusId'] == 1)
                                        In Progress
                                    @elseif($value['statusId'] == 2)
                                        Complete
                                    @endif
                                </td>
                            </tr>

                            @if (isset($value['kpis']))
                                @foreach ($value['kpis'] as $kpi)
                                    <tr class="kpi-style">
                                        <td>{{ $kpi['kpiName'] }}</td>
                                        <td>
                                            {{ $data['activity_title']['kpiTitle'] ?? 'Kpi' }}
                                        </td>
                                        <td>
                                            @if ($kpi['statusId'] == 0)
                                                Pending
                                            @elseif($kpi['statusId'] == 1)
                                                In Progress
                                            @elseif($kpi['statusId'] == 2)
                                                Complete
                                            @endif
                                        </td>
                                    </tr>

                                    @if ($data['checkType'] == 1 || $data['checkType'] == 4 || $data['checkType'] == 8 && !is_null($kpi['description']))
                                        <tr>
                                            <td colspan="3" class="description">
                                                @php

                                                    $contentState = '';
                                                    $getFinalVal = '';
                                                    $getLinkSep = '';
                                                    $description = json_decode($kpi['description'], true);

                                                    if (isset($description['blocks'])) {
                                                        foreach ($description['blocks'] as $key => $desc) {
                                                            $getFinalVal .= $desc['text'];
                                                        }
                                                    } else {
                                                        $getFinalVal = $kpi['description'];
                                                    }

                                                    $getLinkSep = explode('~#~', $getFinalVal);
                                                    $getFinalVal = $getLinkSep[0];

                                                @endphp

                                                <table>
                                                    <tr>
                                                        <th>Delegate To</th>
                                                        <th>Date Assigned</th>
                                                        <th>Due Date</th>
                                                        <th>Tracking</th>
                                                    </tr>
                                                    <tr>

                                                        @if ($kpi['delegateTo'] != '')
                                                            <td>{{ VMapHelpers::processDelegatesIds($kpi['delegateTo']) }}
                                                            </td>
                                                        @else
                                                            <td>No Delegate</td>
                                                        @endif

                                                        <td>{{ $kpi['assignDate'] }}</td>
                                                        <td>{{ $kpi['dueDate'] }}</td>
                                                        <td>{{ $kpi['trackSign'] }}</td>
                                                    </tr>
                                                </table>

                                                @if (isset($kpi['trackSign']) && $kpi['trackSign'] == '@')
                                                    <table>
                                                        <tr>
                                                            <th>Jan</th>
                                                            <th>Feb</th>
                                                            <th>Mar</th>
                                                            <th>Apr</th>
                                                            <th>May</th>
                                                            <th>Jun</th>
                                                            <th>Jul</th>
                                                            <th>Aug</th>
                                                            <th>Sep</th>
                                                            <th>Oct</th>
                                                            <th>Nov</th>
                                                            <th>Dec</th>
                                                        </tr>

                                                        <tr class="activity-detail-innner">
                                                            @foreach (explode(',', $kpi['seasonalGoal']) as $item)
                                                                <td>{{ $item != '' ? $item : 0 }}</td>
                                                            @endforeach
                                                        </tr>
                                                    </table>
                                                @else
                                                    <table>
                                                        <tr>
                                                            <th>Daily</th>
                                                            <th>Weekly</th>
                                                            <th>Monthly</th>
                                                            <th>Quarterly</th>
                                                            <th>Anually</th>
                                                            <th>Goal</th>
                                                            <th>Success scale</th>
                                                        </tr>
                                                        <tr class="activity-detail-innner">
                                                            <td>${{ $kpi['daily'] }}</td>
                                                            <td>${{ $kpi['weekly'] }}</td>
                                                            <td>${{ $kpi['monthly'] }}</td>
                                                            <td>${{ $kpi['quarterly'] }}</td>
                                                            <td>${{ $kpi['annually'] }}</td>
                                                            <td>${{ $kpi['goal'] }}</td>
                                                            <td>${{ $kpi['successScale'] }}</td>
                                                        </tr>

                                                        <tr>
                                                            <td colspan="7"><strong>Description:
                                                                </strong>{{ $getFinalVal }}</td>
                                                        </tr>
                                                    </table>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif

                                    @if (isset($kpi['strategy']))
                                        @foreach ($kpi['strategy'] as $strategy)
                                            <tr class="strategy-style">
                                                <td>{{ $strategy['strategyName'] }}</td>
                                                <td>
                                                    {{ $data['activity_title']['strategyTitle'] ?? 'Strategy' }}
                                                </td>
                                                <td>
                                                    @if ($strategy['statusId'] == 0)
                                                        Pending
                                                    @elseif($strategy['statusId'] == 1)
                                                        In Progress
                                                    @elseif($strategy['statusId'] == 2)
                                                        Complete
                                                    @endif
                                                </td>
                                            </tr>

                                            @if (
                                                $data['checkType'] == 1 ||
                                                    $data['checkType'] == 4 ||
                                                    $data['checkType'] == 8 && !is_null($strategy['description']))
                                                <tr>
                                                    <td colspan="3" class="description">
                                                        @php

                                                            $contentState = '';
                                                            $getFinalVal = '';
                                                            $getLinkSep = '';
                                                            $description = json_decode($strategy['description'], true);

                                                            if (isset($description['blocks'])) {
                                                                foreach ($description['blocks'] as $key => $desc) {
                                                                    $getFinalVal .= $desc['text'];
                                                                }
                                                            } else {
                                                                $getFinalVal = $strategy['description'];
                                                            }

                                                            $getLinkSep = explode('~#~', $getFinalVal);
                                                            $getFinalVal = $getLinkSep[0];

                                                        @endphp

                                                        <table>
                                                            <tr>
                                                                <th>Delegate To</th>
                                                                <th>Date Assigned</th>
                                                                <th>Due Date</th>
                                                                <th>Tracking</th>
                                                            </tr>
                                                            <tr>

                                                                @if ($strategy['delegateTo'] != '')
                                                                    <td>{{ VMapHelpers::processDelegatesIds($strategy['delegateTo']) }}
                                                                    </td>
                                                                @else
                                                                    <td>No Delegate</td>
                                                                @endif

                                                                <td>{{ $strategy['assignDate'] }}</td>
                                                                <td>{{ $strategy['dueDate'] }}</td>
                                                                <td>{{ $strategy['trackSign'] }}</td>
                                                            </tr>
                                                        </table>

                                                        @if (isset($strategy['trackSign']) && $strategy['trackSign'] == '@')
                                                            <table>
                                                                <tr>
                                                                    <th>Jan</th>
                                                                    <th>Feb</th>
                                                                    <th>Mar</th>
                                                                    <th>Apr</th>
                                                                    <th>May</th>
                                                                    <th>Jun</th>
                                                                    <th>Jul</th>
                                                                    <th>Aug</th>
                                                                    <th>Sep</th>
                                                                    <th>Oct</th>
                                                                    <th>Nov</th>
                                                                    <th>Dec</th>
                                                                </tr>

                                                                <tr class="activity-detail-innner">
                                                                    @foreach (explode(',', $strategy['seasonalGoal']) as $item)
                                                                        <td>${{ $item != '' ? $item : 0 }}</td>
                                                                    @endforeach
                                                                </tr>
                                                            </table>
                                                            <table>
                                                                <tr>
                                                                    <th>Daily</th>
                                                                    <th>Weekly</th>
                                                                    <th>Monthly</th>
                                                                    <th>Quarterly</th>
                                                                    <th>Anually</th>
                                                                    <th>Goal</th>
                                                                    <th>Success scale</th>
                                                                </tr>
                                                                <tr class="activity-detail-innner">
                                                                    <td>${{ $strategy['daily'] }}</td>
                                                                    <td>${{ $strategy['weekly'] }}</td>
                                                                    <td>${{ $strategy['monthly'] }}</td>
                                                                    <td>${{ $strategy['quarterly'] }}</td>
                                                                    <td>${{ $strategy['annually'] }}</td>
                                                                    <td>${{ $strategy['goal'] }}</td>
                                                                    <td>${{ $strategy['successScale'] }}</td>
                                                                </tr>

                                                                <tr>
                                                                    <td colspan="7">
                                                                        <strong>Description:</strong>
                                                                        {{ $getFinalVal }}
                                                                    </td>
                                                                </tr>



                                                            </table>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                            @if (isset($strategy['project']) && !is_null($strategy['project']))
                                                @foreach ($strategy['project'] as $project)
                                                    <tr class="project-style">
                                                        <td>{{ $project['projectName'] }}</td>
                                                        <td>
                                                            {{ $data['activity_title']['projectTitle'] ?? 'Project' }}
                                                        </td>
                                                        <td>
                                                            @if ($project['statusId'] == 0)
                                                                Pending
                                                            @elseif($project['statusId'] == 1)
                                                                In Progress
                                                            @elseif($project['statusId'] == 2)
                                                                Complete
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    @if (
                                                    $data['checkType'] == 1 ||
                                                    $data['checkType'] == 4 ||
                                                    $data['checkType'] == 8 && !is_null($project['description']))
                                                <tr>
                                                    <td colspan="3" class="description">
                                                        @php

                                                            $contentState = '';
                                                            $getFinalVal = '';
                                                            $getLinkSep = '';
                                                            $description = json_decode($project['description'], true);

                                                            if (isset($description['blocks'])) {
                                                                foreach ($description['blocks'] as $key => $desc) {
                                                                    $getFinalVal .= $desc['text'];
                                                                }
                                                            } else {
                                                                $getFinalVal = $project['description'];
                                                            }

                                                            $getLinkSep = explode('~#~', $getFinalVal);
                                                            $getFinalVal = $getLinkSep[0];

                                                        @endphp

                                                        <table>
                                                            <tr>
                                                                <th>Delegate To</th>
                                                                <th>Date Assigned</th>
                                                                <th>Due Date</th>
                                                                <th>Tracking</th>
                                                            </tr>
                                                            <tr>

                                                                @if ($project['delegateTo'] != '')
                                                                    <td>{{ VMapHelpers::processDelegatesIds($project['delegateTo']) }}
                                                                    </td>
                                                                @else
                                                                    <td>No Delegate</td>
                                                                @endif

                                                                <td>{{ $project['assignDate'] }}</td>
                                                                <td>{{ $project['dueDate'] }}</td>
                                                                <td>{{ $project['trackSign'] }}</td>
                                                            </tr>
                                                        </table>

                                                        @if (isset($project['trackSign']) && $project['trackSign'] == '@')
                                                            <table>
                                                                <tr>
                                                                    <th>Jan</th>
                                                                    <th>Feb</th>
                                                                    <th>Mar</th>
                                                                    <th>Apr</th>
                                                                    <th>May</th>
                                                                    <th>Jun</th>
                                                                    <th>Jul</th>
                                                                    <th>Aug</th>
                                                                    <th>Sep</th>
                                                                    <th>Oct</th>
                                                                    <th>Nov</th>
                                                                    <th>Dec</th>
                                                                </tr>

                                                                <tr class="activity-detail-innner">
                                                                    @foreach (explode(',', $project['seasonalGoal']) as $item)
                                                                        <td>${{ $item != '' ? $item : 0 }}</td>
                                                                    @endforeach
                                                                </tr>
                                                            </table>
                                                            <table>
                                                                <tr>
                                                                    <th>Daily</th>
                                                                    <th>Weekly</th>
                                                                    <th>Monthly</th>
                                                                    <th>Quarterly</th>
                                                                    <th>Anually</th>
                                                                    <th>Goal</th>
                                                                    <th>Success scale</th>
                                                                </tr>
                                                                <tr class="activity-detail-innner">
                                                                    <td>${{ $project['daily'] }}</td>
                                                                    <td>${{ $project['weekly'] }}</td>
                                                                    <td>${{ $project['monthly'] }}</td>
                                                                    <td>${{ $project['quarterly'] }}</td>
                                                                    <td>${{ $project['annually'] }}</td>
                                                                    <td>${{ $project['goal'] }}</td>
                                                                    <td>${{ $project['successScale'] }}</td>
                                                                </tr>

                                                                <tr>
                                                                    <td colspan="7">
                                                                        <strong>Description:</strong>
                                                                        {{ $getFinalVal }}
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif

                                                    @if (isset($project['critical_activity']))
                                                        @foreach ($project['critical_activity'] as $criticalActivity)
                                                            <tr class="critical-activity-style">
                                                                <td>{{ $criticalActivity['criticalActivityName'] }}
                                                                </td>
                                                                <td>{{ $data['activity_title']['projectTitle'] ?? 'Critical Activity' }}
                                                                </td>
                                                                <td>
                                                                    @if ($criticalActivity['statusId'] == 0)
                                                                        Pending
                                                                    @elseif($criticalActivity['statusId'] == 1)
                                                                        In Progress
                                                                    @elseif($criticalActivity['statusId'] == 2)
                                                                        Complete
                                                                    @endif
                                                                </td>
                                                            </tr>

                                                            @if (
                                                $data['checkType'] == 1 ||
                                                    $data['checkType'] == 4 ||
                                                    $data['checkType'] == 8 && !is_null($criticalActivity['description']))
                                                <tr>
                                                    <td colspan="3" class="description">
                                                        @php

                                                            $contentState = '';
                                                            $getFinalVal = '';
                                                            $getLinkSep = '';
                                                            $description = json_decode($criticalActivity['description'], true);

                                                            if (isset($description['blocks'])) {
                                                                foreach ($description['blocks'] as $key => $desc) {
                                                                    $getFinalVal .= $desc['text'];
                                                                }
                                                            } else {
                                                                $getFinalVal = $criticalActivity['description'];
                                                            }

                                                            $getLinkSep = explode('~#~', $getFinalVal);
                                                            $getFinalVal = $getLinkSep[0];

                                                        @endphp

                                                        <table>
                                                            <tr>
                                                                <th>Delegate To</th>
                                                                <th>Date Assigned</th>
                                                                <th>Due Date</th>
                                                                <th>Tracking</th>
                                                            </tr>
                                                            <tr>

                                                                @if ($criticalActivity['delegateTo'] != '')
                                                                    <td>{{ VMapHelpers::processDelegatesIds($criticalActivity['delegateTo']) }}
                                                                    </td>
                                                                @else
                                                                    <td>No Delegate</td>
                                                                @endif

                                                                <td>{{ $criticalActivity['assignDate'] }}</td>
                                                                <td>{{ $criticalActivity['dueDate'] }}</td>
                                                                <td>{{ $criticalActivity['trackSign'] }}</td>
                                                            </tr>
                                                        </table>

                                                        @if (isset($criticalActivity['trackSign']) && $criticalActivity['trackSign'] == '@')
                                                            <table>
                                                                <tr>
                                                                    <th>Jan</th>
                                                                    <th>Feb</th>
                                                                    <th>Mar</th>
                                                                    <th>Apr</th>
                                                                    <th>May</th>
                                                                    <th>Jun</th>
                                                                    <th>Jul</th>
                                                                    <th>Aug</th>
                                                                    <th>Sep</th>
                                                                    <th>Oct</th>
                                                                    <th>Nov</th>
                                                                    <th>Dec</th>
                                                                </tr>

                                                                <tr class="activity-detail-innner">
                                                                    @foreach (explode(',', $criticalActivity['seasonalGoal']) as $item)
                                                                        <td>${{ $item != '' ? $item : 0 }}</td>
                                                                    @endforeach
                                                                </tr>
                                                            </table>
                                                            <table>
                                                                <tr>
                                                                    <th>Daily</th>
                                                                    <th>Weekly</th>
                                                                    <th>Monthly</th>
                                                                    <th>Quarterly</th>
                                                                    <th>Anually</th>
                                                                    <th>Goal</th>
                                                                    <th>Success scale</th>
                                                                </tr>
                                                                <tr class="activity-detail-innner">
                                                                    <td>${{ $criticalActivity['daily'] }}</td>
                                                                    <td>${{ $criticalActivity['weekly'] }}</td>
                                                                    <td>${{ $criticalActivity['monthly'] }}</td>
                                                                    <td>${{ $criticalActivity['quarterly'] }}</td>
                                                                    <td>${{ $criticalActivity['annually'] }}</td>
                                                                    <td>${{ $criticalActivity['goal'] }}</td>
                                                                    <td>${{ $criticalActivity['successScale'] }}</td>
                                                                </tr>

                                                                <tr>
                                                                    <td colspan="7">
                                                                        <strong>Description:</strong>
                                                                        {{ $getFinalVal }}
                                                                    </td>
                                                                </tr>



                                                            </table>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    @endif
                @endif
            </table>
        @endif
    </div>

</body>

</html>
