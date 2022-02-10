<?php

namespace Webup\LaravelSendinBlue;

use Illuminate\Mail\Mailable;
use Swift_Message;

trait SendinBlue
{
    /**
     * @param null|array $extraFields
     * @return $this
     */
    public function sendinblue($extraFields)
    {
        if ($this instanceof Mailable && ($this->mailDriver() == "sendinblue" || $this->mailDriver() == "log")) {
            $this->withSwiftMessage(
                function (Swift_Message $message) use ($extraFields) {
                    $message->embed(
                        new \Swift_Image(
                            static::sgEncode($extraFields),
                            SendinBlueTransport::EXTRA_FIELDS_ENTITY_NAME
                        )
                    );
                }
            );
        }
        return $this;
    }

    /**
     * @return string
     */
    private function mailDriver()
    {
        return function_exists('config')
            ? config('mail.default', config('mail.driver')) : env('MAIL_MAILER', env('MAIL_DRIVER'));
    }

    /**
     * @param array $params
     * @return string
     */
    public static function sgEncode($params)
    {
        if (is_string($params)) {
            return $params;
        }
        return json_encode($params);
    }

    /**
     * @param string $strParams
     * @return array
     */
    public static function sgDecode($strParams)
    {
        if (!is_string($strParams)) {
            return (array)$strParams;
        }
        $params = json_decode($strParams, true);
        return is_array($params) ? $params : [];
    }
}
