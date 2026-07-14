<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Medical Record</title>

    <style>

        body{
            font-family: DejaVu Sans, sans-serif;
            font-size:13px;
            color:#222;
            margin:25px;
        }

        .header{
            text-align:center;
            margin-bottom:30px;
        }

        .header h1{
            margin:0;
            font-size:28px;
        }

        .header p{
            margin-top:8px;
            color:#666;
            font-size:14px;
        }

        .patient-card{

            border:1px solid #444;

            padding:15px;

            margin-bottom:30px;

        }

        .patient-card table{

            width:100%;

        }

        .patient-card td{

            padding:6px;

        }

        .report{

            border:1px solid #555;

            margin-bottom:25px;

            padding:15px;

        }

        .report-title{

            font-size:18px;

            font-weight:bold;

            margin-bottom:15px;

            border-bottom:1px solid #999;

            padding-bottom:8px;

        }

        .info-table{

            width:100%;

            margin-bottom:15px;

        }

        .info-table td{

            padding:5px;

            vertical-align:top;

        }

        .label{

            font-weight:bold;

            width:150px;

        }

        .report-body{

            margin-top:15px;

            border:1px solid #ddd;

            padding:15px;

            min-height:80px;

            line-height:1.8;

        }

        .footer{

            margin-top:40px;

            text-align:center;

            font-size:12px;

            color:#777;

        }

    </style>

</head>

<body>

<div class="header">

    <h1>Medical Record</h1>

    <p>Clinic Management System</p>

</div>

<div class="patient-card">

    <table>

        <tr>

            <td class="label">Patient Name</td>

            <td>{{ $patient->user->first_name }} {{ $patient->user->last_name }}</td>

        </tr>

        <tr>

            <td class="label">Generated At</td>

            <td>{{ now()->format('Y-m-d H:i') }}</td>

        </tr>

        <tr>

            <td class="label">Number of Reports</td>

            <td>{{ $reports->count() }}</td>

        </tr>

    </table>

</div>

@foreach($reports as $report)

    <div class="report">

        <div class="report-title">

            Medical Report #{{ $loop->iteration }}

        </div>

        <table class="info-table">

            <tr>

                <td class="label">Doctor</td>

                <td>{{ $report->doctor->user->first_name }} {{ $report->doctor->user->last_name }}</td>

            </tr>

            <tr>

                <td class="label">Department</td>

                <td>{{ $report->doctor->section->name ?? 'N/A' }}</td>

            </tr>

            <tr>

                <td class="label">Appointment Date</td>

                <td>{{ $report->appointment->start_at }}</td>

            </tr>

            <tr>

                <td class="label">Created At</td>

                <td>{{ $report->created_at->format('Y-m-d H:i') }}</td>

            </tr>

        </table>

        <div class="report-body">

            {{ $report->report }}

        </div>

    </div>

@endforeach

<div class="footer">

    This document was generated automatically by the Clinic Management System.

</div>

</body>

</html>
