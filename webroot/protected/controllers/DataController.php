<?php

/**
 * @author Han
 *
 */
class DataController extends Controller {

    public function filters() {
        return array(
            'accessControl'
        );
    }

    public function accessRules() {
        return array(
            array('allow', 'actions' => array('image', 'book', 'group', 'groupembed'), 'users' => array('*')),
            array('deny')
        );
    }
	
	
    public function actionBook($id) {
        $book = Product::model()->findByEan($id);

        if ($book == null)
            throw new CHttpException(404);

        $count = Copy::model()->count(array('ean' => $book->ean, 'deleted' => false));

        // parse description
        $description = $book->ean;
        $edition = $book->getParsedEdition();
        $year = $book->getYear();
        if (!empty($edition))
            $description .= ' - ' . $edition;
        if (!empty($year))
            $description .= ' - ' . $year;


        $this->tags = array(
            'type' => 'book',
            'isbn' => $book->ean,
            'url' => Yii::app()->request->hostInfo . '/book/' . $book->ean . '/' . Common::slug($book->title),
            'image' => Yii::app()->request->hostInfo . '/data/' . $book->ean . '_small_3x.jpg',
            'title' => CHtml::encode($book->title) . ' - ' . implode(', ', $book->authors) . ' - ' . Yii::app()->name,
            'description' => $description
                ) + $this->tags;

        $this->pageTitle = $this->tags['title'];
        $this->layout = '//layouts/angular';
        $this->render('product', array(
            'product' => $book,
            'productCount' => $count
        ));
    }

    private function parsePath($path) {
        
        // ean_book_3x.jpg
        // id_user_3x.jpg
        
        $pathParts = explode('_', $path);
        
        if(count($pathParts) < 1)
            throw new CException('Invalid image, show default');
        
        $sizes = array(
            'book' => [320, 485],
            'user' => [32, 32]
        );
        
        return array(
            'id' => $pathParts[0],
            'type' => $pathParts[1],
            'size' => isset($sizes[$pathParts[1]]) ? $sizes[$pathParts[1]] : $sizes['book'],
            'factor' => isset($pathParts[2]) ? trim($pathParts[2],'x') : 1
        );
    }
    
    private function getImageFromPath($path) {
        switch($path['type']) {
            case 'book':
                
                $product = Product::getProductByEan($path['id']);
                return $product->image;
                
            case 'user':
                
                $original_image = Yii::app()->basePath.'/../data/'.$path['id'].'_user_org.png';
                
                if(file_exists($original_image))
                    return $original_image;
                
            break;
        }
        
        throw new CException('Image could not be found');
    }
    
    private function resizeImage($url, $imagePathData, $outputFile) {
        
        $x = $imagePathData['size'][0] * $imagePathData['factor'];
        $y = $imagePathData['size'][1] * $imagePathData['factor'];
        
        if(!empty($url)) {
            $image = \WideImage\WideImage::load($url);
            $image->resize($x, $y, 'outside')->crop('center', 'center', $x, $y)->saveToFile($outputFile, 80);
        }
        
    }
    
    public function actionImage($path) {
        
        $imagePathData = $this->parsePath($path);  
        
        try {
                      
            $imageUrl = $this->getImageFromPath($imagePathData);
            $imageOutputPath = Yii::app()->basePath.'/../data/'.$path.'.jpg';           
            
            // create thumb
            if(!file_exists($imageOutputPath)) {
                $this->resizeImage($imageUrl, $imagePathData, $imageOutputPath);
            }
            
            // check again
            if(!file_exists($imageOutputPath)) {
                throw new CException('File could not be written');
            }
            
            header('Content-type: image/jpg');
            readfile($imageOutputPath);
            die;
            
        } catch(CException $e) {
            
            if($imagePathData['type'] == 'user') {
                $this->redirect(Yii::app()->baseUrl . '/images/missingbook.svg');
            } else
                throw new CHttpException(404);
        }
        
    }

}
