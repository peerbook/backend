<?php

class BatchController extends APIBaseController {

    public function hasAccess() {
        return true;
    }

    public function add() {

        $requestObject = $this->getApiRequest();

        /**
         * [
         * 		{
         * 			method,
         * 			url,
         * 			params,
         * 			data,
         * 			returnKey
         * 		}
         * ]
         */
        if (!is_array($requestObject->getData())) {
            throw new CHttpException(400);
        }

        $ret = [];

        foreach ($requestObject->getData() as $object) {

            $config = $object + array(
                'method' => 'GET'
            );

            $apiCall = ApiRequest::newRequest($config);
            $result = $apiCall->execute()->getResult();

            if ($apiCall->hasReturnKey())
                $ret[$apiCall->returnKey] = $result;
            else
                $ret[] = $result;
        }

        return $ret;
    }

}
