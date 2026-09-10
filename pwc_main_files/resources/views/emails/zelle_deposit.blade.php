<!DOCTYPE html>
<html>
<head>
    <title>Zelle Deposit Notification</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <h2 style="color: #00ADEE; text-align: center;">Zelle Deposit Notification</h2>

        <p>Hello Paul</p>
        <p>A deposit was just marked as paid via <strong>Zelle</strong>. Details below.</p>

        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold; background: #f9f9f9;">Staff Name</td>
                <td style="padding: 10px; border: 1px solid #ddd;">{{ $data['staff_name'] }}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold; background: #f9f9f9;">Date Range</td>
                <td style="padding: 10px; border: 1px solid #ddd;">{{ $data['date_range'] }}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold; background: #f9f9f9;">Date of Deposit</td>
                <td style="padding: 10px; border: 1px solid #ddd;">{{ $data['deposit_date'] }}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold; background: #e9ecef; color: #000;">Amount</td>
                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold; background: #e9ecef; color: #000;">${{ number_format($data['amount'], 2) }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
