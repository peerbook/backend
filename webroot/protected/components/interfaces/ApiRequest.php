<?php

class ApiRequest extends CComponent {

    public $requestMethod;
    public $returnKey;
    public $url;
    private $getParams;
    private $data;

    public function __construct($requestMethod, $url) {
        $this->requestMethod = $requestMethod;
        $this->url = $url;

        $this->data = $this->getDefaultPostData();
        $this->getParams = $this->getDefaultGetParams();
    }

    private function getDefaultGetParams() {
        return isset($_GET) ? $_GET : [];
    }

    private function getDefaultPostData() {
        $postData = isset($_POST) ? $_POST : [];

        if ($_SERVER['REQUEST_METHOD'] !== "GET") { // thus PUT, DELETE or POST
            $input = file_get_contents('php://input');
            if (!empty($input)) {
                $postData = json_decode($input, true);
            }
        }

        return $postData;
    }

    public function getMethod() {
        return $this->requestMethod;
    }

    public function setMethod($method) {
        $this->requestMethod = $method;
    }

    public function hasReturnKey() {
        return $this->returnKey !== null;
    }

    public function setData($data) {
        $this->data = $data;
    }

    public function setParams($params) {
        $this->getParams = $params;
    }

    public function getParams() {
        return $this->getParams;
    }

    public function getData() {
        return $this->data;
    }

    public function getDataByKey($key) {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function getParam($key) {
        return isset($this->getParams[$key]) ? $this->getParams[$key] : null;
    }
    
    public function getToken() {
        
        $token = $this->getParam('token');
        
        if(isset($_GET['token']))
            $token = (string) $_GET['token'];

        if(isset($_SERVER['HTTP_PEERBOOKTOKEN']))
            $token = (string) $_SERVER['HTTP_PEERBOOKTOKEN'];
        
        return $token;
    }

    /**
     * @return ApiResponse
     * @throws CHttpException
     */
    public function execute() {

        try {
            $path = explode('/', $this->url);

            $model = array_shift($path);
            $controllerName = ucfirst($model) . 'Controller';

            if (class_exists($controllerName)) {
                $controller = new $controllerName();
                $controller->init();
                return new ApiResponse(200, $controller->run($this, $path));
            } else {
                throw new CHttpException(501, 'Class "' . $controllerName . '" not defined');
            }
        } catch (ModelException $e) {
            $message = $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ') ' . print_r($e->getModelErrors(), true);
            Yii::log($message, CLogger::LEVEL_ERROR);
            return new ApiResponse($e->statusCode, array(
                'code' => $e->statusCode,
                'message' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
                'errors' => $e->getModelErrors()
            ));
        } catch (CHttpException $e) {
            if ($e->getCode() == 500) {
                $message = $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ')';
                Yii::log($message, CLogger::LEVEL_ERROR);
            }
            return new ApiResponse($e->statusCode, array(
                'code' => $e->statusCode,
                'message' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine()
            ));
        } catch (Exception $e) {
            $message = $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ')';
            Yii::log($message, CLogger::LEVEL_ERROR);
            return new ApiResponse(500, array(
                'code' => 500,
                'message' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine()
            ));
        }
    }

    /**
     * 
     * @param type $config
     * @return \ApiRequest@return ApiRequest
     */
    public static function newRequest($config) {

        $obj = new ApiRequest($config['method'], $config['url']);
        foreach ($config as $k => $v)
            $obj->$k = $v;

        return $obj;
    }

}
