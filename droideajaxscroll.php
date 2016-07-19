<?php
/**
 * @package     droideajaxscroll
 * @subpackage  droideajaxscroll
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

//require_once __DIR__ . '/helper.php';

class PlgSystemDroideajaxscroll extends JPlugin
{



 public function onBeforeRender()
	 {

		$app = JFactory::getApplication();


		if($app->isAdmin()) {
		    return;
		}

		$temp   = JRequest::getString('id');
		$temp   = explode(':', $temp);

		$dinamico = array(
				'option' => JRequest::getCmd('option'),
				'view'	 => JRequest::getCmd('view'),
				'id'   	 => $temp[0]
			);

		if($dinamico['option'] == 'com_content' && $dinamico['view'] == 'category'):

		$menus = $app->getMenu()->getActive();

		$elementos_dinamicos = array();


		$dataTransf = array(
			'menuparans'=>$menus->params,
			'blocoparam'=>$this->params->get('blocoparam'),
			'art_limit'=>($menus->params->get('num_intro_articles'))?$menus->params->get('num_intro_articles'):0,
			'menu_limit'=>($menus->params->get('num_intro_articles'))?$menus->params->get('num_intro_articles'):0,
			'num_columns'=>$menus->params->get('num_columns',1),
			'cat_id'=>(isset($menus->query['id']))?$menus->query['id']:0,
			'menu_layout'=>($menus->params->get('layout_type'))?$menus->params->get('layout_type'):0,
			'load'=>$this->params->get('load'),
      'loadjquery'=>$this->params->get('loadjquery'),
      'loadcss'=>$this->params->get('loadcss'),
			'categorias'=>json_decode($this->params->get('categorias'))
			);

		if($dinamico['option'] == 'com_content' && $dinamico['view'] == 'category' && $dataTransf['cat_id'] && $dinamico['id'] != $dataTransf['cat_id']){
			$dataTransf['cat_id'] = $dinamico['id'];
		}

		if($dataTransf['art_limit'] && $dataTransf['cat_id'] && $dataTransf['menu_layout'] && $dataTransf['menu_limit'] ){

			return $this->Scrtipt($dataTransf);
		}

		endif;

	}


private function Scrtipt($data){
	$doc = JFactory::getDocument();

	$limit = $data['art_limit'];
	$menu_limit = $data['menu_limit'];
	$menuparans = $data['menuparans'];
	$catid = $data['cat_id'];
	$nColunas = $data['num_columns'];
	$blocoparam = $data['blocoparam'];
	$loadurl = JUri::base(true).'/'.$data['load'];
	$script = <<<html

	and = jQuery.noConflict();

	and('document').ready(function(){
		and('body').attr('data-transf',$menu_limit);
		setTimeout(function(){
			and(window).scroll(function()
				{
					var heightbox = (and('$blocoparam').height() / 2);
					var heightlimit = heightbox + 100;

					if(and(window).scrollTop() > heightbox &&  and(window).scrollTop() < heightlimit )
	   				 {

	   				 	loadposition = and('$blocoparam').width() / 2;
	   				 	and.ajax({
	   				 		dataType:'json',
	   				 		method: 'GET',
	   				 		data:{start:and('body').data('transf'),colunas:$nColunas,menuparans:$menuparans},
	   				 		url:"index.php?droide-ajax=json&cat_id=$catid&limit=$limit&menu_limit=$menu_limit",
	   				 		beforeSend:function(){

	   				 			and('$blocoparam').append("<div id='load' class='droideajaxscroll' style='left:"+loadposition+"px;' ><img style='border:10px solid #ff0000;' width='250' src='$loadurl'/></div>");

	   				 		},
	   				 		success:function(data){
	   				 			and('#load').remove();
	   				 			if(data){
		   				 			and('$blocoparam').append(data.layout);
		   				 			and('body').data('transf', (and('body').data('transf')+data.total));
		   				 			//alert(and('body').data('transf'));
	   				 			}
	   				 		}

	   				 	});
	   				 }
				});


		},1000);


	});

html;

if($data['loadjquery']){
  $doc->addScript("media/plg_system_droideajaxscroll/assets/js/jquery-3.1.0.min.js");
}

if($data['loadcss']){
    $doc->addStyleSheet("media/plg_system_droideajaxscroll/assets/css/stylescroll.css");
}

$doc->addScriptDeclaration($script);


}

private function verificaCat($data){

	$Categorias = JCategories::getInstance('Content');
	$cat = $Categorias->get($data['cat_id']);
	$parent = $cat->getParent();
	$children = $cat->getChildren();
	$filhosCat = array();

	//verifica se existe filhos
	if(count($children)>0){

		foreach ($children as $key => $value) {
			$filhosCat[] = $value->id;
		}

	}

	//checa se a cateogira principal está na lista
	if(in_array($data['cat_id'], $data['categorias']->categoria)){

		return true;
	}else{

		//verifica se o pai das categorias estão setados
		if(in_array($parent->id, $data['categorias']->categoria)){
			return true;
		}

		//caso não verifica se as categoras filhos selecionadas bo plugin estão nos filhos
		foreach ($data['categorias']->categoria as $i => $v) {
			if(in_array($v, $filhosCat)){
				return true;
			}
		}
	}


	//retorna falso se nenhuma for verdadeira
	return false;

}


public function onAfterRoute()
	{
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$retorno = array('total'=>0, 'layout'=>'');
		if ($app->isAdmin())
		{
			return;
		}

		if($app->get('droide-ajax',0,'STRING')){
			if($app->get('droide-ajax',0,'STRING') == 'json'){

				$items = array();

				$retorno = array();

				if(($app->get('cat_id',0,'INT') && $app->get('limit',0,'INT') &&  $app->get('menu_limit',0,'INT')){

					$catid 		= $app->get('cat_id',0,'INT');
					$start 		= $app->get('start',0,'INT');
					$limit 		= $app->get('limit',0,'INT');
					$menu_limit = $app->get('menu_limit',0,'INT');

					$colunas 	= $app->get('colunas',1,'INT');
					$menuparans = $app->get('menuparans',0,'STRING');
					require_once __DIR__ . '/helper.php';

					$items = BlogHelper::getList($start,$limit,$catid,$menuparans,$menu_limit);
				}

				$retorno['total'] = count($items);
				$retorno['layout'] = JLayoutHelper::render($this->params->get('layoutbloco'), array('data'=>$items, 'col'=>$colunas), JPATH_SITE .'/plugins/system/droideajaxscroll/tmpl/');

				$doc->setMimeEncoding('application/json');
				JResponse::setHeader('Content-Disposition','attachment;filename="progress-report-results.json"');
				echo json_encode($retorno);
				$app->close();

			}else{

				$items = array();

				if($app->get('cat_id',0) && $app->get('limit',0)){

					$catid = $app->get('cat_id',0,'INT');
					$limit =  $app->get('limit',0,'INT');
					$start = $app->get('start',0,'INT');
					$colunas = $app->get('colunas',1,'INT');
					$menuparans = $app->get('menuparans',0,'STRING');
					require_once __DIR__ . '/helper.php';

					$items = BlogHelper::getList($start,$limit,$catid,$menuparans);
				}


					echo $items;


				$app->close();


			}



		}

	}



}
