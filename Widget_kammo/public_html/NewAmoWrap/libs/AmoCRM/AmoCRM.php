<?php

/**
 * Created by PhpStorm.
 * User: Roistat
 * Date: 21.07.17
 * Time: 17:11
 */

namespace Roistat\AmoCRM_Wrap;

use DateTime;
use Roistat\AmoCRM_Wrap\Helpers\Config;
use stdClass;

/**
 * Class Amo
 * @package Roistat\AmoCRM_Wrap
 *
 * @version Version 7.0.5
 */
class AmoCRM extends Base
{
    /**
     * Wrap Version
     */
    const VERSION = '7.0.5';

    /**
     * @var int
     */
    private static $phoneFieldId;

    /**
     * @var int
     */
    private static $emailFieldId;

    /**
     * @var array
     */
    private static $phoneEnums = array();

    /**
     *
     * @var array
     */
    private static $emailEnums = array();

    /**
     * @var array
     */
    private static $users = array();

    /**
     * @var array
     */
    private static $contactCustomFields = array();

    /**
     * @var array
     */
    private static $contactCustomFieldsEnums = array();

    /**
     * @var array
     */
    private static $leadCustomFields = array();

    /**
     * @var array
     */
    private static $leadCustomFieldsEnums = array();

    /**
     * @var array
     */
    private static $companyCustomFields = array();

    /**
     * @var array
     */
    private static $companyCustomFieldsEnums = array();

    /**
     * @var array
     */
    private static $pipelinesName = array();

    /**
     * @var array
     */
    private static $pipelinesStatusesName = array();

    /**
     * @var array
     */
    private static $pipelinesStatusesColor = array();

    /**
     * @var array
     */
    private static $taskTypes = array();

    /**
     * @param string $domain
     *
     * @throws AmoWrapException
     */
    public function __construct($domain, $token)
    {
        if (!defined('CURL_SSLVERSION_TLSv1_2')) {
            define('CURL_SSLVERSION_TLSv1_2', 6);
        }

        Base::$domain = $domain;
        Base::$token = $token;

        if (Base::$token->getToken() === null) {
            throw new AmoWrapException('Не удалось авторизоваться');
        } else {
            Base::$authorization = true;
        }

        try {
            //TODO::проверить
            self::loadInfo();
        } catch (AmoWrapException $ex) {
            throw new AmoWrapException('Не удалось получить данные аккаунта');
        }
    }

    /**
     * @return bool
     */
    public static function isAuthorization()
    {
        return Base::$authorization;
    }

    /**
     * @return int
     */
    public static function getPhoneFieldId()
    {
        return self::$phoneFieldId;
    }

    /**
     * @return int
     */
    public static function getEmailFieldId()
    {
        return self::$emailFieldId;
    }

    /**
     * @return array
     */
    public static function getPhoneEnums()
    {
        return self::$phoneEnums;
    }

    /**
     * @return array
     */
    public static function getEmailEnums()
    {
        return self::$emailEnums;
    }

    /**
     * @return array
     */
    public static function getUsers()
    {
        return self::$users;
    }

    /**
     * @return array
     */
    public static function getPipelinesName()
    {
        return self::$pipelinesName;
    }

    /**
     * @param int $pipelineId
     *
     * @return array
     */
    public static function getStatusesName($pipelineId)
    {
        return self::$pipelinesStatusesName[$pipelineId];
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public static function getCustomFields($type)
    {
        $attribute = "{$type}CustomFields";

        return self::$$attribute;
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public static function getCustomFieldsEnums($type)
    {
        $attribute = "{$type}CustomFieldsEnums";

        return self::$$attribute;
    }

    /**
     * @return array
     */
    public static function getTaskTypes()
    {
        return self::$taskTypes;
    }

    /**
     * @param int|string $pipelineIdOrName
     *
     * @return int
     * @throws AmoWrapException
     */
    public static function searchPipelineId($pipelineIdOrName)
    {
        if (isset(self::$pipelinesName[$pipelineIdOrName])) {
            return $pipelineIdOrName;
        }

        foreach (self::$pipelinesName as $id => $name) {
            if (mb_stripos($name, $pipelineIdOrName) !== false) {
                return $id;
            }
        }

        throw new AmoWrapException('Воронка не найден');
    }

    /**
     * @param int|string $pipelineIdOrName
     * @param int|string $statusIdOrName
     *
     * @return int
     *
     * @throws AmoWrapException
     */
    public static function searchStatusId($pipelineIdOrName, $statusIdOrName)
    {
        $pipelineId = self::searchPipelineId($pipelineIdOrName);

        if (isset(self::$pipelinesStatusesName[$pipelineId][$statusIdOrName])) {
            return $statusIdOrName;
        }

        foreach (self::$pipelinesStatusesName[$pipelineId] as $id => $name) {
            if (mb_stripos($name, $statusIdOrName) !== false) {
                return $id;
            }
        }

        throw new AmoWrapException('Статус не найден');
    }

    /**
     * @param int|string $pipelineIdOrName
     * @param int|string $statusIdOrName
     *
     * @return string
     *
     * @throws AmoWrapException
     */
    public static function searchStatusColor($pipelineIdOrName, $statusIdOrName)
    {
        $pipelineId = self::searchPipelineId($pipelineIdOrName);
        $statusId = self::searchStatusId($pipelineId, $statusIdOrName);

        return self::$pipelinesStatusesColor[$pipelineId][$statusId];
    }

    /**
     * @param int|string $userIdOrName
     *
     * @return int
     *
     * @throws AmoWrapException
     */
    public static function searchUserId($userIdOrName)
    {
        if (isset(self::$users[$userIdOrName])) {
            return $userIdOrName;
        }

        foreach (self::$users as $id => $name) {
            if (mb_stripos($name, $userIdOrName) !== false) {
                return $id;
            }
        }

        throw new AmoWrapException('Пользователь не найден');
    }

    /**
     * @param $taskIdOrName
     *
     * @return int
     *
     * @throws AmoWrapException
     */
    public static function searchTaskType($taskIdOrName)
    {
        if (isset(self::$taskTypes[$taskIdOrName])) {
            return $taskIdOrName;
        }

        foreach (self::$taskTypes as $id => $name) {
            if (mb_stripos($name, $taskIdOrName) !== false) {
                return $id;
            }
        }

        throw new AmoWrapException('Не удалось найти тип задачи');
    }

    /**
     * @param string $phone
     * @param string $email
     *
     * @return Contact[]
     *
     * @throws AmoWrapException
     */
    public function searchContactsByPhoneAndEmail($phone, $email = null)
    {
        $resultContacts = array();

        $phone = Base::onlyNumbers($phone);
        if (!empty($phone)) {
            $contacts = $this->searchContacts($phone);
            if (count($contacts) > 0) {
                foreach ($contacts as $contact) {
                    foreach ($contact->getPhones() as $value) {
                        if (mb_strpos(Base::onlyNumbers($value), Base::onlyNumbers($phone)) !== false) {
                            $resultContacts[$contact->getId()] = $contact;
                        }
                    }
                }
            }
        }

        if (!empty($email)) {
            $contacts = $this->searchContacts($email);
            if (count($contacts) > 0) {
                foreach ($contacts as $contact) {
                    foreach ($contact->getEmails() as $value) {
                        if (mb_strpos($value, $email) !== false) {
                            $resultContacts[$contact->getId()] = $contact;
                        }
                    }
                }
            }
        }

        return $resultContacts;
    }

    /**
     * @param string|null      $query
     * @param int              $limit
     * @param int              $offset
     * @param array|string|int $responsibleUsersIdOrName
     * @param DateTime|null    $modifiedSince
     * @param bool             $isRaw
     *
     * @return Contact[]|stdClass[]
     *
     * @throws AmoWrapException
     */
    public function searchContacts(
        $query = null,
        $limit = 0,
        $offset = 0,
        $responsibleUsersIdOrName = array(),
        DateTime $modifiedSince = null,
        $isRaw = false
    )
    {
        return $this->search(
            'Contact',
            $query,
            $limit,
            $offset,
            $responsibleUsersIdOrName,
            $modifiedSince,
            $isRaw
        );
    }

    /**
     * @param string|null      $query
     * @param int              $limit
     * @param int              $offset
     * @param array|string|int $responsibleUsersIdOrName
     * @param DateTime|null    $modifiedSince
     * @param bool             $isRaw
     *
     * @return Company[]|stdClass[]
     *
     * @throws AmoWrapException
     */
    public function searchCompanies(
        $query = null,
        $limit = 0,
        $offset = 0,
        $responsibleUsersIdOrName = array(),
        DateTime $modifiedSince = null,
        $isRaw = false
    )
    {
        return $this->search(
            'Company',
            $query,
            $limit,
            $offset,
            $responsibleUsersIdOrName,
            $modifiedSince,
            $isRaw
        );
    }

    /**
     * @param string|null      $query
     * @param string|int|null  $pipelineIdOrName
     * @param array|string|int $statuses
     * @param int              $limit
     * @param int              $offset
     * @param array|string|int $responsibleUsersIdOrName
     * @param DateTime|null    $modifiedSince
     * @param bool             $isRaw
     * @param array|null       $filter
     *
     * @return Lead[]|stdClass[]
     *
     * @throws AmoWrapException
     */
    public function searchLeads(
        $query = null,
        $pipelineIdOrName = null,
        $statuses = array(),
        $limit = 0,
        $offset = 0,
        $responsibleUsersIdOrName = array(),
        DateTime $modifiedSince = null,
        $isRaw = false,
        $filter = null
    )
    {
        return $this->search(
            'Lead',
            $query,
            $limit,
            $offset,
            $responsibleUsersIdOrName,
            $modifiedSince,
            $isRaw,
            $pipelineIdOrName,
            $statuses,
            $filter
        );
    }

    /**
     * @param string|null      $query
     * @param int              $limit
     * @param int              $offset
     * @param array|string|int $responsibleUsersIdOrName
     * @param DateTime|null    $modifiedSince
     * @param bool             $isRaw
     *
     * @return Task[]|stdClass[]
     *
     * @throws AmoWrapException
     */
    public function searchTasks(
        $query = null,
        $limit = 0,
        $offset = 0,
        $responsibleUsersIdOrName = array(),
        DateTime $modifiedSince = null,
        $isRaw = false
    )
    {
        return $this->search(
            'Task',
            $query,
            $limit,
            $offset,
            $responsibleUsersIdOrName,
            $modifiedSince,
            $isRaw
        );
    }

    /**
     * @param int              $limit
     * @param int              $offset
     * @param array|string|int $responsibleUsersIdOrName
     * @param DateTime|null    $modifiedSince
     * @param bool             $isRaw
     *
     * @return Note[]|stdClass[]
     *
     * @throws AmoWrapException
     */
    public function getContactNotes(
        $limit = 0,
        $offset = 0,
        $responsibleUsersIdOrName = array(),
        DateTime $modifiedSince = null,
        $isRaw = false
    )
    {
        return $this->search(
            'Note-Contact',
            null,
            $limit,
            $offset,
            $responsibleUsersIdOrName,
            $modifiedSince,
            $isRaw
        );
    }

    /**
     * @param int              $limit
     * @param int              $offset
     * @param array|string|int $responsibleUsersIdOrName
     * @param DateTime|null    $modifiedSince
     * @param bool             $isRaw
     *
     * @return Note[]|stdClass[]
     *
     * @throws AmoWrapException
     */
    public function getCompanyNotes(
        $limit = 0,
        $offset = 0,
        $responsibleUsersIdOrName = array(),
        DateTime $modifiedSince = null,
        $isRaw = false
    )
    {
        return $this->search(
            'Note-Company',
            null,
            $limit,
            $offset,
            $responsibleUsersIdOrName,
            $modifiedSince,
            $isRaw
        );
    }

    /**
     * @param int              $limit
     * @param int              $offset
     * @param array|string|int $responsibleUsersIdOrName
     * @param DateTime|null    $modifiedSince
     * @param bool             $isRaw
     *
     * @return Note[]|stdClass[]
     *
     * @throws AmoWrapException
     */
    public function getLeadNotes(
        $limit = 0,
        $offset = 0,
        $responsibleUsersIdOrName = array(),
        DateTime $modifiedSince = null,
        $isRaw = false
    )
    {
        return $this->search(
            'Note-Lead',
            null,
            $limit,
            $offset,
            $responsibleUsersIdOrName,
            $modifiedSince,
            $isRaw
        );
    }

    /**
     * @param int              $limit
     * @param int              $offset
     * @param array|string|int $responsibleUsersIdOrName
     * @param DateTime|null    $modifiedSince
     * @param bool             $isRaw
     *
     * @return Note[]|stdClass[]
     *
     * @throws AmoWrapException
     */
    public function getTaskNotes(
        $limit = 0,
        $offset = 0,
        $responsibleUsersIdOrName = array(),
        DateTime $modifiedSince = null,
        $isRaw = false
    )
    {
        return $this->search(
            'Note-Task',
            null,
            $limit,
            $offset,
            $responsibleUsersIdOrName,
            $modifiedSince,
            $isRaw
        );
    }

    public function addCall($query)
    {
        return Base::cUrl('api/v4/calls',$query,null,false,'POST');
    }

    public function createTask($query)
    {
        return Base::cUrl('api/v4/tasks',$query,null,false,'POST');
    }

    public function saveUnsorted($query)
    {
        return Base::cUrl('api/v4/leads', $query, null, false, 'PATCH');
    }

    public function getLeadsList($query = '')
    {
        return Base::cUrl('api/v4/leads' . $query, '', null, false, 'GET');
    }

    public function getUsersList()
    {
        return Base::cUrl('api/v4/users', '', null, false, 'GET');
    }

    /**
     * @param string $directory
     *
     * @throws AmoWrapException
     */
    public function backup($directory)
    {
        $this->createBackupFile(
            $directory,
            'contacts.backup',
            $this->searchContacts(null, 0, 0, array(), null, true)
        );
        $this->createBackupFile(
            $directory,
            'company.backup',
            $this->searchCompanies(null, 0, 0, array(), null, true)
        );
        $this->createBackupFile(
            $directory,
            'leads.backup',
            $this->searchLeads(null, null, null, 0, 0, null, null, true)
        );
        $this->createBackupFile(
            $directory,
            'tasks.backup',
            $this->searchTasks(null, 0, 0, array(), null, true)
        );
        $this->createBackupFile(
            $directory,
            'notes-contacts.backup',
            $this->getContactNotes(0, 0, array(), null, true)
        );
        $this->createBackupFile(
            $directory,
            'notes-company.backup',
            $this->getCompanyNotes(0, 0, array(), null, true)
        );
        $this->createBackupFile(
            $directory,
            'notes-leads.backup',
            $this->getLeadNotes(0, 0, array(), null, true)
        );
        $this->createBackupFile(
            $directory,
            'notes-tasks.backup',
            $this->getTaskNotes(0, 0, array(), null, true)
        );
    }

    /**
     * @param string $directory
     * @param string $nameFile
     * @param mixed  $data
     *
     * @throws AmoWrapException
     */
    private function createBackupFile($directory, $nameFile, $data)
    {
        if (!mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new AmoWrapException("Директория '$directory' не может быть создана");
        }
        $f = fopen("$directory/$nameFile", 'wb+');
        fwrite($f, serialize($data));
        fclose($f);
    }

    /**
     * @param string           $entityName
     * @param string           $query
     * @param integer          $limit
     * @param integer          $offset
     * @param array|string|int $responsibleUsers
     * @param DateTime|null    $modifiedSince
     * @param bool             $isRaw
     * @param int|string|null  $pipelineIdOrName
     * @param array            $statuses
     * @param array|null       $filter
     *
     * @return Company[]|Contact[]|Lead[]|Task[]|Note[]|stdClass[]
     *
     * @throws AmoWrapException
     */
    private function search(
        $entityName,
        $query = null,
        $limit = 0,
        $offset = 0,
        $responsibleUsers = array(),
        DateTime $modifiedSince = null,
        $isRaw = false,
        $pipelineIdOrName = null,
        $statuses = array(),
        $filter = null
    )
    {
        $offset = (int)$offset;
        $limit = (int)$limit;

        if ($responsibleUsers === null) {
            $responsibleUsers = array();
        } elseif (!is_array($responsibleUsers)) {
            $responsibleUsers = array($responsibleUsers);
        }

        if ($statuses === null) {
            $statuses = array();
        } elseif (!is_array($statuses)) {
            $statuses = array($statuses);
        }

        $options = explode('-', $entityName);
        $className = $options[0];
        $type = isset($options[1]) ? mb_strtolower($options[1]) : null;

        $entityFulName = __NAMESPACE__ . "\\$className";
        $attribute     = mb_strtolower($className);
        $config        = Config::$$attribute;
        $url           = 'api/v4/' . $config['url'] . '?';
        if ($query !== null) {
            $url .= "&query=$query";
        }
        if ($type !== null) {
            $url .= "&type=$type";
        }

        if ($pipelineIdOrName !== null && count($statuses) > 0) {
            foreach ($statuses as $statusIdOrName) {
                $statusId = self::searchStatusId($pipelineIdOrName, $statusIdOrName);
                $url .= "&status[]=$statusId";
            }
        }

        if (count($responsibleUsers) > 0) {
            foreach ($responsibleUsers as $responsibleUserIdOrName) {
                $responsibleUserId = self::searchUserId($responsibleUserIdOrName);
                $url .= "&responsible_user_id[]=$responsibleUserId";
            }
        }

        if($filter !== null){
            $url .= http_build_query($filter);
        }

        $totalCount = $limit;
        $results = array();

        $page = 1;
        while (true) {
            if ($totalCount > 500 || $limit === 0) {
                $limitRows = 500;
            } else {
                $limitRows = $totalCount;
            }

            $res = Base::cUrl($url . "&limit=$limitRows&page=$page", array(), $modifiedSince);
            if ($res === null) {
                break;
            }

            $entityName = $config['url'];
            $results[] = $res->_embedded->$entityName;
            if ($limit !== 0) {
                $totalCount -= count($res->_embedded->$entityName);
                if ($totalCount <= 0) {
                    break;
                }
            }
            $offset += 500;
            $page++;
        }

        $resultRaw = array();
        if ($isRaw) {
            foreach ($results as $result) {
                foreach ($result as $baseRaw) {
                    if ($isRaw) {
                        $resultRaw[] = $baseRaw;
                    }
                }
            }

            return $resultRaw;
        }

        $entities = array();
        foreach ($results as $result) {
            foreach ($result as $baseRaw) {
                /** @var BaseEntity $entity */
                $entity = new $entityFulName();
                $entity->loadInRaw($baseRaw);
                $entities[] = $entity;
            }
        }

        return $entities;
    }

    /**
     * @throws AmoWrapException
     */
    private static function loadInfo()
    {
        $users = Base::cUrl('/api/v4/users?limit=250');
        foreach ($users->_embedded->users as $user) {
            self::$users[$user->id] = $user->name;
        }
        $fieldsContacts = Base::cUrl('/api/v4/contacts/custom_fields?limit=250');
        foreach ($fieldsContacts->_embedded->custom_fields as $field) {
            self::$contactCustomFields[$field->id] = $field->name;
            if ($field->name === 'Телефон' && $field->is_predefined) {
                self::$phoneFieldId = $field->id;
                $enums = array();
                foreach ($field->enums as $enum) {
                    $enums[$enum->value] = json_decode(json_encode($enum), true);
                }
                self::$phoneEnums = $enums;
                //self::$phoneEnums = array_flip(json_decode(json_encode($field->enums), true));
            }
            if ($field->name === 'Email' && $field->is_predefined) {
                self::$emailFieldId = $field->id;
                $enums = array();
                foreach ($field->enums as $enum) {
                    $enums[$enum->value] = json_decode(json_encode($enum), true);
                }
                self::$emailEnums = $enums;
                //self::$emailEnums = array_flip(json_decode(json_encode($field->enums), true));
            }
//            if ($field->field_type === 4 || $field->field_type === 5) {
//                self::$contactCustomFieldsEnums[$field->id] = json_decode(json_encode($field->enums), true);
//            }
        }
        $fieldsLeads = Base::cUrl('/api/v4/leads/custom_fields?limit=250');
        foreach ($fieldsLeads->_embedded->custom_fields as $field) {
            self::$leadCustomFields[$field->id] = $field->name;
//            if ($field->field_type === 4 || $field->field_type === 5) {
//                self::$leadCustomFieldsEnums[$field->id] = json_decode(json_encode($field->enums), true);
//            }
        }
        $fieldsCompanies = Base::cUrl('/api/v4/companies/custom_fields?limit=250');
        foreach ($fieldsCompanies->_embedded->custom_fields as $field) {
            self::$companyCustomFields[$field->id] = $field->name;
//            if ($field->field_type === 4 || $field->field_type === 5) {
//                self::$companyCustomFieldsEnums[$field->id] = json_decode(json_encode($field->enums), true);
//            }
        }
        $pipelines = Base::cUrl('/api/v4/leads/pipelines?limit=250');
        foreach ($pipelines->_embedded->pipelines as $pipeline) {
            self::$pipelinesName[$pipeline->id] = $pipeline->name;
            foreach ($pipeline->_embedded->statuses as $status) {
                self::$pipelinesStatusesName[$pipeline->id][$status->id] = $status->name;
                self::$pipelinesStatusesColor[$pipeline->id][$status->id] = $status->color;
            }
        }
        $taskTypes = Base::cUrl('api/v4/account?with=task_types');
        foreach ($taskTypes->_embedded->task_types as $type) {
            self::$taskTypes[$type->id] = $type->name;
        }
    }
}