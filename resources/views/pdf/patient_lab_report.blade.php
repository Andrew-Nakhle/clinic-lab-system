<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Patient Test Results Report</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

        @page {
            size: A4;
            margin: 15mm 12mm;
        }

        * {
            box-sizing: border-box;
            font-family: 'Inter', sans-serif !important;
        }

        body {
            color: #1e293b;
            margin: 0;
            padding: 0;
            font-size: 9.5pt;
            direction: ltr;
            text-align: left;
        }

        .header-container {
            background-color: #0284c7;
            color: #ffffff;
            padding: 16px 20px;
            margin: -15mm -12mm 20px -12mm;
        }

        .header-table { width: 100%; border-collapse: collapse; }
        .lab-title { font-size: 15pt; font-weight: 700; }
        .report-badge {
            background-color: #ffffff;
            color: #0369a1;
            padding: 5px 12px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 9.5pt;
        }

        .card {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 15px;
        }

        .card-title {
            font-size: 10.5pt;
            font-weight: 700;
            border-left: 4px solid #0284c7;
            padding-left: 8px;
            margin-bottom: 12px;
            color: #0369a1;
        }

        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td {
            padding: 4px 6px;
            vertical-align: top;
            width: 33.33%;
            text-align: left;
        }

        .info-label { color: #64748b; font-size: 8pt; margin-bottom: 2px; }
        .info-value { font-weight: 600; color: #1e293b; }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .results-table th {
            background-color: #f1f5f9;
            color: #334155;
            text-align: left;
            padding: 8px;
            border-bottom: 2px solid #cbd5e1;
            font-size: 9pt;
        }

        .results-table td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        .result-value { font-weight: 700; color: #0284c7; }

        .footer-section {
            margin-top: 30px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            text-align: center;
        }

        .signature-table { width: 100%; margin-top: 15px; }
        .signature-table td { text-align: center; width: 50%; }
        .signature-line { border-top: 1px dashed #94a3b8; width: 150px; margin: 30px auto 5px auto; }
    </style>
</head>
<body>

<div class="header-container">
    <table class="header-table">
        <tr>
            <td>
                <div class="lab-title">{{ $laboratory->name ?? 'Medical Laboratory Report' }}</div>
                <div style="font-size: 8.5pt;">License No: {{ $laboratory->license_number ?? 'N/A' }}</div>
            </td>
            <td style="text-align: right;">
                <span class="report-badge">Patient Lab Report</span>
            </td>
        </tr>
    </table>
</div>
<div class="card">
    <div class="card-title">Patient Personal & Medical Info</div>
    <table class="info-table">
        <tr>
            <td>
                <div class="info-label">Patient Name</div>
                <div class="info-value">{{ $patientUser->first_name ?? '' }} {{ $patientUser->last_name ?? '' }}</div>
            </td>
            <td>
                <div class="info-label">Phone</div>
                <div class="info-value">{{ $patientUser->phone ?? '-' }}</div>
            </td>
            <td>
                <div class="info-label">Blood Group</div>
                <div class="info-value" style="color: #dc2626;">{{ $patientProfile->blood_group ?? 'N/A' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="info-label">Height / Weight</div>
                <div class="info-value">{{ $patientProfile->tall ?? '-' }} cm / {{ $patientProfile->weight ?? '-' }} kg</div>
            </td>
            <td>
                <div class="info-label">Attending Doctor</div>
                <div class="info-value">Dr. {{ $doctor->first_name ?? 'N/A' }} {{ $doctor->last_name ?? '' }}</div>
            </td>
            <td>
                <div class="info-label">Test Date</div>
                <div class="info-value">{{ $labRequest->updated_at->format('Y-m-d') }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="card">
    <div class="card-title">Completed Medical Test Results</div>
    <table class="results-table">
        <thead>
        <tr>
            <th style="width: 40%;">Test Name</th>
            <th style="width: 20%;">Result</th>
            <th style="width: 25%;">Reference Range</th>
            <th style="width: 15%;">Unit</th>
        </tr>
        </thead>
        <tbody>
        @foreach($tests as $test)
            <tr>
                <td><strong>{{ $test->name }}</strong></td>
                <td class="result-value">{{ $test->pivot->result_value ?? '-' }}</td>
                <td>{{ $test->reference_range ?? 'N/A' }}</td>
                <td>{{ $test->unit ?? '-' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="footer-section">
    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div style="font-size: 8.5pt; color: #64748b;">Lab Technician Signature</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div style="font-size: 8.5pt; color: #64748b;">Lab Stamp & Approval</div>
            </td>
        </tr>
    </table>
    <p style="font-size: 8pt; color: #94a3b8; margin-top: 15px;">
        This report is issued for the above-mentioned patient only and is electronically verified.
    </p>
</div>

</body>
</html>
