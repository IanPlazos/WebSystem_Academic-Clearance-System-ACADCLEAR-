
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Clearance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #4e73df;
            font-size: 24px;
        }
        .header h3 {
            margin: 5px 0;
            color: #858796;
            font-weight: normal;
            font-size: 16px;
        }
        .report-info {
            margin-bottom: 20px;
            padding: 10px;
            background: #f8f9fc;
            border: 1px solid #e3e6f0;
        }
        .report-info p {
            margin: 5px 0;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #fff;
            border: 1px solid #e3e6f0;
            border-left: 4px solid #4e73df;
            padding: 15px;
        }
        .stat-card .label {
            font-size: 12px;
            color: #858796;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .stat-card .value {
            font-size: 20px;
            font-weight: bold;
            color: #5a5c69;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #4e73df;
            color: white;
            font-weight: bold;
            padding: 10px;
            text-align: left;
            font-size: 11px;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #e3e6f0;
        }
        .text-success { color: #1cc88a; }
        .text-warning { color: #f6c23e; }
        .text-danger { color: #e74a3b; }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #858796;
        }
        .progress {
            width: 100px;
            height: 8px;
            background: #eaecf4;
            border-radius: 4px;
            display: inline-block;
            margin-right: 10px;
        }
        .progress-bar {
            height: 100%;
            background: #1cc88a;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>AcadClear - {{ $tenantName ?? config('app.name', 'AcadClear') }}</h1>
        <h3>Clearance System Report</h3>
    </div>

    <div class="report-info">
        <p><strong>Report Generated:</strong> {{ now()->format('F d, Y H:i:s') }}</p>
        <p><strong>Date Range:</strong> {{ date('F d, Y', strtotime($dateFrom)) }} - {{ date('F d, Y', strtotime($dateTo)) }}</p>
        @if(isset($college))
            <p><strong>College:</strong> {{ $college->name }}</p>
        @else
            <p><strong>College:</strong> All Colleges</p>
        @endif
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="label">Total Clearances</div>
            <div class="value">{{ $stats['total_clearances'] }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Students Served</div>
            <div class="value">{{ $stats['students_served'] }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Completion Rate</div>
            <div class="value">{{ $stats['completion_rate'] }}%</div>
        </div>
        <div class="stat-card">
            <div class="label">Pending</div>
            <div class="value">{{ $stats['pending'] }}</div>
        </div>
    </div>

    <h4>Status Summary</h4>
    <table>
        <tr>
            <th>Status</th>
            <th>Count</th>
            <th>Percentage</th>
        </tr>
        <tr>
            <td class="text-success">Approved</td>
            <td>{{ $stats['approved'] }}</td>
            <td>{{ $stats['total_clearances'] > 0 ? round(($stats['approved'] / $stats['total_clearances']) * 100, 2) : 0 }}%</td>
        </tr>
        <tr>
            <td class="text-warning">Pending</td>
            <td>{{ $stats['pending'] }}</td>
            <td>{{ $stats['total_clearances'] > 0 ? round(($stats['pending'] / $stats['total_clearances']) * 100, 2) : 0 }}%</td>
        </tr>
        <tr>
            <td class="text-danger">Rejected</td>
            <td>{{ $stats['rejected'] }}</td>
            <td>{{ $stats['total_clearances'] > 0 ? round(($stats['rejected'] / $stats['total_clearances']) * 100, 2) : 0 }}%</td>
        </tr>
    </table>

    <h4>Department Performance</h4>
    <table>
        <thead>
            <tr>
                <th>College</th>
                <th>Department</th>
                <th>Total</th>
                <th>Approved</th>
                <th>Pending</th>
                <th>Rejected</th>
                <th>Rate</th>
                <th>Avg Response</th>
            </tr>
        </thead>
        <tbody>
            @foreach($departmentPerformance as $dept)
            <tr>
                <td>{{ $dept['college'] }}</td>
                <td>{{ $dept['name'] }}</td>
                <td>{{ $dept['total'] }}</td>
                <td class="text-success">{{ $dept['approved'] }}</td>
                <td class="text-warning">{{ $dept['pending'] }}</td>
                <td class="text-danger">{{ $dept['rejected'] }}</td>
                <td>
                    <div style="display: flex; align-items: center;">
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ $dept['rate'] }}%"></div>
                        </div>
                        {{ $dept['rate'] }}%
                    </div>
                </td>
                <td>{{ $dept['avg_response_time'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the AcadClear Academic Clearance System.</p>
        <p>{{ $tenantName ?? config('app.name', 'AcadClear') }} - All rights reserved.</p>
    </div>
</body>
</html>