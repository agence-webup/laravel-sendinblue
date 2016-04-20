<?php

namespace Webup\LaravelSendinBlue;

use Illuminate\Mail\Transport\Transport;
use Sendinblue\Mailin;
use Swift_Attachment;
use Swift_Mime_Message;
use Swift_MimePart;

class SendinBlueTransport extends Transport
{
    /**
     * The SendinBlue instance.
     *
     * @var \Sendinblue\Mailin
     */
    protected $mailin;

    /**
     * Create a new SendinBlue transport instance.
     *
     * @param  \Sendinblue\Mailin  $mailin
     * @return void
     */
    public function __construct(Mailin $mailin)
    {
        $this->mailin = $mailin;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $res = $this->mailin->send_email($this->buildData($message));

        if (!$res) {
            throw new SendinBlueException("Unknown error");
        }

        if ($res['code'] != 'success') {
            throw new SendinBlueException($res['message']);
        }

        // Should return the number of recipients who were accepted for delivery.
        return 0;
    }

    /**
     * Transforms Swift_Message into data array for SendinBlue's API
     * cf. https://apidocs.sendinblue.com/tutorial-sending-transactional-email/
     *
     * @todo implements headers, inline_image
     * @param  Swift_Mime_Message $message
     * @return array
     */
    protected function buildData($message)
    {
        $data = [];

        if ($message->getTo()) {
            $data['to'] = $message->getTo();
        }

        if ($message->getSubject()) {
            $data['subject'] = $message->getSubject();
        }

        if ($message->getFrom()) {
            $from = $message->getFrom();
            reset($from);
            $key = key($from);
            $data['from'] = [$key, $from[$key]];
        }

        // set content
        if ($message->getContentType() == 'text/plain') {
            $data['text'] = $message->getBody();
        } else {
            $data['html'] = $message->getBody();
        }

        $children = $message->getChildren();
        foreach ($children as $child) {
            if ($child instanceof Swift_MimePart && $child->getContentType() == 'text/plain') {
                $data['text'] = $child->getBody();
            }
        }

        if (! isset($data['text'])) {
            $data['text'] = strip_tags($message->getBody());
        }
        // end set content

        if ($message->getCc()) {
            $data['cc'] = $message->getCc();
        }

        if ($message->getBcc()) {
            $data['bcc'] = $message->getBcc();
        }

        if ($message->getReplyTo()) {
            $replyTo = $message->getReplyTo();
            reset($replyTo);
            $key = key($replyTo);
            $data['replyto'] = [$key, $replyTo[$key]];
        }

        // attachment
        $attachment = [];
        foreach ($children as $child) {
            if ($child instanceof Swift_Attachment) {
                $filename = $child->getFilename();
                $content = chunk_split(base64_encode($child->getBody()));
                $attachment[$filename] = $content;
            }
        }

        if (count($attachment)) {
            $data['attachment'] = $attachment;
        }

        return $data;
    }
}
