<!DOCTYPE html>
<html>

<head>
    <title>Employee Profile - {{ $employee->first_name }} {{ $employee->last_name }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 30px;
        }

        .header {
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
            margin-bottom: 20px;
            display: table;
            width: 100%;
        }

        .header-content {
            display: table-cell;
            vertical-align: middle;
        }

        .institution-name {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }

        .report-title {
            font-size: 16px;
            color: #666;
            margin: 5px 0 0 0;
        }

        .profile-container {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .photo-cell {
            display: table-cell;
            width: 120px;
            vertical-align: top;
        }

        .info-cell {
            display: table-cell;
            vertical-align: top;
            padding-left: 20px;
        }

        .profile-photo {
            width: 120px;
            height: 120px;
            border-radius: 60px;
            object-fit: cover;
            border: 3px solid #f0f0f0;
        }

        .section-title {
            background-color: #f8f9fa;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 13px;
            border-left: 4px solid #007bff;
            margin: 20px 0 10px 0;
            text-transform: uppercase;
        }

        .detail-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }

        .detail-label {
            display: table-cell;
            width: 150px;
            color: #666;
            font-weight: bold;
        }

        .detail-value {
            display: table-cell;
            font-weight: normal;
        }

        .footer {
            position: fixed;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        .grid-container {
            display: table;
            width: 100%;
        }

        .grid-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .hobbies-box {
            background-color: #fff;
            border: 1px solid #eee;
            padding: 10px;
            min-height: 40px;
            font-style: italic;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <h1 class="institution-name">{{ $institution->name ?? 'ALBASAM COMPREHENSIVE SCHOOL' }}</h1>
                <p class="report-title">Official Employee Record</p>
            </div>
        </div>

        <div class="profile-container">
            <div class="photo-cell">
                @if($photoBase64)
                <img src="{{ $photoBase64 }}" class="profile-photo" alt="Photo">
                @else
                <div style="width: 120px; height: 120px; border-radius: 60px; background-color: #e9ecef; display: flex; align-items: center; justify-content: center; color: #adb5bd; font-size: 40px; font-weight: bold; line-height: 120px; text-align: center;">
                    {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                </div>
                @endif
            </div>
            <div class="info-cell">
                <h2 style="margin: 0 0 5px 0; font-size: 22px;">{{ $employee->honorific?->name }} {{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->last_name }}</h2>
                <p style="margin: 0; color: #007bff; font-weight: bold; font-size: 14px;">{{ $employee->staff_number }}</p>
                <div style="margin-top: 10px;">
                    <span style="background-color: {{ $employee->employmentStatus?->name == 'Active' ? '#d4edda' : '#f8d7da' }}; color: {{ $employee->employmentStatus?->name == 'Active' ? '#155724' : '#721c24' }}; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold;">
                        {{ $employee->employmentStatus?->name ?? 'Unknown' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid-container">
            <div class="grid-column" style="padding-right: 15px;">
                <div class="section-title">Personal Details</div>
                <div class="detail-row">
                    <div class="detail-label">Gender:</div>
                    <div class="detail-value">{{ $employee->gender?->name ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Marital Status:</div>
                    <div class="detail-value">{{ $employee->maritalStatus?->name ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Religion:</div>
                    <div class="detail-value">{{ $employee->religion?->name ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">ID Number:</div>
                    <div class="detail-value">{{ $employee->identification_number }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">KRA PIN:</div>
                    <div class="detail-value">{{ $employee->tax_identification_pin ?? 'N/A' }}</div>
                </div>
                @if($employee->tsc_number)
                <div class="detail-row">
                    <div class="detail-label">TSC Number:</div>
                    <div class="detail-value">{{ $employee->tsc_number }}</div>
                </div>
                @endif
            </div>
            <div class="grid-column" style="padding-left: 15px;">
                <div class="section-title">Contact Information</div>
                <div class="detail-row">
                    <div class="detail-label">Email:</div>
                    <div class="detail-value">{{ $employee->email ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Phone:</div>
                    <div class="detail-value">{{ $employee->primary_phone }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Secondary Phone:</div>
                    <div class="detail-value">{{ $employee->secondary_phone ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Postal Address:</div>
                    <div class="detail-value">{{ $employee->postal_address ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <div class="grid-container">
            <div class="grid-column" style="padding-right: 15px;">
                <div class="section-title">Employment Information</div>
                <div class="detail-row">
                    <div class="detail-label">Employment Type:</div>
                    <div class="detail-value">{{ $employee->employmentType?->name ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Date of Hire:</div>
                    <div class="detail-value">{{ $employee->date_of_hire ? \Carbon\Carbon::parse($employee->date_of_hire)->format('d M, Y') : 'N/A' }}</div>
                </div>
            </div>
            <div class="grid-column" style="padding-left: 15px;">
                <div class="section-title">Payroll Status</div>
                <div class="detail-row">
                    <div class="detail-label">In Payroll:</div>
                    <div class="detail-value">{{ $employee->in_payroll ? 'Yes' : 'No' }}</div>
                </div>
                @if($employee->in_payroll)
                <div class="detail-row">
                    <div class="detail-label">NSSF No:</div>
                    <div class="detail-value">{{ $employee->nssf_no ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">SHA No:</div>
                    <div class="detail-value">{{ $employee->sha_no ?? 'N/A' }}</div>
                </div>
                @endif
            </div>
        </div>

        @if($employee->hobbies)
        <div class="section-title">Hobbies & Interests</div>
        <div class="hobbies-box">
            {{ $employee->hobbies }}
        </div>
        @endif

        @if($employee->permanent_physical_address)
        <div class="section-title">Permanent Physical Address</div>
        <div style="background-color: #fff; border: 1px solid #eee; padding: 10px;">
            {{ $employee->permanent_physical_address }}
        </div>
        @endif

        <div class="footer">
            Printed on {{ $generated_at }} | Generated by School Management System
        </div>
    </div>
</body>

</html>