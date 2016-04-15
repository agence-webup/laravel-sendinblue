<?php

namespace Webup\LaravelSendinBlue;

use Illuminate\Mail\Transport\Transport;
use Sendinblue\Mailin;
use Swift_Mime_Message;

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

        if ($res['code'] != 'success') {
            throw new \Exception("Mail not sent : ".$res['message'], 1);
        }

        // Should return value is the number of recipients who were accepted for delivery.
        return 0;
    }

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
            foreach ($from as $key => $value) {
                $data['from'] = [$key, $value];
            }
        }

        $data['html'] = $message->getBody();
        $data['text'] = strip_tags($message->getBody());

        // @todo implements all mail api (text, cc, bcc, attachment...)
        // cf. https://apidocs.sendinblue.com/tutorial-sending-transactional-email/
        return $data;
    }
}
