<?php

class Controller extends CController {

    public $layout = '//layouts/main';
    public $tags = array();

    public function init() {
	parent::init();

	$this->tags = array(
	    'url' => Yii::app()->request->hostInfo . Yii::app()->request->requestUri,
	    'type' => 'website',
	    'title' => 'Deelplatform voor studieboeken',
	    'description' => '',
	    'image' => 'https://spull.nl/images/logo_500px.png'
	);
    }

    /**
     * Page title
     * @see CController::setPageTitle()
     */
    public function setPageTitle($value) {
	if (substr($value, -5) !== 'Spull')
	    parent::setPageTitle($value . ' - ' . Yii::app()->name);
	else
	    parent::setPageTitle($value);
    }

    /**
     * Filter isAdmin
     * @param $filterChain
     * @throws CHttpException
     */
    public function filterIsAdmin($filterChain) {

	$allowed_ids = array('100000229084173', '722801799');

	if (Yii::app()->user->isGuest) {
	    throw new CHttpException(401, 'Not authorized');
	}

	if (YII_DEBUG || isset(Yii::app()->user->getUser()->nid) && in_array(Yii::app()->user->getUser()->nid, $allowed_ids)) {
	    $filterChain->run();
	} else
	    throw new CHttpException(403, 'Not allowed');
    }

}
