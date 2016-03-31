<?php

/**
 * Product
 * @author Han
 */
class Product extends APIModel implements Indexable {

    public $ean;
    public $deleted = false;
    public $last_updated;
    public $create_date;
    
    public $source;
    public $sourceId;
    
    public $title;
    public $url;
    public $image;
    public $description;

    public static function model($cl = __CLASS__) {
        return parent::model($cl);
    }
	
    public function collectionName() {
        return 'products';
    }

    public function rules() {
        return array(
            array('ean, title, source, sourceId', 'required'),
            array('image, url, type, authors, url, edition, language, publisher, release_date, price, rating', 'safe'),
            array('ean', 'filter', 'filter' => function ($v) {
                return (string) $v;
            })
        );
    }
    
    public function getStaticImage() {
        return Yii::app()->params['cdn'].'/data/'.$this->ean.'_book.jpg';
    }

    public function relations() {
        return array(
            'copies' => array('many', 'Copy', 'product_ref.$id', 
                'sort' => array('last_updated' => -1), 
                'where' => array('deleted' => false),

                // update owner when this embedded is changed
                'embedded' => true,
                'embedded_relation' => 'product'
            )
        );
    }
	
    public function beforeSave() {
        if($this->isNewRecord) {
            $this->create_date = new MongoDate();
        }
        
        $this->last_updated = new MongoDate();
        return true;
    }
    
    public function fields($type) {  
        
        if($type === APIElement::REP_FULL) {
            return [
                'ean',
                'title',
                'image' => function () {
                    return $this->staticImage;
                },
                'authors',
                'release_date' => function () {
                    return mongoDate($this->release_date);
                }
            ];
        } else {
            return array('ean', 'title', 'image' => function () {
                return $this->staticImage;
            });
        }
    }

    public function hasAccess($action, $context = null) {
        if($action === API::ACTION_READ)
            return true;
        
        return false;
    }
    
    public function indexes() {
        $this->getCollection()->createIndex(array(
            'ean' => 1
        ));
    }

    public static function getProductByEan($ean) {
        
        $model = Product::model()->findOne(['ean' => $ean]);
        if($model != null) {
            return $model;
        } else {
            
            $searchItems = SearchController::search($ean);
            
            if(isset($searchItems[0])) {
                return self::externalAdd ($searchItems[0]);
            }
        }
        
        throw new CHttpException(404, 'Product not found locally and on the web');
    }
    
    public static function externalAdd(ExternalProduct $e) {

        // only add book
        if ($e->type() !== 'book')
            return null;

        // already exists?
        $alreadyExistsCheck = Product::model()->findOne(array('ean' => $e->getEan()));
        if($alreadyExistsCheck != null) {
            return $alreadyExistsCheck;
        }
        
        $attr = $e->getAttributes();

        $p = new Product;
        $p->sourceId = $e->getSourceId();
        $p->ean = (string) $e->getEan();
        $p->source = $e->source();
        $p->type = $e->type();
        $p->title = $e->getTitle();
        $p->image = $e->getImage();

        if ($e instanceof BolProduct) {
            $p->attributes = $e->getAttributes();
            $p->release_date = new MongoDate(strtotime($attr['release_date']));
        }

        if(!$p->save()) 
            throw new ModelException($p);

        return $p;
    }

}
        