<?php 
namespace IRecruitBundle\Admin;
 
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Form\Type\BooleanType;
use Sonata\AdminBundle\Route\RouteCollection;
 
class AffiliateAdmin extends Admin
{
    protected $baseRouteName = 'sonata_affiliate';
    protected $baseRoutePattern = 'affiliate';
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'isActive',
        // 'isActive' => ['value' => BooleanType::TYPE_NO] 
        // The value 2 represents that the displayed affiliate accounts are not activated yet
    ];
 
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('email')
            ->add('url')
            ->add('categories')
        ;
    }
 
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('email')
            ->add('isActive');
    }
 
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('isActive')
            ->addIdentifier('email')
            ->add('url')
            ->add('createdAt')
            ->add('token')
            ->add('_action', 'actions', 
                [ 'actions' => 
                    [
                    'activate' => ['template' => 'IRecruitBundle:AffiliateAdmin:list__action_activate.html.twig'],
                    'deactivate' => ['template' => 'IRecruitBundle:AffiliateAdmin:list__action_deactivate.html.twig']
                    ]
                ])
        ;
    }


    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
 
        if($this->hasRoute('edit') && $this->isGranted('EDIT') && $this->hasRoute('delete') && $this->isGranted('DELETE')) {
            $actions['activate'] = [
                'label'            => 'Activate',
                'ask_confirmation' => true
            ];
 
            $actions['deactivate'] = [
                'label'            => 'Deactivate',
                'ask_confirmation' => true
            ];
        }
 
        return $actions;
    }

    protected function configureRoutes(RouteCollection $collection) {
        parent::configureRoutes($collection);
 
        $collection->add('activate',
            $this->getRouterIdParameter().'/activate')
        ;
 
        $collection->add('deactivate',
            $this->getRouterIdParameter().'/deactivate')
        ;
    }
}