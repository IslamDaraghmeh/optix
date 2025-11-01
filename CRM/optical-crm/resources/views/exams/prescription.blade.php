<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('app.eye_prescription') }} - {{ $exam->patient->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #31B6AC;
            padding-bottom: 20px;
        }

        .clinic-name {
            font-size: 24px;
            font-weight: bold;
            color: #31B6AC;
            margin-bottom: 10px;
        }

        .prescription-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .patient-info {
            margin-bottom: 30px;
        }

        .patient-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .patient-info td {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }

        .patient-info td:first-child {
            font-weight: bold;
            width: 120px;
        }

        .prescription-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .prescription-table th,
        .prescription-table td {
            border: 1px solid #333;
            padding: 10px;
            text-align: center;
        }

        .prescription-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .eye-label {
            font-weight: bold;
            background-color: #31B6AC;
            color: white;
        }

        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #31B6AC;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .doctor-signature {
            margin-top: 40px;
            text-align: right;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            width: 200px;
            display: inline-block;
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="clinic-name">{{ __('app.optical_crm') }}</div>
        <div>{{ __('app.eye_care_clinic') }}</div>
        <div style="font-size: 12px; color: #666; margin-top: 10px;">
            {{ __('app.clinic_address') }}<br>
            {{ __('app.clinic_contact') }}
        </div>
    </div>

    <div class="prescription-title">{{ strtoupper(__('app.eye_prescription')) }}</div>

    <div class="patient-info">
        <table>
            <tr>
                <td>{{ __('app.patient_name') }}:</td>
                <td>{{ $exam->patient->name }}</td>
            </tr>
            <tr>
                <td>{{ __('app.birth_date') }}:</td>
                <td>{{ $exam->patient->birth_date ? $exam->patient->birth_date->format('M d, Y') : __('app.not_provided') }}
                </td>
            </tr>
            <tr>
                <td>{{ __('app.phone') }}:</td>
                <td>{{ $exam->patient->phone }}</td>
            </tr>
            @if($exam->patient->email)
                <tr>
                    <td>{{ __('app.email') }}:</td>
                    <td>{{ $exam->patient->email }}</td>
                </tr>
            @endif
            <tr>
                <td>{{ __('app.exam_date') }}:</td>
                <td>{{ $exam->exam_date->format('M d, Y') }}</td>
            </tr>
            <tr>
                <td>{{ __('app.prescription_id') }}:</td>
                <td>#{{ str_pad($exam->id, 6, '0', STR_PAD_LEFT) }}</td>
            </tr>
        </table>
    </div>

    <table class="prescription-table">
        <thead>
            <tr>
                <th class="eye-label">{{ __('app.eye') }}</th>
                <th>{{ __('app.sphere') }} (SPH)</th>
                <th>{{ __('app.cylinder') }} (CYL)</th>
                <th>{{ __('app.axis') }}</th>
                <th>{{ __('app.prism') }}</th>
                <th>{{ __('app.base') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="eye-label">{{ __('app.right_eye_od') }}</td>
                <td>{{ $exam->right_eye_sphere ? ($exam->right_eye_sphere > 0 ? '+' : '') . $exam->right_eye_sphere : '-' }}
                </td>
                <td>{{ $exam->right_eye_cylinder ? ($exam->right_eye_cylinder > 0 ? '+' : '') . $exam->right_eye_cylinder : '-' }}
                </td>
                <td>{{ $exam->right_eye_axis ? $exam->right_eye_axis . '°' : '-' }}</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td class="eye-label">{{ __('app.left_eye_os') }}</td>
                <td>{{ $exam->left_eye_sphere ? ($exam->left_eye_sphere > 0 ? '+' : '') . $exam->left_eye_sphere : '-' }}
                </td>
                <td>{{ $exam->left_eye_cylinder ? ($exam->left_eye_cylinder > 0 ? '+' : '') . $exam->left_eye_cylinder : '-' }}
                </td>
                <td>{{ $exam->left_eye_axis ? $exam->left_eye_axis . '°' : '-' }}</td>
                <td>-</td>
                <td>-</td>
            </tr>
        </tbody>
    </table>

    @if($exam->notes)
        <div class="notes">
            <strong>{{ __('app.notes') }}:</strong><br>
            {{ $exam->notes }}
        </div>
    @endif

    <div class="doctor-signature">
        <div class="signature-line"></div><br>
        <div>{{ __('app.doctor_name_placeholder') }}</div>
        <div style="font-size: 12px; color: #666;">{{ __('app.optometrist_ophthalmologist') }}</div>
        <div style="font-size: 12px; color: #666;">{{ __('app.license_number_placeholder') }}</div>
    </div>

    <div class="footer">
        <p><strong>{{ __('app.important') }}:</strong> {{ __('app.prescription_validity') }}</p>
        <p>{{ __('app.prescription_consultation_note') }}</p>
        <p>{{ __('app.generated_on') }} {{ now()->format('M d, Y \a\t g:i A') }}</p>
    </div>
</body>

</html>
