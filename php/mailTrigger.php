<?php

require_once("./vendor/autoload.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class sndMail
{
    public function contactEnquiry($data)
    {
        try {

            $mail = new PHPMailer(true);


            // SMTP Settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'contact@blackitechs.com';
            $mail->Password   = 'YOUR_NEW_PASSWORD';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;


            // UTF-8
            $mail->CharSet = "UTF-8";


            // From
            $mail->setFrom(
                'contact@blackitechs.com',
                'Blood Test Consultancy'
            );


            // Admin Mail
            $mail->addAddress(
                'soundarya.ramesh0712@gmail.com'
            );


            $mail->isHTML(true);


            $mail->Subject = "New Consultation Request";


            $mail->Body = '

<div style="max-width:700px;margin:auto;font-family:Arial,sans-serif;background:#f4f6f8;padding:20px;">


    <div style="background:#00984a;padding:25px;text-align:center;border-radius:8px 8px 0 0;">

        <h1 style="color:#ffffff;margin:0;">
            Blood Test Consultancy
        </h1>

        <p style="color:#e5e7eb;margin:5px 0 0;">
            New Consultation Enquiry Received
        </p>

    </div>



    <div style="background:#ffffff;padding:30px;border-radius:0 0 8px 8px;">


        <p style="font-size:16px;color:#333;">
            You have received a new enquiry from your website contact form.
        </p>



        <div style="background:#eef2ff;padding:12px 15px;border-left:4px solid #00984a;margin-bottom:20px;">

            <strong>
                Enquiry Details
            </strong>

        </div>




        <table style="width:100%;border-collapse:collapse;font-size:14px;">


            <tr>

                <td style="padding:12px;background:#f9fafb;border:1px solid #e5e7eb;width:30%;">
                    Name
                </td>

                <td style="padding:12px;border:1px solid #e5e7eb;">
                    '.$data['name'].'
                </td>

            </tr>



            <tr>

                <td style="padding:12px;background:#f9fafb;border:1px solid #e5e7eb;">
                    Phone Number
                </td>

                <td style="padding:12px;border:1px solid #e5e7eb;">
                    '.$data['phone'].'
                </td>

            </tr>



            <tr>

                <td style="padding:12px;background:#f9fafb;border:1px solid #e5e7eb;">
                    Email
                </td>

                <td style="padding:12px;border:1px solid #e5e7eb;">
                    '.$data['email'].'
                </td>

            </tr>



            <tr>

                <td style="padding:12px;background:#f9fafb;border:1px solid #e5e7eb;">
                    services
                </td>

                <td style="padding:12px;border:1px solid #e5e7eb;">
                    '.$data['services'].'
                </td>

            </tr>



            <tr>

                <td style="padding:12px;background:#f9fafb;border:1px solid #e5e7eb;vertical-align:top;">
                    Message
                </td>

                <td style="padding:12px;border:1px solid #e5e7eb;">
                    '.nl2br(htmlspecialchars($data['message'])).'
                </td>

            </tr>


        </table>




        <div style="margin-top:25px;padding:15px;background:#fff7ed;border-left:4px solid #f59e0b;">

            <strong style="color:#92400e;">
                Action Required:
            </strong>

            <p style="margin:8px 0 0;color:#78350f;">
                Please contact the customer as soon as possible.
            </p>

        </div>


    </div>



    <p style="text-align:center;color:#6b7280;font-size:12px;margin-top:15px;">

        © '.date('Y').' Blood Test Consultancy

    </p>


</div>';



            $mail->send();



            return [
                "success" => true,
                "message" => "Message sent successfully."
            ];



        } catch (Exception $e) {


            return [
                "success" => false,
                "message" => $mail->ErrorInfo
            ];

        }
    }
}

?>