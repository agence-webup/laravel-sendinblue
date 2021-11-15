<?php

namespace Webup\LaravelSendinBlue;

use Illuminate\Mail\Transport\Transport;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\Model\SendSmtpEmail;
use SendinBlue\Client\Model\SendSmtpEmailAttachment;
use SendinBlue\Client\Model\SendSmtpEmailBcc;
use SendinBlue\Client\Model\SendSmtpEmailCc;
use SendinBlue\Client\Model\SendSmtpEmailReplyTo;
use SendinBlue\Client\Model\SendSmtpEmailSender;
use SendinBlue\Client\Model\SendSmtpEmailTo;
use Swift_Attachment;
use Swift_Image;
use Swift_MimePart;
use Swift_Mime_Headers_UnstructuredHeader;
use Swift_Mime_SimpleMessage;

class SendinBlueTransport extends Transport
{
    use SendinBlue;

    /**
     * The entity name for storing extra fields.
     *
     * @var string
     */
    const EXTRA_FIELDS_ENTITY_NAME = 'sendinblue/x-extra-fields';

    /**
     * The subject placeholder telling we want to use the subject defined in the template.
     *
     * With subject set to this value we actually unset the subject and subject header.
     * Otherwise, the library would use the default subject derived from the Mailable class name.
     *
     * @var string
     */
    const USE_TEMPLATE_SUBJECT = '___TEMPLATE_SUBJECT___';

    /**
     * The SendinBlue instance.
     *
     * @var \SendinBlue\Client\Api\TransactionalEmailsApi
     */
    protected $api;

    /**
     * Create a new SendinBlue transport instance.
     *
     * @param  \SendinBlue\Client\Api\TransactionalEmailsApi  $mailin
     * @return void
     */
    public function __construct(TransactionalEmailsApi $api)
    {
        $this->api = $api;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $this->api->sendTransacEmail($this->buildData($message));

        return 0;
    }

    /**
     * Transforms Swift_Message into SendinBlue's email
     * cf. https://github.com/sendinblue/APIv3-php-library/blob/master/docs/Model/SendSmtpEmail.md
     *
     * @param  Swift_Mime_SimpleMessage $message
     * @return SendinBlue\Client\Model\SendSmtpEmail
     */
    protected function buildData($message)
    {
        $smtpEmail = new SendSmtpEmail();

        if ($message->getFrom()) {
            $from = $message->getFrom();
            reset($from);
            $key = key($from);
            $smtpEmail->setSender(new SendSmtpEmailSender([
                'email' => $key,
                'name' => $from[$key],
            ]));
        }

        if ($message->getTo()) {
            $to = [];
            foreach ($message->getTo() as $email => $name) {
                $to[] = new SendSmtpEmailTo([
                    'email' => $email,
                    'name' => $name,
                ]);
            }
            $smtpEmail->setTo($to);
        }

        if ($message->getCc()) {
            $cc = [];
            foreach ($message->getCc() as $email => $name) {
                $cc[] = new SendSmtpEmailCc([
                    'email' => $email,
                    'name' => $name,
                ]);
            }
            $smtpEmail->setCC($cc);
        }

        if ($message->getBcc()) {
            $bcc = [];
            foreach ($message->getBcc() as $email => $name) {
                $bcc[] = new SendSmtpEmailBcc([
                    'email' => $email,
                    'name' => $name,
                ]);
            }
            $smtpEmail->setBcc($bcc);
        }

        // set content
        $html = null;
        $text = null;
        if ($message->getContentType() == 'text/plain') {
            $text = $message->getBody();
        } else {
            $html = $message->getBody();
        }

        $children = $message->getChildren();
        foreach ($children as $child) {
            if ($child instanceof Swift_MimePart && $child->getContentType() == 'text/plain') {
                $text = $child->getBody();
            }
        }

        if ($text === null) {
            $text = strip_tags($message->getBody());
        }

        if ($html !== null) {
            $smtpEmail->setHtmlContent($html);
        }
        $smtpEmail->setTextContent($text);
        // end set content

        if ($message->getSubject() !== self::USE_TEMPLATE_SUBJECT) {
            $smtpEmail->setSubject($message->getSubject());
        }

        // remove the subject if we want to use the one defined in the template
        if ($message->getSubject() === self::USE_TEMPLATE_SUBJECT) {
            $smtpEmail->setSubject(null);
        }

        if ($message->getReplyTo()) {
            $replyTo = [];
            foreach ($message->getReplyTo() as $email => $name) {
                $replyTo[] = new SendSmtpEmailReplyTo([
                    'email' => $email,
                    'name' => $name,
                ]);
            }
            $smtpEmail->setReplyTo(end($replyTo));
        }

        $attachment = [];
        foreach ($message->getChildren() as $child) {
            if ($child instanceof Swift_Attachment) {
                $attachment[] = new SendSmtpEmailAttachment([
                    'name' => $child->getFilename(),
                    'content' => chunk_split(base64_encode($child->getBody())),
                ]);
            }
        }
        if (count($attachment)) {
            $smtpEmail->setAttachment($attachment);
        }

        if ($message->getHeaders()) {
            $headers = [];

            foreach ($message->getHeaders()->getAll() as $header) {
                if ($header instanceof Swift_Mime_Headers_UnstructuredHeader) {
                    // ignore content type because it creates conflict with content type sets by sendinblue api
                    if (strtolower($header->getFieldName()) === 'content-type') {
                        continue;
                    }

                    // ignore subject header if we want to use the subject defined in the template
                    if (
                        strtolower($header->getFieldName()) === 'subject'
                        && $message->getSubject() === self::USE_TEMPLATE_SUBJECT
                    ) {
                        continue;
                    }

                    $headers[$header->getFieldName()] = $header->getValue();
                }
            }
            $smtpEmail->setHeaders($headers);
        }

        // read extra fields passed through sendinblue() method
        $extraFields = $this->getExtraFields($message);
        foreach ($extraFields as $key => $val) {
            switch ($key) {
                case 'template_id':
                    $smtpEmail->setTemplateId((int)$val);
                    // even though we have set the template id, the main library may complain about missing textContent
                    // [400] Client error: `POST https://api.sendinblue.com/v3/smtp/email` resulted in a
                    // `400 Bad Request` response: {"code":"missing_parameter","message":"textContent is missing"}
                    $smtpEmail->setTextContent('-');
                    continue 2;

                case 'tags':
                    if (is_array($val) && !empty($val)) {
                        $smtpEmail->setTags($val);
                    }
                    continue 2;

                case 'params':
                    if (is_array($val) && !empty($val)) {
                        $smtpEmail->setParams($val);
                    }
                    continue 2;
            }
        }

        return $smtpEmail;
    }

    private function getExtraFields($message)
    {
        foreach ($message->getChildren() as $attachment) {
            if (
                $attachment instanceof Swift_Image
                && in_array(self::EXTRA_FIELDS_ENTITY_NAME, [$attachment->getFilename()]
                )
            ) {
                return self::sgDecode($attachment->getBody());
            }
        }

        return [];
    }
}
