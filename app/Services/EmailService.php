<?php

/**
 * EmailService.php
 *
 * @author Ruban Edward
 * @category Service
 *
 * This is the mail service class. This class helps to send emails
 * to members added.
 */

namespace App\Services;

class EmailService
{
    protected $email;

    /**
     * Constructor to initialize the email service
     */
    public function __construct()
    {
        $this->email = \Config\Services::email();
    }

    /**
     * Method to send an email using the provided email data
     *
     * @param array $emailData Array containing email details
     * @return bool Returns true if the email was sent successfully, false otherwise
     */
    public function sendMail($emailData)
    {

        $response = json_encode(['success' => true, 'mail' => true]);
        if (function_exists('fastcgi_finish_request')) {
            echo $response;
            fastcgi_finish_request();
        } else {
            echo $response;
            ob_end_clean();
            ob_start();
            header("Connection: close");
            header('Content-Encoding: none');
            ignore_user_abort(true);
            $size = ob_get_length() + 1;
            header("Content-Length: $size");
            ob_end_flush();
            ob_flush();
            flush();
        }
        $this->asynMail($emailData);

    }

    public function asynMail($emailData)
    {
        if (is_array($emailData['email_id'])) {
            if (count($emailData['email_id']) > 1) {
                $emailAddresses = implode(',', $emailData['email_id']);
            } elseif (count($emailData['email_id']) === 1) {
                $emailAddresses = $emailData['email_id'][0];
            }
        } elseif (is_string($emailData['email_id'])) {
            $emailAddresses = $emailData['email_id'];
        }

        if (empty($emailAddresses)) {
            log_message('error', 'Email "' . ($emailData['fileName'] ?? '') . '" not sent: no recipients.');

            return false;
        }

        // On dev/test, set email.redirectTo in .env to send every email to one test inbox instead
        // of the real recipients. Leave it unset in production.
        $redirectTo = trim((string) env('email.redirectTo', ''));
        if ($redirectTo !== '') {
            log_message('info', 'Email redirected to test inbox (intended for: ' . $emailAddresses . ').');
        }
        $this->email->setTo($redirectTo !== '' ? $redirectTo : $emailAddresses);
        $this->email->setSubject($emailData['contents']['subject']);
        $this->email->setMailType('html');

        $templatePath = APPPATH . 'Views/mailTemplate/' . $emailData['fileName'] . '.php';
        if (file_exists($templatePath)) {
            // Use output buffering to capture the contents of the PHP file as a string
            ob_start();
            include $templatePath;
            $htmlTemplate = ob_get_clean();

            // Replace placeholders with actual content
            $htmlContent = $this->replacePlaceholders($htmlTemplate, $emailData['contents']);
            // echo $htmlContent;

            // Set the HTML content as the message
            $this->email->setMessage($htmlContent);
        } else {
            throw new \Exception("Email template not found: " . $templatePath);
        }

        // Optional in-memory attachments, e.g. a meeting invite from IcsBuilder:
        // ['attachments' => [['content' => ..., 'name' => 'invite.ics', 'mime' => 'text/calendar']]]
        foreach ($emailData['attachments'] ?? [] as $file) {
            $this->email->attach($file['content'], 'attachment', $file['name'], $file['mime'] ?? 'application/octet-stream');
        }

        // Send the email
        return $this->email->send();
    }

    /**
     * Method to replace placeholders in the email template with actual data
     *
     * @param string $template The email template with placeholders
     * @param array $data Array containing the actual data to replace the placeholders
     * @return string The email template with placeholders replaced by actual data
     */
    protected function replacePlaceholders($template, $data)
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }
}
