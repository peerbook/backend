<?php

class APIChildController extends APIBaseController {

    public $context_id;
    public $context_record;
    public $context_class;
    public $path; // read-only path variable
    
    public $context_permission_check = API::ACTION_READ;

    public function children() {
        return array();
    }
    
    public function getContextClass() {
        return $this->context_class;
    }

    public function getContext() {
        return $this->context_id;
    }

    public function getContextRecord() {
        return $this->context_record;
    }

    public function add() {
        throw new CHttpException(501);
    }

    public function edit($id) {
        throw new CHttpException(501);
    }

    public function read($id) {
        throw new CHttpException(501);
    }

    public function browse() {
        throw new CHttpException(501);
    }

    public function delete($id) {
        throw new CHttpException(501);
    }

    public function run(ApiRequest $apiRequest, $path) {
		$this->setApiRequest($apiRequest);
		
		if(!$this->hasAccess()) {
			throw new CHttpException(403, 'Cannot run '.get_class($this));
		}
		
        $this->path = $path; // store path for read-only purposes
        
        $contextKey = array_shift($path);

        // meaning that there is a child action
        if (isset($path[0])) {

            $sub = array_shift($path);
            $allowed = $this->children();

            if (!in_array($sub, $allowed)) {
                throw new CHttpException(404, 'Sub controller "' . $sub . '" not found');
            }

            $subController = str_replace('Controller', '', get_class($this)) . ucfirst($sub) . 'Controller';

            $ctrl = new $subController;

            if (!($ctrl instanceof APIChildController)) {
                throw new CHttpException(500, 'Is not an child controller');
            }
			
			if(!$this->hasAccess()) {
				throw new CHttpException(403, 'Cannot run '.get_class($this));
			}

            // set context ID's and items
            if ($this instanceof APIModelController) {
                $item = $this->getItem($contextKey, $ctrl->context_permission_check);
                $ctrl->context_record = $item;
            }
            $ctrl->context_id = $contextKey;
            $ctrl->context_class = $this;

            return $ctrl->run($apiRequest, $path);
        } else {
            array_unshift($path, $contextKey);
            return parent::run($apiRequest, $path);
        }
    }

}
