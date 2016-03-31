<?php

/**
 * Routing aka bit.ly
 * @author Han
 *
 */
class Routing extends CBaseUrlRule {

    public function createUrl($manager, $route, $params, $ampersand) {
	return false;
    }

    /**
     * Search the route and redirect when found!
     * 
     * @see CBaseUrlRule::parseUrl()
     */
    public function parseUrl($manager, $request, $pathInfo, $rawPathInfo) {
	$p = explode('/', trim($pathInfo, '/'));
	$key = $p[0];

	// Geen DB query als verwezen wordt naar module
	if (array_key_exists($key, Yii::app()->getModules()))
	    return false;

	$r = Route::model()->findOne(array(
	    'name' => $key
	));

	if ($r != null) {
	    Yii::app()->request->redirect($r->url, true);
	}

	return false;
    }

}
