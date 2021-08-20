<?php
namespace ProjectDashboard\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\Api\Exception\NotFoundException;
use Omeka\Api\Exception\BadRequestException;
use ProjectDashboard\Form\AddTemplateForm;

class IndexController extends AbstractActionController 
{
    public function indexAction() {

        //$settings = $this->getServiceLocator()->get('Omeka\Settings');
        $templateLabels = $this->settings()->get('dashboardResourceTemplates');
        $templateLabels = array_map('trim', explode('<br />', nl2br($templateLabels)));
       
        $templateIds = $this->settings()->get('dbResourceTemplates');
        $templateIds = explode(",", $templateIds);
        $templates = [];


        foreach($templateIds as $id) 
        {
            if ($id != "") 
            {
                $rt = $this->api()->read('resource_templates', $id)->getContent();
                $content = [];
                $content['template'] = $rt;

                $itemCount = $this->api()->search('items', [ 'resource_template_id' => $rt->id()])->getTotalResults();
                $content['total'] = $itemCount;

                $items = $this->api()->search('items', [ 'resource_template_id' => $rt->id(), 'sort_by' => 'modified', 'sort_order' => 'desc', 'limit' => 10 ])->getContent();
                $content['items'] = $items;

                try {
                    $customVocabs = $this->api()->read('custom_vocabs', ['label' => $rt->label()])->getContent();
                    $customVocabs = "exists";
                } catch(NotFoundException $e) {
                    $customVocabs = "create";
                } catch(BadRequestException $e) {
                    $customVocabs = "not_available";
                }
                $content['customVocab'] = $customVocabs;

                array_push($templates, $content);
            }
        }

        $view = new ViewModel;
        $view->setVariable("templates", $templates);
        $view->setVariable("ids", $templateIds);
        return $view;
    }

    public function addItemAction() {
        $resourceTemplateId = $this->params('id');

        $item = [];

        $resourceTemplate = $this->api()->read('resource_templates', $resourceTemplateId)->getContent();
        $resourceClass = $resourceTemplate->resourceClass();

        $itemSet = $this->api()->search('item_sets', [ 'fulltext_search' => $resourceTemplate->label() ] )->getContent();
        if ($itemSet) {
            $item['o:item_set'] = [ 'o:id' => $itemSet[0]->id()];
        }

        $item['o:resource_class'] = [
            'o:id' => $resourceClass->id()
        ];
        $item['o:resource_template'] = [
            'o:id' => $resourceTemplateId
        ];

        $newItem = $this->api()->create('items', $item)->getContent();

        return $this->redirect()->toURL($newItem->url('edit'));
    }

    public function addCustomVocab() {
        $resourceTemplateId = $this->params('id');

    }

    public function addTemplateAction()
    {
        
        $form = $this->getForm(AddTemplateForm::class);

        if ($this->getRequest()->isPost()) 
        {
            $form->setData($this->params()->fromPost());
            if ($form->isValid()) 
            {
                $formData = $form->getData();
                $templates = $this->settings()->get("dbResourceTemplates");
                $templates = explode(",", $templates);
                
                if (!in_array($formData['o:resource-template'], $templates)) {
                    array_push($templates, $formData['o:resource-template']);

                    $resourceTemplate = $this->api()->read('resource_templates', $formData['o:resource-template'])->getContent();
                    $label = $resourceTemplate->label();

                    $itemSet = $this->api()->search('item_sets', [ 'fulltext_search' => $label ] )->getContent();
                    if (!$itemSet)
                    {
                        $itemSetData = [
                            'dcterms:title' => [
                                [
                                    'type' => 'literal',
                                    'property_id' => 1,
                                    '@value' => $resourceTemplate->label(),
                                ],
                            ],
                        ];
                        $itemSet = $this->api()->create('item_sets', $itemSetData)->getContent();
                    }
                    else {
                        $itemSet = $itemSet[0];
                    }

                    try {
                        $customVoocab = $this->api()->read('custom_vocabs', ['label' => $label])->getContent();
                    } 
                    catch (NotFoundException $e)
                    {
                        $customVocabData = [
                            'o:label' => $resourceTemplate->label(),
                            'o:item_set' => [ 'o:id' => $itemSet->id() ]
                        ];
                        $customVocab = $this->api()->create('custom_vocabs', $customVocabData);
                    }
                    
                    $items = $this->api()->search('items', [ 'resource_template_id' => $resourceTemplate->id()])->getContent();
                    foreach ($items as $item) {
                        $this->api()->update('items', $item->id(), [ 'o:item_set' => [ 'o:id' => $itemSet->id() ]  ], [], ['isPartial'=>true, 'collectionAction' => 'append']);
                    }

                    $templates = implode(",", $templates);
                    $this->settings()->set("dbResourceTemplates", $templates);
                    $this->messenger()->addSuccess('Resource template added to dashboard.');
                }
                else {
                    $this->messenger()->addwarning('Resource template already in dashboard.');
                }

                $this->redirect()->toRoute('admin/project-dashboard');
            }
        }        

        $view = new ViewModel();
        $view->setVariable('form', $form);
        return $view;    
    }

    public function removeTemplateAction() 
    {
        $id = $this->params('id');
        $templates = $this->settings()->get("dbResourceTemplates");
        $templates = explode(",", $templates);        
        $key = array_search($id, $templates);
        unset($templates[$key]);
        $templates = implode(",", $templates);
        $this->settings()->set("dbResourceTemplates", $templates);
        $this->redirect()->toRoute('admin/project-dashboard');
    }
}