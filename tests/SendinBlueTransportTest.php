<?php

use Webup\LaravelSendinBlue\SendinBlueTransport;

class SendinBlueTransportTest extends PHPUnit_Framework_TestCase
{
    public function testSend()
    {
        $expected = [
            'to' => ['to@example.net' => 'to whom!'],
            'cc' => ['cc@example.net' => 'cc whom!'],
            'bcc' => ['bcc@example.net' => 'bcc whom!'],
            'from' => ['from@email.com', 'from email!'],
            'replyto' => ['replyto@email.com', 'reply to!'],
            'subject' => 'My subject',
            'text' => 'This is the text',
            'html' => 'This is the <h1>HTML</h1>',
            // 'attachment' => $attachment,
            // 'headers' => []
            // 'inline_image' => []
        ];

        $message = new Swift_Message();
        $message->setTo('to@example.net', 'to whom!');
        $message->setCc('cc@example.net', 'cc whom!');
        $message->setBcc('bcc@example.net', 'bcc whom!');
        $message->setFrom('from@email.com', 'from email!');
        $message->setReplyTo('replyto@email.com', 'reply to!');
        $message->setSubject('My subject');
        $message->setBody('This is the <h1>HTML</h1>', 'text/html');
        $message->addPart('This is the text', 'text/plain');

        $client = $this->getMockBuilder('Sendinblue\Mailin')
            ->disableOriginalConstructor()
            ->getMock();
        $client->method('send_email')->will($this->returnValue(['code' => 'success']));

        $transport = new SendinBlueTransport($client);

        $client->expects($this->once())
            ->method('send_email')
            ->with($this->equalTo($expected));

        $transport->send($message);
    }
}
