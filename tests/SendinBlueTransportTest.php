<?php

use PHPUnit\Framework\TestCase;
use Webup\LaravelSendinBlue\SendinBlueTransport;

class SendinBlueTransportTest extends TestCase
{
    public function testSend()
    {
        $expected = file_get_contents('./tests/request.json');

        $message = new Swift_Message();
        $message->setTo('to@example.net', 'to whom!');
        $message->setCc('cc@example.net', 'cc whom!');
        $message->setBcc('bcc@example.net', 'bcc whom!');
        $message->setFrom('from@email.com', 'from email!');
        $message->setReplyTo('replyto@email.com', 'reply to!');
        $message->setSubject('My subject');
        $message->setBody('This is the <h1>HTML</h1>', 'text/html');
        $message->addPart('This is the text', 'text/plain');
        $message->getHeaders()->addTextHeader('X-Mailin-Tag', 'test');

        $client = $this->getMockBuilder('SendinBlue\Client\Api\TransactionalEmailsApi')
            ->disableOriginalConstructor()
            ->getMock();

        $transport = new SendinBlueTransport($client);

        $client->expects($this->once())
            ->method('sendTransacEmail')
            ->with(new ToStringIsEqual($expected));

        $transport->send($message);
    }
}
