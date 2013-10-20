<?php
return array(
    'router' => array(
        'routes' => array(
            'zftool-diagnostics' => array(
                'type'  => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/diagnostics',
                    'defaults' => array(
                        'controller' => 'ZFTool\Controller\Diagnostics',
                        'action'     => 'run'
                    )
                )
            )
        )
    ),
);