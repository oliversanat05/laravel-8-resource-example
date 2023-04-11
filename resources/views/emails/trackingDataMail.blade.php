<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proadvisor Dials and Activity Email Template</title>
</head>



<body>
    <table
        style="padding: 20px 15px; width: 650px; margin: 0 auto; font-family: 'Open Sans', sans-serif !important;
    font-weight: 700;">
        <tbody>
            <tr>
                <td>
                    <p style="font-size: 12px !important; line-height: 14px; font-weight: 400;">Please Do Not Reply to
                        this
                        Message</p>
                </td>
                <td>
                    <p
                        style="font-size: 12px !important; line-height: 14px; font-weight: 400; text-align: right; link-blue">
                        <a href="/">Personalize MyAvatar</a>
                    </p>
                </td>
            </tr>
            <tr>
                <td style="width: 50%;">
                    <table
                        style="width: 100%;
            padding: 0px;
            margin-bottom: 43px;
            margin-top: 43px; border-right: 1px solid #949494;">
                        <tbody>
                            <tr>
                                <td style="width: 170px;">
                                    <h2 style="font-size: 14px !important; line-height: 16px;">ACTIVITIES</h2>
                                    <h3 style="font-size: 12px !important; line-height: 14px; color: #0097A6;">Avatar
                                        Health Score =
                                        {{ $message['healthScore'] ?? '' }}</h3>
                                    <h4
                                        style="font-size: 12px !important; line-height: 14px; color: #949494; color: #949494;">
                                        {{ $message['activityTitle'] }}
                                    </h4>
                                    <p style="font-size: 10px; line-height: 12px; color: #646464;">
                                        <span {{-- style=" padding: 5px 8px; color: white;
            background-color: #007DC5;
            border: 1px solid #007DC5;
            border-radius: 50%;
            margin-right: 8px;"> --}}
                                            style="width:30px;height:30px;text-align:center;vertical-align:middle;background:#007dc5;border-radius:50%;display:inline-block;line-height:30px;color:#fff;margin-right:5px;">
                                            {{ $message['activityCount'] }}
                                        </span>
                                        Activities
                                    </p>
                                </td>
                                <td style="width: 100px; text-align : right;">
                                    <table style="padding-right: 12px;">
                                        <tbody>
                                            <tr>
                                                <td>
                                                    {{-- <img src="{{ $message['imageUrl'] }}" alt="avatar" width="45"
                                                        height="45"> --}}

                                                    <p
                                                        style="width:70px;height:70px;border-radius:50%;border:5px solid #e5e5e4;font-size:22px;color:#fff;margin-right:auto;background-image:url({{ $message['imageUrl'] }}); background-repeat: no-repeat; background-position: center; background-size: contain;">
                                                    </p>
                                                </td>
                                                <td>
                                                    <p
                                                        style="font-size:18px;line-height:21px;color: #0097A6;  margin-left: 8px;">
                                                        {{ $message['averageActivityPer'] }}%</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td style="width: 50%;">
                    <table
                        style="width: 100%;
            padding: 0px;
            margin-bottom: 43px;
            margin-top: 43px;">
                        <tbody>
                            <tr>
                                <td style="padding-left: 40px; width: 170px;">
                                    <h2 style="font-size: 14px !important; line-height: 16px;">PERFORMANCE</h2>
                                    <h3 style="font-size: 12px !important; line-height: 14px; color: #39B54A;">Avatar
                                        Health Score =
                                        {{ $message['performance'] }}</h3>
                                    <h4
                                        style="font-size: 12px !important; line-height: 14px; color: #949494; color: #949494;">
                                        {{ $message['dialTitle'] }}</h4>
                                    <p style="font-size: 10px; line-height: 12px; color: #646464;">
                                        <span
                                            style="width:30px;height:30px;text-align:center;vertical-align:middle;background:#007dc5;border-radius:50%;display:inline-block;line-height:30px;color:#fff;margin-right:5px;">
                                            {{ $message['dialCount'] }}
                                        </span>
                                        Dials
                                    </p>
                                </td>
                                <td style="width: 100px; text-align : right;">
                                    <table style="margin-right: 0 !important; margin-left: auto !important;">
                                        <tbody>
                                            <tr>
                                                <td>
                                                    {{-- <img src="{{ $message['dialUrl'] }}" alt="avatar" width="45"
                                                        height="45"> --}}

                                                    <p
                                                        style="width:70px;height:70px;border-radius:50%;border:5px solid #e5e5e4;margin-right:auto;background-image:url({{ $message['dialUrl'] }});background-repeat: no-repeat; background-position: center; background-size: contain;">
                                                    </p>
                                                </td>
                                                <td>
                                                    <p
                                                        style="font-size:18px;line-height:21px;color: #39B54A;  margin-left: 8px;">
                                                        {{ $message['averageDialActivityPer'] }}%
                                                    </p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                <p style="width:300px; border-bottom: 1px solid black;"></p>
                            </td>
                            <td>
                                <p>DIALS</p>
                            </td>
                            <td>
                                <p style="width:300px; border-bottom: 1px solid black;"></p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <h6
                        style="font-size: 12px !important; line-height: 14px; color: #3FA9F5;
            width: 132px;
            margin: 0 auto;
            padding-bottom: 20px;
            text-align:center;
            text-decoration: underline; font-weight: 400;">
                        MANAGE DIALS</h6>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <table
                        style="border-spacing: 1px;
                    margin-bottom: 15px;
                    display: block;">

                        <tr>

                            @foreach ($message['dials']['mergingContent']['values'] as $dials)
                                <td
                                    style=" margin-right: 1px; border: 1px solid #D9D9D9;
                                text-align: center;
                                padding: 0;
                                display: inline-block;
                                width: calc((100% / 3) - 2px) !important;
                                margin-bottom: 15px; font-weight:700 !important;">

                                    <h4
                                        style="background-color: #D9D9D9; padding: 10px;
            min-height: 60px;
            height: auto;text-align: center; font-size: 12px !important; line-height: 14px; color: #646464 !important; margin: 0px;">
                                        <a href="{{ $dials['url'] }}"
                                            style="text-decoration:none;color:#646464 !important;">{{ $dials['name'] }}</a>
                                    </h4>
                                    <p
                                        style="color: #646464; font-size: 11px !important; line-height: 14px; padding-top: 10px; text-align: center !important; margin-bottom:4px;">
                                        Goal: {{ $dials['goal'] }}</p>
                                    <p
                                        style="color: #39B54A; font-size: 11px !important; line-height: 14px; text-align: center !important; margin-bottom:4px;">
                                        Actual:
                                        {{ $dials['totalActual'] }}</p>
                                    <p
                                        style="color: #39B54A; font-size: 11px !important; line-height: 14px; text-align: center !important; margin-bottom:4px;">
                                        Run Goal: {{ $dials['goal'] }}
                                    </p>
                                    <p
                                        style="padding-bottom: 20px; color: #39B54A; font-size: 11px !important; line-height: 14px; text-align: center !important; margin-bottom:4px;">
                                        Run Rate:
                                        {{ $dials['runRate'] }}%</p>
                                    <p
                                        style="padding-bottom: 4px;color: #646464; font-size: 11px !important; line-height: 14px; text-align: center !important; margin-bottom:4px;">
                                        Start Date: {{ Carbon\Carbon::parse($dials['assignDate'])->format('m-d-Y') }}
                                    </p>
                                    <p
                                        style="padding-bottom: 4px;color: #646464; font-size: 11px !important; line-height: 14px; text-align: center !important; margin-bottom:4px;">
                                        End Date: {{ Carbon\Carbon::parse($dials['dueDate'])->format('m-d-Y') }}</p>
                                    <p
                                        style="padding-bottom: 10px; color: #646464; font-size: 11px !important; line-height: 14px; text-align: center !important; margin-bottom:4px;">
                                        Last Tracking Date:
                                        {{ Carbon\Carbon::parse($dials['trackingDate'])->format('m-d-Y') }}</p>
                                </td>
                            @endforeach

                        </tr>
                    </table>
                </td>

            </tr>

            <tr>
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                <p style="width:300px; border-bottom: 1px solid black;"></p>
                            </td>
                            <td>
                                <p>ACTIVITY</p>
                            </td>
                            <td>
                                <p style="width:300px; border-bottom: 1px solid black;"></p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <h6
                        style="font-size: 12px !important; line-height: 14px; color: #3FA9F5;
            width: 132px;
            margin: 0 auto;
            padding-bottom: 20px;
            text-decoration: underline; font-weight: 400;
            text-align: center;">
                        MANAGE ACTIVITY</h6>
                </td>
            </tr>
            <tr>
                <td colspan="2 ">
                    <table style="margin: 0 auto;">
                        <tr>
                            <td>
                                <p
                                    style="width: 22px;
            height: 22px;
            border: 1px solid transparent;
            border-radius: 50%;
            margin-bottom: 10px;
            color: #ffffff;
            margin-right: 20px;
            font-size:12px;
            line-height:20px;
            text-align: center; background-color: #EC2024;">
                                    {{ $message['activity']['mergingContent']['getActivity']['twenty'] }}</p>
                                <p style="font-size: 10px !important; line-height: 12px; font-weight: 400;">20%</p>
                            </td>
                            <td>
                                <p
                                    style="width: 22px;
            height: 22px;
            border: 1px solid transparent;
            border-radius: 50%;
            margin-bottom: 10px;
            color: #ffffff;
            margin-right: 20px;
            font-size:12px;
            line-height:20px;
            text-align: center; background-color: #F7981D;">
                                    {{ $message['activity']['mergingContent']['getActivity']['forty'] }}</p>
                                <p style="font-size: 10px !important; line-height: 12px; font-weight: 400;">40%</p>
                            </td>
                            <td>
                                <p
                                    style="width: 22px;
            height: 22px;
            border: 1px solid transparent;
            border-radius: 50%;
            margin-bottom: 10px;
            color: #ffffff;
            margin-right: 20px;
            font-size:12px;
            line-height:20px;
            text-align: center; background-color: #A170AF;">
                                    {{ $message['activity']['mergingContent']['getActivity']['sixty'] }}</p>
                                <p style="font-size: 10px !important; line-height: 12px; font-weight: 400;">60%</p>
                            </td>
                            <td>
                                <p
                                    style="width: 22px;
            height: 22px;
            border: 1px solid transparent;
            border-radius: 50%;
            margin-bottom: 10px;
            color: #ffffff;
            margin-right: 20px;
            font-size:12px;
            line-height:20px;
            text-align: center; background-color: #0096A5;">
                                    {{ $message['activity']['mergingContent']['getActivity']['eighty'] }}</p>
                                <p style="font-size: 10px !important; line-height: 12px; font-weight: 400;">80%</p>
                            </td>
                            <td>
                                <p
                                    style="width: 22px;
            height: 22px;
            border: 1px solid transparent;
            border-radius: 50%;
            margin-bottom: 10px;
            color: #ffffff;
            margin-right: 20px;
            font-size:12px;
            line-height:20px;
            text-align: center; background-color: #39B54A;">
                                    {{ $message['activity']['mergingContent']['getActivity']['hundred'] }}</p>
                                <p style="font-size: 10px !important; line-height: 12px; font-weight: 400;">100%</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td colspan=“2”>
                    <table>
                        @foreach ($message['activity']['mergingContent']['values']['values'] as $key => $activity)
                            @foreach ($activity as $date => $item)
                                <tr>
                                    <td>
                                        <p
                                            style="font-size: 12px !important; line-height: 14px; color: #3FA9F5; padding-top: 15px;">
                                            Due Date: {{ Carbon\Carbon::parse($date)->format('m-d-Y') }}</p>
                                        @foreach ($item as $activityData)
                                            <p
                                                style="padding-left: 15px !important; padding-top: 15px; font-size: 12px !important; line-height: 14px; font-weight: 400;">
                                                <span
                                                    style="width: 4px;
            height: 4px;
            margin-right: 5px;
            background-color: #000000;
            border: 1px solid transparent;
            border-radius: 50%;
            display: inline-block;
            vertical-align: middle;"></span>{{ $activityData['parent'] }}
                                                >> <a style="color: #3FA9F5;"
                                                    href="/"">{{ $activityData['name'] }}</a>
                                            </p>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
