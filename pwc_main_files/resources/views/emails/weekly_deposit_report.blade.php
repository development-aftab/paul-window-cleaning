<!DOCTYPE html>
<html>
<head>
    <title>Weekly Deposit Report</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 700px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <h2 style="color: #00ADEE; text-align: center;">Weekly Deposit Report</h2>

        <p>Hello Wise Eyes Bookkeeping,</p>
        <p>Here are the deposits recorded for <strong>{{ $data['period_label'] }}</strong> (Monday through Saturday).</p>

        @if(count($data['rows']) === 0)
            <p style="text-align: center; padding: 20px; color: #777;">No deposits were recorded for this period.</p>
        @else
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr>
                        <th style="padding: 10px; border: 1px solid #ddd; background: #f9f9f9; text-align: left;">Staff Name</th>
                        <th style="padding: 10px; border: 1px solid #ddd; background: #f9f9f9; text-align: left;">Date Range</th>
                        <th style="padding: 10px; border: 1px solid #ddd; background: #f9f9f9; text-align: left;">Date of Deposit</th>
                        <th style="padding: 10px; border: 1px solid #ddd; background: #f9f9f9; text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['rows'] as $row)
                        <tr>
                            <td style="padding: 10px; border: 1px solid #ddd;">{{ $row['staff_name'] }}</td>
                            <td style="padding: 10px; border: 1px solid #ddd;">{{ $row['date_range'] }}</td>
                            <td style="padding: 10px; border: 1px solid #ddd;">{{ $row['deposit_date'] }}</td>
                            <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">${{ number_format($row['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="padding: 10px; border: 1px solid #ddd; font-weight: bold; background: #e9ecef; color: #000;">Total</td>
                        <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold; background: #e9ecef; color: #000; text-align: right;">${{ number_format($data['total'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>
</body>
</html>
