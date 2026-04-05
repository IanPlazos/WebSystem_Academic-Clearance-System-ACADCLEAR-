<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Account Credentials</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f7fb;font-family:Arial,sans-serif;color:#1f2937;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f5f7fb;padding:24px 12px;">
        <tr>
            <td align="center">
                <table width="640" cellpadding="0" cellspacing="0" border="0" style="max-width:640px;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="background:#2f5ed8;color:#ffffff;padding:20px 24px;">
                            <h1 style="margin:0;font-size:22px;line-height:1.3;">Welcome to AcadClear</h1>
                            <p style="margin:8px 0 0 0;font-size:14px;opacity:0.95;">Your university tenant account is now ready.</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 14px 0;font-size:15px;">Hello,</p>
                            <p style="margin:0 0 16px 0;font-size:15px;line-height:1.6;">
                                The super administrator has created your university environment in AcadClear.
                                Below are your admin credentials and subscription details.
                            </p>

                            <h2 style="margin:20px 0 10px 0;font-size:17px;color:#2f5ed8;">Admin Credentials</h2>
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                                <tr>
                                    <td style="padding:10px 12px;background:#f9fafb;width:35%;font-weight:700;font-size:14px;">University</td>
                                    <td style="padding:10px 12px;font-size:14px;">{{ $tenantName }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;background:#f9fafb;font-weight:700;font-size:14px;">Email</td>
                                    <td style="padding:10px 12px;font-size:14px;">{{ $adminEmail }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;background:#f9fafb;font-weight:700;font-size:14px;">Password</td>
                                    <td style="padding:10px 12px;font-size:14px;">{{ $adminPassword }}</td>
                                </tr>
                            </table>

                            <h2 style="margin:24px 0 10px 0;font-size:17px;color:#2f5ed8;">Subscription Details</h2>
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                                <tr>
                                    <td style="padding:10px 12px;background:#f9fafb;width:35%;font-weight:700;font-size:14px;">Plan</td>
                                    <td style="padding:10px 12px;font-size:14px;">{{ $planName }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;background:#f9fafb;font-weight:700;font-size:14px;">Amount Paid</td>
                                    <td style="padding:10px 12px;font-size:14px;">PHP {{ number_format($amountPaid, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;background:#f9fafb;font-weight:700;font-size:14px;">Payment Method</td>
                                    <td style="padding:10px 12px;font-size:14px;">{{ strtoupper(str_replace('_', ' ', $paymentMethod)) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;background:#f9fafb;font-weight:700;font-size:14px;">Start Date</td>
                                    <td style="padding:10px 12px;font-size:14px;">{{ $startsAt->format('F d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;background:#f9fafb;font-weight:700;font-size:14px;">End Date</td>
                                    <td style="padding:10px 12px;font-size:14px;">{{ $endsAt->format('F d, Y') }}</td>
                                </tr>
                            </table>

                            <h2 style="margin:24px 0 10px 0;font-size:17px;color:#2f5ed8;">Access Information</h2>
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                                <tr>
                                    <td style="padding:10px 12px;background:#f9fafb;width:35%;font-weight:700;font-size:14px;">Domain</td>
                                    <td style="padding:10px 12px;font-size:14px;">{{ $domain }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;background:#f9fafb;font-weight:700;font-size:14px;">Login URL</td>
                                    <td style="padding:10px 12px;font-size:14px;word-break:break-all;">
                                        <a href="{{ $loginUrl }}" style="color:#2f5ed8;text-decoration:none;">{{ $loginUrl }}</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0 0;font-size:13px;color:#6b7280;line-height:1.6;">
                                For security, please log in immediately and change the password.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
