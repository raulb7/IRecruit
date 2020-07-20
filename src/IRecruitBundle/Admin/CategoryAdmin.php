<?php 
namespace IRecruitBundle\Admin;
 
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
 
class CategoryAdmin extends Admin
{
    protected $baseRouteName = 'sonata_category';
    protected $baseRoutePattern = 'category';

    // setup the default sort column and order
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'name'
    ];
    // Contextul de creare si editare in care vor fi vizibile campurile
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name')
        ;
    }
    // Contextul de organizare si filtrare a informatiei citite din tabela asociata
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
        ;
    }
    // Contextul de citire in care vor fi afisate la gramada randurile din tabela asociata
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
        ;
    }
}