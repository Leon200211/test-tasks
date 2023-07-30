<?php
/**
 * Created by PhpStorm.
 * User: Roistat
 * Date: 09.10.2017
 * Time: 12:51
 */

namespace Roistat\AmoCRM_Wrap;


/**
 * Class Unsorted
 * @package Roistat\AmoCRM_Wrap
 */
class Unsorted extends Base
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $formId;

    /**
     * @var string
     */
    private $formName;

    /**
     * @var int
     */
    private $pipelineId;

    /**
     * @var Contact[]|array
     */
    private $contacts = array();

    /**
     * @var Lead|array
     */
    private $lead = array();

    /**
     * @var Company[]|array
     */
    private $companies = array();

    /**
     * @var Note[]
     */
    private $notes;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @throws AmoWrapException
     */
    public function __construct()
    {
        if (!AmoCRM::isAuthorization()) {
            throw new AmoWrapException('Требуется авторизация');
        }
    }

    /**
     * @param string $formId
     */
    public function setFormId($formId)
    {
        $this->formId = $formId;
        return $this;
    }

    /**
     * @param string $formName
     */
    public function setFormName($formName)
    {
        $this->formName = $formName;
        return $this;
    }

    /**
     * @param int $pipelineId
     */
    public function setPipelineId($pipelineId)
    {
        if ($pipelineId !== null) {
            $this->pipelineId = AmoCRM::searchPipelineId($pipelineId);
        }
        return $this;
    }

    /**
     * @param array|Contact[] $contacts
     */
    public function setContacts($contacts)
    {
        $this->contacts = $contacts;
        return $this;
    }

    /**
     * @param array|Lead $lead
     */
    public function setLead($lead)
    {
        $this->lead = $lead;
        return $this;
    }

    /**
     * @param array|Company[] $companies
     */
    public function setCompanies($companies)
    {
        $this->companies = $companies;
        return $this;
    }

    /**
     * @return Unsorted
     *
     * @throws AmoWrapException
     */
    public function save()
    {
        if (!empty($this->lead) || !empty($this->contacts)) {
            $lead = null;
            if (!empty($this->lead)) {
                $lead = $this->lead->getRaw();
//                if (!empty($this->notes)) {
//                    foreach ($this->notes as $note) {
//                        $lead['notes'][] = $note->getRaw();
//                    }
//                }
            }
            $contacts = array();
            foreach ($this->contacts as $contact) {
                $contacts[] = $contact->getRaw();
            }
            $companies = array();
            foreach ($this->companies as $company) {
                $companies[] = $company->getRaw();
            }
            $request = array(
                array(
                    'source_name' => 'Roistat AmoCRM Wrap',
                    'source_uid' => md5('Roistat AmoCRM Wrap'),
                    'created_at' => (int) date('U'),
                    'pipeline_id' => $this->pipelineId,
                    '_embedded' => array(
                        'leads' => array($lead),
                        'contacts' => $contacts,
                    ),
                    'metadata' => array(
                        'form_id'       => $this->formId,
                        'form_name'     => $this->formName,
                        'form_sent_at'  => (int) date('U'),
                        'form_page'     => "https://".self::$domain.".kommo.com"
                    ),
                ),
            );

            $response = AmoCRM::cUrl('api/v4/leads/unsorted/forms', $request,null, false, 'POST');
            if ($response !== null && !empty($response->_embedded)) {
                $this->id = $this->getUnsertedId($response);
                return $this;
            }
        }
        throw new AmoWrapException('Не удалось сохранить заявку в неразобранное');
    }

    /**
     * @param $result
     * @return mixed
     */
    private function getUnsertedId($result)
    {
        $unsorted = $result->_embedded->unsorted[0];
        $lead = $unsorted->_embedded->leads[0];
        return $lead->id;
    }

    /**
     * @param $text
     * @param string $type
     * @return $this
     * @throws AmoWrapException
     */
    public function addNote($text, $type = 'common')
    {
        if (is_null($text) || is_null($this->getId())) return $this;

        $url = "/api/v4/leads/{$this->getId()}/notes";
        $requestData = array(
            array(
                "entity_id" => (int) $this->getId(),
                'note_type' => $type,
                'params'    => array(
                    'text' => $text
                )
            )
        );

        $this->notes[] = Base::cUrl($url, $requestData, null, false, 'POST');
        return $this;
    }
//    public function addNote($text, $type = 4)
//    {
//        $note = new Note();
//        $note
//            ->setElementId(30631639)
//            ->setText($text)
//            ->setType($type)
//            ->setElementType('lead');
//        $this->notes[] = $note;
//        return $this;
//    }
}