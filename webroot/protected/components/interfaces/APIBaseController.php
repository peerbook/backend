<?php

class APIBaseController {

    /**
     * @var ApiRequest
     */
    private $activeApiRequest;

    public function init() {
	
    }

    /**
     * @return ApiRequest
     */
    public function getApiRequest() {
	return $this->activeApiRequest;
    } 

    public function setApiRequest(ApiRequest $apiRequest) {
	$this->activeApiRequest = $apiRequest;
    }

    public function hasAccess() {
	return isset(Yii::app()->user->me);
    }

    public function run(ApiRequest $apiRequest, $path) {
	$this->setApiRequest($apiRequest);

	// checking pre-conditions for the controller
	if (!$this->hasAccess()) {
	    throw new CHttpException(403, 'Cannot run ' . get_class($this));
	}

	$action = 'browse';
	$contextKey = array_shift($path);

	if (!$this->hasAccess()) {
	    throw new CHttpException(403, 'Cannot run ' . get_class($this));
	}

	switch ($apiRequest->requestMethod) {
	    case "POST":
		$action = 'add';
		break;
	    case "PUT":
		$action = 'edit';
		break;
	    case "GET":
		if ($contextKey === null)
		    $action = 'browse';
		else
		    $action = 'read';
		break;
	    case "DELETE":
		$action = 'delete';
		break;
	}

	if (!method_exists($this, $action))
	    throw new CHttpException(501, 'Action "' . $action . '" not implemented in "' . get_class($this) . '"');

	switch ($action) {
	    case 'add':
	    case 'browse':
		return $this->$action();
		break;
	    default:
		return $this->$action($contextKey);
	}
    }

}
