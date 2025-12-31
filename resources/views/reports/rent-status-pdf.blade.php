<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rent Status Report - {{ date('d/m/Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        h1 {
            color: #1e3c72;
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #1e3c72;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .expired {
            background-color: #f8d7da !important;
            color: #721c24;
        }
        .warning {
            background-color: #fff3cd !important;
            color: #856404;
        }
        .active {
            background-color: #d4edda !important;
            color: #155724;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>Rent Status Report</h1>
    <p><strong>Generated on:</strong> {{ $today->format('d F Y') }}</p>
    
    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Block</th>
                <th>Room</th>
                <th>Bed</th>
                <th>Rent Start Date</th>
                <th>Rent End Date</th>
                <th>Days Remaining</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($studentsWithStatus as $item)
            <tr class="{{ $item['status'] }}">
                <td>{{ $item['student']->full_name }}</td>
                <td>{{ $item['student']->room ? $item['student']->room->block->name : 'N/A' }}</td>
                <td>{{ $item['student']->room ? $item['student']->room->name : 'N/A' }}</td>
                <td>{{ $item['student']->bed ? $item['student']->bed->name : 'N/A' }}</td>
                <td>{{ $item['rent_start_date'] ? $item['rent_start_date']->format('d/m/Y') : 'N/A' }}</td>
                <td>{{ $item['rent_end_date'] ? $item['rent_end_date']->format('d/m/Y') : 'N/A' }}</td>
                <td>
                    @if($item['days_remaining'] !== null)
                        @if($item['days_remaining'] < 0)
                            {{ abs($item['days_remaining']) }} days overdue
                        @else
                            {{ $item['days_remaining'] }} days
                        @endif
                    @else
                        N/A
                    @endif
                </td>
                <td>{{ ucfirst($item['status']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        <p>ISACK HOSTEL - Rent Status Report | Generated on {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
