<?php declare(strict_types=1);
namespace ProjectDashboard;

use Omeka\Module\AbstractModule;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\Mvc\Controller\AbstractController;


class Module extends AbstractModule 
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getConfigForm(PhpRenderer $renderer)
    {
        $settings = $this->getServiceLocator()->get('Omeka\Settings');
        $form = $this->getServiceLocator()->get('FormElementManager')->get('ProjectDashboard\Form\ModuleConfigForm');
        $form->init();
        $templates = $settings->get('dashboardResourceTemplates');

        $form->setData([
            'resource-templates' => $templates,
        ]);
        return $renderer->formCollection($form, false);
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $settings = $this->getServiceLocator()->get('Omeka\Settings');
        $form = $this->getServiceLocator()->get('FormElementManager')->get('ProjectDashboard\Form\ModuleConfigForm');
        $form->init();
        $form->setData($controller->params()->fromPost());
        if ($form->isValid()) {
            $formData = $form->getData();
            $settings->set('dashboardResourceTemplates', $formData['resource-templates']);
            return true;
        }
        $controller->messenger()->addErrors($form->getMessages());
        return false;
    }       
}