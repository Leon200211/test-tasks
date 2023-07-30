<?php
/**
 * Created by PhpStorm.
 * User: Roistat
 * Date: 26.09.17
 * Time: 14:31
 */

namespace Roistat\AmoCRM_Wrap;

use DateTime;
use Exception;
use stdClass;

/**
 * Class Task
 * @package Roistat\AmoCRM_Wrap
 */
class Task extends BaseEntity
{
    /**
     * @var bool
     */
    private $isComplete;

    /**
     * @var DateTime
     */
    private $completeTill;

    /**
     * Task constructor.
     *
     * @param null $id
     *
     * @throws AmoWrapException
     */
    public function __construct($id = null)
    {
        parent::__construct($id);

        try {
            $this->completeTill = new DateTime();
            $this->type = 1;
        } catch (Exception $e) {
            throw new AmoWrapException("Ошибка в обёртке: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * @param stdClass $data
     *
     * @return Task
     * @throws AmoWrapException
     */
    public function loadInRaw($data)
    {
        BaseEntity::loadInRaw($data);
        $this->isComplete = $data->is_completed;
        $this->completeTill->setTimestamp($data->complete_till_at);
        return $this;
    }

    /**
     * @return bool
     */
    public function isIsComplete()
    {
        return $this->isComplete;
    }

    /**
     * @param bool $isComplete
     *
     * @return Task
     */
    public function setIsComplete($isComplete)
    {
        $this->isComplete = $isComplete;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCompleteTill()
    {
        return $this->completeTill;
    }

    /**
     * @param DateTime $completeTill
     *
     * @return Task
     */
    public function setCompleteTill(DateTime $completeTill)
    {
        $this->completeTill = $completeTill;

        return $this;
    }

    /**
     * @return array
     */
    protected function getExtraRaw()
    {
        return array(
            'complete_till' => intval($this->completeTill->format('U')),
            'is_completed' => $this->isComplete,
            'entity_id' => $this->elementId,
            'entity_type' => $this->elementType,
            'task_type_id' => $this->type,
            'text' => $this->text,
        );
    }
}