<?php
namespace Hawk\Plugins\BuildHawk;

App::router()->auth(App::session()->getUser()->isAllowed('admin.all'), function(){
	App::router()->setProperties(
		array(
			'prefix' => '/build-hawk',
			'namespace' => __NAMESPACE__
		),
		function(){
			App::router()->get('build-hawk-index', '', array('action' => 'BuildController.index'));		

	        App::router()->get('build-hawk-builds-list', '/versions', array('action' => 'BuildController.buildsList'));       

	        App::router()->any('build-hawk-new-build', '/new-build', array('action' => 'BuildController.build'));

	        App::router()->get('build-hawk-deploy', '/versions/{id}/deploy/{env}', array('where' => array('id' => '\d+', 'env' => 'dev|prod'), 'action' => 'BuildController.deploy'));
		}
	);
});