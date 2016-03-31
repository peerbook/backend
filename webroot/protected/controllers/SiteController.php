<?php
/**
 * 
 * @author Han
 */
class SiteController extends Controller {

    public $layout = '//layouts/angular';

    public function filters() {
        return array(
            'accessControl'
        );
    }

    public function accessRules() {
        return array(
            array('allow',
                'actions' => array('index', 'error', 'sitemap', 'page', 'explorer' , 'init', 'test'),
                'users' => array('*')
            ),
            array('deny')
        );
    }
	
    public function actionSitemap() {
	$this->layout = false;
	$this->render('sitemap', array(
	    'pages' => ['voorwaarden', 'privacy', 'faq', 'features'],
	    'other' => array(
		'verenigingen'
	    ),
	));
    }

    /**
     * Show index
     * @return number
     */
    public function actionIndex() {
        $this->layout = false;
        $this->pageTitle = 'Peerboek - Welk verhaal wil jij delen?';
        $this->render('index', array(

        ));
    }

    /**
     * Error view of Spull
     */
    public function actionError() {
        $this->pageTitle = "Sorry, er is een fout opgetreden";
        $error = Yii::app()->errorHandler->error;
        if ($error) {
            $this->render('error', $error);
        }
		
		if (YII_DEBUG) {
			echo '<pre>';
			var_dump($error);
			echo '</pre>';
		}
    }

    /**
     * Pages viewer
     * @param string $p
     */
    public function actionPage($p) {

	switch ($p) {
	    case 'voorwaarden':
		$this->pageTitle = 'Voorwaarden';
		$this->render('//pages/voorwaarden');
		break;
	    case 'privacy':
		$this->pageTitle = 'Privacy';
		$this->render('//pages/privacy');
		break;
	    case 'features':
		$this->pageTitle = 'Featurelist';
		$this->render('//pages/features');
		break;
	    case 'faq':
		$this->pageTitle = 'Featurelist';
		$this->render('//pages/faq');
		break;
	    default:
		throw new CHttpException(404);
	}
    }

    public function actionExplorer() {
	$this->layout = false;
	$this->render('explorer');
    }
    
    public function actionInit($ids = null) {
        
        $models = array('Copy', 'Product', 'User');
        echo 'Start indexing <br />';
        foreach($models as $model) {
            $mod = new $model;
            if($mod instanceof Indexable) {
                $mod->indexes();
                echo 'Settings and running indexes of '.$model.'<br />';
            }
        }
        
        // init admin chats
        $p = Product::model()->findOne(['ean' => '1337']);
        if(!$p) {
        
            $p = new Product;
            $p->sourceId = 'Contact admin';
            $p->ean = 1337;
            $p->source = 'custom';
            $p->type = 'book';
            $p->title = 'Supportkanaal';
            $p->image = 'http://peerbook.co/images/peerbook_logo_512px.png';
            if(!$p->save())
                var_dump($p->errors);
        }
        
        $ids = ($ids == null) ? [] : explode(',', $ids);
        
        foreach($ids as $id) {
            $c = new Copy;
            $u = User::model()->findByPk($id);
            
            if(!$u)
                continue;
            
            $c->owner = $u;
            $c->product = $p;
            $c->position = Location::convert('6.892951,52.220151');
            $c->isAdmin = true;
            
            if($c->save())
                echo mongoId($c). ' for '.$id .' <br />';
            else
                var_dump($c->errors);
        }
        
    }
    
    public function actionTest() {
        
        $data = "data:image/png;base64,asdfad";
        
        var_dump(substr(strstr($data,';', true), 5));
        var_dump(substr($data, strpos($data,',')+1));
        var_dump(substr($data, 0, 11));
        
        //phpinfo();
        
        //Yii::app()->cache->clear();
    }

}
