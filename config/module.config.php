<?php
namespace ProjectDashboard;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
    'controllers' => [
        'invokables' => [
            Controller\IndexController::class => Controller\IndexController::class,
        ],
    ],
    'navigation' => [
        'AdminModule' => [
            [
                'label' => 'Project Dashboard',
                'route' => 'admin/project-dashboard',
                'resource' => Controller\IndexController::class,
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'project-dashboard' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/project-dashboard',
                            'defaults' => [
                                '__NAMESPACE__' => 'ProjectDashboard\Controller',
                                'controller' => Controller\IndexController::class,
                                'action' => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'add-item' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/add-item/:id',
                                    'defaults' => [
                                        'action' => 'addItem'
                                    ],
                                ],
                            ],
                            'add-template' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/add-template',
                                    'defaults' => [
                                        'action' => 'addTemplate'
                                    ],
                                ],
                            ],
                            'remove-template' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/remove-template/:id',
                                    'defaults' => [
                                        'action' => 'removeTemplate'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]    
];