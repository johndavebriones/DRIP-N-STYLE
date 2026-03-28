<?php
// Email configuration for authentication (forgot password, etc.)
// Using PHPMailer for sending emails

return [
    // SMTP Configuration
    'smtp' => [
        'host'     => 'smtp.gmail.com', // Replace with your SMTP host (e.g., smtp.gmail.com for Gmail)
        'port'     => 587,              // 587 for TLS, 465 for SSL
        'username' => 'johndavealdaybriones009@gmail.com', // Your email address
        'password' => 'ncko vorv chhs dmwt',    // Your email password or app-specific password
        'encryption' => 'tls',          // 'tls' or 'ssl'
        'auth'     => true,             // Enable SMTP authentication
    ],

    // Email Settings
    'from' => [
        'email' => 'johndavealdaybriones009@gmail.com', // Sender email
        'name'  => 'DRIP-N-STYLE Support', // Sender name
    ],

    // Forgot Password Email Template
    'forgot_password' => [
        'subject' => 'Password Reset Request - DRIP-N-STYLE',
        'body'    => '
            <!DOCTYPE html>
            <html>
            <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            </head>
            <body style="margin:0;padding:20px;background:#f5f0eb;font-family:Arial,sans-serif;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f0eb;padding:40px 20px;">
                <tr><td align="center">
                <table width="520" cellpadding="0" cellspacing="0" style="background:#faf8f5;border:1px solid #e8e0d8;border-radius:3px;overflow:hidden;">

                    <tr><td style="background:linear-gradient(135deg,#b8934a 0%,#d4a84b 50%,#c9a96e 100%);padding:3px 0;"></td></tr>

                    <tr>
                    <td style="padding:40px 48px 30px;border-bottom:1px solid #ede8e2;background:#faf8f5;">
                        <div style="font-family:Georgia,serif;font-size:21px;font-weight:700;letter-spacing:6px;color:#b8934a;text-transform:uppercase;">DRIP-N-STYLE</div>
                        <div style="height:1px;background:linear-gradient(90deg,#c9a96e 0%,transparent 100%);margin-top:6px;width:160px;"></div>
                    </td>
                    </tr>

                    <tr>
                    <td style="padding:44px 48px 36px;background:#faf8f5;">
                        <div style="font-family:Georgia,serif;font-size:25px;color:#2d2520;font-weight:400;line-height:1.25;margin-bottom:4px;">Password Reset</div>
                        <div style="font-family:Georgia,serif;font-size:25px;color:#b8934a;font-weight:700;line-height:1.25;margin-bottom:26px;">Request</div>
                        <div style="height:1px;background:#ede8e2;margin-bottom:26px;"></div>

                        <p style="font-size:14px;color:#5c4f44;line-height:1.85;margin:0 0 10px;">Hello,</p>
                        <p style="font-size:14px;color:#6b5e54;line-height:1.85;margin:0 0 30px;font-weight:300;">
                        We received a request to reset the password for your
                        <span style="color:#b8934a;font-weight:500;">DRIP-N-STYLE</span> account.
                        Click the button below to create a new password.
                        </p>

                        <table cellpadding="0" cellspacing="0" style="margin:8px 0 36px;">
                        <tr>
                            <td style="background:linear-gradient(135deg,#b8934a,#d4a84b);border-radius:2px;">
                            <a href="{reset_link}" style="display:inline-block;padding:15px 44px;font-size:12px;font-weight:500;letter-spacing:3px;text-transform:uppercase;color:#fff;text-decoration:none;">Reset Password</a>
                            </td>
                        </tr>
                        </table>

                        <table cellpadding="0" cellspacing="0" style="background:#f2ece4;border-left:2px solid #c9a96e;margin-bottom:28px;width:100%;">
                        <tr>
                            <td style="padding:14px 18px;">
                            <p style="font-size:12px;color:#8a7060;margin:0;line-height:1.75;font-weight:300;">
                                This link will expire in <span style="color:#b8934a;font-weight:500;">1 hour</span>.
                                If you did not request this, you can safely ignore this email — your account remains secure.
                            </p>
                            </td>
                        </tr>
                        </table>

                        <p style="font-size:11px;color:#b0a090;line-height:1.8;margin:0;word-break:break-all;">
                        If the button does not work, copy and paste this link into your browser:<br>
                        <span style="color:#b8934a;">{reset_link}</span>
                        </p>
                    </td>
                    </tr>

                    <tr>
                    <td style="background:#f2ece4;padding:26px 48px;border-top:1px solid #e4dbd0;">
                        <p style="font-size:11px;color:#b0a090;margin:0 0 5px;letter-spacing:2px;text-transform:uppercase;">DRIP-N-STYLE Team</p>
                        <p style="font-size:11px;color:#c0b5a8;margin:0;line-height:1.8;">This is an automated message. Please do not reply directly to this email.</p>
                    </td>
                    </tr>

                    <tr><td style="background:linear-gradient(135deg,#b8934a 0%,#d4a84b 50%,#c9a96e 100%);padding:2px 0;"></td></tr>

                </table>
                </td></tr>
            </table>
            </body>
            </html>
        ',
    ],
];
?>