<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Medical Prescriptions</title>

    <style>

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #222;
            margin: 25px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
        }

        .header p {
            margin-top: 8px;
            color: #666;
            font-size: 14px;
        }

        .patient-card {
            border: 1px solid #444;
            padding: 15px;
            margin-bottom: 30px;
        }

        .patient-card table {
            width: 100%;
        }

        .patient-card td {
            padding: 6px;
        }

        .prescription {
            border: 1px solid #555;
            margin-bottom: 25px;
            padding: 15px;
        }

        .prescription-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 1px solid #999;
            padding-bottom: 8px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 15px;
        }

        .info-table td {
            padding: 5px;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            width: 150px;
        }

        .medicines-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 10px;
        }

        .medicines-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .medicines-table th,
        .medicines-table td {
            border: 1px solid #555;
            padding: 10px;
            vertical-align: top;
        }

        .medicines-table th {
            font-weight: bold;
            text-align: left;
            background-color: #eee;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }

    </style>

</head>

<body>

<div class="header">

    <h1>Medical Prescriptions</h1>

    <p>Clinic Management System</p>

</div>


{{-- Patient Information --}}

<div class="patient-card">

    <table>

        <tr>

            <td class="label">
                Patient Name
            </td>

            <td>
                {{ $patient->user->first_name ?? 'N/A' }}
                {{ $patient->user->last_name ?? '' }}
            </td>

        </tr>

        <tr>

            <td class="label">
                Generated At
            </td>

            <td>
                {{ now()->format('Y-m-d H:i') }}
            </td>

        </tr>

        <tr>

            <td class="label">
                Number of Prescriptions
            </td>

            <td>
                {{ $prescriptions->count() }}
            </td>

        </tr>

    </table>

</div>


{{-- Prescriptions --}}

@foreach($prescriptions as $prescription)

    <div class="prescription">

        <div class="prescription-title">

            Medical Prescription #{{ $loop->iteration }}

        </div>


        {{-- Prescription Information --}}

        <table class="info-table">

            <tr>

                <td class="label">
                    Doctor
                </td>

                <td>
                    {{ $prescription->doctor->user->first_name ?? 'N/A' }}
                    {{ $prescription->doctor->user->last_name ?? '' }}
                </td>

            </tr>


            <tr>

                <td class="label">
                    Department
                </td>

                <td>
                    {{ $prescription->doctor->section->name ?? 'N/A' }}
                </td>

            </tr>


            <tr>

                <td class="label">
                    Appointment Date
                </td>

                <td>
                    {{ optional($prescription->appointment)->start_at ?? 'N/A' }}
                </td>

            </tr>


            <tr>

                <td class="label">
                    Prescription Date
                </td>

                <td>
                    {{ optional($prescription->created_at)->format('Y-m-d H:i') ?? 'N/A' }}
                </td>

            </tr>

        </table>


        {{-- Medicines --}}

        <div class="medicines-title">
            Medicines
        </div>


        <table class="medicines-table">

            <thead>

            <tr>

                <th>
                    Medicine
                </th>

                <th>
                    Instructions
                </th>

            </tr>

            </thead>

            <tbody>

            @forelse($prescription->items as $item)

                <tr>

                    <td>
                        {{ $item->medicine_name }}
                    </td>

                    <td>
                        {{ $item->instructions }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="2">
                        No medicines found.
                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

@endforeach


<div class="footer">

    This document was generated automatically by the Clinic Management System.

</div>

</body>

</html>
