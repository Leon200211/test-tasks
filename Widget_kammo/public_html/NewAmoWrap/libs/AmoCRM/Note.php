<?php
/**
 * Created by PhpStorm.
 * User: Roistat
 * Date: 17.09.17
 * Time: 20:28
 */

namespace Roistat\AmoCRM_Wrap;

use stdClass;

/**
 * Class Note
 * @package Roistat\AmoCRM_Wrap
 */
class Note extends BaseEntity
{
    /**
     * @var bool
     */
    private $editable;

    /**
     * @var string
     */
    private $attachment;

    /**
     * @var string
     */
    private $service;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var array
     * @see https://www.amocrm.ru/developers/content/crm_platform/events-and-notes
     */
    private $params;

    const TYPE_COMMON  = 'common';
    const TYPE_CALL_IN  = 'call_in';
    const TYPE_CALL_OUT  = 'call_out';
    const TYPE_SMS_IN  = 'sms_in';
    const TYPE_SMS_OUT  = 'sms_out';
    const TYPE_SERVICE_MESSAGE  = 'service_message';
    const TYPE_MESSAGE_CASHIER  = 'message_cashier';
    const TYPE_INVOICE_PAID  = 'invoice_paid';
    const TYPE_GEOLOCATION  = 'geolocation';
    const TYPE_EXTENDED_SERVICE_MESSAGE  = 'extended_service_message';

    /**
     * @param stdClass $data
     *
     * @return Note
     *
     * @throws AmoWrapException
     */
    public function loadInRaw($data)
    {
        BaseEntity::loadInRaw($data);
        $this->editable = $data->is_editable;
        $this->attachment = $data->attachment;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        return $this->editable;
    }

    /**
     * @return string
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * @param string $attachment
     *
     * @return Note
     */
    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;

        return $this;
    }

    /**
     * @param string $service
     *
     * @return Note
     */
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * @param $phone
     *
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @param string $userId
     *
     * @return $this
     */
    public function setCreatedUser($userId)
    {
        $this->createdUserId = $userId;

        return $this;
    }

    /**
     * @return array
     */
    protected function getExtraRaw()
    {
        $list = array(
            'element_id'   => $this->elementId,
            'element_type' => $this->elementType,
            'note_type'    => $this->type,
            'text'         => $this->text,
            'created_by'   => $this->createdUserId,
            'params'       => array(
                'text'    => $this->text
            ),
        );

        if(!is_null($this->service)) {
            $list['service'] = $this->service;
        }

        if(!is_null($this->phone)) {
            $list['phone'] = $this->phone;
        }

        return $list;
    }
}