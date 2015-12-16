<?php
// This file is not a CODE, it makes no sense and won't run or validate
// Its AST serves IDE as DATA source to make advanced type inference decisions.

namespace PHPSTORM_META {
    $STATIC_METHOD_TYPES = [
        \Zend\ServiceManager\ServiceLocatorInterface::get('') => [
             // Zend global services
            'config' instanceof \Zend\Config\Config,
            'translator' instanceof \Zend\I18n\Translator\Translator,
            'app_zend_db_adapter' instanceof \Zend\Db\Adapter\AdapterInterface,
            'acl_zend_db_adapter' instanceof \Zend\Db\Adapter\AdapterInterface,
            'Zend\Authentication\AuthenticationService' instanceof \Zend\Authentication\AuthenticationService,
            'Zend\Session\SessionManager' instanceof \Zend\Session\SessionManager,

             // Miranda global services
            'Miranda\Service\Config' instanceof \Application\TraversableConfig,
            'Miranda\Service\Cache' instanceof \Zend\Cache\Storage\StorageInterface,
            'Miranda\Service\Mailer' instanceof \Application\Mail\Mailer,
            'Miranda\Service\Paginator\ItemsPerPageManager' instanceof \Application\Model\Paginator\ItemsPerPageManager,
            'Miranda\Model\PictureTable' instanceof \Application\Model\PictureTable,
            'Miranda\TableGateway\Pictures' instanceof \Zend\Db\TableGateway\TableGateway,
            'Miranda\Service\Acl' instanceof \Acl\Model\AclManager,
            'Miranda\Service\AuthService' instanceof \Zend\Authentication\AuthenticationService,
            'Miranda\Service\AuthSessionStorage' instanceof \User\Authentification\Storage\Session,
            'Miranda\Service\AuthDb' instanceof \User\Authentification\Adapter\DbCallbackCheckAdapter,
            'Miranda\Service\AuthBCrypt' instanceof \Zend\Crypt\Password\Bcrypt,

            // Acl
            'Acl\Model\AclManager' instanceof \Acl\Model\AclManager,
            'Acl\Model\RightsManager' instanceof \Acl\Model\RightsManager,
            'Acl\TableGateway\Rights' instanceof \Zend\Db\TableGateway\TableGateway,
            'Acl\TableGateway\RightsGroups' instanceof \Zend\Db\TableGateway\TableGateway,
            'Acl\TableGateway\Roles' instanceof \Zend\Db\TableGateway\TableGateway,
            'Acl\TableGateway\RolesRights' instanceof \Zend\Db\TableGateway\TableGateway,
            'Acl\Model\RoleTable' instanceof \Acl\Model\RoleTable,
            'Acl\Form\Role' instanceof \Acl\Form\Role,

            // User
            'User\TableGateway\Users' instanceof \Zend\Db\TableGateway\TableGateway,
            'User\TableGateway\UsersRoles' instanceof \Zend\Db\TableGateway\TableGateway,
            'User\Model\UserTable' instanceof \User\Model\UserTable,
            'User\Form\Login' instanceof \User\Form\Login,
            'User\Form\ForgotPassword' instanceof \User\Form\ForgotPassword,
            'User\Form\User' instanceof \User\Form\User,
            'User\Form\Profile' instanceof \User\Form\User,
            'User\Form\Password' instanceof \User\Form\Password,
            'User\Form\ResetPassword' instanceof \User\Form\Password,
            'User\Form\ValidateAccount' instanceof \User\Form\ValidateAccount,

            // Costume
            'Costume\TableGateway\Costumes' instanceof \Zend\Db\TableGateway\TableGateway,
            'Costume\TableGateway\CostumePicture' instanceof \Zend\Db\TableGateway\TableGateway,
            'Costume\TableGateway\Color' instanceof \Zend\Db\TableGateway\TableGateway,
            'Costume\TableGateway\Material' instanceof \Zend\Db\TableGateway\TableGateway,
            'Costume\TableGateway\Tag' instanceof \Zend\Db\TableGateway\TableGateway,
            'Costume\TableGateway\CostumeTag' instanceof \Zend\Db\TableGateway\TableGateway,
            'Costume\TableGateway\Types' instanceof \Zend\Db\TableGateway\TableGateway,
            'Costume\TableGateway\CostumeType' instanceof \Zend\Db\TableGateway\TableGateway,
            'Costume\Model\CostumeTable' instanceof \Costume\Model\CostumeTable,
            'Costume\Model\SearchCostumeTable' instanceof \Costume\Model\CostumeTable,
            'Costume\Model\LightCostumeTable' instanceof \Costume\Model\CostumeTable,
            'Costume\Model\CostumePictureTable' instanceof \Costume\Model\CostumePictureTable,
            'Costume\Model\ColorTable' instanceof \Costume\Model\ColorTable,
            'Costume\Model\MaterialTable' instanceof \Costume\Model\MaterialTable,
            'Costume\Model\TagTable' instanceof \Costume\Model\TagTable,
            'Costume\Model\TypeTable' instanceof \Costume\Model\TypeTable,
            'Costume\Form\Search' instanceof \Costume\Form\Search,
            'Costume\Form\Costume' instanceof \Costume\Form\Costume,
            'Costume\Form\Picture' instanceof \Costume\Form\Picture,
            'Costume\Hydrator\CostumeForm' instanceof \Costume\Model\Costume\FormHydrator
        ]
    ];
}