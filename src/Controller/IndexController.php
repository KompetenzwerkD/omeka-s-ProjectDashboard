<?php
namespace ProjectDashboard\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class IndexController extends AbstractActionController 
{
    public function indexAction() {

        //$settings = $this->getServiceLocator()->get('Omeka\Settings');
        $templateLabels = $this->settings()->get('dashboardResourceTemplates');
        $templateLabels = array_map('trim', explode('<br />', nl2br($templateLabels)));
       
        $templates = [];

        foreach ($this->api()->search('resource_templates')->getContent() as $rt) 
        {
            if (in_array($rt->label(), $templateLabels)) 
            {
                $content = [];
                $content['template'] = $rt;

                $itemCount = $this->api()->search('items', [ 'resource_template_id' => $rt->id()])->getTotalResults();
                $content['total'] = $itemCount;

                $items = $this->api()->search('items', [ 'resource_template_id' => $rt->id(), 'sort_by' => 'modified', 'sort_order' => 'desc', 'limit' => 10 ])->getContent();
                $content['items'] = $items;

                array_push($templates, $content);
            }
        }

        $view = new ViewModel;
        $view->setVariable("templates", $templates);
        return $view;
    }

    public function addItemAction() {
        $resourceTemplateId = $this->params('id');

        $item = [];

        $resourceTemplate = $this->api()->read('resource_templates', $resourceTemplateId)->getContent();
        $resourceClass = $resourceTemplate->resourceClass();

        $item['o:resource_class'] = [
            'o:id' => $resourceClass->id()
        ];
        $item['o:resource_template'] = [
            'o:id' => $resourceTemplateId
        ];

        $newItem = $this->api()->create('items', $item)->getContent();

        return $this->redirect()->toURL($newItem->url('edit'));
    }
}