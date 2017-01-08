<?php

namespace CoreUI;
use CoreUI\Utils\Session\SessionNamespace;
use CoreUI\Utils\Db\Adapters;

require_once 'Exception.php';
require_once 'Registry.php';
require_once 'Utils/Session/SessionNamespace.php';


/**
 * Class Handlers
 * @package CoreUI
 */
class Handlers {

    protected $resource = '';
    protected $process  = '';
    protected $response = array();

    /**
     * @var Adapters\PDO|Adapters\Mysqli
     */
    protected $db;


    public function __construct() {
        $this->resource = ! empty($_SERVER['HTTP_X_CMB_RESOURCE']) ? $_SERVER['HTTP_X_CMB_RESOURCE'] : '';
        $this->process  = ! empty($_SERVER['HTTP_X_CMB_PROCESS']) ? $_SERVER['HTTP_X_CMB_PROCESS'] : '';
        $this->db       = Registry::getDbConnection()->getAdapter();
    }


    /**
     * @return string
     */
    public function getProcess() {
        return $this->process;
    }


    /**
     * @return string
     */
    public function getResource() {
        return $this->resource;
    }


    /**
     * @return bool
     */
    public function isHandler() {
        return ! empty($this->process) &&
               in_array($this->process, array(
                   'save', 'delete', 'order',
                   'status', 'search', 'clear_search',
                   'records_per_page', 'upload'
               ));
    }


    /**
     * Получение ответа
     * @return string
     */
    public function getResponse() {

        $session = new SessionNamespace($this->resource);
        if (isset($session->form) && isset($session->form->back_url)) {
            $this->response['back_url'] = $session->form->back_url;
        }

        if ( ! isset($this->response['status'])) {
            $this->response['status']  = 'success';
        }

        return json_encode($this->response);
    }


    /**
     * @return bool
     */
    public function process() {

        if ($this->isHandler()) {
            try {
                if (empty($this->resource)) {
                    throw new Exception('Resource empty');
                }

                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $data = $_POST;

                } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
                    $data = $_GET;

                } else {
                    $data = array();
                    parse_str(file_get_contents('php://input'), $data);
                }

                switch ($this->process) {
                    case 'save' :             $this->saveData($data); break;
                    case 'delete' :           $this->deleteData($data); break;
                    case 'search' :           $this->setSearch($data); break;
                    case 'clear_search' :     $this->setClearSearch(); break;
                    case 'records_per_page' : $this->setRecordPerPage($data); break;
                    case 'status' :           $this->setStatus($data); break;
                    case 'order' :            $this->setOrder($data); break;
                    case 'upload' :           $this->uploadFile(); break;
                    case 'export' :
                    case 'sequence' :
                    case 'sort' :
                    default : throw new Exception('Unknown process name'); break;
                }

                $this->response['status']  = 'success';
                return true;

            } catch (Exception $e) {
                $this->addError($e->getMessage());
                return false;
            }
        }

        return false;
    }


    /**
     * Установка текста ошибки
     * @param string $message
     */
    protected function addError($message) {

        $this->response['message'] = $message;
        $this->response['status']  = 'error';
    }


    /**
     * @return bool
     */
    protected function isValidRequest($component) {

        if ( ! empty($this->resource)) {
            $session = new SessionNamespace($this->resource);

            if (isset($session->$component) &&
                isset($session->$component->__csrf_token) &&
                isset($_SERVER['HTTP_X_CMB_CSRF_TOKEN'])
            ) {
                return $session->$component->__csrf_token === $_SERVER['HTTP_X_CMB_CSRF_TOKEN'];
            }
        }

        return false;
    }
}