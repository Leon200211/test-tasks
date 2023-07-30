<?php
/**
 * Created by PhpStorm.
 * User: Roistat
 * Date: 11.09.17
 * Time: 16:07
 */

namespace Roistat\AmoCRM_Wrap;

use stdClass;

/**
 * Class Lead
 * @package Roistat\AmoCRM_Wrap
 */
class Lead extends BaseEntity
{
    /**
     * @var int
     */
    private $statusId;

    /**
     * @var int
     */
    private $sale;

    /**
     * @var int
     */
    private $pipelineId;

    /**
     * @var int
     */
    private $mainContactId;

    /**
     * @param stdClass $data
     *
     * @return Lead
     *
     * @throws AmoWrapException
     */
    public function loadInRaw($data)
    {
        BaseEntity::loadInRaw($data);
        $this->sale = (int)$data->price;
        $this->pipelineId = (int)$data->pipeline_id;
        $this->statusId = (int)$data->status_id;

        if (isset($data->_embedded->contacts)) {
            $main_contact = null;
            foreach ($data->_embedded->contacts as $contact) {
                if ($contact->is_main){
                    $main_contact = $contact;
                    break;
                }
            }
            $this->mainContactId = (int)$main_contact->id;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getSale()
    {
        return $this->sale;
    }

    /**
     * @param int $sale
     *
     * @return Lead
     */
    public function setSale($sale)
    {
        $this->sale = $sale;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatusId()
    {
        return $this->statusId;
    }

    /**
     * @return string
     */
    public function getStatusName()
    {
        $statuses = AmoCRM::getStatusesName($this->pipelineId);

        return $statuses[$this->statusId];
    }

    /**
     * @param int|string $pipelineIdOrName
     *
     * @return Lead
     *
     * @throws AmoWrapException
     */
    public function setPipeline($pipelineIdOrName)
    {
        $this->pipelineId = AmoCRM::searchPipelineId($pipelineIdOrName);

        return $this;
    }

    /**
     * @param int|string $statusIdOrName
     * @param int|string $pipelineIdOrName
     *
     * @return Lead
     *
     * @throws AmoWrapException
     */
    public function setStatus($statusIdOrName, $pipelineIdOrName = null)
    {
        $pipelineId = $pipelineIdOrName !== null ? AmoCRM::searchPipelineId($pipelineIdOrName) : $this->pipelineId;
        $this->statusId = AmoCRM::searchStatusId($pipelineId, $statusIdOrName);

        return $this;
    }

    /**
     * @return int
     */
    public function getPipelineId()
    {
        return $this->pipelineId;
    }

    /**
     * @return string
     */
    public function getPipelineName()
    {
        $pipelines = AmoCRM::getPipelinesName();

        return $pipelines[$this->pipelineId];
    }

    /**
     * @return int
     */
    public function getMainContactId()
    {
        return $this->mainContactId;
    }

    /**
     * @return Contact
     *
     * @throws AmoWrapException
     */
    public function getMainContact()
    {
        return new Contact($this->mainContactId);
    }

    /**
     * @param Contact|string|int $contact
     *
     * @return Lead
     */
    public function setMainContact($contact)
    {
        $id = $contact instanceof Contact ? $contact->getId() : Base::onlyNumbers($contact);
        $this->mainContactId = $id;

        if ($this->getId() === null) {
            $this->save();
        }

        $linkRequestData[] = array(
            'to_entity_id' => intval($id),
            'to_entity_type' => 'contacts',
            'metadata' => array (
                'is_main' => true,
            )
        );

        $linkResponse = Base::cUrl("/api/v4/{$this->config['url']}/{$this->getId()}/link", $linkRequestData, null, false, 'POST');

        return $this;
    }

    /**
     * @return bool
     */
    public function isClosed()
    {
        return $this->statusId === 142 || $this->statusId === 143;
    }

    /**
     * @param string $text
     * @return Company|Contact|Lead|void
     */
    public function addNote($text) {
        if (empty($text)) {
            return $this;
        }
        if ($this->getId() === null) {
            $this->save();
        }
        $url = "/api/v4/leads/{$this->getId()}/notes";
        //$url = "/api/v4/leads/notes";
        $requestData = array(array(
            "entity_id" => intval($this->getId()),
            'note_type' => 'common',
            'params' => array(
                'text' => $text
            ))
        );
        $result = Base::cUrl($url, $requestData, null, false, 'POST');
        return $this;
    }


    public function getProducts()
    {

    }


    /**
     * @return array
     */
    protected function getExtraRaw()
    {
        $list = array();

        if(!is_null($this->pipelineId)) {
            $list['pipeline_id'] = $this->pipelineId;
        }

        if(!is_null($this->sale)) {
            $list['price'] = $this->sale;
        }

        if(!is_null($this->statusId)) {
            $list['status_id'] = $this->statusId;
        }

        return $list;
    }
}