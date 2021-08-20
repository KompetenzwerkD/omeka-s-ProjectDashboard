<?php 
namespace ProjectDashboard\Form;

use Laminas\Form\Form;
use Omeka\Form\Element\ResourceTemplateSelect;


class AddTemplateForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'o:resource-template',
            'type' => ResourceTemplateSelect::class,
            'options' => [
                'label' => 'Resource template',
                'info' => 'Name of the resource template',
            ],
            'attributes' => [
                'required' => true,
                'id' => 'o-resource-template',
            ]
        ]);
    }
}