<?php

class ApiController extends CController {

    public $path;

    public function filterCors($filterChain) {
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: peerbooktoken, Accept, Content-Type');
        
        if($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            Yii::app()->end();
        } else 
            $filterChain->run();
    }
    
    public function filterApiKey($filterChain) {

        $allowed_keys = Yii::app()->params['api_keys'];

        $key = null;
        if (isset($_GET['key'])) {
            $key = $_GET['key'];
        } else if (isset($_SERVER['HTTP_SPULLAPIKEY'])) {
            $key = $_SERVER['HTTP_SPULLAPIKEY'];
        } else if (isset($_SERVER['HTTP_X_SPULL_API_KEY'])) {
            $key = $_SERVER['HTTP_X_SPULL_API_KEY'];
        }

        if (isset($key) && in_array($key, $allowed_keys)) {
            $filterChain->run();
        } else {
	    
            $response = new ApiResponse(403, array(
                'code' => 403,
                'message' => 'Your API key is not allowed'
            ));
	    
            $response->sendResponse();
        }
    }

    public function filters() {
        return array(
            'cors'
        );
    }

    public function actionIndex() {
	
	$response = new ApiResponse(200, array(
	    'name' => Yii::app()->name . ' API',
	    'version' => Yii::app()->params['version']
	));
	
	$response->sendResponse();
    }
    
    public function actionCall($path) {
        
        $apiRequest = new ApiRequest((string) $_SERVER['REQUEST_METHOD'], $path);

        $result = $apiRequest->execute();

        if (YII_DEBUG)
            $result->attachDebugInformation();

        $result->sendResponse();
    }

}
